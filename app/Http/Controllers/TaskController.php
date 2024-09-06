<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskNotification;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskDoc;
use App\Models\Comment;
use App\Models\TaskSubmission;
use App\Models\TaskSubmissionFiles;
use Illuminate\Support\Facades\DB;
use App\Models\KRA;
use App\Events\TaskEvent;

class TaskController extends Controller
{
    public function createTask(Request $request){
       
        if($request->id){
            $request->validate([
                'title' => 'required',
                'due_date' => 'required',
                'priority' => 'required',
            ]);
        }else{
            $request->validate([
                'title' => 'required',
                'due_date' => 'required',
                'priority' => 'required',
                'users' => 'required'
            ]);
        }

        $files = [];

        $branchType = $this->getBranchType();
    
        try {
            DB::beginTransaction();
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
    
            $task = $request->id? Task::with('user')->find($request->id) :new Task();
            $task->title = $request->title;
            $task->description = $request->description;
            $task->due_date = $request->due_date;
            $task->priority = $request->priority;
            if(auth()->user()->hasRole('unit heads')){
                $task->assigned_by = auth()->user()->id;
                $task->assigned_by_unit = auth()->user()->unit_id;
            }
            if(auth()->user()->hasRole('division heads')){
                $task->assigned_by_division = auth()->user()->division_id;
            }
            if(auth()->user()->hasRole('directors')){
                $task->assigned_by_department = auth()->user()->department_id;
            }
            if(!$request->id){
                $task->status = 'pending';
            }
            if(auth()->user()->hasRole('super users')){
                $task->assigned_by_super_user = auth()->user()->id;
            }
            
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
            if(!$request->id){
                if(auth()->user()->hasRole('unit heads')){
                    $task->user()->sync($request->users); 
                    $users = $task->user()->get();
                }

                if(auth()->user()->hasRole('division heads')){
                    //branch can only assign to units
                    $task->unitTask()->sync($request->users);
                    $users = User::role('unit heads')->get();
                }

                if(auth()->user()->hasRole('directors')){
                    //department can assign a task to divisions
                    $task->divisionTask()->sync($request->users);
                    $users = User::role('division heads')->get();
                    //$task->departmentTask()->sync($request->users);
                }

                if(auth()->user()->hasRole('super users')){
                   // $task->superuserTask()->sync($request->users);
                   $task->departmentTask()->sync($request->users);
                   $users = User::role('directors')->get();
                }
               
                 //user notification
                // $users = User::whereIn('id', $request->users)->get();
            }else{
                $users = $task->user()->get();
            }
           DB::commit();
           //$users = User::where('id', $request->users)->get();
           //dd($users);
          
            Notification::send($users, new TaskNotification($task));
            //broadcast()
           
        } catch (Exception $e) {
            DB::rollback();
            //dd($e->getMessage());
        }

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
        if($request->type == 'assigned_to_me'){
            $task = auth()->user()->task()->with('assignedBy')->where('status', $request->status)->paginate(10);
        }else{
            $task = Task::with('assignedBy')->where('status', $request->status)->paginate(10);
        }
        return response()->json($task);
    }

    public function viewTask($id) {
       
        $task = Task::with(['taskdocs','user', 'rating', 'taskSubmission'=>function($query){
            $query->with('taskSubmissionFiles', 'user');
        }, 'comments' => function($query) {
            $query->with(['user' => function($query) {
                $query->select('id', 'first_name', 'last_name','profile_image');
            }]);
        }])->find($id);

        // if($task){
        //     $type = $task->assigned_by == auth()->user()->id? 1: 2;
        // }else{
        //     $type = 2;
        // }
        // $task = is_null($task)? []: $task;
        //dd(auth()->user()->id);
        $is_assigned = request()->is_assigned? request()->is_assigned: 1;
        $branchType = $this->getBranchType();
        return inertia('Workflow/ViewTask', compact('task', 'is_assigned', 'branchType'));
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

    public function removeTaskImage(Request $request){
        $taskdoc = TaskDoc::find($request->id);
        if($taskdoc){
            if(file_exists(public_path($taskdoc->path))){
                unlink(public_path($taskdoc->path));
            }
            $taskdoc->delete();
        }
        
        return response()->json(['success'=>true]);
    }

    public function isRole($role){
        $roles = auth()->user()->getRoleNames();
        $is_role = $roles->contains(function($item)use ($role){
            //dd($item);
            return $item == $role;
        });
        if($is_role){
            return true;
        }else{
            return false;
        }
    }

    public function insertTaskDoc($task, $task2){
        $docs = $task->taskdocs->map(function($item)use ($task2){
            $item->task_id = $task2->id;
            $item->created_at = null;
            $item->updated_at = null;
            $item->id = null;
            return $item;
        });
        $docs = $docs->toArray();
        TaskDoc::insert($docs);
    }

    public function getBranchIds($branch){
        $user_ids = [];
            foreach($branch as $user){
                array_push($user_ids, $user['id']);
            }
            return $user_ids;
    }

    public function reAssignTask(Request $request){

        if($request->users){
            $user_ids = [];
            foreach($request->users as $user){
                array_push($user_ids, $user['id']);
            }

            $task = Task::with('taskdocs')->find($request->task_id);
            
            if($request->description){
                $task->description = $request->description;
                $task->save();
            }
            
            $task2 = $task->replicate();
            if($this->isRole('unit heads')){
                $task2->assigned_by = auth()->user()->id;
                $task2->assigned_by_unit = auth()->user()->unit_id;
                $task2->assigned_by_division = null;
                $task2->assigned_by_department = null;
                $task2->save();
                $task2->user()->sync($user_ids);

                $this->insertTaskDoc($task, $task2);
            }

            if($this->isRole('division heads')){
                $task2->assigned_by_division = auth()->user()->division_id;
                $task2->assigned_by_department = null;
                $task2->save();

                $branch_ids = $this->getBranchIds($request->users);
                $task2->unitTask()->sync($branch_ids);

                $this->insertTaskDoc($task, $task2);
            }

            if($this->isRole('directors')){
                $task2->assigned_by_department = auth()->user()->department_id;
                $task2->save();
                $branch_ids = $this->getBranchIds($request->users);
                $task2->divisionTask()->sync($branch_ids);

                $this->insertTaskDoc($task, $task2);
            }
            
        }
        $title = 'New task assigned';
        if($this->isRole('unit heads')){
             $users = User::whereIn('id', $user_ids)->get();
            Notification::send($users, new TaskNotification($title));
        }
       
        return redirect()->back();
    }

    public function getBranchType(){
        $user = auth()->user();
        if($user->unit_id){
            return 'unit';
        }
        if($user->division_id){
            return 'division';
        }
        if($user->department_id){
            return 'department';
        }
    }

    public function taskSubmission(Request $request){
       DB::transaction(function () use($request) {
        $submission = new TaskSubmission();
        $submission->description = $request->description;
        $submission->task_id = $request->task_id;
        $submission->user_id = auth()->user()->id;
        $submission->save();

        if($request->file('files')){
            $files = [];
            foreach($request->file('files') as $file){

                $fileName = time() . '_' . $file->getClientOriginalName();
                $original_name = $file->getClientOriginalName();
                $path =  "/task/".date('Y') . '/' . date('m');
                $size = $this->formatSize(filesize($file));
                $fullPath = public_path($path);

                if (!file_exists($fullPath)) {
                    mkdir($fullPath, 0777, true);
                }
                $file->move(public_path($path),$fileName);

                $arr = [
                    'path' => $path.'/'.$fileName,
                    'task_id' => $request->task_id,
                    'task_submissions_id' =>$submission->id,
                    'name' => $original_name,
                    'size'=>$size
                ];

                array_push($files, $arr);
            }

            $tsf = new TaskSubmissionFiles();
            $tsf->insert($files);

        }
        $task = Task::find($request->task_id);
        $task->status = 'under review';
        $task->save();
       });

        return redirect()->back();
    }

    public function markAsComplete(Request $request){
        $task = Task::find($request->id);
        $task->status = 'completed';
        $task->save();
        return redirect()->back();
    }

    public function requestRevision(Request $request){
        $task = Task::find($request->id);
        $task->status = 'revision';
        $task->save();

        if($request->description){
            $comment = new Comment();
            $comment->comment = $request->description;
            $comment->task_id =$task->id;
            $comment->user_id = auth()->user()->id;
            $comment->save();
        }
        return redirect()->back();
    }

    public function reopenTask(Request $request){
        $task = Task::find($request->id);
        $task->status = 'pending';
        $task->save();
        return redirect()->back();
    }

    public function getDepartmentKRA(Request $request){
        $user = User::find($request->user_id);
        $department = $user->department_id;
        if($department){
            $kra = KRA::with('kpi')->where('department_id',$department)->first();
            $kpi = $kra->kpi->chunk(2);
            return response()->json($kpi);
        }
    }
}
