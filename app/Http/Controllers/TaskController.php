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
use App\Notifications\CommentNotification;
use App\Models\Correspondence;
use App\Models\TaskActivity;
use App\Models\Unit;
use App\Models\Division;
use App\Models\Department;

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
    
            $docs = [];
            if($request->file('files')){
                
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

             //add extra files from AGF assign task
             if($request->extra_files){
                $path =  "/task/".date('Y') . '/' . date('m');
                foreach($request->extra_files as $file){
                 
                    array_push($docs, [
                      'path' => $file['path'],
                      'task_id' => $task->id,
                      //'size' => $file['size'],
                      //'file_name' => $file['original_name'],
                      'size' => '22kb',
                      'file_name' =>$this->getExtraFileName($file['path']),
                      'created_at' => \Carbon\Carbon::now(),
                      'updated_at' => \Carbon\Carbon::now()
                    ]);
                  }

                  TaskDoc::insert($docs);
            }

            //mark the correspondence as assigned
            if($request->is_from_correspondece){
                $correspondence = Correspondence::find($request->correspondence_id);
                $correspondence->is_assigned = true;
                $correspondence->task_id = $task->id;
                $correspondence->save();
            }

            //task activities
            $ta = new TaskActivity();
            $ta->description = "Task created";
            $ta->task_id = $task->id;
            $ta->created_by = auth()->user()->id;
            $ta->save();


            //$ids = explode(',', $request->users);
            if(!$request->id){
                if(auth()->user()->hasRole('unit heads')){
                    $task->user()->sync($request->users); 
                    $users = $task->user()->get();

                    //task activities
                    $this->assignedTaskActivity($request->users, 'unit heads', $task);
                }

                if(auth()->user()->hasRole('division heads')){
                    //branch can only assign to units
                    $task->unitTask()->sync($request->users);
                    $users = User::role('unit heads')->get();

                    //task activities
                    $this->assignedTaskActivity($request->users, 'divisions heads', $task);
                }

                if(auth()->user()->hasRole('directors')){
                    //department can assign a task to divisions
                    $task->divisionTask()->sync($request->users);
                    $users = User::role('division heads')->get();
                    //$task->departmentTask()->sync($request->users);

                    //task activities
                    $this->assignedTaskActivity($request->users, 'directors', $task);
                }

                if(auth()->user()->hasRole('super users')){
                   // $task->superuserTask()->sync($request->users);
                   $task->departmentTask()->sync($request->users);
                   $users = User::role('directors')->get();

                   //task activities
                   $this->assignedTaskActivity($request->users, 'super users', $task);
                }
               
                 //user notification
                // $users = User::whereIn('id', $request->users)->get();
            }else{
                $users = $task->user()->get();
            }

           DB::commit();
           //$users = User::where('id', $request->users)->get();
           //dd($users);
            $user = User::find(auth()->user()->id);
            Notification::send($users, new TaskNotification($task,$user));
            //broadcast()
           
        } catch (Exception $e) {
            DB::rollback();
            //dd($e->getMessage());
        }

        return redirect()->back();
    }

    public function addToTaskActivity($data, $text, $task){
                $ta = new TaskActivity();
                $ta->description = $text.$data;
                $ta->task_id = $task->id;
                $ta->created_by = auth()->user()->id;
                $ta->save();
    }

    public function assignedTaskActivity($users, $branch, $task, $text=''){
        if(auth()->user()->hasRole('unit heads')){
            $users = User::whereIn('id', $users)
                ->select(DB::raw("CONCAT(first_name,' ',last_name) AS full_name"))
                ->pluck('full_name')
                ->implode(', ');
            
                $text = $text? $text: 'Task assigned to ';
             $this->addToTaskActivity($users, $text, $task);   
        }
         if(auth()->user()->hasRole('division heads')){
            $users = Unit::whereIn('id', $users)
                ->pluck('title')
                ->implode(', '); 

                $text = $text? $text: 'Task assigned to ';
                $this->addToTaskActivity($users, $text, $task); 
        }
         if(auth()->user()->hasRole('directors')){
            $users = Division::whereIn('id', $users)
                ->pluck('title')
                ->implode(', '); 

                $text = $text? $text: 'Task assigned to ';
                $this->addToTaskActivity($users, $text, $task); 
        }
         if(auth()->user()->hasRole('super users')){
            $users = Department::whereIn('id', $users)
                ->pluck('title')
                ->implode(', '); 

                $text = $text? $text: 'Task assigned to ';
                $this->addToTaskActivity($users, $text, $task); 
        }
    }

    public function getExtraFileName($path){
        $parts = explode('/', $path);
        $lastPart = end($parts);
        return $lastPart;
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

    public function isUserPermittedToViewTask($task){
        $user = auth()->user();
        //for directors
        if($user->hasRole('directors')){
            if($task->assigned_by_department == $user->department_id){
                return true;
            }

            if($task->superuserTask->count() > 0){
                $super_user = $task->superuserTask->where('department_id', $user->department_id);
                if($super_user){
                    return true;
                }
            }
            return false;
        }

        //for division heads
        if($user->hasRole('division heads')){
            if($task->assigned_by_division == $user->division_id){
                return true;
            }

            if($task->divisionTask->count() > 0){ 
                $division_task = $task->divisionTask->where('division_id', $user->division_id);
                if($division_task){
                    return true;
                }
            }
            return false;
        }

        //for unit heads
        if($user->hasRole('unit heads')){
            if($task->assigned_by_unit == $user->unit_id){
                return true;
            }
            if($task->unitTask->count() > 0){
                $unit_task = $task->unitTask->where('unit_id', $user->unit_id);
                if($unit_task){
                    return true;
                }
            }
            return false;
        }

        //for super admin, admin and super users, they have all access
        if($user->hasRole('super admin') || $user->hasRole('super users') || $user->hasRole('admin')){
            return true;
        }

        //for users
        if($user->hasRole('regular users')){
            if($user->user->count() > 0){
                $found = $user->user->where('user_id', $user->id);
                if($found){
                    return true;
                }
            }
            return false;
        }
    }

    public function viewTask(Request $request, $id) {

      //mark notification as read if found
      if($request->notification_id){
        $notification = DB::table('notifications')
            ->where('id', $request->notification_id)
            ->update(['read_at'=> \Carbon\Carbon::now()]);
      }

        $task = Task::with(['unitTask', 'superuserTask', 'taskActivity' => function($query){
            $query->with('user')->oldest();
        }, 'divisionTask', 'departmentTask', 'taskdocs','user', 'rating', 'taskSubmission'=>function($query){
            $query->with('taskSubmissionFiles', 'user');
        }, 'comments' => function($query) {
            $query->with(['user' => function($query) {
                $query->select('id', 'first_name', 'last_name','profile_image');
            }])->latest();
        }])->find($id);

        // if(!$this->isUserPermittedToViewTask($task)){
        //     return redirect()->to('dashboard/workflow');
        // }

       $user = auth()->user();
       if($user->hasRole('super users')){
        if($task->assigned_by_super_user == $user->id){
            $is_assigned = 2;
        }
       }else
       if($user->hasRole('directors')){
        if($task->assigned_by_department == $user->department_id){
            $is_assigned = 2;
        }else{
            $is_assigned = 1;
        }
       }else
       if($user->hasRole('division heads')){
       
        if($task->assigned_by_division == $user->division_id){
            $is_assigned = 2;
        }else{
            $is_assigned = 1;
        }
       }else
       if($user->hasRole('unit heads')){
        if($task->assigned_by_unit == $user->unit_id){
            $is_assigned = 2;
        }else{
            $is_assigned = 1;
        }
       }else{
        $is_assigned = 1;
       }
        
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
   
        $comment = new Comment();
        $comment->comment = $request->comment;
        $comment->task_id = $request->task_id;
        $comment->user_id = auth()->user()->id;
        $comment->save();

        $comment->load('user');

        $user = auth()->user();
        /**
         * handles notifications
         * is_assigned = 2  when a user assigns a task and 1 when it was assigned to a user
         * */
        //$task = Task::with(['user','unitTask', 'divisionTask', 'departmentTask'])->find($request->task_id);
        // if($task->user && $task->user->count() > 0){
        //    $users = $task->user;
        //    Notification::send($users, new CommentNotification($comment,$users));
        // }

        if($request->is_assigned == 2){

             //comment communication from top to down
            if($user->hasRole('super users')){
                $director = User::role('directors')->get();
                Notification::send($director, new CommentNotification($comment,$director));
            }
            if($user->hasRole('directors')){
                $div_head = User::role('division heads')->get();
                Notification::send($div_head, new CommentNotification($comment,$div_head));
            }
            if($user->hasRole('division heads')){
                $unit_head = User::role('unit heads')->get();
                Notification::send($unit_head, new CommentNotification($comment,$unit_head));
            }
            if($user->hasRole('unit heads')){
               
                $task = Task::with(['user'])->find($request->task_id);
                if($task->user && $task->user->count() > 0){
                    $users = $task->user;
                    Notification::send($users, new CommentNotification($comment,$users));
                }
            }
        }else{
            $task = Task::with(['assignedByUnit','assignedByDivision', 'assignedByDepartment', 'assignedBySuperUser'])->find($request->task_id);
        
            if($task->assignedByUnit){
                $user = User::role('unit heads')->first();
                Notification::send($user, new CommentNotification($comment,$user));
            }
            if($task->assignedByDivision){
                $user = User::role('division heads')->first();
                Notification::send($user, new CommentNotification($comment,$user));
            }
            if($task->assignedByDepartment){
                $user = User::role('directors')->first();
                Notification::send($user, new CommentNotification($comment,$user));
            }
            if($task->assigned_by_super_user){
                $user = User::role('super users')->first();
                Notification::send($user, new CommentNotification($comment,$user));
            }
        }

        //task activity
        $ta = new TaskActivity();
        $ta->task_id = $request->task_id;
        $ta->description = 'Added Comments & Feedback:';
        $ta->created_by = auth()->user()->id;
        $ta->save();
        
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

        try {
            DB::beginTransaction();

        if($request->users){
            $user_ids = [];
            foreach($request->users as $user){
                array_push($user_ids, $user['id']);
            }

            $task = Task::with('taskdocs')->find($request->task_id);
            $task->status = 'in progress';
            $task->save();
            
            $task2 = $task->replicate();
            if($this->isRole('unit heads')){
                $task2->assigned_by = auth()->user()->id;
                $task2->assigned_by_unit = auth()->user()->unit_id;
                $task2->assigned_by_division = null;
                $task2->assigned_by_department = null;
                if($request->description){
                    $task->description = $request->description;
                }
                $task2->status = "pending";
                $task2->save();
                $task2->user()->sync($user_ids);

                $this->insertTaskDoc($task, $task2);

                //task activity
                $this->assignedTaskActivity($user_ids, 'unit heads', $task, 'Task Re-assigned to ');
            }

            if($this->isRole('division heads')){
                $task2->assigned_by_division = auth()->user()->division_id;
                $task2->assigned_by_department = null;
                if($request->description){
                    $task->description = $request->description;
                }
                $task2->status = "pending";
                $task2->save();

                $branch_ids = $this->getBranchIds($request->users);
                $task2->unitTask()->sync($branch_ids);

                $this->insertTaskDoc($task, $task2);

                 //task activity
                 $this->assignedTaskActivity($branch_ids, 'unit heads', $task, 'Task Re-assigned to ');
            }

            if($this->isRole('directors')){
                $task2->assigned_by_department = auth()->user()->department_id;
                if($request->description){
                    $task->description = $request->description;
                }
                $task2->status = "pending";
                $task2->save();
                $branch_ids = $this->getBranchIds($request->users);
                $task2->divisionTask()->sync($branch_ids);

                $this->insertTaskDoc($task, $task2);

                 //task activity
                 $this->assignedTaskActivity($branch_ids, 'unit heads', $task, 'Task Re-assigned to ');
            }

            if($this->isRole('super users')){
                $task2->assigned_by_super_user = auth()->user()->id;
                if($request->description){
                    $task->description = $request->description;
                }
                $task2->status = "pending";
                $task2->save();
                $branch_ids = $this->getBranchIds($request->users);
                $task2->departmentTask()->sync($branch_ids);

                $this->insertTaskDoc($task, $task2);

                //task activity
                $this->assignedTaskActivity($branch_ids, 'unit heads', $task, 'Task Re-assigned to ');
            }
            
        }
        DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
       
        $title = 'New task assigned';
        if($this->isRole('unit heads')){
             $users = User::whereIn('id', $user_ids)->get();
           // Notification::send($users, new TaskNotification($title));
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

        //task activity
        // $ta = new TaskActivity();
        // $ta->task_id = $request->id;
        // $ta->description = 'Task marked as completed';
        // $ta->created_by = auth()->user()->id;
        // $ta->save();
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

        //task activity
        // $ta = new TaskActivity();
        // $ta->task_id = $request->id;
        // $ta->description = 'Requested Revision';
        // $ta->created_by = auth()->user()->id;
        // $ta->save();
        return redirect()->back();
    }

    public function reopenTask(Request $request){
        $task = Task::find($request->id);
        $task->status = 'pending';
        $task->save();

        //task activity
        // $ta = new TaskActivity();
        // $ta->task_id = $request->id;
        // $ta->description = 'Task reopened';
        // $ta->created_by = auth()->user()->id;
        // $ta->save();
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
