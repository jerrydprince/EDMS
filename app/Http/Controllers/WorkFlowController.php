<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\Workflow;
use App\Notifications\TaskNotification;
use App\Models\Task;
use App\Models\User;

class WorkFlowController extends Controller
{
    public function index(){
        $userworkflows = Workflow::with('task')->where('created_by', auth()->user()->id)->latest()->get();
        return inertia('Workflow/Workflows', compact('userworkflows'));
    }

    public function createWorkFlow(Request $request){
        $wkflow = new Workflow();
        $wkflow->title = $request->title;
        $wkflow->emoji = $request->emoji;
        $wkflow->created_by = auth()->user()->id;
        $wkflow->save();
        return redirect()->back();
    }

    public function createTask(Request $request){
        $request->validate([
            'start' => 'required',
            'end' => 'required',
            'title' => 'required',
            'workflow_id' => 'required'
        ]);

        if($request->file('file')){
            $file = $request->file('file');
           // $extension = strtolower($file->getClientOriginalExtension());
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = "/workflow";
            $file->move(public_path($path),$fileName);
        }

        $task = new Task();
        $task->start = $request->start;
        $task->end = $request->end;
        $task->title = $request->title;
        $task->description = $request->description;
        $task->workflow_id = $request->workflow_id;
        $task->document_type = $request->documentType;
        if($request->file('file')){
            $task->file_path = $path.'/'.$fileName;
        }
        $task->save();

        $ids = explode(',', $request->userIds);
        $task->user()->sync($ids);

        //user notification
        $users = User::whereIn('id', $ids)->get();
        Notification::send($users, new TaskNotification($request->title));

        return redirect()->back();
    }
}
