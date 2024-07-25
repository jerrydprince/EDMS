<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskNotification;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskDoc;
use App\Models\Comment;

class TaskController extends Controller
{
    public function createTask(Request $request){
        $request->validate([
            'title' => 'required',
            'due_date' => 'required',
            'priority' => 'required',
            'users' => 'required'
        ]);

        $files = [];
        if($request->file('files')){
            foreach($request->file('files') as $file){
               // $file = $request->file('file');
            // $extension = strtolower($file->getClientOriginalExtension());
            $size = $this->formatSize(filesize($file));

                $fileName = time() . '_' . $file->getClientOriginalName();
                $original_name = $file->getClientOriginalName();
                $path =  "/task/".date('Y') . '/' . date('m');
                $fullPath = public_path($path);

                if (!file_exists($fullPath)) {
                    mkdir($fullPath, 0777, true);
                }
                $file->move(public_path($path),$fileName);

                $arr = [
                    'fileName' => $fileName,
                    'size' => $size,
                    'original_name' => $original_name
                ];

                array_push($files, $arr);
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
            foreach($files as $file){
              array_push($docs, [
                'path' => $path.'/'.$file['fileName'],
                'task_id' => $task->id,
                'size' => $file['size'],
                'file_name' => $file['original_name'],
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

    function formatSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
    
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getTaskByStatus(Request $request){
        $task = Task::where('status', $request->status)->paginate(10);
        return response()->json($task);
    }

    public function viewTask($id) {
        $task = Task::with(['taskdocs', 'comments' => function($query) {
            $query->with(['user' => function($query) {
                $query->select('id', 'first_name', 'last_name','profile_image');
            }]);
        }])->find($id);
        
        return inertia('Workflow/ViewTask', compact('task'));
    }

    public function downloadFile($filename)
    {
        $path = public_path($filename);

        // Check if the file exists
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }

    public function taskComment(Request $request){
        $request->validate([
            'comment'=>'required'
        ]);
        $comment = new Comment();
        $comment->comment = $request->comment;
        $comment->task_id = $request->task_id;
        $comment->user_id = auth()->user()->id;
        $comment->save();
        
        return redirect()->back()->with(['success'=>true]);
    }
}
