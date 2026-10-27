<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskAssignment;
use App\Models\TaskDoc;
use App\Models\TaskDocumentVersion;
use App\Models\TaskAnnotation;
use App\Models\TaskActivity;
use App\Services\ChainResolver;
use App\Services\DocumentSigningService;
use App\Notifications\TaskNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class StreamlinedTaskWorkflowController extends Controller
{
    protected DocumentSigningService $signingService;

    public function __construct(DocumentSigningService $signingService)
    {
        $this->signingService = $signingService;
    }

    /**
     * Get routing chain information for the given task and current user.
     */
    public function getChainInfo(Request $request, Task $task)
    {
        $user = auth()->user();
        $userRank = ChainResolver::getUserRank($user);

        // 1. Resolve next approver in strict upward hierarchy chain (Staff -> AD -> DD -> Director -> AGF)
        $chainApprover = ChainResolver::resolveNextApprover($user, $task);

        // Check if there is an explicit assigner who assigned downward to this user (strictly higher rank)
        $higherAssigner = null;
        $higherAssignment = TaskAssignment::where('task_id', $task->id)
            ->where('assigned_to', $user->id)
            ->where('assigned_by', '!=', $user->id)
            ->latest('id')
            ->get()
            ->first(function ($assign) use ($userRank) {
                $candidate = User::find($assign->assigned_by);
                return $candidate && ChainResolver::getUserRank($candidate) > $userRank && !$candidate->is_on_leave;
            });

        if ($higherAssignment) {
            $higherAssigner = User::find($higherAssignment->assigned_by);
        }

        $nextApprover = $chainApprover ?: $higherAssigner;

        // If this is a generic workflow blueprint task, override nextApprover with the workflow transition's target!
        if ($task->workflow_version_id) {
            $genericService = app(\App\Services\GenericWorkflowService::class);
            $availableActions = $genericService->getAvailableActions($task, $user);
            $forwardAction = $availableActions->first(fn($a) => empty($a->is_auto_return) && ($a->action_type ?? '') !== 'return');
            if ($forwardAction && !empty($forwardAction->target_user)) {
                $targetUser = User::find($forwardAction->target_user['id']);
                if ($targetUser) {
                    $nextApprover = $targetUser;
                }
            }
        }

        // 2. Resolve eligible skip targets (AD -> Director, DD -> AGF)
        $skipTargets = ChainResolver::getEligibleSkipTargets($user, $task);

        // 3. Default return target (strictly downward to the subordinate who submitted to me)
        $currentAssignment = TaskAssignment::where('task_id', $task->id)
            ->where('assigned_to', $user->id)
            ->latest('id')
            ->first();

        $returnTargetUser = null;

        // A. If the current assignment to me was submitted by a subordinate officer
        if ($currentAssignment && $currentAssignment->assigned_by && $currentAssignment->assigned_by !== $user->id) {
            $senderCandidate = User::find($currentAssignment->assigned_by);
            if ($senderCandidate && ChainResolver::getUserRank($senderCandidate) < $userRank) {
                $returnTargetUser = $senderCandidate;
            }
        }

        // B. If I previously assigned to a subordinate officer who is under review / completed
        if (!$returnTargetUser) {
            $subAssignment = TaskAssignment::where('task_id', $task->id)
                ->where('assigned_by', $user->id)
                ->whereIn('status', ['under review', 'review', 'completed'])
                ->latest('id')
                ->first();

            if ($subAssignment) {
                $returnTargetUser = User::find($subAssignment->assigned_to);
            }
        }

        // C. Fallback: previous assignee who has lower rank
        if (!$returnTargetUser) {
            $prevAssign = TaskAssignment::where('task_id', $task->id)
                ->where('assigned_to', '!=', $user->id)
                ->latest('id')
                ->get()
                ->first(function ($assign) use ($userRank) {
                    $u = User::find($assign->assigned_to);
                    return $u && ChainResolver::getUserRank($u) < $userRank;
                });
            if ($prevAssign) {
                $returnTargetUser = User::find($prevAssign->assigned_to);
            }
        }

        if (!$returnTargetUser && $task->created_by && $task->created_by !== $user->id) {
            $creator = User::find($task->created_by);
            if ($creator && ChainResolver::getUserRank($creator) < $userRank) {
                $returnTargetUser = $creator;
            }
        }

        // For generic workflow blueprint tasks, override returnTargetUser with workflow return transition target!
        if ($task->workflow_version_id) {
            $genericService = app(\App\Services\GenericWorkflowService::class);
            $availableActions = $genericService->getAvailableActions($task, $user);
            $returnAction = $availableActions->first(fn($a) => !empty($a->is_auto_return) || ($a->action_type ?? '') === 'return');
            if ($returnAction && !empty($returnAction->target_user)) {
                $targetReturnUser = User::find($returnAction->target_user['id']);
                if ($targetReturnUser) {
                    $returnTargetUser = $targetReturnUser;
                }
            }
        }

        $isDirector = $user->hasRole('director');
        $isAgf = $user->hasRole('super user') || $user->hasRole('super admin') || $user->hasRole('Accountant-General');
        $isCompulsoryFullSig = ($isDirector || $isAgf);

        // 5. Active assignment & Current submission state
        $activeAssignment = TaskAssignment::where('task_id', $task->id)->latest('id')->first();
        $isTaskCurrentlyWithMe = false;
        $isTaskSubmittedByMe = false;
        $isTaskReturned = false;
        $currentHolderUser = null;

        if ($activeAssignment) {
            $currentHolderUser = User::find($activeAssignment->assigned_to);
            if ((int)$activeAssignment->assigned_to === (int)$user->id) {
                if ($activeAssignment->status === 'revision' || $task->status === 'returned') {
                    $isTaskCurrentlyWithMe = true;
                    $isTaskReturned = true;
                } elseif (in_array($activeAssignment->status, ['pending', 'in progress', 'under review', 'review'])) {
                    $isTaskCurrentlyWithMe = true;
                } elseif ($activeAssignment->status === 'completed' && $task->status !== 'completed') {
                    $isTaskCurrentlyWithMe = true;
                }
            } else {
                // Check if user previously held an assignment on this task that is completed or forwarded to current holder
                $myCompletedAssignment = TaskAssignment::where('task_id', $task->id)
                    ->where('assigned_to', $user->id)
                    ->where('status', 'completed')
                    ->latest('id')
                    ->first();

                $iForwardedToCurrent = ((int)$activeAssignment->assigned_by === (int)$user->id);

                if (($myCompletedAssignment || $iForwardedToCurrent) && $task->status !== 'completed') {
                    $isTaskSubmittedByMe = true;
                }
            }
        }

        return response()->json([
            'current_user' => [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'role' => $this->signingService->getUserRoleDisplayName($user),
                'has_signature' => !empty($user->digital_signature_path) && file_exists(public_path($user->digital_signature_path)),
                'is_compulsory_full_sig' => $isCompulsoryFullSig,
                'is_agf' => $isAgf,
                'is_director' => $isDirector,
            ],
            'next_approver' => $nextApprover ? [
                'id' => $nextApprover->id,
                'name' => $nextApprover->first_name . ' ' . $nextApprover->last_name,
                'role' => $this->signingService->getUserRoleDisplayName($nextApprover),
                'department' => $nextApprover->department ? $nextApprover->department->title : 'N/A',
            ] : null,
            'assigner' => $nextApprover ? [
                'id' => $nextApprover->id,
                'name' => $nextApprover->first_name . ' ' . $nextApprover->last_name,
                'role' => $this->signingService->getUserRoleDisplayName($nextApprover),
                'department' => $nextApprover->department ? $nextApprover->department->title : 'N/A',
            ] : null,
            'skip_targets' => $skipTargets,
            'return_target' => $returnTargetUser ? [
                'id' => $returnTargetUser->id,
                'name' => $returnTargetUser->first_name . ' ' . $returnTargetUser->last_name,
                'role' => $this->signingService->getUserRoleDisplayName($returnTargetUser),
                'department' => $returnTargetUser->department ? $returnTargetUser->department->title : 'N/A',
            ] : null,
            'is_task_currently_with_me' => $isTaskCurrentlyWithMe,
            'is_task_submitted_by_me' => $isTaskSubmittedByMe,
            'is_task_returned' => $isTaskReturned,
            'current_holder' => $currentHolderUser ? [
                'id' => $currentHolderUser->id,
                'name' => $currentHolderUser->first_name . ' ' . $currentHolderUser->last_name,
                'role' => $this->signingService->getUserRoleDisplayName($currentHolderUser),
                'department' => $currentHolderUser->department ? $currentHolderUser->department->title : 'N/A',
            ] : null,
        ]);
    }

    /**
     * Sign & Submit to Assigner action: Single PIN signs, stamps, saves new version, and forwards.
     * Supports optional attachments for reviewing officers (Assistant Director, Deputy Director, Director).
     */
    public function signAndForward(Request $request, Task $task)
    {
        $request->validate([
            'pin' => 'required',
            'remark' => 'nullable|string',
            'skip_target_id' => 'nullable|exists:users,id',
            'skip_reason' => 'required_with:skip_target_id|nullable|string',
            'overlays' => 'nullable',
            'attachments.*' => 'nullable|file|max:20480',
        ]);

        $user = auth()->user();

        // 1. PIN verification
        if ((string)$user->approval_pin !== (string)$request->pin) {
            return response()->json(['success' => false, 'message' => 'Invalid digital signature PIN.'], 422);
        }

        // 2. Compulsory signature verification for Director and AGF
        $isDirector = $user->hasRole('director');
        $isAgf = $user->hasRole('super user') || $user->hasRole('super admin') || $user->hasRole('Accountant-General');
        if (($isDirector || $isAgf) && (empty($user->digital_signature_path) || !file_exists(public_path($user->digital_signature_path)))) {
            return response()->json([
                'success' => false,
                'message' => 'Full digital signature image is compulsory for your executive role but is not configured in your profile.'
            ], 422);
        }

        // Rule: Subordinate submission must be approved before supervisor can submit or sign & forward
        $hasUnapprovedSubmission = TaskAssignment::where('task_id', $task->id)
            ->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('assigned_by', $user->id);
            })
            ->whereIn('status', ['under review', 'review'])
            ->exists();

        if ($hasUnapprovedSubmission && !$isAgf) {
            return response()->json([
                'success' => false,
                'message' => 'The task submission must be approved before you can submit or forward it.'
            ], 422);
        }

        return DB::transaction(function () use ($request, $task, $user, $isAgf) {
            // A. Handle optional supporting attachments
            if ($request->hasFile('attachments')) {
                $dir = public_path('documents/tasks');
                if (!file_exists($dir)) {
                    @mkdir($dir, 0777, true);
                }
                foreach ($request->file('attachments') as $file) {
                    $fileSize = $file->getSize() ?: 0;
                    $origName = $file->getClientOriginalName();
                    $ext = strtolower($file->getClientOriginalExtension());
                    $rawBaseName = pathinfo($origName, PATHINFO_FILENAME);
                    $safeSlug = \Illuminate\Support\Str::slug($rawBaseName);
                    if (empty($safeSlug)) {
                        $safeSlug = 'attachment';
                    }
                    $safeSlug = substr($safeSlug, 0, 60);
                    $fileName = time() . '_' . uniqid() . '_' . $safeSlug . '.' . $ext;
                    $file->move($dir, $fileName);
                    $relPath = '/documents/tasks/' . $fileName;

                    TaskDoc::create([
                        'task_id' => $task->id,
                        'file_name' => mb_substr(trim($origName), 0, 250),
                        'path' => $relPath,
                        'size' => $this->signingService->formatFileSize($fileSize),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // B. Determine recipient: skip target vs assigner vs next chain approver
            $nextRecipient = null;
            $skipReason = null;

            if ($request->filled('skip_target_id')) {
                if (!$user->hasRole('assistant director') && !$user->hasRole('deputy director')) {
                    throw new \Exception("Only Assistant Directors and Deputy Directors can skip approval levels.");
                }
                $nextRecipient = User::findOrFail($request->skip_target_id);
                $skipReason = $request->skip_reason;
            } else {
                $userRank = ChainResolver::getUserRank($user);
                $chainApprover = ChainResolver::resolveNextApprover($user, $task);

                $higherAssignment = TaskAssignment::where('task_id', $task->id)
                    ->where('assigned_to', $user->id)
                    ->where('assigned_by', '!=', $user->id)
                    ->latest('id')
                    ->get()
                    ->first(function ($assign) use ($userRank) {
                        $candidate = User::find($assign->assigned_by);
                        return $candidate && ChainResolver::getUserRank($candidate) > $userRank && !$candidate->is_on_leave;
                    });

                $higherAssigner = $higherAssignment ? User::find($higherAssignment->assigned_by) : null;
                $nextRecipient = $chainApprover ?: $higherAssigner;
            }

            $remark = $request->remark ?: ($isAgf ? 'Executive approval granted.' : 'Reviewed, endorsed and submitted to assigner.');

            // C. Sign document, stamp margin initials, full signature if Director/AGF, and append minute row
            $overlays = is_array($request->overlays) ? $request->overlays : json_decode($request->overlays ?: '[]', true);
            $newVersion = $this->signingService->processSigning(
                $task,
                $user,
                $remark,
                $skipReason,
                $overlays
            );

            // D. Complete current assignments
            TaskAssignment::where('task_id', $task->id)
                ->where('assigned_to', $user->id)
                ->whereNotIn('status', ['completed', 'returned'])
                ->update([
                    'status' => 'completed',
                    'updated_at' => now(),
                ]);

            // E. Forward or Final Approval
            if ($nextRecipient && !$isAgf) {
                // Forward to assigner / next approver
                TaskAssignment::create([
                    'task_id' => $task->id,
                    'assigned_to' => $nextRecipient->id,
                    'assigned_by' => $user->id,
                    'status' => 'under review',
                    'due_at' => now()->addHours(24),
                    'description' => $remark . ($skipReason ? " (Note: Skipped directly with reason: {$skipReason})" : ""),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $task->update([
                    'status' => 'under review',
                    'updated_at' => now(),
                ]);

                // Notify next recipient
                try {
                    $nextRecipient->notify(new TaskNotification($task, $nextRecipient));
                } catch (\Exception $e) {
                    Log::warning("Could not dispatch notification: " . $e->getMessage());
                }

                $message = "Document signed and submitted successfully to {$nextRecipient->first_name} {$nextRecipient->last_name}.";
            } else {
                // Final executive approval by AGF
                // 1. Auto-generate Official AGF Executive Approval & Mandate Instrument PDF
                $approvalInstrumentAttached = false;
                try {
                    $approvalTmpl = \App\Models\DocumentTemplate::where('type', 'approval_mandate')
                        ->orWhere('title', 'like', '%AGF Executive Approval%')
                        ->first();

                    if ($approvalTmpl) {
                        $hydrationService = app(\App\Services\TemplateHydrationService::class);
                        $hydratedHtml = $hydrationService->hydrate($approvalTmpl->content_html, $task, $user, [
                            'agf_directive' => $remark,
                            'approval_remark' => $remark,
                            'approval_date' => now()->format('d M Y, H:i'),
                            'approving_officer' => trim($user->first_name . ' ' . $user->last_name) . ' (AGF)',
                        ]);

                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($hydratedHtml);
                        $pdf->setPaper('a4', 'portrait');

                        $dir = public_path('documents/tasks');
                        if (!file_exists($dir)) {
                            @mkdir($dir, 0777, true);
                        }
                        $fileName = 'AGF_Approval_Instrument_' . $task->id . '_' . time() . '.pdf';
                        $fullPath = $dir . DIRECTORY_SEPARATOR . $fileName;
                        $pdf->save($fullPath);

                        TaskDoc::create([
                            'task_id' => $task->id,
                            'file_name' => 'Official AGF Executive Approval Instrument.pdf',
                            'path' => '/documents/tasks/' . $fileName,
                            'size' => $this->signingService->formatFileSize(file_exists($fullPath) ? filesize($fullPath) : 0),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $approvalInstrumentAttached = true;
                    }
                } catch (\Throwable $docErr) {
                    Log::warning("Could not auto-generate AGF Approval Instrument: " . $docErr->getMessage());
                }

                // 2. Advance Generic Workflow to Step 6 (Central Registry Dispatch & Closure)
                $hasTransitionedToDispatch = false;
                if ($task->workflow_version_id) {
                    $activeStepRecord = DB::table('task_active_steps')
                        ->where('task_id', $task->id)
                        ->where('status', 'in_progress')
                        ->first();

                    if ($activeStepRecord) {
                        $dispatchTransition = \App\Models\WorkflowTransition::where('workflow_version_id', $task->workflow_version_id)
                            ->where('from_step_id', $activeStepRecord->step_id)
                            ->first();

                        if ($dispatchTransition) {
                            $taskDept = $task->department_id ?? ($task->creator?->department_id);
                            $taskDiv = $task->division_id ?? ($task->creator?->division_id);
                            $taskUnit = $task->unit_id ?? ($task->creator?->unit_id);

                            $registryRoleUsers = \App\Models\User::registryInHierarchy($taskDept, $taskDiv, $taskUnit)->pluck('id')->toArray();
                            if (empty($registryRoleUsers)) {
                                $registryRoleUsers = \App\Models\User::role('AGF Registry')->pluck('id')->toArray();
                            }
                            if (empty($registryRoleUsers)) {
                                $registryRoleUsers = \App\Models\User::role('record manager')->pluck('id')->toArray();
                            }
                            if (empty($registryRoleUsers) && $task->created_by) {
                                $registryRoleUsers = [$task->created_by];
                            }

                            try {
                                $wfService = app(\App\Services\GenericWorkflowService::class);
                                $wfService->executeAction($task, $user, $dispatchTransition, $registryRoleUsers, $remark);
                                $hasTransitionedToDispatch = true;
                            } catch (\Throwable $wfEx) {
                                Log::warning("Workflow transition to Step 6 Dispatch error: " . $wfEx->getMessage());
                            }
                        }
                    }
                }

                if ($hasTransitionedToDispatch) {
                    $task->update([
                        'status' => 'in progress',
                        'is_signed' => 1,
                        'updated_at' => now(),
                    ]);
                } else {
                    $task->update([
                        'status' => 'completed',
                        'is_signed' => 1,
                        'updated_at' => now(),
                    ]);
                }

                // Automatically provision Department Folder & Archive all task documents
                $archivedFolder = null;
                try {
                    $archivingService = app(\App\Services\TaskArchivingService::class);
                    $archivedFolder = $archivingService->archiveApprovedTask($task, $user);
                } catch (\Throwable $th) {
                    Log::error("Archiving error on final AGF approval: " . $th->getMessage());
                }

                if ($task->created_by && $task->created_by !== $user->id) {
                    $creator = User::find($task->created_by);
                    if ($creator) {
                        try {
                            $creator->notify(new TaskNotification($task, $creator));
                        } catch (\Exception $e) {}
                    }
                }

                $folderNotice = $archivedFolder ? " Automatically archived to Department Folder '{$archivedFolder->name}' (No: {$archivedFolder->number})." : "";
                $docNotice = $approvalInstrumentAttached ? " Official Approval Instrument generated and attached." : "";
                $dispatchNotice = $hasTransitionedToDispatch ? " Dossier routed to Central Registry for final dispatch." : "";
                $message = "Task granted final executive approval and signed off successfully by the AGF." . $docNotice . $dispatchNotice . $folderNotice;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'version' => $newVersion,
            ]);
        });
    }

    /**
     * Approve Subordinate Submission (without submitting upward):
     * Marks the subordinate's assignment as completed, keeping the task with the current reviewer.
     * This enables the officer to reassign the task to another user or review further.
     */
    public function approveSubmission(Request $request, Task $task)
    {
        $request->validate([
            'remark' => 'nullable|string',
            'pin' => 'nullable|string',
        ]);

        $user = auth()->user();

        if ($request->filled('pin') && (string)$user->approval_pin !== (string)$request->pin) {
            return response()->json(['success' => false, 'message' => 'Invalid digital signature PIN.'], 422);
        }

        return DB::transaction(function () use ($request, $task, $user) {
            $isAgf = $user->hasRole('super user') || $user->hasRole('super admin') || $user->hasRole('Accountant-General');

            $subAssignmentQuery = TaskAssignment::where('task_id', $task->id)
                ->whereIn('status', ['under review', 'review', 'in progress', 'pending']);

            if (!$isAgf) {
                $subAssignmentQuery->where(function ($q) use ($user) {
                    $q->where('assigned_by', $user->id)
                      ->orWhere('assigned_to', $user->id);
                });
            }

            $subAssignment = $subAssignmentQuery->latest('id')->first();

            if ($subAssignment) {
                $subAssignment->update([
                    'status' => 'completed',
                    'updated_at' => now(),
                ]);
            }

            // Keep task with the reviewer so they can reassign or hold
            $task->update([
                'status' => 'in progress',
                'updated_at' => now(),
            ]);

            $remark = $request->remark ?: 'Subordinate submission approved and verified.';

            $minuteService = app(\App\Services\MinuteSheetService::class);
            $minuteService->appendMinute($task->id, $user->id, 'Approved Subordinate Submission', $remark, (bool)$request->filled('pin'));

            $ta = new TaskActivity();
            $ta->task_id = $task->id;
            $ta->description = "{$user->first_name} {$user->last_name} approved subordinate submission. Task is now available for reassignment or submission to assigner.";
            $ta->created_by = $user->id;
            $ta->save();

            return response()->json([
                'success' => true,
                'message' => 'Submission approved successfully. You can now reassign this task to another user or submit to your assigner.',
            ]);
        });
    }

    /**
     * Return Task: Annotates the document, routes back with revision notes.
     */
    public function returnTask(Request $request, Task $task)
    {
        $request->validate([
            'reason' => 'required|string',
            'target_user_id' => 'nullable|exists:users,id',
            'annotations' => 'nullable',
        ]);

        $user = auth()->user();

        // Guard: Task cannot be returned once approved
        $latestAssignment = TaskAssignment::where('task_id', $task->id)->latest('id')->first();
        if ($latestAssignment && $latestAssignment->status === 'completed' && $task->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'This task submission has already been approved and cannot be returned with notes.'
            ], 422);
        }

        return DB::transaction(function () use ($request, $task, $user) {
            // 1. Resolve return target
            $targetUser = null;
            if ($request->filled('target_user_id')) {
                $targetUser = User::findOrFail($request->target_user_id);
            } else {
                // Find previous assignment
                $currentAssign = TaskAssignment::where('task_id', $task->id)
                    ->where('assigned_to', $user->id)
                    ->latest('id')
                    ->first();

                if ($currentAssign && $currentAssign->assigned_by && $currentAssign->assigned_by !== $user->id) {
                    $targetUser = User::find($currentAssign->assigned_by);
                }

                if (!$targetUser && $task->created_by && $task->created_by !== $user->id) {
                    $targetUser = User::find($task->created_by);
                }
            }

            if (!$targetUser) {
                return response()->json(['success' => false, 'message' => 'No valid return recipient found.'], 422);
            }

            // 2. Process return version and annotations
            $annotations = is_array($request->annotations) ? $request->annotations : json_decode($request->annotations ?: '[]', true);
            $newVersion = $this->signingService->processReturn(
                $task,
                $user,
                $request->reason,
                $annotations ?: []
            );

            // 3. Mark current assignment as returned
            $currentAssignment = TaskAssignment::where('task_id', $task->id)
                ->where('assigned_to', $user->id)
                ->whereIn('status', ['pending', 'under review', 'in progress'])
                ->latest('id')
                ->first();

            if ($currentAssignment) {
                $currentAssignment->update([
                    'status' => 'returned',
                    'updated_at' => now(),
                ]);
            }

            // 4. Create new revision assignment for target user
            TaskAssignment::create([
                'task_id' => $task->id,
                'assigned_to' => $targetUser->id,
                'assigned_by' => $user->id,
                'status' => 'revision',
                'due_at' => now()->addHours(24),
                'description' => "Return for Revision: " . $request->reason,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $task->update([
                'status' => 'returned',
                'updated_at' => now(),
            ]);

            // 5. Notify target user
            try {
                $targetUser->notify(new TaskNotification($task, $targetUser));
            } catch (\Exception $e) {
                Log::warning("Could not dispatch notification: " . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => "Task returned with review notes to {$targetUser->first_name} {$targetUser->last_name}.",
                'version' => $newVersion,
            ]);
        });
    }

    /**
     * Submit for Review: Replaces MarkComplete for Staff.
     * Document already loaded, quick-pick remark, optional attachments, PIN verified.
     */
    public function submitForReview(Request $request, Task $task)
    {
        $request->validate([
            'pin' => 'required',
            'remark' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:20480',
        ]);

        $user = auth()->user();

        // 1. PIN verification
        if ((string)$user->approval_pin !== (string)$request->pin) {
            return response()->json(['success' => false, 'message' => 'Invalid digital signature PIN.'], 422);
        }

        return DB::transaction(function () use ($request, $task, $user) {
            // A. Handle optional attachments
            if ($request->hasFile('attachments')) {
                $dir = public_path('documents/tasks');
                if (!file_exists($dir)) {
                    @mkdir($dir, 0777, true);
                }
                foreach ($request->file('attachments') as $file) {
                    $fileSize = $file->getSize() ?: 0;
                    $origName = $file->getClientOriginalName();
                    $ext = strtolower($file->getClientOriginalExtension());
                    $rawBaseName = pathinfo($origName, PATHINFO_FILENAME);
                    $safeSlug = \Illuminate\Support\Str::slug($rawBaseName);
                    if (empty($safeSlug)) {
                        $safeSlug = 'attachment';
                    }
                    $safeSlug = substr($safeSlug, 0, 60);
                    $fileName = time() . '_' . uniqid() . '_' . $safeSlug . '.' . $ext;
                    $file->move($dir, $fileName);
                    $relPath = '/documents/tasks/' . $fileName;

                    TaskDoc::create([
                        'task_id' => $task->id,
                        'file_name' => mb_substr(trim($origName), 0, 250),
                        'path' => $relPath,
                        'size' => $this->signingService->formatFileSize($fileSize),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // B. Resolve next approver (AD)
            $nextRecipient = ChainResolver::resolveNextApprover($user, $task);
            if (!$nextRecipient) {
                throw new \Exception("Could not determine next upward supervisor in chain.");
            }

            $remark = $request->remark ?: 'Task completed and submitted for review.';

            // C. Sign & create version (v1 or next)
            $newVersion = $this->signingService->processSigning(
                $task,
                $user,
                $remark,
                null,
                null,
                'submit'
            );

            // D. Complete current assignment
            $currentAssignment = TaskAssignment::where('task_id', $task->id)
                ->where('assigned_to', $user->id)
                ->latest('id')
                ->first();

            if ($currentAssignment) {
                $currentAssignment->update([
                    'status' => 'completed',
                    'updated_at' => now(),
                ]);
            }

            // E. Create assignment for AD
            TaskAssignment::create([
                'task_id' => $task->id,
                'assigned_to' => $nextRecipient->id,
                'assigned_by' => $user->id,
                'status' => 'under review',
                'due_at' => now()->addHours(24),
                'description' => $remark,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $task->update([
                'status' => 'under review',
                'updated_at' => now(),
            ]);

            // F. Notify AD
            try {
                $nextRecipient->notify(new TaskNotification($task, $nextRecipient));
            } catch (\Exception $e) {
                Log::warning("Could not dispatch notification: " . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => "Task submitted for review to {$nextRecipient->first_name} {$nextRecipient->last_name}.",
                'version' => $newVersion,
            ]);
        });
    }

    /**
     * Get Version History with annotations and differences.
     */
    public function getVersionHistory(Request $request, Task $task)
    {
        $versions = TaskDocumentVersion::with(['createdBy.department', 'annotations.createdBy'])
            ->where('task_id', $task->id)
            ->orderBy('version', 'desc')
            ->get();

        // Also check legacy submissions so old tasks stay visible during transition
        $legacySubmissions = DB::table('task_submissions')
            ->where('task_id', $task->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'versions' => $versions,
            'legacy_submissions_count' => $legacySubmissions->count(),
        ]);
    }

    /**
     * Resolve / dismiss an annotation note.
     */
    public function resolveAnnotation(Request $request, TaskAnnotation $annotation)
    {
        $annotation->update([
            'resolved_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Annotation resolved.']);
    }

    /**
     * Awaiting My Action tasks queue.
     */
    public function awaitingMyActionQueue(Request $request)
    {
        $userId = auth()->id();

        $assignments = TaskAssignment::with([
            'task.latestDocumentVersion',
            'task.file',
            'assignedBy.department',
            'assignedBy.roles'
        ])
        ->where('assigned_to', $userId)
        ->whereIn('status', ['pending', 'under review', 'in progress', 'revision'])
        ->orderBy('due_at', 'asc')
        ->paginate(15);

        return response()->json($assignments);
    }

    /**
     * Upload supporting attachment for Assistant Director, Deputy Director, Director, and AGF.
     */
    public function uploadAttachment(Request $request, Task $task)
    {
        $request->validate([
            'files' => 'required',
            'files.*' => 'file|max:20480', // 20MB
        ]);

        try {
            $user = auth()->user();
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
            $uploadedDocs = [];

            $files = $request->file('files');
            if (!is_array($files)) {
                $files = [$files];
            }

            $dir = public_path('documents/tasks');
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            foreach ($files as $file) {
                $ext = strtolower($file->getClientOriginalExtension());
                if (!in_array($ext, $allowedExtensions)) continue;

                $origName = $file->getClientOriginalName();
                $rawBaseName = pathinfo($origName, PATHINFO_FILENAME);
                $safeSlug = \Illuminate\Support\Str::slug($rawBaseName);
                if (empty($safeSlug)) {
                    $safeSlug = 'attachment';
                }
                $safeSlug = substr($safeSlug, 0, 60);
                $fileName = time() . '_' . uniqid() . '_' . $safeSlug . '.' . $ext;
                $size = $this->signingService->formatFileSize($file->getSize());

                $file->move($dir, $fileName);
                $path = '/documents/tasks/' . $fileName;

                $doc = TaskDoc::create([
                    'task_id' => $task->id,
                    'path' => $path,
                    'file_name' => mb_substr(trim($origName), 0, 250),
                    'size' => $size,
                ]);

                $uploadedDocs[] = $doc;
            }

            if (count($uploadedDocs) > 0) {
                $count = count($uploadedDocs);
                try {
                    $minuteService = app(\App\Services\MinuteSheetService::class);
                    $minuteService->appendMinute(
                        $task->id,
                        $user->id,
                        "Attached {$count} supporting document(s)",
                        "Supporting document(s) uploaded during review stage.",
                        true
                    );
                } catch (\Throwable $me) {
                    \Illuminate\Support\Facades\Log::warning("Minute sheet append failed during attachment upload: " . $me->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($uploadedDocs) . ' document(s) attached successfully.',
                'docs' => $uploadedDocs,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("uploadAttachment failed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to attach documents: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete supporting attachment from task.
     */
    public function deleteAttachment(Request $request, Task $task, TaskDoc $attachment)
    {
        if ((int)$attachment->task_id !== (int)$task->id) {
            return response()->json(['success' => false, 'message' => 'Document does not belong to this task.'], 404);
        }

        if (str_starts_with($attachment->file_name, 'Main Document (v') || 
            str_contains($attachment->path, '/documents/minutes/') || 
            str_contains($attachment->path, '/task_docs/versions/')) {
            return response()->json(['success' => false, 'message' => 'Cannot delete main task document or minute sheet from supporting documents.'], 403);
        }

        $filePath = public_path(ltrim($attachment->path, '/\\'));
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $attachmentName = $attachment->file_name;
        $attachment->delete();

        $minuteService = app(\App\Services\MinuteSheetService::class);
        $minuteService->appendMinute(
            $task->id,
            auth()->id(),
            "Removed supporting document: {$attachmentName}",
            "Attachment deleted by reviewer.",
            false
        );

        return response()->json([
            'success' => true,
            'message' => 'Supporting document removed successfully.',
        ]);
    }
}
