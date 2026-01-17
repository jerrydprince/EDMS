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
use App\Models\TaskAssignment;
use App\Models\TaskAssignmentComments;
use App\Notifications\TaskChangedNotification;

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
               // 'users' => 'required'
            ]);
        }

        $files = [];

        //$branchType = $this->getBranchType();
    
        try {
            DB::beginTransaction();
            if($request->file('files')){
                foreach($request->file('files') as $file){
                 
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
           
            // if(auth()->user()->hasRole('unit heads')){
            //     $task->assigned_by = auth()->user()->id;
            //     $task->assigned_by_unit = auth()->user()->unit_id;
            //     $task->created_by_branch = auth()->user()->unit_id;
            // }
            // if(auth()->user()->hasRole('division heads')){
            //     $task->assigned_by_division = auth()->user()->division_id;
            //     $task->created_by_branch = auth()->user()->division_id;
            // }
            // if(auth()->user()->hasRole('directors')){
            //     $task->assigned_by_department = auth()->user()->department_id;
            //     $task->created_by_branch = auth()->user()->department_id;
            // }
            if(!$request->id){
                $task->status = 'pending';
            }
            // if(auth()->user()->hasRole('super users')){
            //     $task->assigned_by_super_user = auth()->user()->id;
            // }
            $task->created_by = auth()->user()->id;
            $task->save();
    
            $docs = [];
            //if the task has some files, save the files
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
                      'size' => $file['size'],
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
            // $ta = new TaskActivity();
            // $ta->description = "Task created";
            // $ta->task_id = $task->id;
            // $ta->created_by = auth()->user()->id;
            // $ta->save();
           if(!$request->id){
             $description = "Task created";
                $task_id = $task->id;
                $this->newTaskActivity($task_id, $description);
           }


            //$ids = explode(',', $request->users);
            if(!$request->id){
                // if(auth()->user()->hasRole('unit heads')){
                //     $task->user()->sync($request->users); 
                //     $users = $task->user()->get();

                //     //task activities
                //     $this->assignedTaskActivity($request->users, 'unit heads', $task);
                // }

                // if(auth()->user()->hasRole('division heads')){
                //     //branch can only assign to units
                //     $task->unitTask()->sync($request->users);
                //     $users = User::role('unit heads')->get();

                //     //task activities
                //     $this->assignedTaskActivity($request->users, 'divisions heads', $task);
                // }

                // if(auth()->user()->hasRole('directors')){
                //     //department can assign a task to divisions
                //     $task->divisionTask()->sync($request->users);
                //     $users = User::role('division heads')->get();
                //     //$task->departmentTask()->sync($request->users);

                //     //task activities
                //     $this->assignedTaskActivity($request->users, 'directors', $task);
                // }

                // if(auth()->user()->hasRole('super users')){
                //    // $task->superuserTask()->sync($request->users);
                //    $task->departmentTask()->sync($request->users);
                //    $users = User::role('directors')->get();

                //    //task activities
                //    $this->assignedTaskActivity($request->users, 'super users', $task);
                // }
               
                 //user notification
                // $users = User::whereIn('id', $request->users)->get();

                //assign task if there are users
                if($request->users){
                    $users = $request->users;
                    $task_assignments = [];
                    foreach($users as $user){
                        $data = [
                            'task_id' => $task->id,
                            'assigned_to' => $user,
                            'assigned_by' => auth()->user()->id,
                            'status' => 'pending',
                            //'due_date' => $request->due_date,
                            'created_at' => now()
                        ];
                        array_push($task_assignments, $data);
                    }
                    TaskAssignment::insert($task_assignments);

                    //notify users
                   
                        $user = User::find(auth()->user()->id);
                        $users = User::where('id', $request->users)->get();
                        Notification::send($users, new TaskChangedNotification($task->id, $user, 'assigned'));
                    
                }

            }else{
                $users = $task->user()->get();
            }

           DB::commit();
           //$users = User::where('id', $request->users)->get();
           //dd($users);
           
            //broadcast()
           
        } catch (Exception $e) {
            DB::rollback();
            //dd($e->getMessage());
        }

        return redirect()->back();
    }

    public function newTaskActivity($task_id, $description){
            $ta = new TaskActivity();
            $ta->description = $description;
            $ta->task_id = $task_id;
            $ta->created_by = auth()->user()->id;
            $ta->save();
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

        // $task = Task::with(['assignedToMe'=>function($q){
        //     $q->with(['assignedBy', 'comments'=>function($q){
        //         $q->with('sentByUser')->latest();
        //     }, 'taskSubmissions'=>function($q){
        //         $q->with('taskSubmissionFiles');
        //     }]);
        // }, 'assignments'=>function($query){
        //     $query->with(['comments'=>function($q){
        //         $q->with('sentToUser')->latest();
        //     }, 'taskSubmissions'=>function($q){
        //         $q->with('taskSubmissionFiles');
        //     },'assignedTo'=>function($q){
        //         $q->with(['department', 'division', 'unit','roles']);
        //     }])->where('assigned_by', auth()->user()->id);
        // }, 'taskActivity' => function($query){
        //     $query->with('user')->oldest();
        // },  'taskdocs', 'rating', 'taskSubmission'=>function($query){
        //     $query->with('taskSubmissionFiles', 'user');
        // }, 'comments' => function($query) {
        //     $query->with(['user' => function($query) {
        //         $query->select('id', 'first_name', 'last_name','profile_image');
        //     }])->latest();
        // }])->find($id);

        $task = Task::with(['assignedToMe'=>function($q){
            $q->with(['assignedBy', 'comments'=>function($q){
                $q->with('sentByUser')->latest();
            }, 'taskSubmissions'=>function($q){
                $q->with('taskSubmissionFiles');
            }]);
        }, 'assignments'=>function($query){
            $query->with(['rating','comments'=>function($q){
                $q->with('sentToUser')->latest();
            }, 'taskSubmissions'=>function($q){
                $q->with('taskSubmissionFiles');
            },'assignedTo'=>function($q){
                $q->with(['department', 'division', 'unit','roles']);
            }])->where('assigned_by', auth()->user()->id);
        }, 'taskActivity' => function($query){
            $query->with('user')->oldest();
        },  'taskdocs'])->find($id);
//dd($task);

        //  $task = Task::with('assignedToMe', ['assignments'=>function($query){
        //     $query->with(['assignedTo'=>function($q){
        //         $q->with(['department', 'division', 'unit']);
        //     }, 'assignedBy']);
        //  }, 'taskActivity' => function($query){
        //     $query->with('user')->oldest();
        // },  'taskdocs', 'rating', 'taskSubmission'=>function($query){
        //     $query->with('taskSubmissionFiles', 'user');
        // }, 'comments' => function($query) {
        //     $query->with(['user' => function($query) {
        //         $query->select('id', 'first_name', 'last_name','profile_image');
        //     }])->latest();
        // }])->find($id);

        // if(!$this->isUserPermittedToViewTask($task)){
        //     return redirect()->to('dashboard/workflow');
        // }

       $user = auth()->user();
       if($user->hasRole('super users')){
        $is_assigned = 2;
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

    public function makeComment($comment_to, $comment, $comment_obj){
        $user = user::find(auth()->user()->id);
       
        $to = User::find($comment_to);
        Notification::send($to, new CommentNotification($comment,$user, $comment_obj));
           
    }

    public function taskComment(Request $request){
 
        $comment = new TaskAssignmentComments();
        $comment->task_assignment_id = $request->task_assignment_id;
        $comment->sent_by = auth()->user()->id;
        $comment->sent_to = $request->comment_to;
        $comment->comments = $request->comment;
        $comment->comment_by = auth()->user()->id;
        $comment->save();

        //notify user
        $comment_obj = $comment->load('sentByUser');
        $this->makeComment($request->comment_to, $request->comment, $comment_obj);

        // $comment = new Comment();
        // $comment->comment = $request->comment;
        // $comment->task_id = $request->task_id;
        // $comment->user_id = auth()->user()->id;
        // $comment->save();

        // $comment->load('user');

        // $user = auth()->user();
        /**
         * handles notifications
         * is_assigned = 2  when a user assigns a task and 1 when it was assigned to a user
         * */
        //$task = Task::with(['user','unitTask', 'divisionTask', 'departmentTask'])->find($request->task_id);
        // if($task->user && $task->user->count() > 0){
        //    $users = $task->user;
        //    Notification::send($users, new CommentNotification($comment,$users));
        // }

        // if($request->is_assigned == 2){

        //      //comment communication from top to down
        //     if($user->hasRole('super users')){
        //         $director = User::role('directors')->get();
        //         Notification::send($director, new CommentNotification($comment,$director));
        //     }
        //     if($user->hasRole('directors')){
        //         $div_head = User::role('division heads')->get();
        //         Notification::send($div_head, new CommentNotification($comment,$div_head));
        //     }
        //     if($user->hasRole('division heads')){
        //         $unit_head = User::role('unit heads')->get();
        //         Notification::send($unit_head, new CommentNotification($comment,$unit_head));
        //     }
        //     if($user->hasRole('unit heads')){
               
        //         $task = Task::with(['user'])->find($request->task_id);
        //         if($task->user && $task->user->count() > 0){
        //             $users = $task->user;
        //             Notification::send($users, new CommentNotification($comment,$users));
        //         }
        //     }
        // }else{
        //     $task = Task::with(['assignedByUnit','assignedByDivision', 'assignedByDepartment', 'assignedBySuperUser'])->find($request->task_id);
        
        //     if($task->assignedByUnit){
        //         $user = User::role('unit heads')->first();
        //         Notification::send($user, new CommentNotification($comment,$user));
        //     }
        //     if($task->assignedByDivision){
        //         $user = User::role('division heads')->first();
        //         Notification::send($user, new CommentNotification($comment,$user));
        //     }
        //     if($task->assignedByDepartment){
        //         $user = User::role('directors')->first();
        //         Notification::send($user, new CommentNotification($comment,$user));
        //     }
        //     if($task->assigned_by_super_user){
        //         $user = User::role('super users')->first();
        //         Notification::send($user, new CommentNotification($comment,$user));
        //     }
        // }

        //task activity
        // $ta = new TaskActivity();
        // $ta->task_id = $request->task_id;
        // $ta->description = 'Added Comments & Feedback:';
        // $ta->created_by = auth()->user()->id;
        // $ta->save();
        
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

    // public function AssignTask(Request $request){

    //     try {
    //         DB::beginTransaction();

    //     if($request->users){
    //         //  $description = "Task created";
    //         //     $task_id = $task->id;
    //         //     $this->newTaskActivity($task_id, $description);
  
        
    //         $task_assignments = [];

    //         foreach($request->users as $user){
    //              if(isset($user['users']) && count($user['users']) > 0){
    //                 foreach($user['users'] as $us){
    //                      $data = [
    //                         'task_id' => $request->task_id,
    //                         'assigned_to' => $us['id'],
    //                         'assigned_by' => auth()->user()->id,
    //                         'status' => 'pending',
    //                         //'due_date' => $request->due_date,
    //                         'created_at' => now()
    //                     ];
    //                     array_push($task_assignments, $data);

    //                     //add to task activity
    //                     $description = "Task assigned to ".$us['last_name']." ".$us['first_name'];
    //                     $task_id = $request->task_id;
    //                     $this->newTaskActivity($task_id, $description);
                
    //                 }
      
    //                  TaskAssignment::insert($task_assignments);
    //          //send notification
    //         $title = 'New task assigned';
    //         $user_ids = collect($task_assignments)->pluck('assigned_to');
    //         $users = User::whereIn('id', $user_ids)->get();
                   
    //             }else{

    //             }
    //         }

    //     }
    //     DB::commit();
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //     }
       
    //     return redirect()->back();
    // }

    public function AssignTask(Request $request){
    try {
        DB::beginTransaction();

        // Validate request
        // $request->validate([
        //     'users' => 'required|array|min:1',
        //     'task_id' => 'required|exists:tasks,id',
        //     'description' => 'nullable|string'
        // ]);

        // Get the task
        $task = Task::findOrFail($request->task_id);

        // Optional: Remove old assignments if this is a reassignment
        // TaskAssignment::where('task_id', $request->task_id)->delete();

        $task_assignments = [];
        $user_ids = [];
        $user_names = [];

        foreach($request->users as $user){
            // Handle both branch/group assignments and direct user assignments
            if(isset($user['users']) && is_array($user['users']) && count($user['users']) > 0){
                // This is a branch/department/division with multiple users
                foreach($user['users'] as $us){
                    // Check if user is already assigned
                    $existingAssignment = TaskAssignment::where('task_id', $request->task_id)
                        ->where('assigned_to', $us['id'])
                        ->first();
                   
                    if(!$existingAssignment){
                        $data = [
                            'task_id' => $request->task_id,
                            'assigned_to' => $us['id'],
                            'assigned_by' => auth()->user()->id,
                            'status' => 'pending',
                            'description' => $request->description,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                        array_push($task_assignments, $data);
                        array_push($user_ids, $us['id']);
                        
                        $userName = trim(($us['last_name'] ?? '') . ' ' . ($us['first_name'] ?? ''));
                        array_push($user_names, $userName);
                    }
                }
            } else {
                // Direct user assignment
                // Check if user is already assigned
                $existingAssignment = TaskAssignment::where('task_id', $request->task_id)
                    ->where('assigned_to', $user['id'])
                    ->first();
                
                if(!$existingAssignment){
                    $data = [
                        'task_id' => $request->task_id,
                        'assigned_to' => $user['id'],
                        'assigned_by' => auth()->user()->id,
                        'status' => 'pending',
                        'description' => $request->description,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    array_push($task_assignments, $data);
                    array_push($user_ids, $user['id']);
                    
                    // Handle both regular users and branch types
                    if(isset($user['title'])){
                        // This is a branch/department/division
                        array_push($user_names, $user['title']);
                    } else {
                        $userName = trim(($user['last_name'] ?? '') . ' ' . ($user['first_name'] ?? ''));
                        array_push($user_names, $userName);
                    }
                }
             }
        }

        // Insert new assignments
        if(count($task_assignments) > 0){
            TaskAssignment::insert($task_assignments);

            //change the task status to in progress
            $task_assignment_count = TaskAssignment::where('task_id', $request->task_id)->count();
            if($task_assignment_count >= 2){
                $task->status = 'in progress';
                $task->save();
            }
           
            // Log activity for each new assignment
            foreach($user_names as $userName){
                $description = "Task reassigned to " . $userName;
                $this->newTaskActivity($request->task_id, $description);
            }

            // Send notifications to newly assigned users
            $users = User::whereIn('id', array_unique($user_ids))->get();
            $assigner = User::find(auth()->user()->id);
           Notification::send($users, new TaskChangedNotification($task->id, $assigner, 'assigned'));
        }
          
        //     foreach($users as $user){
        //         // Add your notification logic here
        //         // Example: 
        //         // $user->notify(new TaskReassignedNotification($task));
        //         // or
        //         // Notification::send($user, new TaskReassignedNotification($task));
        //     }
        // }

        DB::commit();
        
        return redirect()->back()->with('success', 'Task reassigned successfully');
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollback();
        return redirect()->back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        DB::rollback();
        \Log::error('Task reassignment error: ' . $e->getMessage(), [
            'task_id' => $request->task_id ?? 'unknown',
            'error' => $e->getTraceAsString()
        ]);
        return redirect()->back()->with('error', 'Failed to reassign task. Please try again.');
    }
}

    public function getBranchType(){
        $user = auth()->user();
        if($user->hasRole('unit heads')){
            return 'unit';
        }else
        if($user->hasRole('division heads')){
            return 'division';
        }else
        if($user->hasRole('directors')){
            return 'department';
        }else if($user->hasRole('super users')){
            return 'super users';
        }else{
            return 'users';
        }
    }

    public function taskSubmission(Request $request){
       DB::transaction(function () use($request) {

        $task_assignment = TaskAssignment::where('task_id', $request->task_id)
            ->where('assigned_to', $request->assigned_to)
            ->where('assigned_by', $request->assigned_by)
            ->first();

        if($task_assignment){
            $task_assignment->status = 'review';
            $task_assignment->save();

            //task submissions
            $submission = new TaskSubmission();
            $submission->description = $request->description;
            $submission->task_id = $request->task_id;
            $submission->user_id = auth()->user()->id;
            $submission->task_assignment_id = $request->task_assignment_id;
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
                        'task_assignment_id' => $request->task_assignment_id,
                        'name' => $original_name,
                        'size'=>$size
                    ];

                    array_push($files, $arr);
                }

                $tsf = new TaskSubmissionFiles();
                $tsf->insert($files);

            }

            $description = auth()->user()->last_name." ".auth()->user()->first_name." Submitted a task";
            $task_id = $request->task_id;
            $this->newTaskActivity($task_id, $description);

            //if the user submitting is first on the assignment list, change the task status to under review
            if ($task_assignment) {
                $firstAssignment = TaskAssignment::where('task_id', $task_assignment->task_id)
                    ->orderBy('created_at', 'asc')
                    ->value('id');

                $isFirstForTask = $task_assignment->id === $firstAssignment;
                if ($isFirstForTask) {
                    $task = Task::find($request->task_id);
                    $task->status = 'under review';
                    $task->save();
                }
            }

            //notify user
            $user = auth()->user()->id;
            $task = Task::find($request->task_id);
             $sent_to = User::where('id', $request->assigned_by)->first();
             Notification::send($sent_to, new TaskNotification($task,$user));
        }

        // $task = Task::find($request->task_id);
        // $task->status = 'under review';
        // $task->save();
       });

        return redirect()->back();
    }

    public function markAsComplete(Request $request){
        if($request->id && $request->assignment && count($request->assignment) > 0){
            $assignment = collect($request->assignment)->first();
            $assigned_to = $assignment['assigned_to'];
            $assigned_by = $assignment['assigned_by'];

            TaskAssignment::where('task_id', $request->id)
                ->where('assigned_to', $assigned_to)
                ->where('assigned_by', $assigned_by)
                ->update(['status'=> 'completed']);

            //mark All task as complete by the super user
            if(auth()->user()->hasRole('super users')){
    
                TaskAssignment::where('task_id', $request->id)
                ->update(['status'=> 'completed']);

                $task = Task::find($request->id);
                $task->status = 'completed';
                $task->save();

                //mark the task
                 $description = auth()->user()->last_name." ".auth()->user()->first_name." marked all task as completed";
                $task_id = $request->id;
                $this->newTaskActivity($task_id, $description);
            }
           
            
           if(!auth()->user()->hasRole('super users')){
                $description = auth()->user()->last_name." ".auth()->user()->first_name." Marked task as complete";
                $task_id = $request->id;
                $this->newTaskActivity($task_id, $description);
           }

            //notify user
            $user = User::find(auth()->user()->id);
            $task = Task::find($request->id);
             $sent_to = User::where('id', $assigned_to)->first();
            Notification::send($sent_to, new TaskChangedNotification($task->id, $user, 'completed'));
        }
        return redirect()->back();
    }

    public function requestRevision(Request $request){
         $task = Task::find($request->id);
        // $task->status = 'revision';
        // $task->save();
      
        if($request->id && $request->task_assignment_id){
         
            $task_assignment = TaskAssignment::where('task_id', $request->id)
                ->where('assigned_to', $request->assigned_to)
                ->where('assigned_by', $request->assigned_by)
                ->first();
            
            $task_assignment->status = 'revision';
            $task_assignment->save();

            //update every other assignment after this
            TaskAssignment::where('id', '>=', $task_assignment->id)
                ->update(['status'=> 'revision']);

             if($request->description){
                $comment = new Comment();
                $comment->comment = $request->description;
                $comment->task_id =$request->id;
                $comment->user_id = auth()->user()->id;
                $comment->save();
            }

            $description = auth()->user()->last_name." ".auth()->user()->first_name." Requested a revision";
            $task_id = $request->id;
            $this->newTaskActivity($task_id, $description);

            //notify user
            $user = User::find(auth()->user()->id);
             $sent_to = User::where('id', $request->assigned_to)->first();
             Notification::send($sent_to, new TaskChangedNotification($task->id, $user, 'rejected'));

             //for super user
             if(auth()->user()->hasRole('super users')){
                // $task->status = 'completed';
                // $task->save();
             }

        return redirect()->back();
        }
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

    public function forwardTask(Request $request){
        if($request->task){
            if(isset($request->task['assigned_to_me'])){
                $assigned_to_me = $request->task['assigned_to_me']['assigned_to'];
                $assigned_by = $request->task['assigned_to_me']['assigned_by'];
                $task_id = $request->task['assigned_to_me']['task_id'];
                $assigned_task_assignment_id = $request->task['assigned_to_me']['id'];

                //check if these values exist in the assigned to me values
                if($assigned_to_me && $assigned_by && $task_id && $assigned_task_assignment_id ){
                    if($request->task['assignments'] && count($request->task['assignments'])> 0){
                        $assignments = $request->task['assignments'];
                
                        //check my assignment section
                        if(count($assignments) > 0 && isset($assignments[0]['assigned_to'])){
                            $assigned_to = $assignments[0]['assigned_to']['id'];
                            $task_assignment_id = $assignments[0]['id'];

                            // $task_submissions = TaskSubmission::with('taskSubmissionFiles')->where('task_id', $task_id)
                            //     ->where('user_id', $assigned_to)
                            //     ->where('task_assignment_id', $task_assignment_id)
                            //     ->first();
                            $task_submissions = TaskSubmission::with('taskSubmissionFiles')->find($request->task_submission_id);

                            //if task submissions exist then move on to forward the task
                            if($task_submissions){
                                //check for existing submission for this user
                                $submissions = TaskSubmission::with('taskSubmissionFiles')->where('task_id', $task_id)
                                    ->where('user_id', $assigned_to_me)
                                    ->where('task_assignment_id', $assigned_task_assignment_id)
                                    ->first();
                               
                                if($submissions){
                                    $submissions->delete();
                                }
                                    $task_sub = new TaskSubmission();
                                    $task_sub->task_id = $task_submissions->task_id;
                                    $task_sub->description = "";
                                    $task_sub->is_revision = 0;
                                    $task_sub->user_id = $assigned_to_me;
                                    $task_sub->task_assignment_id = $assigned_task_assignment_id;
                                    $task_sub->save();
                                

                                //check if it has some files
                                $task_submission_files = $task_submissions->taskSubmissionFiles;
                                if(count($task_submission_files) > 0){
                                    //delete old files if it exist
                                    TaskSubmissionFiles::where('task_submissions_id', $task_sub->id)
                                        ->where('task_assignment_id', $assigned_task_assignment_id)
                                        ->where('task_id', $task_submissions->task_id)
                                        ->delete();

                                        $insert_data = [];
                                    foreach($task_submission_files as $file){
                                        array_push($insert_data, [
                                            'path' => $file['path'],
                                            'task_submissions_id' => $task_sub->id,
                                            'task_assignment_id' => $assigned_task_assignment_id,
                                            'task_id' => $task_submissions->task_id,
                                            'name' => $file['name'],
                                            'size' => $file['size'],
                                            'created_at' => now(),
                                        ]);
                                    }
                                    //insert the data
                                    TaskSubmissionFiles::insert($insert_data);
                                }

                                //update the task assignment status
                                TaskAssignment::where('id', $assigned_task_assignment_id)
                                    ->update(['status'=>'review']);

                                //notify user
                                $user = User::find(auth()->user()->id);
                                $task = Task::find($task_id);
                                 $sent_to = User::where('id',  $assigned_to)->first();
                                 Notification::send($sent_to, new TaskChangedNotification($task->id, $user, 'submitted'));
                            }
                        }
                    }

                }
                
            }
        }

    //   if($request->branchType == 'unit'){
    //     $task = Task::with('unitTask')->find($request->task['assigned_by_division']);
    //     $branch_ids = $task->unitTask->pluck('id')->toArray();
    //     $task->divisionTask()->sync($branch_ids);
    //   }
    }
}
