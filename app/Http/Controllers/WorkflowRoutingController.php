<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Services\WorkflowEngine;
use Exception;

class WorkflowRoutingController extends Controller
{
    protected $workflowEngine;

    public function __construct(WorkflowEngine $workflowEngine)
    {
        $this->workflowEngine = $workflowEngine;
    }

    /**
     * Delegate a task downwards.
     */
    public function delegate(Request $request, $taskId)
    {
        $request->validate([
            'assigned_to' => 'required|array',
            'assigned_to.*' => 'exists:users,id',
            'comments' => 'nullable|string'
        ]);

        $task = Task::findOrFail($taskId);
        $this->authorize('update', $task);
        $user = auth()->user();

        try {
            $route = $this->workflowEngine->delegate(
                $task, 
                $user, 
                $request->input('assigned_to'), 
                $request->input('comments', '')
            );
            return redirect()->back()->with('message', 'Task successfully delegated downwards.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Staff submits task upwards.
     */
    public function submit(Request $request, $taskId)
    {
        $request->validate([
            'comments' => 'nullable|string'
        ]);

        $task = Task::findOrFail($taskId);
        $this->authorize('update', $task);
        $user = auth()->user();

        try {
            $route = $this->workflowEngine->submit(
                $task, 
                $user, 
                $request->input('comments', '')
            );
            return redirect()->back()->with('message', 'Task successfully submitted for approval.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Supervisor approves task and routes upwards.
     */
    public function approve(Request $request, $taskId)
    {
        $request->validate([
            'comments' => 'nullable|string'
        ]);

        $task = Task::findOrFail($taskId);
        $this->authorize('update', $task);
        $user = auth()->user();

        try {
            $route = $this->workflowEngine->approve(
                $task, 
                $user, 
                $request->input('comments', '')
            );
            return redirect()->back()->with('message', 'Task approved and routed upwards.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Supervisor rejects task and sends downwards.
     */
    public function returnTask(Request $request, $taskId)
    {
        $request->validate([
            'comments' => 'required|string'
        ]);

        $task = Task::findOrFail($taskId);
        $this->authorize('update', $task);
        $user = auth()->user();

        try {
            $route = $this->workflowEngine->returnTask(
                $task, 
                $user, 
                $request->input('comments', '')
            );
            return redirect()->back()->with('message', 'Task returned for corrections.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
