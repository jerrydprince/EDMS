<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Division;
use App\Models\Unit;
use App\Models\Section;
use App\Models\User;
use App\Models\SystemCapability;
use App\Models\EntityCapability;
use App\Models\Workflow;
use App\Models\WorkflowRule;
use App\Models\DocumentTemplate;
use App\Models\WorkflowWorkingDay;
use App\Models\WorkflowHoliday;
use App\Models\StaffQuery;
use App\Models\Leave;
use App\Models\Task;
use App\Models\File;
use App\Services\TemplateImportService;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DepartmentHubController extends Controller
{
    /**
     * Display the Consolidated Departmental Governance Hub with 4-Tier Interactive Hierarchy
     */
    public function index(Request $request)
    {
        // 1. Dynamic list of all departments (scales automatically as new ones are added)
        $departments = Department::withCount(['user as staff_count', 'division as divisions_count', 'designations as desks_count'])
            ->orderBy('title')
            ->get();

        $selectedDeptId = $request->query('department_id') ? (int) $request->query('department_id') : ($departments->first()?->id);
        $divisionId = $request->query('division_id') ? (int) $request->query('division_id') : null;
        $unitId = $request->query('unit_id') ? (int) $request->query('unit_id') : null;
        $sectionId = $request->query('section_id') ? (int) $request->query('section_id') : null;

        // 2. Determine Active Target Tier & Node
        $activeTier = 'department';
        $targetNode = null;
        $breadcrumbs = [];

        $selectedDepartment = Department::withCount(['user', 'division', 'designations'])->find($selectedDeptId);
        if ($selectedDepartment) {
            $breadcrumbs[] = [
                'tier' => 'department',
                'id' => $selectedDepartment->id,
                'title' => $selectedDepartment->title,
            ];
            $targetNode = [
                'tier' => 'department',
                'id' => $selectedDepartment->id,
                'title' => $selectedDepartment->title,
                'description' => $selectedDepartment->description,
                'head_name' => $selectedDepartment->head ? User::find($selectedDepartment->head)?->only(['id', 'first_name', 'last_name', 'email']) : null,
            ];
        }

        if ($divisionId) {
            $division = Division::withCount(['users', 'unit'])->find($divisionId);
            if ($division && $division->department_id == $selectedDeptId) {
                $activeTier = 'division';
                $breadcrumbs[] = [
                    'tier' => 'division',
                    'id' => $division->id,
                    'title' => $division->title,
                ];
                $targetNode = [
                    'tier' => 'division',
                    'id' => $division->id,
                    'title' => $division->title,
                    'description' => $division->description,
                    'parent_id' => $division->department_id,
                    'parent_title' => $selectedDepartment?->title,
                    'head_name' => $division->head ? User::find($division->head)?->only(['id', 'first_name', 'last_name', 'email']) : null,
                ];
            }
        }

        if ($unitId) {
            $unit = Unit::withCount(['users', 'sections'])->find($unitId);
            if ($unit) {
                $activeTier = 'unit';
                $breadcrumbs[] = [
                    'tier' => 'unit',
                    'id' => $unit->id,
                    'title' => $unit->title,
                ];
                $targetNode = [
                    'tier' => 'unit',
                    'id' => $unit->id,
                    'title' => $unit->title,
                    'description' => $unit->description,
                    'parent_id' => $unit->division_id,
                    'parent_title' => $unit->division?->title,
                    'head_name' => $unit->head ? User::find($unit->head)?->only(['id', 'first_name', 'last_name', 'email']) : null,
                ];
            }
        }

        if ($sectionId) {
            $section = Section::withCount('users')->find($sectionId);
            if ($section) {
                $activeTier = 'section';
                $breadcrumbs[] = [
                    'tier' => 'section',
                    'id' => $section->id,
                    'title' => $section->title,
                ];
                $targetNode = [
                    'tier' => 'section',
                    'id' => $section->id,
                    'title' => $section->title,
                    'description' => $section->description,
                    'parent_id' => $section->unit_id,
                    'parent_title' => $section->unit?->title,
                    'head_name' => $section->head ? User::find($section->head)?->only(['id', 'first_name', 'last_name', 'email']) : null,
                ];
            }
        }

        // 3. Complete 4-Tier Interactive Tree for Selected Department
        $orgTree = [];
        if ($selectedDeptId) {
            $orgTree = Division::where('department_id', $selectedDeptId)
                ->with([
                    'unit' => function ($uq) {
                        $uq->with([
                            'sections' => function ($sq) {
                                $sq->withCount('users');
                            }
                        ])->withCount('users');
                    }
                ])
                ->withCount(['users', 'unit'])
                ->orderBy('title')
                ->get();
        }

        // 4. Capabilities & Bounding Envelope Inheritance
        $systemCapabilities = SystemCapability::orderBy('category')->orderBy('sort_order')->get();

        // Active capabilities on the current target node
        $activeCapabilityCodes = [];
        if ($targetNode) {
            $activeCapabilityCodes = EntityCapability::where('entity_type', $activeTier)
                ->where('entity_id', $targetNode['id'])
                ->where('is_active', true)
                ->pluck('capability_code')
                ->toArray();
        }

        // Parent Allowed Capabilities (The Bounding Envelope)
        $allowedCapabilityCodes = [];
        if ($activeTier === 'department') {
            // Department can activate any capability that allows department tier
            $allowedCapabilityCodes = $systemCapabilities->filter(function ($cap) {
                return in_array('department', $cap->applicable_tiers ?? []);
            })->pluck('code')->toArray();
        } elseif ($activeTier === 'division') {
            // Division can only activate what the Department has active
            $allowedCapabilityCodes = EntityCapability::where('entity_type', 'department')
                ->where('entity_id', $selectedDeptId)
                ->where('is_active', true)
                ->pluck('capability_code')
                ->toArray();
        } elseif ($activeTier === 'unit') {
            // Unit can only activate what the Division has active
            $parentDivId = $targetNode['parent_id'] ?? null;
            $allowedCapabilityCodes = EntityCapability::where('entity_type', 'division')
                ->where('entity_id', $parentDivId)
                ->where('is_active', true)
                ->pluck('capability_code')
                ->toArray();
        } elseif ($activeTier === 'section') {
            // Section can only activate what the Unit has active
            $parentUnitId = $targetNode['parent_id'] ?? null;
            $allowedCapabilityCodes = EntityCapability::where('entity_type', 'unit')
                ->where('entity_id', $parentUnitId)
                ->where('is_active', true)
                ->pluck('capability_code')
                ->toArray();
        }

        // 5. Scoped Document Templates
        $universalTemplates = DocumentTemplate::where('is_universal', true)
            ->orWhereNull('department_id')
            ->with(['documentType.metadata', 'department', 'division', 'unit', 'section'])
            ->latest()
            ->get();

        $scopedTemplatesQuery = DocumentTemplate::with(['documentType.metadata', 'department', 'division', 'unit', 'section']);
        if ($activeTier === 'section') {
            $scopedTemplatesQuery->where('section_id', $targetNode['id']);
        } elseif ($activeTier === 'unit') {
            $scopedTemplatesQuery->where('unit_id', $targetNode['id']);
        } elseif ($activeTier === 'division') {
            $scopedTemplatesQuery->where('division_id', $targetNode['id']);
        } else {
            $scopedTemplatesQuery->where('department_id', $selectedDeptId)->where('is_universal', false);
        }
        $nodeTemplates = $scopedTemplatesQuery->latest()->get();

        // 6. Workflows & Rules
        $workflows = Workflow::with(['versions' => function ($q) {
                $q->orderBy('version_number', 'desc');
            }])
            ->where(function ($q) use ($selectedDeptId) {
                $q->where('department_id', $selectedDeptId)
                  ->orWhereNull('department_id');
            })
            ->get();

        $rules = WorkflowRule::where(function ($q) use ($selectedDeptId) {
                $q->where('department_id', $selectedDeptId)
                  ->orWhereNull('department_id');
            })
            ->latest()
            ->get();

        // 7. Operating Calendar
        $workingDays = WorkflowWorkingDay::orderBy('day_of_week')->get();
        $holidays = WorkflowHoliday::orderBy('holiday_date', 'desc')->take(12)->get();

        // 8. Staff for Selected Target Node
        $staffQuery = User::with('designation:id,title,grade_level')
            ->select('id', 'first_name', 'last_name', 'email', 'phone', 'profile_image', 'designation_id', 'is_department_head', 'grade_level', 'is_approved');

        if ($activeTier === 'section') {
            $staffQuery->where('section_id', $targetNode['id']);
        } elseif ($activeTier === 'unit') {
            $staffQuery->where('unit_id', $targetNode['id']);
        } elseif ($activeTier === 'division') {
            $staffQuery->where('division_id', $targetNode['id']);
        } else {
            $staffQuery->where('department_id', $selectedDeptId);
        }
        $nodeStaff = $staffQuery->get();

        // 9. Queries & Leaves for this node
        $queries = StaffQuery::whereHas('recipient', function ($q) use ($activeTier, $targetNode, $selectedDeptId) {
                if ($activeTier === 'section') $q->where('section_id', $targetNode['id']);
                elseif ($activeTier === 'unit') $q->where('unit_id', $targetNode['id']);
                elseif ($activeTier === 'division') $q->where('division_id', $targetNode['id']);
                else $q->where('department_id', $selectedDeptId);
            })
            ->with(['recipient:id,first_name,last_name,email'])
            ->latest()
            ->take(10)
            ->get();

        $leaves = Leave::whereHas('user', function ($q) use ($activeTier, $targetNode, $selectedDeptId) {
                if ($activeTier === 'section') $q->where('section_id', $targetNode['id']);
                elseif ($activeTier === 'unit') $q->where('unit_id', $targetNode['id']);
                elseif ($activeTier === 'division') $q->where('division_id', $targetNode['id']);
                else $q->where('department_id', $selectedDeptId);
            })
            ->with(['user:id,first_name,last_name,email'])
            ->latest()
            ->take(10)
            ->get();

        $allDepartmentsStructure = Department::with(['division.unit.sections'])->orderBy('title')->get();

        return Inertia::render('Directorates/Index', [
            'departments' => $departments,
            'allDepartmentsStructure' => $allDepartmentsStructure,
            'isSuperAdmin' => (auth()->user()?->hasRole('super admin') || auth()->user()?->hasRole('super user') || auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('director')) ?? false,
            'selectedDepartment' => $selectedDepartment,
            'activeTier' => $activeTier,
            'targetNode' => $targetNode,
            'breadcrumbs' => $breadcrumbs,
            'orgTree' => $orgTree,
            'systemCapabilities' => $systemCapabilities,
            'activeCapabilityCodes' => $activeCapabilityCodes,
            'allowedCapabilityCodes' => $allowedCapabilityCodes,
            'universalTemplates' => $universalTemplates,
            'nodeTemplates' => $nodeTemplates,
            'documentTypes' => \App\Models\DocumentType::with('metadata')->orderBy('name')->get(),
            'workflows' => $workflows,
            'workflowRules' => $rules,
            'workingDays' => $workingDays,
            'holidays' => $holidays,
            'staff' => $nodeStaff,
            'queries' => $queries,
            'leaves' => $leaves,
        ]);
    }

    /**
     * Toggle a function/capability on an organizational node (Enforcing Bounding Envelope & Cascade Deactivation)
     */
    public function toggleCapability(Request $request)
    {
        $validated = $request->validate([
            'entity_type' => 'required|in:department,division,unit,section',
            'entity_id' => 'required|integer',
            'capability_code' => 'required|string|exists:system_capabilities,code',
            'is_active' => 'required|boolean',
        ]);

        $entityType = $validated['entity_type'];
        $entityId = $validated['entity_id'];
        $code = $validated['capability_code'];
        $isActive = (bool) $validated['is_active'];

        // Enforce Bounding Envelope: If activating, verify parent has it active
        if ($isActive && $entityType !== 'department') {
            $parentId = null;
            $parentType = null;

            if ($entityType === 'division') {
                $div = Division::find($entityId);
                $parentId = $div?->department_id;
                $parentType = 'department';
            } elseif ($entityType === 'unit') {
                $unit = Unit::find($entityId);
                $parentId = $unit?->division_id;
                $parentType = 'division';
            } elseif ($entityType === 'section') {
                $sec = Section::find($entityId);
                $parentId = $sec?->unit_id;
                $parentType = 'unit';
            }

            if ($parentId && $parentType) {
                $parentActive = EntityCapability::where('entity_type', $parentType)
                    ->where('entity_id', $parentId)
                    ->where('capability_code', $code)
                    ->where('is_active', true)
                    ->exists();

                if (!$parentActive) {
                    return back()->with('error', "Cannot activate capability: Parent {$parentType} does not have this capability enabled.");
                }
            }
        }

        // Update or create the record
        EntityCapability::updateOrCreate(
            [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'capability_code' => $code,
            ],
            [
                'is_active' => $isActive,
            ]
        );

        // If deactivating, cascade deactivation to children
        if (!$isActive) {
            $this->cascadeDeactivation($entityType, $entityId, $code);
        }

        // Flush cached permissions so active capability changes take immediate effect
        \Illuminate\Support\Facades\Cache::flush();

        return back()->with('success', "Capability '{$code}' successfully updated for {$entityType}.");
    }

    /**
     * Recursively deactivate capabilities on child nodes when parent turns it off
     */
    private function cascadeDeactivation($parentType, $parentId, $capabilityCode)
    {
        if ($parentType === 'department') {
            $divisionIds = Division::where('department_id', $parentId)->pluck('id');
            EntityCapability::where('entity_type', 'division')
                ->whereIn('entity_id', $divisionIds)
                ->where('capability_code', $capabilityCode)
                ->update(['is_active' => false]);

            $unitIds = Unit::whereIn('division_id', $divisionIds)->pluck('id');
            EntityCapability::where('entity_type', 'unit')
                ->whereIn('entity_id', $unitIds)
                ->where('capability_code', $capabilityCode)
                ->update(['is_active' => false]);

            $sectionIds = Section::whereIn('unit_id', $unitIds)->pluck('id');
            EntityCapability::where('entity_type', 'section')
                ->whereIn('entity_id', $sectionIds)
                ->where('capability_code', $capabilityCode)
                ->update(['is_active' => false]);
        } elseif ($parentType === 'division') {
            $unitIds = Unit::where('division_id', $parentId)->pluck('id');
            EntityCapability::where('entity_type', 'unit')
                ->whereIn('entity_id', $unitIds)
                ->where('capability_code', $capabilityCode)
                ->update(['is_active' => false]);

            $sectionIds = Section::whereIn('unit_id', $unitIds)->pluck('id');
            EntityCapability::where('entity_type', 'section')
                ->whereIn('entity_id', $sectionIds)
                ->where('capability_code', $capabilityCode)
                ->update(['is_active' => false]);
        } elseif ($parentType === 'unit') {
            $sectionIds = Section::where('unit_id', $parentId)->pluck('id');
            EntityCapability::where('entity_type', 'section')
                ->whereIn('entity_id', $sectionIds)
                ->where('capability_code', $capabilityCode)
                ->update(['is_active' => false]);
        }
    }

    /**
     * Assign or update a Document Template's organizational scope
     */
    public function assignTemplate(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'is_universal' => 'nullable|boolean',
            'department_id' => 'nullable|integer',
            'division_id' => 'nullable|integer',
            'unit_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
        ]);

        $template = DocumentTemplate::findOrFail($validated['template_id']);
        $template->update([
            'is_universal' => $validated['is_universal'] ?? false,
            'department_id' => $validated['department_id'] ?? null,
            'division_id' => $validated['division_id'] ?? null,
            'unit_id' => $validated['unit_id'] ?? null,
            'section_id' => $validated['section_id'] ?? null,
        ]);

        return back()->with('success', "Document template scope updated successfully.");
    }

    /**
     * Convert an uploaded Word (.docx, .doc) or PDF document into an editable template.
     */
    public function convertTemplateFile(Request $request, TemplateImportService $importService)
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('super admin') && !$user->hasRole('super user') && !$user->hasRole('admin') && !$user->hasRole('director'))) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have administrative permission to import document templates.',
                'message' => 'You do not have administrative permission to import document templates.'
            ], 403);
        }

        $request->validate([
            'file' => 'required|file|max:20480',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['docx', 'doc', 'pdf', 'rtf', 'txt'])) {
            return response()->json([
                'success' => false,
                'error' => "Unsupported file type (.{$extension}). Please upload a Word (.docx, .doc) or PDF (.pdf) file.",
                'message' => "Unsupported file type (.{$extension}). Please upload a Word (.docx, .doc) or PDF (.pdf) file."
            ], 422);
        }

        try {
            $result = $importService->convertFile($file);
            return response()->json([
                'success' => true,
                'title' => $result['title'] ?? 'Document Template',
                'type' => $result['type'] ?? 'memo',
                'content_html' => $result['content_html'] ?? '',
                'body_html' => $result['content_html'] ?? '',
                'placeholders' => $result['placeholders'] ?? [],
                'original_filename' => $file->getClientOriginalName(),
                'file_type' => $file->getClientOriginalExtension(),
                'data' => $result,
                'message' => 'Document successfully parsed and converted into editable template.'
            ]);
        } catch (\Throwable $e) {
            \Log::error("convertTemplateFile error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Create a new document template from Departmental Hub.
     */
    public function createTemplate(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('super admin') && !$user->hasRole('super user') && !$user->hasRole('admin') && !$user->hasRole('director'))) {
            return back()->withErrors(['message' => 'Only Administrators and Directors have permission to create document templates.']);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'document_type_id' => 'nullable|integer|exists:document_types,id',
            'type' => 'nullable|string|max:50',
            'content_html' => 'required|string',
            'is_universal' => 'nullable|boolean',
            'department_id' => 'nullable|integer|exists:departments,id',
            'division_id' => 'nullable|integer|exists:divisions,id',
            'unit_id' => 'nullable|integer|exists:units,id',
            'section_id' => 'nullable|integer|exists:sections,id',
            'reference_prefix' => 'nullable|string|max:100',
        ]);

        $isUniversal = (bool)($validated['is_universal'] ?? false);
        $deptId = $isUniversal ? null : ($validated['department_id'] ?? null);
        $divId = $isUniversal ? null : ($validated['division_id'] ?? null);
        $unitId = $isUniversal ? null : ($validated['unit_id'] ?? null);
        $secId = $isUniversal ? null : ($validated['section_id'] ?? null);

        $typeString = $validated['type'] ?? 'memo';
        if (!empty($validated['document_type_id'])) {
            $dt = \App\Models\DocumentType::find($validated['document_type_id']);
            if ($dt) {
                $typeString = $dt->name;
            }
        }

        $referencePrefix = !empty($validated['reference_prefix'])
            ? $validated['reference_prefix']
            : \App\Services\DocumentReferenceService::buildPrefix($deptId, $divId, $unitId, $secId);

        $template = DocumentTemplate::create([
            'title' => $validated['title'],
            'type' => $typeString,
            'document_type_id' => $validated['document_type_id'] ?? null,
            'content_html' => $validated['content_html'],
            'version' => 1,
            'is_active' => true,
            'is_universal' => $isUniversal,
            'department_id' => $deptId,
            'division_id' => $divId,
            'unit_id' => $unitId,
            'section_id' => $secId,
            'reference_prefix' => $referencePrefix,
            'created_by' => $user->id,
        ]);

        return back()->with('success', "Template '{$template->title}' created with Reference Prefix [{$referencePrefix}].");
    }

    /**
     * Delete a document template.
     */
    public function deleteTemplate($id)
    {
        $user = auth()->user();
        if (!$user->hasRole('super admin') && !$user->hasRole('super user')) {
            return back()->withErrors(['message' => 'Only System Administrator has permission to delete document templates.']);
        }

        $template = DocumentTemplate::findOrFail($id);
        $title = $template->title;
        $template->delete();

        return back()->with('success', "Template '{$title}' removed successfully.");
    }

    /**
     * Dedicated Executive Directorate Oversight Dashboard
     */
    public function oversight(Request $request)
    {
        $user = auth()->user();
        $departments = Department::orderBy('title')->get();
        $selectedDeptId = $request->query('department_id') ?: ($user->department_id ?: $departments->first()?->id);
        $selectedDepartment = Department::with(['headOfficer:id,first_name,last_name,email'])->find($selectedDeptId);

        // 1. Bottleneck & Dwell Time Radar by Division
        $divisions = Division::where('department_id', $selectedDeptId)
            ->withCount(['users', 'unit'])
            ->get();

        $divisionStats = [];
        foreach ($divisions as $div) {
            $staffIds = User::where('division_id', $div->id)->pluck('id');
            $activeTasksCount = Task::whereIn('user_id', $staffIds)->where('status', '!=', 'completed')->count();
            $delayedTasksCount = Task::whereIn('user_id', $staffIds)
                ->where('status', '!=', 'completed')
                ->where('created_at', '<', now()->subDays(3))
                ->count();

            $divisionStats[] = [
                'id' => $div->id,
                'title' => $div->title,
                'staff_count' => $div->users_count,
                'units_count' => $div->unit_count,
                'active_tasks' => $activeTasksCount,
                'delayed_tasks' => $delayedTasksCount,
                'health_status' => $delayedTasksCount > 5 ? 'critical' : ($delayedTasksCount > 2 ? 'warning' : 'healthy'),
            ];
        }

        // 2. High-Level Executive Stream (Files awaiting Director's clearance)
        $executiveStream = Task::where('department_id', $selectedDeptId)
            ->where('status', '!=', 'completed')
            ->latest()
            ->take(15)
            ->get();

        return Inertia::render('Directorates/Oversight', [
            'departments' => $departments,
            'selectedDepartment' => $selectedDepartment,
            'divisionStats' => $divisionStats,
            'executiveStream' => $executiveStream,
        ]);
    }

    /**
     * Director Emergency File Call-Up
     */
    public function callUpFile(Request $request)
    {
        $validated = $request->validate([
            'task_id' => 'required|exists:task,id',
            'reason' => 'required|string|max:500',
        ]);

        $task = Task::findOrFail($validated['task_id']);
        $user = auth()->user();

        // Reassign task directly to the calling Director
        $task->update([
            'user_id' => $user->id,
            'status' => 'in_progress',
        ]);

        return back()->with('success', "File #{$task->id} successfully called up to Directorate Executive Desk.");
    }
}
