<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskNotification;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskDoc;

class TaskController extends Controller
{
    public function createTask(Request $request){
        $request->validate([
            'title' => 'required',
            'due_date' => 'required',
            'priority' => 'required',
            'users' => 'required'
        ]);

        $fileNames = [];
        if($request->file('files')){
            foreach($request->file('files') as $file){
               // $file = $request->file('file');
            // $extension = strtolower($file->getClientOriginalExtension());
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = "/task";
                $file->move(public_path($path),$fileName);

                array_push($fileNames, $fileName);
            }
        }

        $task = new Task();
        $task->title = $request->title;
        $task->description = $request->description;
        $task->due_date = $request->due_date;
        $task->priority = $request->priority;
        $task->assigned_by = auth()->user()->id;
        $task->status = 'pending';
        $task->save();

        if($request->file('files')){
            $docs = [];
            foreach($fileNames as $fileName){
              array_push($docs, [
                'path' => $path.'/'.$fileName,
                'task_id' => $task->id,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
              ]);
            }
            TaskDoc::insert($docs);
        }

        //$ids = explode(',', $request->users);
        $task->user()->sync($request->users);

        //user notification
       // $users = User::whereIn('id', $request->users)->get();
       $users = User::where('id', $request->users)->get();
        Notification::send($users, new TaskNotification($request->title));

        return redirect()->back();
    }

    public function getTaskByStatus(Request $request){
        $task = Task::where('status', $request->status)->paginate(10);
        return response()->json($task);
    }
}
