<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskMinute;
use App\Models\TaskDoc;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class MinuteSheetService
{
    /**
     * Appends a new minute to the task and regenerates the Minute Sheet PDF.
     *
     * @param int $taskId
     * @param int $userId
     * @param string $action (e.g. 'Assigned to ICT', 'Submitted')
     * @param string $directive (e.g. 'Please review and advise')
     * @param bool $isSigned (whether the user provided a valid PIN)
     * @return bool
     */
    public function appendMinute($taskId, $userId, $action, $directive, $isSigned)
    {
        try {
            // 1. Save the new minute to the database
            TaskMinute::create([
                'task_id' => $taskId,
                'user_id' => $userId,
                'action' => $action,
                'directive' => $directive,
                'is_signed' => $isSigned,
            ]);

            // 2. Fetch all minutes for this task
            $task = Task::findOrFail($taskId);
            $minutes = TaskMinute::with('user.department')->where('task_id', $taskId)->orderBy('created_at', 'asc')->get();

            // 3. Generate PDF
            $pdf = Pdf::loadView('pdfs.minute_sheet', compact('task', 'minutes'));
            
            // 4. Ensure directory exists
            $directory = public_path('documents/minutes');
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // 5. Save the PDF file
            $fileName = 'Minute_Sheet_Task_' . $taskId . '.pdf';
            $filePath = $directory . '/' . $fileName;
            $pdf->save($filePath);

            // 6. Check if Minute Sheet is already in taskdocs, if not, attach it.
            $relativePath = '/documents/minutes/' . $fileName;
            
            $existingDoc = TaskDoc::where('task_id', $taskId)
                                  ->where('path', $relativePath)
                                  ->first();

            if (!$existingDoc) {
                // To make sure it always shows first, we might manipulate ordering, but for now just attach.
                // We'll attach it as a normal taskdoc. Wait, if we attach it as a taskdoc, it will be visible in the viewer!
                $sizeInBytes = filesize($filePath) ?: 0;
                $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                $i = 0;
                while ($sizeInBytes >= 1024 && $i < 4) {
                    $sizeInBytes /= 1024;
                    $i++;
                }
                $formattedSize = round($sizeInBytes, 2) . ' ' . $units[$i];

                TaskDoc::create([
                    'task_id' => $taskId,
                    'path' => $relativePath,
                    'file_name' => 'Minute Sheet.pdf',
                    'file_type' => 'pdf',
                    'size' => $formattedSize,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to generate minute sheet: " . $e->getMessage());
            return false;
        }
    }
}
