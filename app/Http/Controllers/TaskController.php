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
use App\Models\Groups;
use App\Models\categories;
use App\Services\PdfSignatureService;
use App\Services\AuditTrailService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function createTask(Request $request){
    
        if ($request->has('workflow_version_id') && $request->workflow_version_id != '') {
            $version = \App\Models\WorkflowVersion::with('workflow.department')->find($request->workflow_version_id);
            if ($version && $version->workflow && !$version->workflow->canBeInitiatedBy(auth()->user())) {
                $deptTitle = $version->workflow->department?->title ?? $version->workflow->department?->name ?? 'Department';
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'workflow_version_id' => "Unauthorized: Departmental workflows can only be initiated by the Director of {$deptTitle}."
                ]);
            }

            $request->validate([
                'title' => 'required',
                'approval_pin' => 'required|string',
            ]);
        } else {
            if($request->id){
                $request->validate([
                    'title' => 'required',
                    'due_date' => 'required',
                    'priority' => 'required',
                    'approval_pin' => 'required|string',
                ]);
            }else{
                $request->validate([
                    'title' => 'required',
                    'due_date' => 'required',
                    'priority' => 'required',
                    'approval_pin' => 'required|string',
                ]);
            }
        }

        $isSigned = false;
        $user = auth()->user();
        \Log::info('PIN check createTask', ['user_pin' => $user->approval_pin, 'req_pin' => $request->approval_pin]);
        if ((string)$user->approval_pin !== (string)$request->approval_pin) {
            AuditTrailService::logSecurity('invalid_approval_pin_attempt', 'Invalid digital signature PIN attempt during task creation', $user, null, ['action' => 'createTask'], 'warning');
            throw \Illuminate\Validation\ValidationException::withMessages(['approval_pin' => 'Invalid digital signature PIN.']);
        }
        if (!$user->digital_signature_path || !file_exists(public_path($user->digital_signature_path))) {
            throw \Illuminate\Validation\ValidationException::withMessages(['approval_pin' => 'You do not have a digital signature configured in your profile.']);
        }
        $isSigned = true;

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
           
            // if(auth()->user()->hasRole('assistant director')){
            //     $task->assigned_by = auth()->user()->id;
            //     $task->assigned_by_unit = auth()->user()->unit_id;
            //     $task->created_by_branch = auth()->user()->unit_id;
            // }
            // if(auth()->user()->hasRole('deputy director')){
            //     $task->assigned_by_division = auth()->user()->division_id;
            //     $task->created_by_branch = auth()->user()->division_id;
            // }
            // if(auth()->user()->hasRole('director')){
            //     $task->assigned_by_department = auth()->user()->department_id;
            //     $task->created_by_branch = auth()->user()->department_id;
            // }
            if(!$request->id){
                $task->status = 'pending';
            }
            // if(auth()->user()->hasRole('super user')){
            //     $task->assigned_by_super_user = auth()->user()->id;
            // }
            $task->created_by = auth()->user()->id;
            
            if ($request->has('allow_step_splitting')) {
                $task->allow_step_splitting = filter_var($request->allow_step_splitting, FILTER_VALIDATE_BOOLEAN);
            }

            // Apply generic workflow mapping if provided
            if ($request->has('workflow_version_id')) {
                $task->workflow_version_id = $request->workflow_version_id;
            }
            
            if ($request->has('form_data')) {
                $task->form_data = is_string($request->form_data) ? json_decode($request->form_data, true) : $request->form_data;
            }

            if ($request->has('mail_id')) {
                $task->mail_id = $request->mail_id;
            }

            if ($request->has('file_id')) {
                $task->file_id = $request->file_id;
            }
            
            $task->save();

            if (!$request->id) {
                if ($task->workflow_version_id) {
                    $genericWorkflowService = app(\App\Services\GenericWorkflowService::class);
                    // Pass assigned_to if this task needs initial dynamic assignments
                    $genericWorkflowService->start($task, auth()->user(), $request->input('assigned_to', []));

                    // When an official application is created and digitally signed, the initiation IS the submission upward.
                    // Automatically execute the initial transition out of the start step so it lands on the reviewing authority's desk.
                    if ($isSigned) {
                        $initialActions = $genericWorkflowService->getAvailableActions($task, auth()->user());
                        if ($initialActions->isNotEmpty()) {
                            $firstAction = $initialActions->first();
                            $genericWorkflowService->executeAction(
                                $task,
                                auth()->user(),
                                $firstAction,
                                [],
                                'Application officially signed and transmitted upward along the chain of command.'
                            );
                        }
                    }
                } else {
                    $workflowEngine = app(\App\Services\WorkflowEngine::class);
                    $workflowEngine->initializeWorkflow($task, auth()->user());
                }
            }
    
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

             // add extra files / pre-generated template files
             if($request->filled('extra_files') || $request->has('extra_files')){
                $extraFiles = $request->extra_files;
                if (is_string($extraFiles)) {
                    $extraFiles = json_decode($extraFiles, true) ?: [];
                }
                if (!empty($extraFiles) && is_array($extraFiles)) {
                    $extraDocs = [];
                    foreach($extraFiles as $file){
                        if (!empty($file['path'])) {
                            $extraDocs[] = [
                              'path' => $file['path'],
                              'task_id' => $task->id,
                              'size' => $file['size'] ?? '0 KB',
                              'file_name' => $file['original_name'] ?? $file['file_name'] ?? $this->getExtraFileName($file['path']),
                              'created_at' => \Carbon\Carbon::now(),
                              'updated_at' => \Carbon\Carbon::now()
                            ];
                        }
                    }
                    if (!empty($extraDocs)) {
                        TaskDoc::insert($extraDocs);
                    }
                }
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
                // if(auth()->user()->hasRole('assistant director')){
                //     $task->user()->sync($request->users); 
                //     $users = $task->user()->get();

                //     //task activities
                //     $this->assignedTaskActivity($request->users, 'assistant director', $task);
                // }

                // if(auth()->user()->hasRole('deputy director')){
                //     //branch can only assign to units
                //     $task->unitTask()->sync($request->users);
                //     $users = User::role('assistant director')->get();

                //     //task activities
                //     $this->assignedTaskActivity($request->users, 'divisions heads', $task);
                // }

                // if(auth()->user()->hasRole('director')){
                //     //department can assign a task to divisions
                //     $task->divisionTask()->sync($request->users);
                //     $users = User::role('deputy director')->get();
                //     //$task->departmentTask()->sync($request->users);

                //     //task activities
                //     $this->assignedTaskActivity($request->users, 'director', $task);
                // }

                // if(auth()->user()->hasRole('super user')){
                //    // $task->superuserTask()->sync($request->users);
                //    $task->departmentTask()->sync($request->users);
                //    $users = User::role('director')->get();

                //    //task activities
                //    $this->assignedTaskActivity($request->users, 'super user', $task);
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
                    
                    // Mark the current user's assignment as completed so it leaves their inbox
                    $existingAssignment = \App\Models\TaskAssignment::where('task_id', $task->id)
                        ->where('assigned_to', auth()->id())
                        ->where('status', '!=', 'completed')
                        ->first();
                    
                    if ($existingAssignment) {
                        $existingAssignment->status = 'completed';
                        $existingAssignment->save();
                    }

                    //notify users
                    try {
                        $user = User::find(auth()->user()->id);
                        $users = User::where('id', $request->users)->get();
                        Notification::send($users, new TaskChangedNotification($task->id, $user, 'assigned'));
                    } catch (\Throwable $e) {
                        \Log::warning('Task creation notification failed: ' . $e->getMessage());
                    }
                }

            }else{
                $users = $task->user()->get();
            }

           DB::commit();

           AuditTrailService::logWorkflow(
               'task_file_created',
               "Workflow task '{$task->title}' initiated (Ref #{$task->id})",
               $task,
               $user,
               [
                   'priority' => $task->priority,
                   'due_date' => $task->due_date,
                   'workflow_version_id' => $task->workflow_version_id
               ],
               'notice'
           );

           // Generate Minute Sheet for newly assigned users
           if ($request->users && count($request->users) > 0) {
               $assignedUsers = \App\Models\User::whereIn('id', $request->users)->get();
               $user_names = [];
               foreach ($assignedUsers as $u) {
                   $user_names[] = trim($u->last_name . ' ' . $u->first_name);
               }
               if (count($user_names) > 0) {
                   $action = 'Assigned to ' . implode(', ', $user_names);
                   $directive = $request->description ?? 'Please treat';
                   $minuteService = new \App\Services\MinuteSheetService();
                   $minuteService->appendMinute($task->id, auth()->id(), $action, $directive, $isSigned);
               }
           }

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('createTask exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
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
        if(auth()->user()->hasRole('assistant director')){
            $users = User::whereIn('id', $users)
                ->select(DB::raw("CONCAT(first_name,' ',last_name) AS full_name"))
                ->pluck('full_name')
                ->implode(', ');
            
                $text = $text? $text: 'Task assigned to ';
             $this->addToTaskActivity($users, $text, $task);   
        }
         if(auth()->user()->hasRole('deputy director')){
            $users = Unit::whereIn('id', $users)
                ->pluck('title')
                ->implode(', '); 

                $text = $text? $text: 'Task assigned to ';
                $this->addToTaskActivity($users, $text, $task); 
        }
         if(auth()->user()->hasRole('director')){
            $users = Division::whereIn('id', $users)
                ->pluck('title')
                ->implode(', '); 

                $text = $text? $text: 'Task assigned to ';
                $this->addToTaskActivity($users, $text, $task); 
        }
         if(auth()->user()->hasRole('super user')){
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
            $task = auth()->user()->task()->with(['assignedBy', 'workflowVersion', 'file'])->where('status', $request->status)->paginate(10);
        }else{
            $task = Task::with(['assignedBy', 'workflowVersion', 'file', 'assignedToMe'])->where('status', $request->status)->paginate(10);
        }
        return response()->json($task);
    }

    public function isUserPermittedToViewTask($task){
        $user = auth()->user();
        //for directors
        if($user->hasRole('director')){
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
        if($user->hasRole('deputy director')){
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
        if($user->hasRole('assistant director')){
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
        if($user->hasRole('super admin') || $user->hasRole('super user') || $user->hasRole('admin')){
            return true;
        }

        //for users
        if($user->hasRole('regular user')){
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
            $q->with(['assignedBy.department', 'comments'=>function($q){
                $q->with(['sentByUser.department', 'sentToUser.department', 'sender.department', 'user.department'])->latest();
            }, 'taskSubmissions'=>function($q){
                $q->with('taskSubmissionFiles');
            }]);
        }, 'assignments'=>function($query){
            $query->with(['assignedBy.department', 'rating','comments'=>function($q){
                $q->with(['sentByUser.department', 'sentToUser.department', 'sender.department', 'user.department'])->latest();
            }, 'taskSubmissions'=>function($q){
                $q->with('taskSubmissionFiles');
            },'assignedTo'=>function($q){
                $q->with(['department', 'division', 'unit','roles']);
            }])->orderBy('id', 'desc');
        }, 'taskActivity' => function($query){
            $query->with('user')->oldest();
        }, 'minutes' => function($q) {
            $q->with('user.department');
        }, 'workflowLogs' => function($q) {
            $q->with(['actionBy', 'actionTo'])->oldest();
        }, 'taskdocs', 'file', 'archivedFolder', 'informationRequests' => function($q) {
            $q->with(['requestedBy', 'requestedFrom'])->latest();
        }, 'documentVersions' => function($q) {
            $q->with(['createdBy.department', 'annotations.createdBy'])->orderBy('version', 'desc');
        }])->find($id);
        if (!$task) {
            abort(404, 'Task not found');
        }
        $this->authorize('view', $task);

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
       if($user->hasRole('super user')){
        $is_assigned = 2;
       }else
       if($user->hasRole('director')){
        if($task->assigned_by_department == $user->department_id){
            $is_assigned = 2;
        }else{
            $is_assigned = 1;
        }
       }else
       if($user->hasRole('deputy director')){
       
        if($task->assigned_by_division == $user->division_id){
            $is_assigned = 2;
        }else{
            $is_assigned = 1;
        }
       }else
       if($user->hasRole('assistant director')){
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

    public function downloadFile(Request $request)
    {
        // The route parameter could be a generic path, we should try to resolve it.
        $filename = $request->route('file_name');
        
        $taskDoc = \App\Models\TaskDoc::where('path', 'like', '%'.$filename.'%')->first();
        if ($taskDoc && $taskDoc->task_id) {
            $task = \App\Models\Task::find($taskDoc->task_id);
            if ($task) {
                $this->authorize('view', $task);
            }
        } else {
            // Also check task submission files
            $submissionFile = \App\Models\TaskSubmissionFiles::where('file', 'like', '%'.$filename.'%')->first();
            if ($submissionFile && $submissionFile->task_submission_id) {
                $submission = \App\Models\TaskSubmission::find($submissionFile->task_submission_id);
                if ($submission && $submission->task_id) {
                    $task = \App\Models\Task::find($submission->task_id);
                    if ($task) {
                        $this->authorize('view', $task);
                    }
                }
            }
        }

        $path = public_path($filename);

        // Check if the file exists
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }

    public function makeComment($comment_to, $comment, $comment_obj, $task = null){
        $user = User::find(auth()->user()->id);
        $to = User::find($comment_to);
        if ($to) {
            Notification::send($to, new CommentNotification($comment, $user, $comment_obj, $task));
        }
    }

    public function taskComment(Request $request){
        $comment = new TaskAssignmentComments();

        // If task_assignment_id is missing, attempt to find an assignment for this task and pair
        $assignmentId = $request->task_assignment_id;
        if (!$assignmentId && $request->task_id && $request->comment_to) {
            $myId = auth()->id();
            $toId = $request->comment_to;
            $matchingAssignment = TaskAssignment::where('task_id', $request->task_id)
                ->where(function($q) use ($myId, $toId) {
                    $q->where(function($sq) use ($myId, $toId) {
                        $sq->where('assigned_by', $myId)->where('assigned_to', $toId);
                    })->orWhere(function($sq) use ($myId, $toId) {
                        $sq->where('assigned_by', $toId)->where('assigned_to', $myId);
                    });
                })->latest()->first();

            if ($matchingAssignment) {
                $assignmentId = $matchingAssignment->id;
            } else {
                $assignmentId = TaskAssignment::where('task_id', $request->task_id)->latest()->value('id');
            }
        }

        $comment->task_assignment_id = $assignmentId;
        $comment->sent_by = auth()->user()->id;
        $comment->sent_to = $request->comment_to;
        $comment->comments = $request->comment ?: '';
        $comment->comment_by = auth()->user()->id;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $path = 'documents/comments/';
            if (!is_dir(public_path($path))) {
                mkdir(public_path($path), 0777, true);
            }
            $file->move(public_path($path), $fileName);
            $comment->attachment = '/' . $path . $fileName;
            $comment->attachment_name = $file->getClientOriginalName();
        }

        $comment->save();

        $task = null;
        if ($request->task_id) {
            $task = Task::find($request->task_id);
        } elseif ($comment->task_assignment_id) {
            $assignment = TaskAssignment::find($comment->task_assignment_id);
            if ($assignment && $assignment->task_id) {
                $task = Task::find($assignment->task_id);
            }
        }

        // Notify recipient user with loaded relationships
        $comment_obj = $comment->load(['sentByUser.department', 'sentToUser.department', 'sender.department', 'user.department', 'taskAssignment.task']);
        $this->makeComment($request->comment_to, $request->comment ?: 'Sent an attachment', $comment_obj, $task);

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
        //     if($user->hasRole('super user')){
        //         $director = User::role('director')->get();
        //         Notification::send($director, new CommentNotification($comment,$director));
        //     }
        //     if($user->hasRole('director')){
        //         $div_head = User::role('deputy director')->get();
        //         Notification::send($div_head, new CommentNotification($comment,$div_head));
        //     }
        //     if($user->hasRole('deputy director')){
        //         $unit_head = User::role('assistant director')->get();
        //         Notification::send($unit_head, new CommentNotification($comment,$unit_head));
        //     }
        //     if($user->hasRole('assistant director')){
               
        //         $task = Task::with(['user'])->find($request->task_id);
        //         if($task->user && $task->user->count() > 0){
        //             $users = $task->user;
        //             Notification::send($users, new CommentNotification($comment,$users));
        //         }
        //     }
        // }else{
        //     $task = Task::with(['assignedByUnit','assignedByDivision', 'assignedByDepartment', 'assignedBySuperUser'])->find($request->task_id);
        
        //     if($task->assignedByUnit){
        //         $user = User::role('assistant director')->first();
        //         Notification::send($user, new CommentNotification($comment,$user));
        //     }
        //     if($task->assignedByDivision){
        //         $user = User::role('deputy director')->first();
        //         Notification::send($user, new CommentNotification($comment,$user));
        //     }
        //     if($task->assignedByDepartment){
        //         $user = User::role('director')->first();
        //         Notification::send($user, new CommentNotification($comment,$user));
        //     }
        //     if($task->assigned_by_super_user){
        //         $user = User::role('super user')->first();
        //         Notification::send($user, new CommentNotification($comment,$user));
        //     }
        // }

        //task activity
        // $ta = new TaskActivity();
        // $ta->task_id = $request->task_id;
        // $ta->description = 'Added Comments & Feedback:';
        // $ta->created_by = auth()->user()->id;
        // $ta->save();
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'comment' => $comment_obj,
            ]);
        }
        
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

        $request->validate([
            'approval_pin' => 'required|string',
        ]);

        $user = auth()->user();
        if ($user->hasRole('regular user') || $user->hasRole('regular users')) {
            return back()->withErrors(['users' => 'Unit staff are not authorized to assign tasks.']);
        }

        $isSigned = false;
        \Log::info('PIN check AssignTask', ['user_pin' => $user->approval_pin, 'req_pin' => $request->approval_pin]);
        if ((string)$user->approval_pin !== (string)$request->approval_pin) {
            AuditTrailService::logSecurity('invalid_approval_pin_attempt', 'Invalid digital signature PIN attempt during task assignment', $user, null, ['action' => 'AssignTask', 'task_id' => $request->task_id], 'warning');
            return back()->withErrors(['approval_pin' => 'Invalid digital signature PIN.']);
        }
        if (!$user->digital_signature_path || !file_exists(public_path($user->digital_signature_path))) {
            return back()->withErrors(['approval_pin' => 'You do not have a digital signature configured in your profile.']);
        }
        $isSigned = true;

        // Validate request
        // $request->validate([
        //     'users' => 'required|array|min:1',
        //     'task_id' => 'required|exists:tasks,id',
        //     'description' => 'nullable|string'
        // ]);

        // Get the task
        $task = Task::findOrFail($request->task_id);

        $isAgf = $user->hasRole('super user') || $user->hasRole('super admin') || $user->hasRole('Accountant-General');
        $hasUnapprovedSubmission = TaskAssignment::where('task_id', $task->id)
            ->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('assigned_by', $user->id);
            })
            ->whereIn('status', ['under review', 'review'])
            ->exists();

        if ($hasUnapprovedSubmission && !$isAgf) {
            DB::rollBack();
            return back()->withErrors(['error' => 'The subordinate submission must be approved before you can reassign this task.']);
        }

        if ($request->has('due_date')) {
            $task->due_date = $request->due_date;
        }
        if ($request->has('priority')) {
            $task->priority = $request->priority;
        }
        if ($request->has('workflow_version_id') && !empty($request->workflow_version_id)) {
            $task->workflow_version_id = $request->workflow_version_id;
        }
        $task->save();

        // Optional: Remove old assignments if this is a reassignment
        // TaskAssignment::where('task_id', $request->task_id)->delete();

        $task_assignments = [];
        $user_ids = [];
        $user_names = [];

        \Log::info('AssignTask request->users', ['users' => $request->users]);
        foreach($request->users as $user){
            // Extract the actual users list whether it's mapped as 'users' or 'user' (from Department relation)
            $users_list = [];
            if (isset($user['users']) && is_array($user['users'])) {
                $users_list = $user['users'];
            } elseif (isset($user['user']) && is_array($user['user'])) {
                $users_list = $user['user'];
            }

            // Handle both branch/group assignments and direct user assignments
            if(count($users_list) > 0){
                // This is a branch/department/division with multiple users
                foreach($users_list as $us){
                    $targetUser = User::find($us['id']);
                    if ($targetUser && $targetUser->is_on_leave && !$request->boolean('allow_on_leave')) {
                        $activeLeave = \App\Models\Leave::where('user_id', $targetUser->id)
                            ->where('status', 'approved')
                            ->whereDate('start_date', '<=', now()->toDateString())
                            ->whereDate('end_date', '>=', now()->toDateString())
                            ->with('reliefUser')
                            ->latest('id')
                            ->first();

                        $reliefMsg = ($activeLeave && $activeLeave->reliefUser)
                            ? " Designated Relief Officer is {$activeLeave->reliefUser->first_name} {$activeLeave->reliefUser->last_name}."
                            : "";

                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'users' => "Cannot assign task to {$targetUser->first_name} {$targetUser->last_name}: staff is currently on leave.{$reliefMsg}"
                        ]);
                    }

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
            } elseif((isset($user['users']) || isset($user['user'])) && count($users_list) === 0) {
                // It's a department/branch but has no users (e.g. no director assigned)
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'users' => 'The selected department/branch does not have a head assigned to receive the task.'
                ]);
            } else {
                // Direct user assignment
                $targetUser = User::find($user['id']);
                if ($targetUser && $targetUser->is_on_leave && !$request->boolean('allow_on_leave')) {
                    $activeLeave = \App\Models\Leave::where('user_id', $targetUser->id)
                        ->where('status', 'approved')
                        ->whereDate('start_date', '<=', now()->toDateString())
                        ->whereDate('end_date', '>=', now()->toDateString())
                        ->with('reliefUser')
                        ->latest('id')
                        ->first();

                    $reliefMsg = ($activeLeave && $activeLeave->reliefUser)
                        ? " Designated Relief Officer is {$activeLeave->reliefUser->first_name} {$activeLeave->reliefUser->last_name}."
                        : "";

                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'users' => "Cannot assign task to {$targetUser->first_name} {$targetUser->last_name}: staff is currently on leave.{$reliefMsg}"
                    ]);
                }

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

        // Check that at least one new assignment will be created
        if(count($task_assignments) === 0){
            throw \Illuminate\Validation\ValidationException::withMessages([
                'users' => 'The selected recipient(s) are already assigned to this task or no valid recipients were found.'
            ]);
        }

        if ($request->has('workflow_version_id') && !empty($request->workflow_version_id)) {
            $genericWorkflowService = app(\App\Services\GenericWorkflowService::class);
            $genericWorkflowService->start($task, auth()->user(), array_unique($user_ids));
            
            TaskAssignment::where('task_id', $task->id)
                ->whereIn('assigned_to', array_unique($user_ids))
                ->update(['description' => $request->description]);
        } else {
            TaskAssignment::insert($task_assignments);
        }

        // change the task status to in progress
        $task_assignment_count = TaskAssignment::where('task_id', $request->task_id)->count();
        if($task_assignment_count >= 2){
            $task->status = 'in progress';
            $task->save();
        }
        
        // Mark the current user's assignment as completed so it leaves their inbox
        TaskAssignment::where('task_id', $request->task_id)
            ->where('assigned_to', auth()->id())
            ->update(['status' => 'completed']);
       
        // Log activity for each new assignment
        foreach($user_names as $userName){
            $description = "Task reassigned to " . $userName;
            $this->newTaskActivity($request->task_id, $description);
        }

        // Commit DB transaction BEFORE external operations (notifications, broadcasting)
        DB::commit();

        AuditTrailService::logWorkflow(
            'task_assigned',
            "Workflow task #{$task->id} assigned to " . implode(', ', $user_names),
            $task,
            $user,
            [
                'target_recipients' => $user_names,
                'directive' => $request->description
            ],
            'routine'
        );

        // Send notifications to newly assigned users (guarded so offline broadcast/mail services never abort assignment)
        try {
            $users = User::whereIn('id', array_unique($user_ids))->get();
            $assigner = User::find(auth()->user()->id);
            Notification::send($users, new TaskChangedNotification($task->id, $assigner, 'assigned'));
        } catch (\Throwable $e) {
            \Log::warning('Task notification failed (broadcasting or mail service offline): ' . $e->getMessage());
        }

        // Generate Minute Sheet
        if (count($user_names) > 0) {
            try {
                $action = 'Assigned to ' . implode(', ', $user_names);
                $directive = $request->description;
                $minuteService = new \App\Services\MinuteSheetService();
                $minuteService->appendMinute($request->task_id, auth()->id(), $action, $directive, $isSigned);
            } catch (\Throwable $e) {
                \Log::warning('Minute sheet creation failed: ' . $e->getMessage());
            }
        }
        
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
        return redirect()->back()->withErrors(['error' => 'Failed to reassign task: ' . $e->getMessage()])->withInput();
    }
}

    public function getBranchType(){
        $user = auth()->user();
        if($user->hasRole('assistant director')){
            return 'unit';
        }else
        if($user->hasRole('deputy director')){
            return 'division';
        }else
        if($user->hasRole('director')){
            return 'department';
        }else if($user->hasRole('super user')){
            return 'super user';
        }else{
            return 'users';
        }
    }

    public function taskSubmission(Request $request){
       DB::transaction(function () use($request) {
        $request->validate([
            'approval_pin' => 'required|string',
        ]);

        $isSigned = false;
        $user = auth()->user();
        \Log::info('PIN check taskSubmission', ['user_pin' => $user->approval_pin, 'req_pin' => $request->approval_pin]);
        if ((string)$user->approval_pin !== (string)$request->approval_pin) {
            throw \Illuminate\Validation\ValidationException::withMessages(['approval_pin' => 'Invalid digital signature PIN.']);
        }
        if (!$user->digital_signature_path || !file_exists(public_path($user->digital_signature_path))) {
            throw \Illuminate\Validation\ValidationException::withMessages(['approval_pin' => 'You do not have a digital signature configured in your profile.']);
        }
        $isSigned = true;

        if ($request->filled('task_assignment_id')) {
            $task_assignment = TaskAssignment::find($request->task_assignment_id);
        } else {
            $task_assignment = TaskAssignment::where('task_id', $request->task_id)
                ->where('assigned_to', $request->assigned_to ?: auth()->id())
                ->when($request->assigned_by, function($q) use ($request) {
                    $q->where('assigned_by', $request->assigned_by);
                })
                ->latest()
                ->first();
        }

        if($task_assignment){
            if ($user->hasRole(['super user', 'super admin'])) {
                $task_assignment->status = 'completed';
                // Mark the main task as completed too if AGF approves
                $taskModel = \App\Models\Task::find($request->task_id);
                if($taskModel) {
                    $taskModel->status = 'completed';
                    $taskModel->save();
                }
            } else {
                $task_assignment->status = 'under review';
                $taskModel = \App\Models\Task::find($request->task_id);
                if($taskModel) {
                    $taskModel->status = 'under review';
                    $taskModel->save();
                }
            }
            $task_assignment->save();

            //task submissions
            $submission = new TaskSubmission();
            $submission->description = $request->description;
            $submission->task_id = $request->task_id;
            $submission->user_id = auth()->user()->id;
            $submission->task_assignment_id = $task_assignment->id;
            $submission->save();

             if($request->hasFile('modified_file')) {
                 $modifiedFile = $request->file('modified_file');
                 $rawPath = $request->input('modified_file_path', '');
                 $cleanPath = parse_url($rawPath, PHP_URL_PATH);
                 $cleanPath = ltrim($cleanPath ?: '', '/');
                 $targetPath = $cleanPath ? public_path($cleanPath) : null;

                 $fileName = time() . '_signed_' . ($modifiedFile->getClientOriginalName() ?: 'document.pdf');
                 $path = "/task/" . date('Y') . '/' . date('m');
                 $fullPath = public_path($path);
                 if (!file_exists($fullPath)) {
                     mkdir($fullPath, 0777, true);
                 }
                 $submissionFilePath = $fullPath . '/' . $fileName;

                 if($targetPath && file_exists($targetPath)){
                     $modifiedFile->move(dirname($targetPath), basename($targetPath));
                     copy($targetPath, $submissionFilePath);
                 } else {
                     $modifiedFile->move($fullPath, $fileName);
                     if ($targetPath) {
                         @mkdir(dirname($targetPath), 0777, true);
                         copy($submissionFilePath, $targetPath);
                     }
                 }

                 $size = "Unknown";
                 if(file_exists($submissionFilePath)) {
                     $size = $this->formatSize(filesize($submissionFilePath));
                 }

                 // Register as a TaskSubmissionFiles record
                 $tsf = new TaskSubmissionFiles();
                 $tsf->path = $path . '/' . $fileName;
                 $tsf->task_id = $request->task_id;
                 $tsf->task_submissions_id = $submission->id;
                 $tsf->task_assignment_id = $request->task_assignment_id;
                 $tsf->name = "signed_" . basename($cleanPath ?: $fileName);
                 $tsf->size = $size;
                 $tsf->save();

                 // Update or Touch TaskDoc if it exists
                 if ($cleanPath) {
                     $existingTaskDoc = \App\Models\TaskDoc::where('task_id', $request->task_id)
                         ->where(function($q) use ($cleanPath) {
                             $q->where('path', '/' . $cleanPath)->orWhere('path', $cleanPath);
                         })->first();
                     if ($existingTaskDoc) {
                         $existingTaskDoc->size = $size;
                         $existingTaskDoc->touch();
                     }
                 }
             } else {
                 \Log::warning("Task Submission: missing modified file");
             }

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
            
            // Generate Minute Sheet
            $action = 'Submitted Task';
            $directive = $request->description;
            $minuteService = new \App\Services\MinuteSheetService();
            $minuteService->appendMinute($request->task_id, auth()->id(), $action, $directive, $isSigned);

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

            // Notify supervisor / assigner
            $task = Task::find($request->task_id);
            $assignerId = $task_assignment ? $task_assignment->assigned_by : $request->assigned_by;
            $sent_to = User::find($assignerId);
            if ($sent_to) {
                try {
                    Notification::send($sent_to, new TaskNotification($task, auth()->id()));
                } catch (\Throwable $e) {
                    \Log::warning("Notification to assigner deferred: " . $e->getMessage());
                }
            }

            try {
                if ($task && $task->workflowRoute) {
                    $workflowEngine = app(\App\Services\WorkflowEngine::class);
                    $workflowEngine->submit($task, auth()->user(), $request->description);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("WorkflowEngine submit error: " . $e->getMessage());
            }
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
            if(auth()->user()->hasRole('super user')){
    
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
           
            
           if(!auth()->user()->hasRole('super user')){
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
      
        if($request->id && ($request->task_assignment_id || $request->assigned_to)){
            $task_assignment = null;
            if ($request->task_assignment_id) {
                $task_assignment = TaskAssignment::where('task_id', $request->id)->where('id', $request->task_assignment_id)->first();
            }
            if (!$task_assignment && $request->assigned_to) {
                $task_assignment = TaskAssignment::where('task_id', $request->id)
                    ->where('assigned_to', $request->assigned_to)
                    ->latest()
                    ->first();
            }
            
            if ($task_assignment) {
                $task_assignment->status = 'revision';
                $task_assignment->save();
            }

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
             if(auth()->user()->hasRole('super user')){
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
        if (!$user) {
            return response()->json([]);
        }
        $department = $user->department_id;
        if ($department) {
            $kra = KRA::with('kpi')->where('department_id', $department)->first();
            if ($kra && $kra->kpi) {
                $kpi = $kra->kpi->chunk(2);
                return response()->json($kpi);
            }
        }
        return response()->json([]);
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

                            DB::transaction(function () use ($request, $assigned_to_me, $assigned_by, $task_id, $assigned_task_assignment_id, $assignments, $assigned_to, $task_assignment_id) {
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
                                        ->update(['status'=>'under review']);
                                    Task::where('id', $task_submissions->task_id)->update(['status' => 'under review']);

                                    //notify user
                                    $user = User::find(auth()->user()->id);
                                    $task = Task::find($task_id);
                                     $sent_to = User::where('id',  $assigned_to)->first();
                                     Notification::send($sent_to, new TaskChangedNotification($task->id, $user, 'submitted'));
                                }
                            });
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

    public function getOrCreateTaskChatGroup(Request $request, Task $task)
    {
        // Determine category ID for 'Tasks', create if not exists
        $category = categories::firstOrCreate(['name' => 'Tasks']);

        if (!$task->chat_group_id) {
            // Create group
            $group = Groups::create([
                'name' => 'Task #' . $task->id . ' - ' . substr($task->title, 0, 50),
                'category_id' => $category->id
            ]);

            $task->chat_group_id = $group->id;
            $task->save();

            // Sync users to the chat group
            // We need to sync all users who are involved in this task
            $userIds = $task->user()->pluck('users.id')->toArray();
            
            // Add creator
            if ($task->created_by && !in_array($task->created_by, $userIds)) {
                $userIds[] = $task->created_by;
            }

            // Sync
            $group->users()->sync($userIds);

            // Audit Material Action
            $ta = new TaskActivity();
            $ta->task_id = $task->id;
            $ta->description = 'Initiated task discussion group';
            $ta->created_by = auth()->user()->id ?? ($task->created_by ?? 1);
            $ta->save();
        } else {
            $group = Groups::find($task->chat_group_id);
            if(!$group) {
                // Failsafe if deleted
                $group = Groups::create([
                    'name' => 'Task #' . $task->id . ' - ' . substr($task->title, 0, 50),
                    'category_id' => $category->id
                ]);
                $task->chat_group_id = $group->id;
                $task->save();
                
                $userIds = $task->user()->pluck('users.id')->toArray();
                if ($task->created_by && !in_array($task->created_by, $userIds)) {
                    $userIds[] = $task->created_by;
                }
                $group->users()->sync($userIds);
            }
        }

        return response()->json([
            'chat_group_id' => $task->chat_group_id,
            'group' => $group
        ]);
    }

    
    public function approveTask(Request $request) {
        $request->validate([
            'task_id' => 'required|exists:task,id',
            'task_assignment_id' => 'required'
        ]);
        
        $task = Task::find($request->task_id);
        $user = auth()->user();

        $assignment = TaskAssignment::find($request->task_assignment_id);
        if(!$assignment || (!in_array($assignment->status, ['under review', 'review', 'in progress']) && !($assignment->status === 'completed' && $task && $task->status !== 'completed'))) {
            return back()->withErrors(['error' => 'Assignment not under review or not found.']);
        }

        // Check PIN if provided
        $isSigned = false;
        if ($request->filled('approval_pin')) {
            if ((string)$user->approval_pin === (string)$request->approval_pin) {
                $isSigned = true;
            } else {
                AuditTrailService::logSecurity('invalid_approval_pin_attempt', 'Invalid digital signature PIN attempt during task approval', $user, null, ['action' => 'approveTask', 'task_id' => $task->id], 'warning');
                return back()->withErrors(['approval_pin' => 'Invalid digital signature PIN.']);
            }
        }

        if($request->hasFile('modified_file')) {
            $modifiedFile = $request->file('modified_file');
            $rawPath = $request->input('modified_file_path', '');
            $cleanPath = parse_url($rawPath, PHP_URL_PATH);
            $cleanPath = ltrim($cleanPath ?: '', '/');
            $targetPath = $cleanPath ? public_path($cleanPath) : null;
            
            $fileName = time() . '_signed_approved_document.pdf';
            $path = "/task/" . date('Y') . '/' . date('m');
            $fullPath = public_path($path);
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0777, true);
            }
            $submissionFilePath = $fullPath . '/' . $fileName;

            if($targetPath && file_exists($targetPath)){
                $modifiedFile->move(dirname($targetPath), basename($targetPath));
                copy($targetPath, $submissionFilePath);
            } else {
                $modifiedFile->move($fullPath, $fileName);
                if ($targetPath) {
                    @mkdir(dirname($targetPath), 0777, true);
                    copy($submissionFilePath, $targetPath);
                }
            }

            $submission = new \App\Models\TaskSubmission();
            $submission->description = 'Approved and signed document';
            $submission->task_id = $request->task_id;
            $submission->user_id = auth()->id();
            $submission->task_assignment_id = $assignment->id;
            $submission->save();

            $size = "Unknown";
            if(file_exists($submissionFilePath)) {
                $size = $this->formatSize(filesize($submissionFilePath));
            }

            $tsf = new \App\Models\TaskSubmissionFiles();
            $tsf->path = $path . '/' . $fileName;
            $tsf->task_id = $request->task_id;
            $tsf->task_submissions_id = $submission->id;
            $tsf->task_assignment_id = $assignment->id;
            $tsf->name = "signed_" . basename($cleanPath ?: 'document.pdf');
            $tsf->size = $size;
            $tsf->save();

            if ($cleanPath) {
                $existingTaskDoc = \App\Models\TaskDoc::where('task_id', $request->task_id)
                    ->where(function($q) use ($cleanPath) {
                        $q->where('path', '/' . $cleanPath)->orWhere('path', $cleanPath);
                    })->first();
                if ($existingTaskDoc) {
                    $existingTaskDoc->size = $size;
                    $existingTaskDoc->touch();
                }
            }
        }

        if($request->hasFile('attachments')) {
            $dir = public_path('documents/tasks');
            if (!file_exists($dir)) {
                @mkdir($dir, 0777, true);
            }
            foreach($request->file('attachments') as $file) {
                $fileSize = $file->getSize() ?: 0;
                $origName = $file->getClientOriginalName();
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '_' . $origName;
                $file->move($dir, $fileName);
                $relPath = '/documents/tasks/' . $fileName;

                \App\Models\TaskDoc::create([
                    'task_id' => $task->id,
                    'file_name' => $origName,
                    'path' => $relPath,
                    'file_type' => $ext,
                    'size' => $this->formatSize($fileSize),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 1. Mark the subordinate's assignment as completed
        $assignment->status = 'completed';
        $assignment->save();

        $minuteService = app(\App\Services\MinuteSheetService::class);
        $directive = $request->directive ?: 'Task submission approved by reviewer.';

        // 2. Hierarchy Upward Routing vs AGF Final Approval
        if ($user->hasRole('super user')) {
            // AGF Level
            if ($request->filled('reroute_department_id')) {
                // AGF reroutes to another department
                $deptId = $request->reroute_department_id;
                $targetDirector = \App\Models\User::role('director')->where('department_id', $deptId)->first()
                    ?: \App\Models\User::role('deputy director')->where('department_id', $deptId)->first()
                    ?: \App\Models\User::where('department_id', $deptId)->first();

                if ($targetDirector) {
                    // Mark any remaining active assignments for current user as completed
                    TaskAssignment::where('task_id', $task->id)
                        ->where('assigned_to', $user->id)
                        ->whereNotIn('status', ['completed', 'returned'])
                        ->update(['status' => 'completed']);

                    TaskAssignment::create([
                        'task_id' => $task->id,
                        'assigned_to' => $targetDirector->id,
                        'assigned_by' => $user->id,
                        'status' => 'pending',
                        'description' => $request->directive ?: 'Rerouted by AGF for cross-departmental execution.',
                    ]);
                    $task->status = 'in progress';
                    $task->save();

                    $dept = \App\Models\Department::find($deptId);
                    $deptName = $dept ? $dept->title : "Dept #{$deptId}";
                    $minuteService->appendMinute($task->id, $user->id, 'Rerouted Task to ' . $deptName, $directive, true);
                    $this->newTaskActivity($task->id, "AGF approved and rerouted task to " . $deptName);

                    AuditTrailService::logWorkflow(
                        'task_rerouted',
                        "Task #{$task->id} ('{$task->title}') rerouted by AGF to {$deptName}",
                        $task,
                        $user,
                        ['target_department' => $deptName, 'directive' => $directive],
                        'notice'
                    );

                    try {
                        Notification::send($targetDirector, new TaskChangedNotification($task->id, $user, 'assigned'));
                    } catch (\Throwable $e) {}

                    return redirect()->back()->with('message', 'Task successfully rerouted to ' . $deptName . '!');
                } else {
                    $dept = \App\Models\Department::find($deptId);
                    $deptName = $dept ? $dept->title : "selected department";
                    return redirect()->back()->withErrors(['error' => "No officer or Department Head found in {$deptName} to receive the rerouted task."]);
                }
            }

            // AGF Final Approval
            $task->status = 'completed';
            $task->save();

            // Mark any remaining active assignments as completed
            TaskAssignment::where('task_id', $task->id)->whereNotIn('status', ['completed', 'returned'])->update(['status' => 'completed']);

            // Automatically provision Department Folder & Archive all task documents
            $archivedFolder = null;
            try {
                $archivingService = app(\App\Services\TaskArchivingService::class);
                $archivedFolder = $archivingService->archiveApprovedTask($task, $user);
            } catch (\Throwable $th) {
                \Illuminate\Support\Facades\Log::error("Archiving error in TaskController final approval: " . $th->getMessage());
            }

            $minuteService->appendMinute($task->id, $user->id, 'Final Executive Approval', $directive, $isSigned);
            $this->newTaskActivity($task->id, "AGF granted final executive approval. Task completed.");

            AuditTrailService::logWorkflow(
                'task_approved',
                "Workflow task #{$task->id} ('{$task->title}') completed under AGF Final Executive Approval",
                $task,
                $user,
                ['status' => 'completed', 'directive' => $directive, 'is_signed' => $isSigned],
                'notice'
            );

            $folderNotice = $archivedFolder ? " Archived to Department Folder '{$archivedFolder->name}'." : "";
            return redirect()->back()->with('message', 'Final Executive Approval granted. Task completed!' . $folderNotice);
        } else {
            // Subordinate reviewer (Assistant Director, Deputy Director, Director)
            // Resolve upward supervisor
            $supervisor = \App\Services\RoutingService::getUpwardSupervisor($user, $task);

            if ($supervisor) {
                // Complete all active assignments for current user
                TaskAssignment::where('task_id', $task->id)
                    ->where('assigned_to', $user->id)
                    ->whereNotIn('status', ['completed', 'returned'])
                    ->update(['status' => 'completed']);

                // Always create a new assignment to the upward supervisor
                TaskAssignment::create([
                    'task_id' => $task->id,
                    'assigned_to' => $supervisor->id,
                    'assigned_by' => $user->id,
                    'status' => 'under review',
                    'due_at' => now()->addHours(24),
                    'description' => $directive,
                ]);

                $task->status = 'under review';
                $task->save();

                $supervisorRole = $supervisor->roles->pluck('name')->first() ?? 'Supervisor';
                $actionText = 'Approved & Submitted to ' . ucwords($supervisorRole) . ' (' . $supervisor->first_name . ' ' . $supervisor->last_name . ')';
                $minuteService->appendMinute($task->id, $user->id, $actionText, $directive, $isSigned);
                $this->newTaskActivity($task->id, $user->last_name . ' ' . $user->first_name . ' approved submission and forwarded to ' . $supervisor->last_name . ' ' . $supervisor->first_name);

                AuditTrailService::logWorkflow(
                    'task_approved',
                    "Workflow task #{$task->id} approved and forwarded to {$supervisor->first_name} {$supervisor->last_name} ({$supervisorRole})",
                    $task,
                    $user,
                    ['supervisor_id' => $supervisor->id, 'role' => $supervisorRole, 'directive' => $directive],
                    'routine'
                );

                try {
                    Notification::send($supervisor, new TaskChangedNotification($task->id, $user, 'assigned'));
                } catch (\Throwable $e) {}

                return redirect()->back()->with('message', 'Task approved and forwarded to ' . $supervisor->first_name . ' ' . $supervisor->last_name . '!');
            } else {
                // No supervisor found, complete task
                $task->status = 'completed';
                $task->save();
                $minuteService->appendMinute($task->id, $user->id, 'Approved Task', $directive, $isSigned);
                return redirect()->back()->with('message', 'Task Approved Successfully!');
            }
        }
    }

    public function saveModifiedPdf(Request $request)
    {
        $request->validate([
            'task_id' => 'required',
            'file' => 'required|file',
        ]);

        $task = Task::findOrFail($request->task_id);
        $modifiedFile = $request->file('file');
        $rawPath = $request->input('target_path', '');
        $cleanPath = parse_url($rawPath, PHP_URL_PATH);
        $cleanPath = ltrim($cleanPath ?: '', '/');
        $targetPath = $cleanPath ? public_path($cleanPath) : null;

        $fileName = time() . '_modified_' . basename($cleanPath ?: 'document.pdf');
        $path = "/task/" . date('Y') . '/' . date('m');
        $fullPath = public_path($path);
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777, true);
        }
        $savedPath = $fullPath . '/' . $fileName;

        if ($targetPath && file_exists($targetPath)) {
            $modifiedFile->move(dirname($targetPath), basename($targetPath));
            copy($targetPath, $savedPath);
        } else {
            $modifiedFile->move($fullPath, $fileName);
            if ($targetPath) {
                @mkdir(dirname($targetPath), 0777, true);
                copy($savedPath, $targetPath);
            }
        }

        $size = "Unknown";
        $finalFileToCheck = ($targetPath && file_exists($targetPath)) ? $targetPath : $savedPath;
        if (file_exists($finalFileToCheck)) {
            $size = $this->formatSize(filesize($finalFileToCheck));
        }

        if ($cleanPath) {
            $taskDoc = \App\Models\TaskDoc::where('task_id', $task->id)
                ->where(function($q) use ($cleanPath) {
                    $q->where('path', '/' . $cleanPath)->orWhere('path', $cleanPath);
                })->first();
            if ($taskDoc) {
                $taskDoc->size = $size;
                $taskDoc->touch();
            }
        }

        $user = auth()->user();
        $userName = $user ? ($user->first_name . ' ' . $user->last_name) : 'User';
        $this->newTaskActivity($task->id, $userName . ' saved document annotations and signatures.');

        return response()->json([
            'status' => 'success',
            'message' => 'Document saved successfully.',
            'path' => $cleanPath ? ('/' . $cleanPath) : ($path . '/' . $fileName)
        ]);
    }

    public function returnTask(Request $request)
    {
        \Log::info('returnTask hit with payload', $request->all());
        $request->validate([
            'task_id' => 'required|exists:task,id',
            'description' => 'required|string',
            'approval_pin' => 'required|string',
        ]);

        $task = Task::find($request->task_id);
        $latestAssignment = TaskAssignment::where('task_id', $request->task_id)->latest('id')->first();
        if ($latestAssignment && $latestAssignment->status === 'completed' && $task && $task->status !== 'completed') {
            return back()->withErrors(['error' => 'This task submission has already been approved and cannot be returned.']);
        }

        $user = auth()->user();

        // 1. Verify PIN
        if ((string)$user->approval_pin !== (string)$request->approval_pin) {
            AuditTrailService::logSecurity('invalid_approval_pin_attempt', 'Invalid digital signature PIN attempt during task return', $user, null, ['action' => 'returnTask', 'task_id' => $request->task_id], 'warning');
            return back()->withErrors(['approval_pin' => 'Invalid digital signature PIN.']);
        }

        // 2. Verify digital signature exists (optional for minute sheet, but keeping consistency)
        $isSigned = true;

        DB::beginTransaction();
        try {
            if($request->hasFile('modified_file') && $request->filled('modified_file_path')) {
                $modifiedFile = $request->file('modified_file');
                $targetPath = public_path(ltrim($request->modified_file_path, '/'));
                if(file_exists($targetPath)){
                    $modifiedFile->move(dirname($targetPath), basename($targetPath));
                }
            }

            // Find the task assignment for the current user
            $existingAssignment = TaskAssignment::where('task_id', $request->task_id)
                ->where('assigned_to', $user->id)
                ->where('status', '!=', 'completed')
                ->first();

            \Log::info('returnTask existing assignment', ['assignment' => $existingAssignment]);

            if (!$existingAssignment) {
                return back()->withErrors(['error' => 'You do not have an active assignment for this task.']);
            }

            $assigner = User::find($existingAssignment->assigned_by);

            // Mark assignment as returned
            $existingAssignment->status = 'returned';
            $existingAssignment->save();

            // Update main task status
            $task = Task::find($request->task_id);
            if ($task) {
                $task->status = 'returned';
                $task->save();
            }

            // Create a minute sheet entry
            $action = 'Returned to ' . ($assigner ? trim($assigner->last_name . ' ' . $assigner->first_name) : 'Assigner');
            $directive = $request->description;
            $minuteService = new \App\Services\MinuteSheetService();
            $minuteService->appendMinute($request->task_id, $user->id, $action, $directive, $isSigned);

            // Log activity
            $this->newTaskActivity($request->task_id, 'Task returned to ' . ($assigner ? trim($assigner->last_name . ' ' . $assigner->first_name) : 'Assigner') . ': ' . $request->description);

            // Notify original assigner
            if ($assigner) {
                Notification::send($assigner, new TaskChangedNotification($request->task_id, $user, 'returned'));
            }

            AuditTrailService::logWorkflow(
                'task_returned',
                "Workflow task #{$request->task_id} returned to " . ($assigner ? trim($assigner->last_name . ' ' . $assigner->first_name) : 'Assigner') . ": {$request->description}",
                $task,
                $user,
                [
                    'directive' => $request->description,
                    'returned_to' => $assigner ? $assigner->id : null
                ],
                'warning'
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('returnTask failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->withErrors(['error' => 'An error occurred while returning the task: ' . $e->getMessage()]);
        }

        return redirect()->back();
    }

    public function appendSignature(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:task,id',
            'approval_pin' => 'required|string',
        ]);

        $user = auth()->user();

        // 1. Verify PIN
        if ((string)$user->approval_pin !== (string)$request->approval_pin) {
            return back()->withErrors(['approval_pin' => 'Invalid digital signature PIN.']);
        }

        // 2. Verify user has a digital signature
        if (!$user->digital_signature_path || !file_exists(public_path($user->digital_signature_path))) {
            return back()->withErrors(['approval_pin' => 'You do not have a digital signature configured in your profile.']);
        }

        $task = Task::with(['taskdocs', 'file'])->findOrFail($request->task_id);

        if ($task->taskdocs->isEmpty() && !$task->file) {
            return back()->withErrors(['approval_pin' => 'No documents attached to this task to sign.']);
        }

        $signaturePath = public_path($user->digital_signature_path);
        $pdfService = new PdfSignatureService();
        $signedCount = 0;

        // Sign task docs if any
        foreach ($task->taskdocs as $doc) {
            $filePath = public_path(ltrim($doc->path, '/'));
            if (file_exists($filePath)) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    if ($pdfService->stampSignatureOnPdf($filePath, $signaturePath, $filePath)) {
                        $signedCount++;
                    }
                }
            }
        }

        // Sign main file if any
        if ($task->file && $task->file->path) {
            $filePath = public_path(ltrim($task->file->path, '/'));
            if (file_exists($filePath)) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    if ($pdfService->stampSignatureOnPdf($filePath, $signaturePath, $filePath)) {
                        $signedCount++;
                    }
                }
            }
        }

        if ($signedCount > 0) {
            $task->is_signed = true;
            $task->save();
            
            $this->newTaskActivity($task->id, 'Digitally signed attached documents');
            return back()->with('success', 'Documents signed successfully.');
        } else {
            return back()->withErrors(['approval_pin' => 'Failed to sign documents. Ensure they are valid PDFs.']);
        }
    }

    public function uploadTaskDocument(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:task,id',
            'files' => 'required|array',
            'files.*' => 'file|max:10240'
        ]);

        $task = \App\Models\Task::findOrFail($request->task_id);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        $uploadedCount = 0;

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $extension = strtolower($file->getClientOriginalExtension());
                if (!in_array($extension, $allowedExtensions)) {
                    continue;
                }
                
                $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $size = $this->formatSize(filesize($file));
                
                $path = 'documents/tasks/';
                if(!is_dir(public_path($path))){
                    mkdir(public_path($path),0777,true);
                }

                $file->move(public_path($path), $fileName);

                \App\Models\TaskDoc::create([
                    'task_id' => $task->id,
                    'path' => '/' . $path . $fileName,
                    'file_name' => $fileName,
                    'size' => $size,
                ]);
                $uploadedCount++;
            }
            
            if ($uploadedCount > 0) {
                $action = "Attached $uploadedCount document(s)";
                $minuteService = new \App\Services\MinuteSheetService();
                $minuteService->appendMinute($task->id, auth()->id(), $action, "Attached documents to task", true);
                
                return back()->with('success', 'Documents attached successfully.');
            } else {
                return back()->withErrors(['error' => 'No valid files uploaded.']);
            }
        }

        return back()->withErrors(['error' => 'No files provided.']);
    }
    public function requestInformation(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:task,id',
            'requested_from' => 'required|exists:users,id',
            'request_message' => 'required|string',
        ]);

        $task = Task::findOrFail($request->task_id);
        
        // Find the active assignment for the current user
        $assignment = \App\Models\TaskAssignment::where('task_id', $task->id)
            ->where('assigned_to', auth()->id())
            ->whereIn('status', ['pending', 'revision'])
            ->first();

        if (!$assignment) {
            return back()->withErrors(['error' => 'You do not have an active assignment for this task.']);
        }

        // Create the request
        \App\Models\TaskInformationRequest::create([
            'task_id' => $task->id,
            'requested_by' => auth()->id(),
            'requested_from' => $request->requested_from,
            'request_message' => $request->request_message,
            'status' => 'pending'
        ]);

        // Put the assignment on hold
        $assignment->update(['status' => 'on_hold']);

        // Update the minute sheet
        $requestedUser = \App\Models\User::find($request->requested_from);
        $action = "Task placed on Hold. Information requested from {$requestedUser->first_name} {$requestedUser->last_name}";
        $minuteService = new \App\Services\MinuteSheetService();
        $minuteService->appendMinute($task->id, auth()->id(), 'On Hold', "{$action}: {$request->request_message}", true);
        
        // Send notification to the requested user
        $requestedUser->notify(new \App\Notifications\TaskChangedNotification(
            $task->id,
            auth()->user(),
            "Information Requested"
        ));

        return back()->with('success', 'Information request sent successfully. Task placed on hold.');
    }

    public function provideInformation(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:task_information_requests,id',
            'response_message' => 'required|string',
            'files.*' => 'nullable|file'
        ]);

        $infoRequest = \App\Models\TaskInformationRequest::findOrFail($request->request_id);

        if ($infoRequest->requested_from != auth()->id()) {
            return back()->withErrors(['error' => 'You are not authorized to respond to this request.']);
        }

        // Handle file uploads if any
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = 'task_docs';
                $name = $file->getClientOriginalName();
                $size = $file->getSize();
                
                $extension = $file->getClientOriginalExtension();
                $nameWithoutExt = pathinfo($name, PATHINFO_FILENAME);
                $nameWithoutExt = substr($nameWithoutExt, 0, 100);
                
                $fileName = time() . '_' . str_replace(" ", "_", $nameWithoutExt) . '.' . $extension;
                $file->move(public_path($path), $fileName);

                \App\Models\TaskDoc::create([
                    'task_id' => $infoRequest->task_id,
                    'file_name' => $fileName,
                    'path' => '/' . $path . '/' . $fileName,
                    'size' => $size,
                ]);
            }
        }

        // Update the request
        $infoRequest->update([
            'response_message' => $request->response_message,
            'status' => 'fulfilled'
        ]);

        // Find the assignment that was put on hold by the requester
        $assignment = \App\Models\TaskAssignment::where('task_id', $infoRequest->task_id)
            ->where('assigned_to', $infoRequest->requested_by)
            ->where('status', 'on_hold')
            ->first();

        if ($assignment) {
            $assignment->update(['status' => 'hold_responded']);
        }

        // Update the minute sheet
        $task = Task::find($infoRequest->task_id);
        $requester = \App\Models\User::find($infoRequest->requested_by);
        $action = "Information provided by " . auth()->user()->first_name . " " . auth()->user()->last_name;
        $minuteService = new \App\Services\MinuteSheetService();
        $minuteService->appendMinute($task->id, auth()->id(), 'Info Provided', "{$action}: {$request->response_message}. Task resumed.", true);

        // Notify the requester
        $requester->notify(new \App\Notifications\TaskChangedNotification(
            $task->id,
            auth()->user(),
            "Information Provided"
        ));

        return back()->with('success', 'Information provided successfully. Task status updated to hold responded.');
    }

    public function resumeTask(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:task,id',
        ]);

        $assignment = \App\Models\TaskAssignment::where('task_id', $request->task_id)
            ->where('assigned_to', auth()->id())
            ->whereIn('status', ['on_hold', 'hold_responded'])
            ->first();

        if (!$assignment) {
            return back()->withErrors(['error' => 'No active hold found for this task.']);
        }

        $assignment->update(['status' => 'pending']);

        $task = Task::find($request->task_id);
        $minuteService = new \App\Services\MinuteSheetService();
        $minuteService->appendMinute($task->id, auth()->id(), 'Hold Released', "Task resumed by " . auth()->user()->first_name . " " . auth()->user()->last_name, true);

        return back()->with('success', 'Task resumed successfully.');
    }

    public function batchAssignTasks(Request $request)
    {
        $request->validate([
            'task_ids' => 'required|array|min:1',
            'task_ids.*' => 'required|integer|exists:task,id',
            'users' => 'required|array|min:1',
            'approval_pin' => 'nullable|string',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:1,2,3',
            'due_date' => 'nullable|date',
        ]);

        $user = auth()->user();
        if ($user->hasRole('regular user') || $user->hasRole('regular users')) {
            return back()->withErrors(['users' => 'Unit staff are not authorized to assign tasks.']);
        }
        $isSigned = false;

        // If approval_pin is provided, verify it
        if ($request->filled('approval_pin')) {
            if ((string)$user->approval_pin !== (string)$request->approval_pin) {
                return back()->withErrors(['approval_pin' => 'Invalid digital signature PIN.']);
            }
            $isSigned = true;
        }

        // Parse recipients list
        $targetUserIds = [];
        $targetUserNames = [];

        foreach ($request->users as $u) {
            $usersList = [];
            if (isset($u['users']) && is_array($u['users'])) {
                $usersList = $u['users'];
            } elseif (isset($u['user']) && is_array($u['user'])) {
                $usersList = $u['user'];
            }

            if (count($usersList) > 0) {
                foreach ($usersList as $subUser) {
                    $targetUserIds[] = $subUser['id'];
                    $targetUserNames[] = trim(($subUser['last_name'] ?? '') . ' ' . ($subUser['first_name'] ?? ''));
                }
            } elseif (isset($u['id'])) {
                $targetUserIds[] = $u['id'];
                $targetUserNames[] = isset($u['title']) ? $u['title'] : trim(($u['last_name'] ?? '') . ' ' . ($u['first_name'] ?? ''));
            }
        }

        $targetUserIds = array_unique(array_filter($targetUserIds));
        $targetUserNames = array_unique(array_filter($targetUserNames));

        if (empty($targetUserIds)) {
            return back()->withErrors(['users' => 'No valid recipients selected for assignment.']);
        }

        $processedCount = 0;
        $minuteService = app(\App\Services\MinuteSheetService::class);

        try {
            DB::beginTransaction();

            foreach ($request->task_ids as $taskId) {
                $task = Task::find($taskId);
                if (!$task) continue;

                if ($request->has('due_date') && $request->due_date) {
                    $task->due_date = $request->due_date;
                }
                if ($request->has('priority') && $request->priority) {
                    $task->priority = $request->priority;
                }
                $task->save();

                // Create assignments for recipients who are not yet assigned
                $newAssignments = [];
                foreach ($targetUserIds as $assigneeId) {
                    $existing = TaskAssignment::where('task_id', $taskId)
                        ->where('assigned_to', $assigneeId)
                        ->first();

                    if (!$existing) {
                        $newAssignments[] = [
                            'task_id' => $taskId,
                            'assigned_to' => $assigneeId,
                            'assigned_by' => $user->id,
                            'status' => 'pending',
                            'description' => $request->description,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                if (!empty($newAssignments)) {
                    TaskAssignment::insert($newAssignments);
                }

                // If task now has multiple assignments, set to in progress
                $assignmentCount = TaskAssignment::where('task_id', $taskId)->count();
                if ($assignmentCount >= 2) {
                    $task->status = 'in progress';
                    $task->save();
                }

                // Complete current user's assignment if it was in their incoming queue
                TaskAssignment::where('task_id', $taskId)
                    ->where('assigned_to', $user->id)
                    ->update(['status' => 'completed']);

                // Log activity
                foreach ($targetUserNames as $name) {
                    $this->newTaskActivity($taskId, "Task batch-assigned to " . $name);
                }

                // Minute sheet
                $directiveText = $request->description ?: 'Batch dispatched and assigned.';
                $actionText = 'Batch Assigned to ' . implode(', ', $targetUserNames);
                try {
                    $minuteService->appendMinute($taskId, $user->id, $actionText, $directiveText, $isSigned);
                } catch (\Throwable $e) {
                    \Log::warning("Minute sheet generation skipped for task {$taskId}: " . $e->getMessage());
                }

                $processedCount++;
            }

            DB::commit();

            // Safe notification sending
            try {
                $recipientModels = User::whereIn('id', $targetUserIds)->get();
                foreach ($request->task_ids as $taskId) {
                    Notification::send($recipientModels, new TaskChangedNotification($taskId, $user, 'assigned'));
                }
            } catch (\Throwable $e) {
                \Log::warning('Batch notification delivery deferred: ' . $e->getMessage());
            }

            return back()->with('success', "Successfully batch-assigned {$processedCount} tasks.");

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Batch assign failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to batch assign tasks: ' . $e->getMessage()]);
        }
    }

    public function batchArchiveTasks(Request $request)
    {
        $request->validate([
            'task_ids' => 'required|array|min:1',
            'task_ids.*' => 'required|integer|exists:task,id',
        ]);

        $count = Task::whereIn('id', $request->task_ids)->update(['status' => 'completed']);
        return back()->with('success', "Successfully archived {$count} tasks.");
    }

    public function getTaskTriageDetails($id)
    {
        $task = Task::with([
            'taskdocs',
            'file.department',
            'minutes.user.department',
            'assignments.assignedTo.department',
            'assignments.assignedTo.roles',
            'assignments.assignedBy.department',
            'assignments.taskSubmissions.taskSubmissionFiles',
            'assignments.comments' => function($cq) {
                $cq->with(['sentByUser.department', 'sentToUser.department', 'sender.department', 'user.department'])->latest();
            },
            'assignedToMe.assignedBy.department',
            'assignedToMe.comments' => function($cq) {
                $cq->with(['sentByUser.department', 'sentToUser.department', 'sender.department', 'user.department'])->latest();
            },
            'assignedOutAssignments.assignedTo.department',
            'assignedOutAssignments.assignedTo.roles'
        ])->findOrFail($id);

        $task->suggested_routing = \App\Services\RoutingService::resolveSmartRoutingForUser($task);

        return response()->json([
            'status' => 'success',
            'task' => $task
        ]);
    }

    public function getTaskDiscussions($id)
    {
        $task = Task::with([
            'assignedToMe' => function($q) {
                $q->with([
                    'assignedBy.department',
                    'comments' => function($cq) {
                        $cq->with(['sentByUser.department', 'sentToUser.department', 'sender.department', 'user.department'])->orderBy('id', 'asc');
                    }
                ]);
            },
            'assignments' => function($query) {
                $query->with([
                    'assignedTo.department',
                    'assignedTo.roles',
                    'assignedBy.department',
                    'comments' => function($cq) {
                        $cq->with(['sentByUser.department', 'sentToUser.department', 'sender.department', 'user.department'])->orderBy('id', 'asc');
                    }
                ])->orderBy('id', 'desc');
            }
        ])->findOrFail($id);

        // Fetch all comments for this task in chronological order
        $allComments = TaskAssignmentComments::whereHas('taskAssignment', function($q) use ($id) {
            $q->where('task_id', $id);
        })->with(['sentByUser.department', 'sentToUser.department', 'sender.department', 'user.department'])
          ->orderBy('id', 'asc')
          ->get();

        return response()->json([
            'status' => 'success',
            'assigned_to_me' => $task->assignedToMe,
            'assignments' => $task->assignments,
            'comments' => $allComments,
            'comments_count' => $allComments->count()
        ]);
    }

    public function getAvailableWorkflows(Request $request)
    {
        $user = auth()->user();
        $versions = \App\Models\WorkflowVersion::with(['workflow.department', 'workflow.documentTemplate', 'steps' => function($q) {
                $q->orderBy('id', 'asc');
            }])
            ->where('is_published', true)
            ->latest('version_number')
            ->get()
            ->filter(function($v) use ($user) {
                if (empty($v->workflow)) return false;
                return $v->workflow->canBeInitiatedBy($user);
            })
            ->values();

        return response()->json($versions);
    }

    /**
     * AGF 1-Click Executive Minuting and Routing to Departmental Director
     */
    public function executiveRouteTask(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:task,id',
            'director_user_id' => 'required|exists:users,id',
            'directive' => 'required|string',
            'priority' => 'nullable|integer'
        ]);

        $user = auth()->user();
        $task = Task::findOrFail($request->task_id);
        $targetDirector = User::with('department')->findOrFail($request->director_user_id);

        return DB::transaction(function () use ($request, $user, $task, $targetDirector) {
            // 1. Log official AGF Minute
            $minute = new \App\Models\TaskMinute();
            $minute->task_id = $task->id;
            $minute->user_id = $user->id;
            $minute->role = 'Accountant General of the Federation';
            $minute->action = 'Executive Directive to Director, ' . ($targetDirector->department->title ?? 'Department');
            $minute->directive = $request->directive;
            $minute->is_signed = true;
            $minute->signed_at = now();
            $minute->signature_path = $user->digital_signature_path;
            $minute->save();

            // 2. Mark existing assignments as completed
            TaskAssignment::where('task_id', $task->id)->update(['status' => 'completed']);

            // 3. Create fresh Assignment to the Departmental Director
            $assignment = TaskAssignment::create([
                'task_id' => $task->id,
                'assigned_to' => $targetDirector->id,
                'assigned_by' => $user->id,
                'status' => 'pending',
                'description' => $request->directive
            ]);

            // 4. Update task status & priority
            $task->status = 'in progress';
            if ($request->filled('priority')) {
                $task->priority = $request->priority;
            }
            $task->due_date = now()->addHours(48)->format('Y-m-d');
            $task->save();

            // 5. Advance GenericWorkflowService to Step 3 (Department Treatment) if on blueprint
            if ($task->workflow_version_id) {
                $version = \App\Models\WorkflowVersion::with('steps')->find($task->workflow_version_id);
                if ($version) {
                    $deptStep = $version->steps->where('step_type', 'review')->first();
                    if ($deptStep) {
                        DB::table('task_active_steps')
                            ->where('task_id', $task->id)
                            ->where('status', 'in_progress')
                            ->update(['status' => 'completed', 'updated_at' => now()]);

                        DB::table('task_active_steps')->insert([
                            'task_id' => $task->id,
                            'step_id' => $deptStep->id,
                            'status' => 'in_progress',
                            'due_date' => now()->addHours(48),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        $task->current_step_id = $deptStep->id;
                        $task->save();
                    }
                }
            }

            $activity = new TaskActivity();
            $activity->task_id = $task->id;
            $activity->description = 'Executive Minuted & Routed to Director ' . ($targetDirector->first_name . ' ' . $targetDirector->last_name) . ' (' . ($targetDirector->department->title ?? '') . '): "' . $request->directive . '"';
            $activity->created_by = $user->id;
            $activity->save();

            // 7. Real-time notification to the Director
            try {
                $targetDirector->notify(new \App\Notifications\WorkflowEventNotification(
                    $task,
                    $user,
                    'assigned',
                    'Executive Directive from the Accountant General of the Federation: ' . $request->directive
                ));
            } catch (\Exception $e) {
                \Log::warning('Notification dispatch error: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Docket executive-routed to Director ' . $targetDirector->first_name . ' ' . $targetDirector->last_name . ' successfully.'
            ]);
        });
    }
}


