<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class AGFTrackTasksController extends Controller
{
    public function index(Request $request)
    {
        $tasks = Task::with(['assignments.assignedTo', 'assignments.assignedBy', 'workflowVersion.workflow', 'activeStep.step'])
            ->where('created_by', auth()->id())
            ->where(function($query) {
                $query->whereNotNull('mail_id')->orWhereNotNull('file_id');
            })
            ->latest()
            ->paginate(20);

        return inertia('Workflow/AGFTrackTasks', [
            'tasks' => $tasks
        ]);
    }
}
