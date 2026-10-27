<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Archive;
use App\Models\ArchiveDocs;
use App\Models\FilePath;
use App\Models\Task;
use App\Models\User;
use App\Models\Unit;
use App\Models\Division;
use App\Models\Department;
use App\Models\Metadata;
use App\Models\Mail;
use App\Services\AuditTrailService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RecycleBinController extends Controller
{
    /**
     * Display a paginated listing of soft-deleted items across EDMS entities.
     */
    public function index(Request $request)
    {
        $perItem = (int) $request->input('per_page', 15);
        $type = $request->input('type', 'Deleted Files');
        $search = $request->input('search', '');
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');

        // Support backward compatibility for datepicker array
        if ($request->has('date') && is_array($request->date) && count($request->date) >= 2) {
            $dateFrom = $request->date[0] ?? $dateFrom;
            $dateTo = $request->date[1] ?? $dateTo;
        }

        // Live category counts across all soft-deleted models
        $counts = [
            'Deleted Files' => Archive::onlyTrashed()->count(),
            'Deleted Tasks' => Task::onlyTrashed()->count(),
            'Deleted Mails' => Mail::onlyTrashed()->count(),
            'Deleted Users' => User::onlyTrashed()->count(),
            'Deleted Units' => Unit::onlyTrashed()->count(),
            'Deleted Divisions' => Division::onlyTrashed()->count(),
            'Deleted Departments' => Department::onlyTrashed()->count(),
            'Deleted Metadata' => Metadata::onlyTrashed()->count(),
        ];
        $counts['total'] = array_sum($counts);

        // Fetch query based on selected category
        $query = match ($type) {
            'Deleted Users' => User::onlyTrashed()->with(['deletedBy', 'department']),
            'Deleted Tasks' => Task::onlyTrashed()->with(['deletedBy', 'taskdocs']),
            'Deleted Units' => Unit::onlyTrashed()->with(['department', 'division']),
            'Deleted Divisions' => Division::onlyTrashed()->with('department'),
            'Deleted Departments' => Department::onlyTrashed(),
            'Deleted Metadata' => Metadata::onlyTrashed(),
            'Deleted Mails' => Mail::onlyTrashed()->with('deletedBy'),
            default => Archive::onlyTrashed()->with([
                'deletedBy',
                'department',
                'archiveDocs' => fn($q) => $q->onlyTrashed(),
                'filePath' => fn($q) => $q->onlyTrashed()
            ])
        };

        // Date range filtering on deleted_at
        if (!empty($dateFrom)) {
            $query->whereDate('deleted_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if (!empty($dateTo)) {
            $query->whereDate('deleted_at', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        // Text search per entity
        if (!empty($search)) {
            switch ($type) {
                case 'Deleted Users':
                    $query->where(function ($q) use ($search) {
                        $q->where('first_name', 'LIKE', "%{$search}%")
                          ->orWhere('last_name', 'LIKE', "%{$search}%")
                          ->orWhere('email', 'LIKE', "%{$search}%");
                    });
                    break;

                case 'Deleted Tasks':
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'LIKE', "%{$search}%")
                          ->orWhere('description', 'LIKE', "%{$search}%");
                    });
                    break;

                case 'Deleted Mails':
                    $query->where(function ($q) use ($search) {
                        $q->where('subject', 'LIKE', "%{$search}%")
                          ->orWhere('body', 'LIKE', "%{$search}%");
                    });
                    break;

                case 'Deleted Units':
                case 'Deleted Divisions':
                case 'Deleted Departments':
                    $query->where('title', 'LIKE', "%{$search}%");
                    break;

                case 'Deleted Metadata':
                    $query->where('name', 'LIKE', "%{$search}%");
                    break;

                default: // Deleted Files (Archive)
                    $query->where(function ($q) use ($search) {
                        $q->where('description', 'LIKE', "%{$search}%")
                          ->orWhereHas('archiveDocs', function ($dq) use ($search) {
                              $dq->where('archive_values', 'LIKE', "%{$search}%");
                          });
                    });
                    break;
            }
        }

        $deleted_files = $query->latest('deleted_at')->paginate($perItem)->withQueryString();

        // Attach retention days remaining metadata (30-day grace policy)
        $deleted_files->getCollection()->transform(function ($item) {
            $deletedAt = $item->deleted_at ? Carbon::parse($item->deleted_at) : now();
            $daysInBin = $deletedAt->diffInDays(now());
            $item->days_in_bin = $daysInBin;
            $item->retention_days_left = max(0, 30 - $daysInBin);
            return $item;
        });

        $filters = [
            'type' => $type,
            'search' => $search,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        return inertia('dashboard/RecycleBin', [
            'deleted_files' => $deleted_files,
            'type' => $type,
            'counts' => $counts,
            'filters' => $filters,
        ]);
    }

    /**
     * Restore a single soft-deleted record back to active production state.
     */
    public function restoreRecycleBin(Request $request)
    {
        $id = $request->input('id');
        $type = $request->input('type', 'Deleted Files');
        $user = auth()->user();

        DB::beginTransaction();
        try {
            $restoredItemName = 'Item';

            switch ($type) {
                case 'Deleted Users':
                    $item = User::onlyTrashed()->find($id);
                    if ($item) {
                        $item->restore();
                        $restoredItemName = "Officer {$item->first_name} {$item->last_name} ({$item->email})";
                    }
                    break;

                case 'Deleted Tasks':
                    $item = Task::onlyTrashed()->find($id);
                    if ($item) {
                        $item->restore();
                        // Also restore associated task documents
                        \App\Models\TaskDoc::where('task_id', $item->id)->update(['deleted_at' => null]);
                        $restoredItemName = "Task #{$item->id}: {$item->title}";
                    }
                    break;

                case 'Deleted Units':
                    $item = Unit::onlyTrashed()->find($id);
                    if ($item) {
                        $item->restore();
                        $restoredItemName = "Unit: {$item->title}";
                    }
                    break;

                case 'Deleted Divisions':
                    $item = Division::onlyTrashed()->find($id);
                    if ($item) {
                        $item->restore();
                        $restoredItemName = "Division: {$item->title}";
                    }
                    break;

                case 'Deleted Departments':
                    $item = Department::onlyTrashed()->find($id);
                    if ($item) {
                        $item->restore();
                        $restoredItemName = "Department: {$item->title}";
                    }
                    break;

                case 'Deleted Metadata':
                    $item = Metadata::onlyTrashed()->find($id);
                    if ($item) {
                        $item->restore();
                        $restoredItemName = "Metadata Field: {$item->name}";
                    }
                    break;

                case 'Deleted Mails':
                    $item = Mail::onlyTrashed()->find($id);
                    if ($item) {
                        $item->restore();
                        $restoredItemName = "Mail: {$item->subject}";
                    }
                    break;

                default: // Deleted Files (Archive)
                    $item = Archive::onlyTrashed()->with([
                        'archiveDocs' => fn($q) => $q->onlyTrashed(),
                        'filePath' => fn($q) => $q->onlyTrashed()
                    ])->find($id);

                    if ($item) {
                        $item->restore();
                        if ($item->archiveDocs) {
                            foreach ($item->archiveDocs as $doc) {
                                $doc->restore();
                            }
                        }
                        if ($item->filePath) {
                            foreach ($item->filePath as $path) {
                                $path->restore();
                            }
                        }
                        $titleDoc = $item->archiveDocs?->firstWhere('archive_keys', 'title');
                        $restoredItemName = $titleDoc ? $titleDoc->archive_values : "Archive #{$item->id}";
                    }
                    break;
            }

            DB::commit();

            // Forensic audit log
            AuditTrailService::logRecord(
                'record_restored_from_recycle_bin',
                "Restored '{$restoredItemName}' ({$type} #{$id}) from Recycle Bin",
                $item ?? null,
                $user,
                ['type' => $type, 'id' => $id, 'restored_by' => $user->id],
                'notice'
            );

            return redirect()->back()->with('success', "'{$restoredItemName}' restored successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to restore item: ' . $e->getMessage()]);
        }
    }

    /**
     * Restore multiple soft-deleted records in a single batch operation.
     */
    public function batchRestore(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'type' => 'required|string',
        ]);

        $ids = $request->input('ids');
        $type = $request->input('type');
        $user = auth()->user();
        $restoredCount = 0;

        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                switch ($type) {
                    case 'Deleted Users':
                        $userItem = User::onlyTrashed()->find($id);
                        if ($userItem) { $userItem->restore(); $restoredCount++; }
                        break;

                    case 'Deleted Tasks':
                        $task = Task::onlyTrashed()->find($id);
                        if ($task) {
                            $task->restore();
                            \App\Models\TaskDoc::where('task_id', $task->id)->update(['deleted_at' => null]);
                            $restoredCount++;
                        }
                        break;

                    case 'Deleted Mails':
                        $mail = Mail::onlyTrashed()->find($id);
                        if ($mail) { $mail->restore(); $restoredCount++; }
                        break;

                    case 'Deleted Units':
                        $unit = Unit::onlyTrashed()->find($id);
                        if ($unit) { $unit->restore(); $restoredCount++; }
                        break;

                    case 'Deleted Divisions':
                        $division = Division::onlyTrashed()->find($id);
                        if ($division) { $division->restore(); $restoredCount++; }
                        break;

                    case 'Deleted Departments':
                        $dept = Department::onlyTrashed()->find($id);
                        if ($dept) { $dept->restore(); $restoredCount++; }
                        break;

                    case 'Deleted Metadata':
                        $meta = Metadata::onlyTrashed()->find($id);
                        if ($meta) { $meta->restore(); $restoredCount++; }
                        break;

                    default: // Files
                        $file = Archive::onlyTrashed()->with([
                            'archiveDocs' => fn($q) => $q->onlyTrashed(),
                            'filePath' => fn($q) => $q->onlyTrashed()
                        ])->find($id);

                        if ($file) {
                            $file->restore();
                            foreach ($file->archiveDocs as $doc) { $doc->restore(); }
                            foreach ($file->filePath as $fp) { $fp->restore(); }
                            $restoredCount++;
                        }
                        break;
                }
            }

            DB::commit();

            AuditTrailService::logRecord(
                'batch_restore_recycle_bin',
                "Batch restored {$restoredCount} items from category '{$type}'",
                null,
                $user,
                ['type' => $type, 'count' => $restoredCount, 'ids' => $ids],
                'notice'
            );

            return redirect()->back()->with('success', "Successfully restored {$restoredCount} items.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Batch restore failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Permanently destroy a single soft-deleted record and clean up physical disk files.
     */
    public function deleteRecyleBinFile(Request $request)
    {
        $user = auth()->user();

        // Authorize: Super Admin, AGF/Super User, or specific permission
        $isAuthorized = $user->hasRole('super admin') 
            || $user->hasRole('super user') 
            || $user->hasRole('Accountant-General') 
            || $user->can('recycle bin delete');

        if (!$isAuthorized) {
            return redirect()->back()->withErrors(['error' => 'Unauthorized: Permanent deletion requires executive administrative privileges.']);
        }

        $id = $request->input('id');
        $type = $request->input('type', 'Deleted Files');
        $deletedItemName = "Item #{$id}";

        DB::beginTransaction();
        try {
            switch ($type) {
                case 'Deleted Users':
                    $targetUser = User::onlyTrashed()->find($id);
                    if ($targetUser) {
                        $deletedItemName = "User: {$targetUser->first_name} {$targetUser->last_name} ({$targetUser->email})";
                        $targetUser->forceDelete();
                    }
                    break;

                case 'Deleted Tasks':
                    $task = Task::onlyTrashed()->with('taskdocs')->find($id);
                    if ($task) {
                        $deletedItemName = "Task #{$task->id}: {$task->title}";
                        // Unlink physical files on disk
                        foreach ($task->taskdocs as $td) {
                            if ($td->path) {
                                $cleanPath = ltrim($td->path, '/');
                                $fullDiskPath = public_path($cleanPath);
                                if (File::exists($fullDiskPath)) {
                                    @File::delete($fullDiskPath);
                                }
                            }
                            $td->delete();
                        }
                        $task->forceDelete();
                    }
                    break;

                case 'Deleted Mails':
                    $mail = Mail::onlyTrashed()->find($id);
                    if ($mail) {
                        $deletedItemName = "Mail: {$mail->subject}";
                        $mail->forceDelete();
                    }
                    break;

                case 'Deleted Units':
                    $unit = Unit::onlyTrashed()->find($id);
                    if ($unit) {
                        $deletedItemName = "Unit: {$unit->title}";
                        $unit->forceDelete();
                    }
                    break;

                case 'Deleted Divisions':
                    $division = Division::onlyTrashed()->find($id);
                    if ($division) {
                        $deletedItemName = "Division: {$division->title}";
                        $division->forceDelete();
                    }
                    break;

                case 'Deleted Departments':
                    $department = Department::onlyTrashed()->find($id);
                    if ($department) {
                        $deletedItemName = "Department: {$department->title}";
                        $department->forceDelete();
                    }
                    break;

                case 'Deleted Metadata':
                    $meta = Metadata::onlyTrashed()->find($id);
                    if ($meta) {
                        $deletedItemName = "Metadata: {$meta->name}";
                        $meta->forceDelete();
                    }
                    break;

                default: // Deleted Files (Archive)
                    $file = Archive::onlyTrashed()->with([
                        'archiveDocs' => fn($q) => $q->onlyTrashed(),
                        'filePath' => fn($q) => $q->onlyTrashed()
                    ])->find($id);

                    if ($file) {
                        $titleDoc = $file->archiveDocs?->firstWhere('archive_keys', 'title');
                        $deletedItemName = $titleDoc ? $titleDoc->archive_values : "Archive #{$file->id}";

                        // Unlink and purge physical files from disk
                        if ($file->filePath) {
                            foreach ($file->filePath as $fp) {
                                if ($fp->path) {
                                    $diskRel = ltrim($fp->path, '/');
                                    // Check public_path and storage
                                    $candidates = [
                                        public_path('storage/' . $diskRel),
                                        public_path($diskRel),
                                        storage_path('app/public/' . $diskRel)
                                    ];
                                    foreach ($candidates as $candidate) {
                                        if (File::exists($candidate)) {
                                            @File::delete($candidate);
                                        }
                                    }
                                }
                                $fp->forceDelete();
                            }
                        }

                        if ($file->archiveDocs) {
                            foreach ($file->archiveDocs as $doc) {
                                $doc->forceDelete();
                            }
                        }

                        $file->forceDelete();
                    }
                    break;
            }

            DB::commit();

            // Forensic audit trail of permanent destruction
            AuditTrailService::logSecurity(
                'record_permanently_destroyed',
                "Permanently destroyed '{$deletedItemName}' ({$type} #{$id}) from system and storage",
                $user,
                null,
                ['type' => $type, 'id' => $id, 'operator' => $user->email],
                'warning'
            );

            return redirect()->back()->with('success', "'{$deletedItemName}' was permanently purged.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Permanent purge failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Permanently destroy multiple soft-deleted records in a single batch operation.
     */
    public function batchDelete(Request $request)
    {
        $user = auth()->user();

        $isAuthorized = $user->hasRole('super admin') 
            || $user->hasRole('super user') 
            || $user->hasRole('Accountant-General') 
            || $user->can('recycle bin delete');

        if (!$isAuthorized) {
            return redirect()->back()->withErrors(['error' => 'Unauthorized: Permanent deletion requires executive administrative privileges.']);
        }

        $request->validate([
            'ids' => 'required|array|min:1',
            'type' => 'required|string',
        ]);

        $ids = $request->input('ids');
        $type = $request->input('type');
        $purgedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                switch ($type) {
                    case 'Deleted Users':
                        $targetUser = User::onlyTrashed()->find($id);
                        if ($targetUser) { $targetUser->forceDelete(); $purgedCount++; }
                        break;

                    case 'Deleted Tasks':
                        $task = Task::onlyTrashed()->with('taskdocs')->find($id);
                        if ($task) {
                            foreach ($task->taskdocs as $td) {
                                if ($td->path && File::exists(public_path(ltrim($td->path, '/')))) {
                                    @File::delete(public_path(ltrim($td->path, '/')));
                                }
                                $td->delete();
                            }
                            $task->forceDelete();
                            $purgedCount++;
                        }
                        break;

                    case 'Deleted Mails':
                        $mail = Mail::onlyTrashed()->find($id);
                        if ($mail) { $mail->forceDelete(); $purgedCount++; }
                        break;

                    case 'Deleted Units':
                        $unit = Unit::onlyTrashed()->find($id);
                        if ($unit) { $unit->forceDelete(); $purgedCount++; }
                        break;

                    case 'Deleted Divisions':
                        $division = Division::onlyTrashed()->find($id);
                        if ($division) { $division->forceDelete(); $purgedCount++; }
                        break;

                    case 'Deleted Departments':
                        $dept = Department::onlyTrashed()->find($id);
                        if ($dept) { $dept->forceDelete(); $purgedCount++; }
                        break;

                    case 'Deleted Metadata':
                        $meta = Metadata::onlyTrashed()->find($id);
                        if ($meta) { $meta->forceDelete(); $purgedCount++; }
                        break;

                    default: // Archive files
                        $file = Archive::onlyTrashed()->with(['filePath' => fn($q) => $q->onlyTrashed(), 'archiveDocs' => fn($q) => $q->onlyTrashed()])->find($id);
                        if ($file) {
                            foreach ($file->filePath as $fp) {
                                if ($fp->path) {
                                    $diskRel = ltrim($fp->path, '/');
                                    $candidates = [
                                        public_path('storage/' . $diskRel),
                                        public_path($diskRel),
                                        storage_path('app/public/' . $diskRel)
                                    ];
                                    foreach ($candidates as $cand) {
                                        if (File::exists($cand)) { @File::delete($cand); }
                                    }
                                }
                                $fp->forceDelete();
                            }
                            foreach ($file->archiveDocs as $doc) { $doc->forceDelete(); }
                            $file->forceDelete();
                            $purgedCount++;
                        }
                        break;
                }
            }

            DB::commit();

            AuditTrailService::logSecurity(
                'batch_purge_recycle_bin',
                "Permanently purged {$purgedCount} items from category '{$type}'",
                $user,
                null,
                ['type' => $type, 'purged_count' => $purgedCount, 'ids' => $ids],
                'warning'
            );

            return redirect()->back()->with('success', "Permanently purged {$purgedCount} items from system storage.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Batch purge failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Completely empty either the current category or all soft-deleted items across the EDMS.
     */
    public function emptyBin(Request $request)
    {
        $user = auth()->user();

        $isAuthorized = $user->hasRole('super admin') 
            || $user->hasRole('super user') 
            || $user->hasRole('Accountant-General') 
            || $user->can('recycle bin delete');

        if (!$isAuthorized) {
            return redirect()->back()->withErrors(['error' => 'Unauthorized: Emptying recycle bin requires executive administrative privileges.']);
        }

        $scope = $request->input('scope', 'all'); // 'all' or specific type
        $totalCleared = 0;

        DB::beginTransaction();
        try {
            if ($scope === 'all' || $scope === 'Deleted Files') {
                $archives = Archive::onlyTrashed()->with(['filePath' => fn($q) => $q->onlyTrashed(), 'archiveDocs' => fn($q) => $q->onlyTrashed()])->get();
                foreach ($archives as $file) {
                    foreach ($file->filePath as $fp) {
                        if ($fp->path) {
                            $cand = public_path('storage/' . ltrim($fp->path, '/'));
                            if (File::exists($cand)) { @File::delete($cand); }
                        }
                        $fp->forceDelete();
                    }
                    foreach ($file->archiveDocs as $doc) { $doc->forceDelete(); }
                    $file->forceDelete();
                    $totalCleared++;
                }
            }

            if ($scope === 'all' || $scope === 'Deleted Tasks') {
                $tasks = Task::onlyTrashed()->with('taskdocs')->get();
                foreach ($tasks as $task) {
                    foreach ($task->taskdocs as $td) {
                        if ($td->path && File::exists(public_path(ltrim($td->path, '/')))) {
                            @File::delete(public_path(ltrim($td->path, '/')));
                        }
                        $td->delete();
                    }
                    $task->forceDelete();
                    $totalCleared++;
                }
            }

            if ($scope === 'all' || $scope === 'Deleted Mails') {
                $totalCleared += Mail::onlyTrashed()->forceDelete();
            }

            if ($scope === 'all' || $scope === 'Deleted Metadata') {
                $totalCleared += Metadata::onlyTrashed()->forceDelete();
            }

            if ($scope === 'all' || $scope === 'Deleted Units') {
                $totalCleared += Unit::onlyTrashed()->forceDelete();
            }

            if ($scope === 'all' || $scope === 'Deleted Divisions') {
                $totalCleared += Division::onlyTrashed()->forceDelete();
            }

            if ($scope === 'all' || $scope === 'Deleted Departments') {
                $totalCleared += Department::onlyTrashed()->forceDelete();
            }

            DB::commit();

            AuditTrailService::logSecurity(
                'recycle_bin_emptied',
                "Executive empty operation on Recycle Bin (Scope: {$scope}, Total Cleared: {$totalCleared})",
                $user,
                null,
                ['scope' => $scope, 'cleared_count' => $totalCleared],
                'critical'
            );

            return redirect()->back()->with('success', "Recycle bin emptied successfully. {$totalCleared} records permanently purged.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to empty recycle bin: ' . $e->getMessage()]);
        }
    }
}
