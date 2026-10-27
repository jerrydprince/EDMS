<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\WorkflowTransition;
use App\Models\WorkflowVersion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkflowBuilderController extends Controller
{
    /**
     * Display a listing of the workflows.
     */
    public function index()
    {
        $workflows = Workflow::with(['versions' => function ($q) {
            $q->orderBy('version_number', 'desc');
        }, 'department', 'documentTemplate'])->get();

        $departments = Department::orderBy('title')->get();

        return Inertia::render('admin/WorkflowBuilder/Index', [
            'workflows' => $workflows,
            'departments' => $departments
        ]);
    }

    /**
     * Dedicated Full Studio Page for designing workflows
     */
    public function studio(Request $request, $versionId = null)
    {
        $workflow = null;
        $version = null;

        if ($versionId) {
            $version = WorkflowVersion::with([
                'workflow.department',
                'workflow.documentTemplate',
                'steps' => fn($q) => $q->orderBy('id', 'asc')->with('slas'),
                'transitions.fromStep',
                'transitions.toStep'
            ])->findOrFail($versionId);
            $workflow = $version->workflow;
        } elseif ($request->workflow_id) {
            $workflow = Workflow::with([
                'versions' => fn($q) => $q->orderBy('version_number', 'desc')->with([
                    'steps' => fn($sq) => $sq->orderBy('id', 'asc')->with('slas'),
                    'transitions.fromStep',
                    'transitions.toStep'
                ]),
                'department',
                'documentTemplate'
            ])->findOrFail($request->workflow_id);
            $version = $workflow->versions->first();
        }

        $departments = Department::orderBy('title')->get();
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->pluck('name');
        $templates = \App\Models\DocumentTemplate::where('is_active', true)
            ->select('id', 'title', 'type', 'department_id', 'content_html')
            ->orderBy('title')
            ->get();

        return Inertia::render('admin/WorkflowBuilder/Studio', [
            'workflow' => $workflow,
            'version' => $version,
            'departments' => $departments,
            'roles' => $roles,
            'templates' => $templates,
        ]);
    }

    /**
     * Atomically save full workflow blueprint, sequential steps, and generated routing
     */
    public function saveStudioWorkflow(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'emoji' => 'nullable|string|max:10',
            'department_id' => 'nullable|exists:departments,id',
            'document_template_id' => 'nullable|exists:document_templates,id',
            'is_confidential' => 'boolean',
            'publish_now' => 'boolean',
            'steps' => 'required|array|min:1',
            'steps.*.name' => 'required|string|max:255',
            'steps.*.role_required' => 'nullable|string',
            'steps.*.assignee_target' => 'nullable|string',
            'steps.*.step_type' => 'required|string|in:approval,review,input,assign',
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            // 1. Create or Update Workflow
            if ($request->workflow_id) {
                $workflow = Workflow::findOrFail($request->workflow_id);
                $workflow->update([
                    'title' => $request->title,
                    'emoji' => $request->emoji ?: '📄',
                    'department_id' => $request->department_id,
                    'document_template_id' => $request->document_template_id,
                    'is_confidential' => (bool)$request->is_confidential,
                ]);
            } else {
                $workflow = Workflow::create([
                    'title' => $request->title,
                    'emoji' => $request->emoji ?: '📄',
                    'department_id' => $request->department_id,
                    'document_template_id' => $request->document_template_id,
                    'is_confidential' => (bool)$request->is_confidential,
                    'created_by' => auth()->id(),
                    'status' => 'active',
                ]);
            }

            // 2. Find or Create Version
            $version = null;
            if ($request->version_id) {
                $version = WorkflowVersion::where('workflow_id', $workflow->id)->find($request->version_id);
            }
            if (!$version) {
                $maxVer = WorkflowVersion::where('workflow_id', $workflow->id)->max('version_number') ?: 0;
                $version = WorkflowVersion::create([
                    'workflow_id' => $workflow->id,
                    'version_number' => $maxVer + 1,
                    'is_published' => false,
                    'is_approved' => false,
                ]);
            }

            // 3. Clear existing transitions and steps for this version before rewriting
            WorkflowTransition::where('workflow_version_id', $version->id)->delete();
            $stepIds = WorkflowStep::where('workflow_version_id', $version->id)->pluck('id');
            \App\Models\WorkflowSla::whereIn('workflow_step_id', $stepIds)->delete();
            WorkflowStep::where('workflow_version_id', $version->id)->delete();

            // 4. Create Sequential Steps in exact array order
            $stepCount = count($request->steps);
            $createdSteps = [];

            foreach ($request->steps as $idx => $stepData) {
                $isStart = ($idx === 0) || !empty($stepData['is_start_step']);
                $isEnd = ($idx === $stepCount - 1) || !empty($stepData['is_end_step']);

                $step = WorkflowStep::create([
                    'workflow_version_id' => $version->id,
                    'name' => $stepData['name'],
                    'role_required' => $stepData['role_required'] ?? null,
                    'assignee_target' => $stepData['assignee_target'] ?? 'role',
                    'step_type' => $stepData['step_type'] ?? 'review',
                    'department_id' => $workflow->department_id,
                    'is_start_step' => $isStart,
                    'is_end_step' => $isEnd,
                    'auto_return_on_rejection' => (bool)($stepData['auto_return_on_rejection'] ?? true),
                    'requires_pin' => (bool)($stepData['requires_pin'] ?? true),
                ]);

                // Create SLA if provided
                if (!empty($stepData['sla_hours']) && is_numeric($stepData['sla_hours'])) {
                    \App\Models\WorkflowSla::create([
                        'workflow_step_id' => $step->id,
                        'duration_hours' => (float)$stepData['sla_hours'],
                        'is_working_hours_only' => true
                    ]);
                }

                $createdSteps[] = $step;
            }

            // Ensure exactly one start step
            $hasStart = false;
            foreach ($createdSteps as $s) {
                if ($s->is_start_step) {
                    if (!$hasStart) {
                        $hasStart = true;
                    } else {
                        $s->update(['is_start_step' => false]);
                    }
                }
            }
            if (!$hasStart && count($createdSteps) > 0) {
                $createdSteps[0]->update(['is_start_step' => true]);
            }

            // 5. Automatic Transition Generation
            // Forward Transitions: Step[i] -> Step[i+1]
            for ($i = 0; $i < count($createdSteps) - 1; $i++) {
                $from = $createdSteps[$i];
                $to = $createdSteps[$i + 1];

                $forwardAction = "Submit Upward along Clearance Pathway";
                if ($i > 0) {
                    if ($from->step_type === 'approval') {
                        $forwardAction = "Endorse & Forward to Higher Authority";
                    } elseif ($from->step_type === 'review') {
                        $forwardAction = "Recommend & Transmit Upward";
                    } else {
                        $forwardAction = "Complete & Transmit Forward";
                    }
                }

                WorkflowTransition::create([
                    'workflow_version_id' => $version->id,
                    'from_step_id' => $from->id,
                    'to_step_id' => $to->id,
                    'action_name' => $forwardAction,
                ]);
            }

            // Rejection Transitions: Step[i] -> Step[i-1] (Requirement 3: Return to Immediate Previous Officer)
            for ($i = 1; $i < count($createdSteps); $i++) {
                $curr = $createdSteps[$i];
                $prev = $createdSteps[$i - 1];

                if ($curr->auto_return_on_rejection) {
                    $returnAction = "Query / Return to " . ($prev->role_required ?: ($prev->name ?: 'Previous Officer'));
                    WorkflowTransition::create([
                        'workflow_version_id' => $version->id,
                        'from_step_id' => $curr->id,
                        'to_step_id' => $prev->id,
                        'action_name' => $returnAction,
                    ]);
                }
            }

            // 6. Handle Publish Now
            if ($request->boolean('publish_now')) {
                WorkflowVersion::where('workflow_id', $workflow->id)->update(['is_published' => false]);
                $version->update([
                    'is_approved' => true,
                    'is_published' => true,
                    'published_at' => now(),
                    'approved_by_id' => auth()->id(),
                    'approved_at' => now(),
                ]);
                $workflow->update(['status' => 'active']);
            }

            return redirect()->route('admin.workflow-builder.index')
                ->with('success', 'Workflow "' . $workflow->title . '" and clearance pathway saved successfully' . ($request->boolean('publish_now') ? ' and published!' : '.'));
        });
    }

    /**
     * Store a newly created workflow and its initial version.
     */
    public function toggleStatus(Request $request, $id)
    {
        $workflow = \App\Models\Workflow::findOrFail($id);
        $workflow->status = $workflow->status === 'inactive' ? 'active' : 'inactive';
        $workflow->save();
        return redirect()->back()->with('success', 'Workflow status updated successfully.');
    }

    public function destroy($id)
    {
        $workflow = \App\Models\Workflow::findOrFail($id);
        $workflow->delete();
        return redirect()->back()->with('success', 'Workflow deleted successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'emoji' => 'nullable|string|max:10',
            'department_id' => 'nullable|exists:departments,id',
            'is_confidential' => 'nullable|boolean',
        ]);

        $workflow = Workflow::create([
            'title' => $request->title,
            'emoji' => $request->emoji ?? '📄',
            'department_id' => $request->department_id,
            'is_confidential' => $request->is_confidential ?? false,
            'created_by' => auth()->id(),
        ]);

        // Create version 1 automatically
        $workflow->versions()->create([
            'version_number' => 1,
            'is_published' => false
        ]);

        return redirect()->back()->with('success', 'Workflow created successfully.');
    }

    /**
     * Display the specified workflow version editor (redirects to Studio).
     */
    public function show($versionId)
    {
        return redirect()->route('admin.workflow-builder.studio', $versionId);
    }

    /**
     * Store a new step in the workflow version.
     */
    public function storeStep(Request $request, $versionId)
    {
        $version = WorkflowVersion::findOrFail($versionId);
        // abort_if($version->is_published, 403, 'Cannot modify a published version.');

        $request->validate([
            'name' => 'required|string|max:255',
            'step_type' => 'required|string|in:approval,review,input,automated,assign,start,end',
            'role_required' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'is_start_step' => 'boolean',
            'is_end_step' => 'boolean',
            'auto_return_on_rejection' => 'boolean',
        ]);
        
        // Organization Isolation Validation
        if ($request->department_id) {
            $user = auth()->user();
            if (!$user->hasRole('super user') && !$user->hasRole('super admin')) {
                if ($user->department_id != $request->department_id) {
                    abort(403, 'Unauthorized. You can only assign steps to your own department.');
                }
            }
        }

        // If this is start step, ensure no other start step exists for this version
        if ($request->is_start_step) {
            $version->steps()->update(['is_start_step' => false]);
        }

        $version->steps()->create([
            'name' => $request->name,
            'step_type' => $request->step_type,
            'role_required' => $request->role_required,
            'department_id' => $request->department_id,
            'is_start_step' => $request->is_start_step ?? false,
            'is_end_step' => $request->is_end_step ?? false,
            'auto_return_on_rejection' => $request->auto_return_on_rejection ?? false,
            'form_schema' => $request->has('form_schema') ? json_encode($request->form_schema) : null,
            'notification_rules' => $request->has('notification_rules') ? json_encode($request->notification_rules) : null,
        ]);

        return redirect()->back()->with('success', 'Step added successfully.');
    }

    /**
     * Store a new transition between steps.
     */
    public function storeTransition(Request $request, $versionId)
    {
        $version = WorkflowVersion::findOrFail($versionId);

        $request->validate([
            'from_step_id' => 'required|exists:workflow_steps,id',
            'to_step_id' => 'required|exists:workflow_steps,id',
            'action_name' => 'required|string|max:255',
        ]);

        $version->transitions()->create([
            'from_step_id' => $request->from_step_id,
            'to_step_id' => $request->to_step_id,
            'action_name' => $request->action_name,
            'notification_rules' => $request->has('notification_rules') ? json_encode($request->notification_rules) : null,
        ]);

        return redirect()->back()->with('success', 'Transition added successfully.');
    }

    /**
     * Approve the workflow version digitally.
     */
    public function approveVersion(Request $request, $versionId)
    {
        $version = WorkflowVersion::with('workflow')->findOrFail($versionId);
        $user = auth()->user();

        // Organization Isolation Check
        if (!$user->hasRole('super user') && !$user->hasRole('super admin')) {
            abort_if($user->department_id != $version->workflow->department_id, 403, 'Unauthorized. You can only approve workflows within your department.');
        }

        $request->validate([
            'approval_pin' => 'required|string',
        ]);

        if ((string)$user->approval_pin !== (string)$request->approval_pin) {
            abort(403, 'Invalid digital signature PIN.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($version, $user) {
            $version->update([
                'is_approved' => true,
                'approved_by_id' => $user->id,
                'approved_at' => now(),
            ]);

            activity('workflow')
                ->performedOn($version)
                ->causedBy($user)
                ->log('Approved workflow version digitally.');
        });

        return redirect()->back()->with('success', 'Workflow version formally approved.');
    }

    /**
     * Emergency rollback to a previous version.
     */
    public function rollbackVersion(Request $request, $versionId)
    {
        $versionToUnpublish = WorkflowVersion::where('id', $versionId)->where('is_published', true)->firstOrFail();
        
        $request->validate([
            'rollback_to_version_id' => 'required|exists:workflow_versions,id',
        ]);

        $versionToRestore = WorkflowVersion::findOrFail($request->rollback_to_version_id);
        
        abort_if($versionToRestore->workflow_id !== $versionToUnpublish->workflow_id, 400, 'Version mismatch.');
        abort_if(!$versionToRestore->is_approved, 403, 'Cannot rollback to an unapproved version.');

        \Illuminate\Support\Facades\DB::transaction(function () use ($versionToUnpublish, $versionToRestore) {
            $versionToUnpublish->update([
                'is_published' => false,
            ]);

            $versionToRestore->update([
                'is_published' => true,
                'published_at' => now(),
            ]);

            activity('workflow')
                ->performedOn($versionToRestore)
                ->causedBy(auth()->user())
                ->log("Critical: Rolled back workflow from version {$versionToUnpublish->version_number} to {$versionToRestore->version_number}.");
        });

        return redirect()->back()->with('success', 'Workflow version rolled back successfully.');
    }

    /**
     * Publish the workflow version.
     */
    public function publish(Request $request, $versionId)
    {
        $version = WorkflowVersion::findOrFail($versionId);
        
        abort_if(!$version->is_approved, 403, 'Workflow version must be formally approved before it can be published.');

        \Illuminate\Support\Facades\DB::transaction(function () use ($version, $request) {
            // Unpublish other versions of this workflow
            WorkflowVersion::where('workflow_id', $version->workflow_id)->update(['is_published' => false]);

            $version->update([
                'is_published' => true,
                'published_at' => now(),
            ]);

            if ($request->has('mandate_parallel_splits')) {
                $version->update([
                    'mandate_parallel_splits' => filter_var($request->mandate_parallel_splits, FILTER_VALIDATE_BOOLEAN)
                ]);
            }
        });

        return redirect()->back()->with('success', 'Version published successfully.');
    }

    /**
     * Update settings for the version (like mandate_parallel_splits).
     */
    public function updateSettings(Request $request, $versionId)
    {
        $version = WorkflowVersion::findOrFail($versionId);
        
        if ($request->has('mandate_parallel_splits')) {
            $version->update([
                'mandate_parallel_splits' => filter_var($request->mandate_parallel_splits, FILTER_VALIDATE_BOOLEAN)
            ]);
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Store SLA configuration for a step.
     */
    public function storeSlaConfiguration(Request $request, $stepId)
    {
        $step = WorkflowStep::findOrFail($stepId);

        $request->validate([
            'duration_hours' => 'required|numeric|min:1',
            'warning_hours' => 'nullable|numeric|min:1',
            'is_working_hours_only' => 'boolean',
            'escalation_action' => 'nullable|string',
            'escalation_role' => 'nullable|string',
            'remove_original_assignee' => 'boolean',
            'requires_pause_approval' => 'boolean',
        ]);

        \App\Models\WorkflowSla::updateOrCreate(
            ['workflow_step_id' => $step->id],
            [
                'duration_hours' => $request->duration_hours,
                'warning_hours' => $request->warning_hours,
                'is_working_hours_only' => $request->is_working_hours_only ?? false,
                'escalation_action' => $request->escalation_action,
                'escalation_role' => $request->escalation_role,
                'remove_original_assignee' => $request->remove_original_assignee ?? false,
                'requires_pause_approval' => $request->requires_pause_approval ?? false,
            ]
        );

        return redirect()->back()->with('success', 'SLA configuration updated successfully.');
    }

    /**
     * Clone a published version into a new draft.
     */
    public function cloneVersion(Request $request, $versionId)
    {
        $version = WorkflowVersion::with(['steps.slas', 'transitions'])->findOrFail($versionId);
        
        $newVersionNumber = WorkflowVersion::where('workflow_id', $version->workflow_id)->max('version_number') + 1;
        
        $newVersion = $version->replicate();
        $newVersion->version_number = $newVersionNumber;
        $newVersion->is_published = false;
        $newVersion->published_at = null;
        $newVersion->save();
        
        $stepMap = [];
        foreach ($version->steps as $step) {
            $newStep = $step->replicate();
            $newStep->workflow_version_id = $newVersion->id;
            $newStep->save();
            $stepMap[$step->id] = $newStep->id;
            
            if ($step->slas && $step->slas->count() > 0) {
                foreach ($step->slas as $sla) {
                    $newSla = $sla->replicate();
                    $newSla->workflow_step_id = $newStep->id;
                    $newSla->save();
                }
            }

            // Replicate checklists
            $checklists = \App\Models\WorkflowChecklist::where('workflow_step_id', $step->id)->get();
            foreach ($checklists as $checklist) {
                $newChecklist = $checklist->replicate();
                $newChecklist->workflow_step_id = $newStep->id;
                $newChecklist->save();
            }
        }
        
        foreach ($version->transitions as $transition) {
            $newTransition = $transition->replicate();
            $newTransition->workflow_version_id = $newVersion->id;
            $newTransition->from_step_id = $stepMap[$transition->from_step_id];
            $newTransition->to_step_id = $stepMap[$transition->to_step_id];
            $newTransition->save();

            // Replicate conditions
            $conditions = \App\Models\WorkflowCondition::where('workflow_transition_id', $transition->id)->get();
            foreach ($conditions as $condition) {
                $newCondition = $condition->replicate();
                $newCondition->workflow_transition_id = $newTransition->id;
                $newCondition->save();
            }
        }
        
        return redirect()->route('admin.workflow-builder.show', $newVersion->id)->with('success', 'Draft cloned successfully.');
    }

    /**
     * Store Checklist for a step.
     */
    public function storeFormSchema(Request $request, $stepId)
    {
        $step = \App\Models\WorkflowStep::with('version')->findOrFail($stepId);
        abort_if($step->version->is_published, 403, 'Cannot modify a published version.');

        $request->validate([
            'form_schema' => 'nullable|array'
        ]);

        $step->form_schema = $request->form_schema ? json_encode($request->form_schema) : null;
        $step->save();

        return redirect()->back()->with('success', 'Form schema saved successfully.');
    }

    public function storeChecklist(Request $request, $stepId)
    {
        $step = \App\Models\WorkflowStep::with('version')->findOrFail($stepId);
        abort_if($step->version->is_published, 403, 'Cannot modify a published version.');

        $request->validate([
            'item_text' => 'required|string|max:500',
            'is_required' => 'boolean',
        ]);

        \App\Models\WorkflowChecklist::create([
            'workflow_step_id' => $step->id,
            'item_description' => $request->item_text,
            'is_required' => $request->boolean('is_required'),
        ]);

        return redirect()->back()->with('success', 'Checklist item added.');
    }

    /**
     * Store Condition for a transition.
     */
    public function storeCondition(Request $request, $transitionId)
    {
        $transition = WorkflowTransition::findOrFail($transitionId);

        $request->validate([
            'field_name' => 'required|string|max:255',
            'operator' => 'required|string|max:20',
            'value' => 'required|string',
        ]);

        \App\Models\WorkflowCondition::create([
            'workflow_transition_id' => $transition->id,
            'field_name' => $request->field_name,
            'operator' => $request->operator,
            'value' => $request->value,
        ]);

        return redirect()->back()->with('success', 'Condition added.');
    }
}
