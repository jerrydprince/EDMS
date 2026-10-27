<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Task;
use App\Models\TaskDocumentVersion;

class MigrateExistingSubmissionsToVersionsSeeder extends Seeder
{
    public function run()
    {
        // For each task, check if it already has document versions
        $tasks = Task::all();

        foreach ($tasks as $task) {
            $existingVersions = TaskDocumentVersion::where('task_id', $task->id)->count();
            if ($existingVersions > 0) {
                continue;
            }

            $currentVersionNum = 1;

            // 1. Check if there are task submissions
            $submissions = DB::table('task_submissions')
                ->where('task_id', $task->id)
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            if ($submissions->isNotEmpty()) {
                foreach ($submissions as $sub) {
                    $subFile = DB::table('task_submission_files')
                        ->where('task_submissions_id', $sub->id)
                        ->first();

                    if ($subFile && !empty($subFile->path)) {
                        $action = $currentVersionNum === 1 ? 'submit' : 'sign';
                        TaskDocumentVersion::create([
                            'task_id' => $task->id,
                            'version' => $currentVersionNum++,
                            'path' => $subFile->path,
                            'created_by' => $sub->user_id ?: ($task->created_by ?: 1),
                            'action' => $action,
                            'note' => $sub->description ?: ($action === 'submit' ? 'Initial task submission' : 'Reviewed and signed version'),
                            'created_at' => $sub->created_at ?: now(),
                            'updated_at' => $sub->updated_at ?: now(),
                        ]);
                    }
                }
            }

            // 2. If no submissions were created, but task has primary task_doc (not Minute Sheet)
            if ($currentVersionNum === 1) {
                $baseDoc = DB::table('task_docs')
                    ->where('task_id', $task->id)
                    ->where('file_name', 'not like', '%Minute Sheet%')
                    ->where('path', 'not like', '%Minute_Sheet%')
                    ->first();

                if ($baseDoc && !empty($baseDoc->path)) {
                    TaskDocumentVersion::create([
                        'task_id' => $task->id,
                        'version' => 1,
                        'path' => $baseDoc->path,
                        'created_by' => $task->created_by ?: 1,
                        'action' => 'submit',
                        'note' => 'Original document version',
                        'created_at' => $baseDoc->created_at ?: now(),
                        'updated_at' => $baseDoc->updated_at ?: now(),
                    ]);
                }
            }
        }
    }
}
