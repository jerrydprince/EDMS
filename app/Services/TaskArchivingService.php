<?php

namespace App\Services;

use App\Models\Archive;
use App\Models\ArchiveDocs;
use App\Models\FilePath;
use App\Models\Folder;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskDoc;
use App\Models\TaskDocumentVersion;
use App\Models\TaskSubmissionFiles;
use App\Models\User;
use App\Models\WorkflowLog;
use App\Notifications\TaskChangedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class TaskArchivingService
{
    /**
     * Automatically provision a folder in the Department Document Manager
     * and archive all task documents upon final approval by the AGF.
     */
    public function archiveApprovedTask(Task $task, User $agfUser): ?Folder
    {
        // 1. Idempotency Check: if task already has an archived folder, return it
        if ($task->folder_id) {
            $existingFolder = Folder::find($task->folder_id);
            if ($existingFolder) {
                return $existingFolder;
            }
        }

        try {
            // 2. Resolve Handling Department, Division, and Unit placement from task history
            $hierarchy = $this->resolveHandlingHierarchy($task, $agfUser);
            $departmentId = $hierarchy['department_id'];
            $divisionId = $hierarchy['division_id'];
            $unitId = $hierarchy['unit_id'];
            $handlingUser = $hierarchy['handling_user'];

            // 3. Resolve Document Number (folder number)
            $docNumber = $this->resolveDocumentNumber($task);

            // 4. Resolve Folder Trace ID
            $traceId = !empty($task->tracking_id) ? $task->tracking_id : ('TSK-' . $task->id);

            // 5. Resolve Folder Title & Disk Directory Name
            $rawTitle = trim($task->title ?: 'Task ' . $task->id);
            $folderName = $rawTitle;
            $safeDirName = preg_replace('/[\\\\\/:*?"<>|]/', '_', $rawTitle);
            $safeDirName = trim(substr($safeDirName, 0, 80));
            if (empty($safeDirName)) {
                $safeDirName = 'Task_' . $task->id;
            }

            // Check if folder with exact name already exists in this department
            $nameCollision = Folder::where('department_id', $departmentId)
                ->where('name', $folderName)
                ->exists();
            if ($nameCollision) {
                $folderName .= ' (' . $traceId . ')';
                $safeDirName .= '_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $traceId);
            }

            // 6. Provision Storage Directories on Disk
            $relPath = "storage/Archives/{$departmentId}/{$divisionId}/{$unitId}/{$safeDirName}";
            $diskPath = public_path($relPath);
            if (!file_exists($diskPath)) {
                @mkdir($diskPath, 0777, true);
            }
            $thumbnailsDiskPath = $diskPath . '/thumbnails';
            if (!file_exists($thumbnailsDiskPath)) {
                @mkdir($thumbnailsDiskPath, 0777, true);
            }

            // 7. Create Folder in Database with 3-tier alignment (Department, Division, Unit)
            $handlerName = $handlingUser ? " Handled by {$handlingUser->first_name} {$handlingUser->last_name}." : "";
            $folder = new Folder();
            $folder->name = $folderName;
            $folder->number = $docNumber;
            $folder->trace_id = $traceId;
            $folder->department_id = $departmentId;
            $folder->division_id = $divisionId;
            $folder->unit_id = $unitId;
            $folder->path = $relPath;
            $folder->year = date('Y-m-d');
            $folder->description = "Archived automatically upon final approval by AGF ({$agfUser->first_name} {$agfUser->last_name}).{$handlerName} Task ID: {$task->id}, Trace ID: {$traceId}.";
            $folder->is_sub_folder = 0;
            $folder->is_open = 1;
            $folder->file_count = 0;
            $folder->save();

            // 8. Harvest Documents from Task and Create an Archive per document
            $documents = $this->harvestTaskDocuments($task);
            $copiedCount = 0;
            $totalBytes = 0;
            $allowedImgExt = ['jpg', 'jpeg', 'png', 'gif'];

            foreach ($documents as $doc) {
                $srcAbsPath = $doc['source_path'];
                if (!file_exists($srcAbsPath) || is_dir($srcAbsPath)) {
                    continue;
                }

                $fileSize = filesize($srcAbsPath) ?: 0;
                $ext = strtolower(pathinfo($srcAbsPath, PATHINFO_EXTENSION));
                $originalName = $doc['original_name'];
                $safeBaseName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                $destFileName = time() . '_' . uniqid() . '_' . $safeBaseName . '.' . $ext;
                $destAbsPath = $diskPath . '/' . $destFileName;

                // Physical Copy
                if (@copy($srcAbsPath, $destAbsPath)) {
                    $copiedCount++;
                    $totalBytes += $fileSize;

                    // Generate Thumbnail if image
                    $thumbRelPath = '';
                    if (in_array($ext, $allowedImgExt)) {
                        try {
                            $manager = new ImageManager(Driver::class);
                            $img = $manager->read($destAbsPath);
                            $img->scale(width: 480);
                            $thumbDest = $thumbnailsDiskPath . '/' . $destFileName;
                            $img->save($thumbDest);
                            $thumbRelPath = '/' . str_replace('storage/', '', $relPath) . '/thumbnails/' . $destFileName;
                        } catch (\Throwable $th) {
                            Log::warning("Thumbnail generation failed for {$destFileName}: " . $th->getMessage());
                        }
                    }

                    // Create separate Archive Record with identical 3-tier placement
                    $archive = new Archive();
                    $archive->folder_id = $folder->id;
                    $archive->department_id = $departmentId;
                    $archive->division_id = $divisionId;
                    $archive->unit_id = $unitId;
                    $archive->is_locked = false;
                    $archive->updated_by = $agfUser->id;
                    $archive->save();

                    // Create FilePath record
                    $filePathRecord = FilePath::create([
                        'path' => '/' . str_replace('storage/', '', $relPath) . '/' . $destFileName,
                        'file_name' => $originalName,
                        'file_size' => $this->formatSize($fileSize),
                        'thumbnails' => $thumbRelPath,
                        'updated_by' => $agfUser->id,
                        'updated_by_name' => $agfUser->first_name . ' ' . $agfUser->last_name,
                    ]);

                    $archive->filePath()->attach($filePathRecord->id);

                    // Add metadata docs
                    try {
                        ArchiveDocs::create([
                            'archive_id' => $archive->id,
                            'archive_keys' => 'title',
                            'archive_values' => $originalName,
                        ]);
                        ArchiveDocs::create([
                            'archive_id' => $archive->id,
                            'archive_keys' => 'description',
                            'archive_values' => "Archived document from Task #{$task->id}: {$originalName}",
                        ]);
                    } catch (\Throwable $th) {
                        Log::warning("Failed to create ArchiveDocs for {$originalName}: " . $th->getMessage());
                    }
                }
            }

            // 10. Update Folder Metadata
            $folder->file_count = $copiedCount;
            $folder->folder_size = $this->formatSize($totalBytes);
            $folder->save();

            // 11. Link Task to Archived Folder
            $task->update([
                'folder_id' => $folder->id,
            ]);

            // 12. Record Activity & Audit Logs
            TaskActivity::create([
                'task_id' => $task->id,
                'activity' => "Folder '{$folder->name}' (Folder No: {$folder->number}, Trace ID: {$traceId}) automatically created in Department Document Manager. {$copiedCount} document(s) archived.",
                'user_id' => $agfUser->id,
            ]);

            try {
                WorkflowLog::create([
                    'task_id' => $task->id,
                    'user_id' => $agfUser->id,
                    'action' => 'Archived to Department Document Manager',
                    'comments' => "Folder: {$folder->name} | Folder No: {$folder->number} | Trace ID: {$traceId} | {$copiedCount} file(s) ingested.",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {}

            // 13. Notify Originating Department Director / Record Manager
            $this->notifyDepartmentStakeholders($task, $folder, $agfUser);

            return $folder;
        } catch (\Throwable $e) {
            Log::error("TaskArchivingService failed for Task #{$task->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Resolve the handling Department, Division, and Unit.
     * Identifies the primary subordinate desk officer who executed the work.
     * If top-down task from executive, finds the lowest-level assignee in assignments.
     * Falls back to creator's hierarchy if no assignments exist.
     */
    protected function resolveHandlingHierarchy(Task $task, User $agfUser): array
    {
        $handlingUser = null;

        // 1. Inspect task assignments chronologically to find desk officers who handled work
        $assignments = \App\Models\TaskAssignment::where('task_id', $task->id)
            ->oldest('id')
            ->get();

        foreach ($assignments as $assignment) {
            $candidate = User::find($assignment->assigned_to);
            if ($candidate && !$this->isExecutiveUser($candidate)) {
                $handlingUser = $candidate;
                // If they belong to a unit, this is the primary handling desk officer
                if (!empty($candidate->unit_id)) {
                    break;
                }
            }
        }

        // 2. If no subordinate assignee found, check task creator
        if (!$handlingUser && $task->created_by) {
            $creator = User::find($task->created_by);
            if ($creator && !$this->isExecutiveUser($creator)) {
                $handlingUser = $creator;
            }
        }

        // 3. Fallback to creator or AGF
        if (!$handlingUser) {
            $handlingUser = $task->created_by ? User::find($task->created_by) : $agfUser;
        }

        $departmentId = $handlingUser && !empty($handlingUser->department_id) 
            ? (int)$handlingUser->department_id 
            : ((int)($task->department_id ?: $agfUser->department_id ?: 1));

        $divisionId = $handlingUser && !empty($handlingUser->division_id) 
            ? (int)$handlingUser->division_id 
            : ((int)($task->division_id ?: 0));

        $unitId = $handlingUser && !empty($handlingUser->unit_id) 
            ? (int)$handlingUser->unit_id 
            : ((int)($task->unit_id ?: 0));

        return [
            'department_id' => $departmentId,
            'division_id' => $divisionId,
            'unit_id' => $unitId,
            'handling_user' => $handlingUser,
        ];
    }

    /**
     * Check if a user has executive / AGF privileges safely.
     */
    protected function isExecutiveUser(?User $user): bool
    {
        if (!$user) return false;
        try {
            if ($user->hasRole('super user') || $user->hasRole('super admin')) {
                return true;
            }
        } catch (\Throwable $e) {}
        try {
            if ($user->hasRole('Accountant-General')) {
                return true;
            }
        } catch (\Throwable $e) {}
        return false;
    }

    /**
     * Resolve Document Number (folder number).
     */
    protected function resolveDocumentNumber(Task $task): string
    {
        if ($task->file_id && $task->file && !empty($task->file->doc_no)) {
            return (string)$task->file->doc_no;
        }
        if ($task->mail_id && $task->mail && !empty($task->mail->document_number)) {
            return (string)$task->mail->document_number;
        }
        if (!empty($task->tracking_id)) {
            return (string)$task->tracking_id;
        }
        return 'DOC-' . date('Ymd') . '-' . $task->id;
    }

    /**
     * Harvest all document assets attached to or produced by the task.
     */
    protected function harvestTaskDocuments(Task $task): array
    {
        $docs = [];
        $addedPaths = [];

        // A. Primary / Final Signed Document
        $latestVersion = TaskDocumentVersion::where('task_id', $task->id)->latest('version')->first();
        if ($latestVersion && !empty($latestVersion->file_path)) {
            $cleanRel = ltrim($latestVersion->file_path, '/');
            $abs = public_path($cleanRel);
            if (file_exists($abs) && !in_array($abs, $addedPaths)) {
                $docs[] = [
                    'source_path' => $abs,
                    'original_name' => 'Final_Signed_Document_v' . $latestVersion->version . '.pdf',
                ];
                $addedPaths[] = $abs;
            }
        }

        // B. Main Task File (if distinct from version)
        if ($task->file_id && $task->file) {
            if (!empty($task->file->path)) {
                $cleanRel = ltrim($task->file->path, '/');
                $abs = public_path($cleanRel);
                if (file_exists($abs) && !in_array($abs, $addedPaths)) {
                    $docs[] = [
                        'source_path' => $abs,
                        'original_name' => $task->file->name ?: basename($abs),
                    ];
                    $addedPaths[] = $abs;
                }
            }
        }

        // C. Task Attachments (TaskDoc records)
        $taskDocs = TaskDoc::where('task_id', $task->id)->get();
        foreach ($taskDocs as $td) {
            if (empty($td->path)) continue;
            $cleanRel = ltrim($td->path, '/');
            $abs = public_path($cleanRel);
            if (file_exists($abs) && !in_array($abs, $addedPaths)) {
                $docs[] = [
                    'source_path' => $abs,
                    'original_name' => $td->file_name ?: basename($abs),
                ];
                $addedPaths[] = $abs;
            }
        }

        // D. Task Submission Files
        $submissionFiles = TaskSubmissionFiles::where('task_id', $task->id)->get();
        foreach ($submissionFiles as $sf) {
            if (empty($sf->path)) continue;
            $cleanRel = ltrim($sf->path, '/');
            $abs = public_path($cleanRel);
            if (file_exists($abs) && !in_array($abs, $addedPaths)) {
                $docs[] = [
                    'source_path' => $abs,
                    'original_name' => basename($abs),
                ];
                $addedPaths[] = $abs;
            }
        }

        return $docs;
    }

    /**
     * Notify department, division, and unit stakeholders about the newly archived folder.
     */
    protected function notifyDepartmentStakeholders(Task $task, Folder $folder, User $agfUser): void
    {
        try {
            $deptId = $folder->department_id;
            $divId = $folder->division_id;
            $unitId = $folder->unit_id;

            $stakeholders = User::where(function ($q) use ($deptId, $divId, $unitId) {
                    $q->where('department_id', $deptId)
                      ->whereHas('roles', function ($rq) {
                          $rq->whereIn('name', ['director', 'deputy director', 'record manager']);
                      });
                    if ($divId) {
                        $q->orWhere(function ($dq) use ($divId) {
                            $dq->where('division_id', $divId)
                               ->whereHas('roles', function ($rq) {
                                   $rq->whereIn('name', ['head of division', 'division head']);
                               });
                        });
                    }
                    if ($unitId) {
                        $q->orWhere(function ($uq) use ($unitId) {
                            $uq->where('unit_id', $unitId)
                               ->whereHas('roles', function ($rq) {
                                   $rq->whereIn('name', ['head of unit', 'unit head']);
                               });
                        });
                    }
                })
                ->limit(10)
                ->get();

            if ($stakeholders->isNotEmpty()) {
                Notification::send($stakeholders, new TaskChangedNotification(
                    $task->id,
                    $agfUser,
                    'completed'
                ));
            }
        } catch (\Throwable $th) {
            Log::warning("Notification to stakeholders failed: " . $th->getMessage());
        }
    }

    /**
     * Format byte sizes into readable units.
     */
    protected function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
