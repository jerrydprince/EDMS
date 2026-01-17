<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\Workflow;
use App\Notifications\TaskNotification;
use App\Models\Task;
use App\Models\User;
use App\Models\Division;
use App\Models\Department;
use App\Models\Unit;

class WorkFlowController extends Controller
{

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

    public function getCreatedTask($request){
        $type = $this->getBranchType();
        $searchTerm = $request->search; 
        $created_task = [];
        $assigned_task = [];
        $btn = $request->assigned_btn;

        //  $created_task = Task::with(['assignments' => function ($query) {
        //         $query->with('assignedTo')
        //             ->where('assigned_by', auth()->id());
        //     }])
        //     ->where('created_by', auth()->id())
        //     ->when($request->status, function ($query, $status) {
        //         $query->where('status', $status);
        //     })
        //     ->when($searchTerm, function ($query, $searchTerm) {
        //         $query->where(function ($subQuery) use ($searchTerm) {
        //             $subQuery->where('title', 'like', "%{$searchTerm}%")
        //                     ->orWhere('description', 'like', "%{$searchTerm}%")
        //                     ->orWhere('status', 'like', "%{$searchTerm}%")
        //                     ->orWhere('priority', 'like', "%{$searchTerm}%")
        //                     ->orWhere('due_date', 'like', "%{$searchTerm}%");
        //         });
        //     })
        //    // ->when($btn == 1, fn ($q) => $q->whereRaw('1 = 0')) // hide when viewing assigned
        //     ->latest()
        //     ->paginate(10)
        //     ->withQueryString();
        $created_task = Task::with('assignedToMe')
            ->whereHas('assignments', function ($q) use ($request) {

                // Always restrict to logged-in user
                $q->where('assigned_by', auth()->id());

                if ($request->status) {

                    if ($request->status === 'uncompleted') {
                        $q->where('status', 'uncompleted');
                    } 
                    else if (! auth()->user()->hasRole('super users')) {
                        // NON super users → assignment status
                        $q->where('status', $request->status);
                    }

                }
            })
            ->when(
                $request->status === 'uncompleted',
                fn ($q) => $q->where('due_date', '<', now())
            )
            ->when(
                $request->status && auth()->user()->hasRole('super users'),
                fn ($q) => $q->where('task.status', $request->status)
            )
            ->when($searchTerm, function ($q) use ($searchTerm) {
                $q->where(function ($s) use ($searchTerm) {
                    $s->where('tasks.title', 'like', "%{$searchTerm}%")
                    ->orWhere('tasks.description', 'like', "%{$searchTerm}%")
                    ->orWhere('tasks.status', 'like', "%{$searchTerm}%")
                    ->orWhere('tasks.priority', 'like', "%{$searchTerm}%")
                    ->orWhereDate('tasks.due_date', $searchTerm);
                });
            })
            ->where('created_by', auth()->id())
            ->latest()
            ->paginate(10)
            ->withQueryString();


        return $created_task;
    }

    public function getAssignedTask($request){
         $type = $this->getBranchType();
        $searchTerm = $request->search; 
        $created_task = [];
        $assigned_task = [];
        $btn = $request->assigned_btn;

       

        $assigned_task = Task::with('assignedToMe')
            ->whereHas('assignments', function ($q) use ($request) {

                // Always restrict to logged-in user
                $q->where('assigned_to', auth()->id());

                if ($request->status) {

                    if ($request->status === 'uncompleted') {
                        $q->where('status', 'uncompleted');
                    } 
                    else if (! auth()->user()->hasRole('super users')) {
                        // NON super users → assignment status
                        $q->where('status', $request->status);
                    }

                }
            })
            ->when(
                $request->status === 'uncompleted',
                fn ($q) => $q->where('due_date', '<', now())
            )
            ->when(
                $request->status && auth()->user()->hasRole('super users'),
                fn ($q) => $q->where('task.status', $request->status)
            )
            ->when($searchTerm, function ($q) use ($searchTerm) {
                $q->where(function ($s) use ($searchTerm) {
                    $s->where('tasks.title', 'like', "%{$searchTerm}%")
                    ->orWhere('tasks.description', 'like', "%{$searchTerm}%")
                    ->orWhere('tasks.status', 'like', "%{$searchTerm}%")
                    ->orWhere('tasks.priority', 'like', "%{$searchTerm}%")
                    ->orWhereDate('tasks.due_date', $searchTerm);
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();


            return $assigned_task;

    }

    public function getBranchTask($request){

        $type = $this->getBranchType();
        $searchTerm = $request->search; 
        $created_task = [];
        $assigned_task = [];
        $btn = $request->assigned_btn;

        // $created_task = Task::with(['assignments'=>function($query){
        //     $query->with('assignedTo')->where('assigned_by', auth()->user()->id);
        // }])
        // ->when($searchTerm, function ($query, $searchTerm) {
        //             $query->where(function ($subQuery) use ($searchTerm) {
        //                 $subQuery->where('title', 'like', "%{$searchTerm}%")
        //                          ->orWhere('description', 'like', "%{$searchTerm}%")
        //                          ->orWhere('status', 'like', "%{$searchTerm}%" )
        //                          ->orWhere('priority', 'like', "%{$searchTerm}%" )
        //                          ->orWhere('due_date', 'like', "%{$searchTerm}%");
        //             });
        //         })
        // ->where('created_by', auth()->user()->id)->latest()->paginate(10)->withQueryString();

        // $assigned_task = Task::withWhereHas('assignments', function($query){
        //     $query->with('assignedBy')->where('assigned_to', auth()->user()->id);
        // })
        // ->when($searchTerm, function ($query, $searchTerm) {
        //             $query->where(function ($subQuery) use ($searchTerm) {
        //                 $subQuery->where('title', 'like', "%{$searchTerm}%")
        //                          ->orWhere('description', 'like', "%{$searchTerm}%")
        //                          ->orWhere('status', 'like', "%{$searchTerm}%" )
        //                          ->orWhere('priority', 'like', "%{$searchTerm}%" )
        //                          ->orWhere('due_date', 'like', "%{$searchTerm}%");
        //             });
        //         })
        // ->latest()->paginate(10)->withQueryString();

        $created_task = $this->getCreatedTask($request);
        $assigned_task = $this->getAssignedTask($request);

        // if($this->isRole('division heads')){
        //     $division = Division::find(auth()->user()->division_id);
        //     $created_task = Task::with('assignedByDivision')
        //         ->where('assigned_by_division', auth()->user()->division_id)
        //         ->when($searchTerm, function ($query, $searchTerm) {
        //             $query->where(function ($subQuery) use ($searchTerm) {
        //                 $subQuery->where('title', 'like', "%{$searchTerm}%")
        //                          ->orWhere('description', 'like', "%{$searchTerm}%")
        //                          ->orWhere('status', 'like', "%{$searchTerm}%" )
        //                          ->orWhere('due_date', 'like', "%{$searchTerm}%");
        //             });
        //         })
        //        // ->user()->division_id)
        //         ->latest()
        //         ->paginate(10)->withQueryString();
              
        //     $assigned_task = $division->task()
        //         ->with('assignedByDepartment')
        //         ->when($searchTerm, function ($query, $searchTerm) {
        //             $query->where(function ($subQuery) use ($searchTerm) {
        //                 $subQuery->where('title', 'like', "%{$searchTerm}%")
        //                          ->orWhere('description', 'like', "%{$searchTerm}%")
        //                          ->orWhere('status', 'like', "%{$searchTerm}%" )
        //                          ->orWhere('due_date', 'like', "%{$searchTerm}%");
        //             });
        //         })
        //         ->latest()
        //         ->paginate(10)->withQueryString();
              
        //         $assigned_task->getCollection()->transform(function ($task) {
        //             $task->is_assigned = 1;  // Add the attribute
        //             return $task;
        //         });

        //         $created_task->getCollection()->transform(function ($task) {
        //             $task->is_assigned = 2;  // Add the attribute
        //             return $task;
        //         });
        // }

        // if($this->isRole('unit heads')){
        //     $unit = Unit::find(auth()->user()->unit_id);
        //     $created_task = Task::with('assignedBy')
        //         ->where('assigned_by_unit', auth()->user()->unit_id)
        //         ->when($searchTerm, function ($query, $searchTerm) {
        //             $query->where(function ($subQuery) use ($searchTerm) {
        //                 $subQuery->where('title', 'like', "%{$searchTerm}%")
        //                          ->orWhere('description', 'like', "%{$searchTerm}%")
        //                          ->orWhere('status', 'like', "%{$searchTerm}%" )
        //                          ->orWhere('due_date', 'like', "%{$searchTerm}%");
        //             });
        //         })
        //         ->latest()
        //         ->paginate(10)->withQueryString();
                
        //     $assigned_task = $unit->task()
        //         ->with('assignedByDivision')
        //         ->when($searchTerm, function ($query, $searchTerm) {
        //             $query->where(function ($subQuery) use ($searchTerm) {
        //                 $subQuery->where('title', 'like', "%{$searchTerm}%")
        //                          ->orWhere('description', 'like', "%{$searchTerm}%")
        //                          ->orWhere('status', 'like', "%{$searchTerm}%" )
        //                          ->orWhere('due_date', 'like', "%{$searchTerm}%");
        //             });
        //         })
        //         ->latest()
        //         ->paginate(10)->withQueryString();
               
        //         $assigned_task->getCollection()->transform(function ($task) {
        //             $task->is_assigned = 1;  // Add the attribute
        //             return $task;
        //         });
        //         $created_task->getCollection()->transform(function ($task) {
        //             $task->is_assigned = 2;  // Add the attribute
        //             return $task;
        //         });
        // }

        // if($this->isRole('directors')){
        //     $department = Department::find(auth()->user()->department_id);
        //     $created_task = Task::with('assignedByDepartment')
        //         ->where('assigned_by_department', auth()->user()->department_id)
        //         ->when($searchTerm, function ($query, $searchTerm) {
        //             $query->where(function ($subQuery) use ($searchTerm) {
        //                 $subQuery->where('title', 'like', "%{$searchTerm}%")
        //                          ->orWhere('description', 'like', "%{$searchTerm}%")
        //                          ->orWhere('status', 'like', "%{$searchTerm}%" )
        //                          ->orWhere('due_date', 'like', "%{$searchTerm}%");
        //             });
        //         })
        //         ->latest()
        //         ->paginate(10)->withQueryString();
                
        //     $assigned_task = $department->task()
        //         ->with('assignedBySuperUser')
        //         ->when($searchTerm, function ($query, $searchTerm) {
        //             $query->where(function ($subQuery) use ($searchTerm) {
        //                 $subQuery->where('title', 'like', "%{$searchTerm}%")
        //                          ->orWhere('description', 'like', "%{$searchTerm}%")
        //                          ->orWhere('status', 'like', "%{$searchTerm}%" )
        //                          ->orWhere('due_date', 'like', "%{$searchTerm}%");
        //             });
        //         })
        //         ->latest()
        //         ->paginate(40)->withQueryString();
               
        //         $assigned_task->getCollection()->transform(function ($task) {
        //             $task->is_assigned = 1;  // Add the attribute
        //             return $task;
        //         });
        //         $created_task->getCollection()->transform(function ($task) {
        //             $task->is_assigned = 2;  // Add the attribute
        //             return $task;
        //         });
            
        // }

        // if(auth()->user()->hasRole('super users')){
            
        //     //$department = Department::find(auth()->user()->department_id);
        //     $created_task = Task::with('assignedBySuperUser')
        //         ->where('assigned_by_super_user', auth()->user()->id)
        //         ->when($searchTerm, function ($query, $searchTerm) {
        //             $query->where(function ($subQuery) use ($searchTerm) {
        //                 $subQuery->where('title', 'like', "%{$searchTerm}%")
        //                          ->orWhere('description', 'like', "%{$searchTerm}%")
        //                          ->orWhere('status', 'like', "%{$searchTerm}%" )
        //                          ->orWhere('due_date', 'like', "%{$searchTerm}%");
        //             });
        //         })
        //         ->latest()
        //         ->paginate(10)->withQueryString();
  
        //    // $assigned_task = $department->task()->with('assignedByDepartment')->paginate(10);
        //     $assigned_task = [];

        //     $created_task->getCollection()->transform(function ($task) {
        //         $task->is_assigned = 2;  // Add the attribute
        //         return $task;
        //     });
        // }

        // if($type == 'unit' && !$this->isRole('unit heads') && !$this->isRole('super users')){
        //     //ordinary users cannot create task;
        //     $created_task = [];
        //     //$created_task = Task::with('assignedBy')->where('assigned_by', auth()->user()->id)->latest()->paginate(10);
        //     $assigned_task = auth()->user()->task()
        //         ->with('assignedBy')
        //         ->when($searchTerm, function ($query, $searchTerm) {
        //             $query->where(function ($subQuery) use ($searchTerm) {
        //                 $subQuery->where('title', 'like', "%{$searchTerm}%")
        //                          ->orWhere('description', 'like', "%{$searchTerm}%")
        //                          ->orWhere('status', 'like', "%{$searchTerm}%" )
        //                          ->orWhere('due_date', 'like', "%{$searchTerm}%");
        //             });
        //         })
        //         ->latest()
        //         ->paginate(10)->withQueryString();
            
        //         $assigned_task->getCollection()->transform(function ($task) {
        //             $task->is_assigned = 1;  // Add the attribute
        //             return $task;
        //         });
        // }

        // if($type == 'division' && !$this->isRole('division heads') && !$this->isRole('super users')){
        //     //ordinary users cannot create task;
        //     $created_task = [];
        //     //$created_task = Task::with('assignedBy')->where('assigned_by', auth()->user()->id)->latest()->paginate(10);
        //     $assigned_task = auth()->user()->task()
        //         ->with('assignedBy')
        //         ->when($searchTerm, function ($query, $searchTerm) {
        //             $query->where(function ($subQuery) use ($searchTerm) {
        //                 $subQuery->where('title', 'like', "%{$searchTerm}%")
        //                          ->orWhere('description', 'like', "%{$searchTerm}%")
        //                          ->orWhere('status', 'like', "%{$searchTerm}%" )
        //                          ->orWhere('due_date', 'like', "%{$searchTerm}%");
        //             });
        //         })
        //         ->latest()
        //         ->paginate(10)->withQueryString();
            
        //         $assigned_task->getCollection()->transform(function ($task) {
        //             $task->is_assigned = 1;  // Add the attribute
        //             return $task;
        //         });
        // }

        // if($type == 'department' && !$this->isRole('directors') && !$this->isRole('super users')){
        //     //ordinary users cannot create task;
        //     $created_task = [];
        //     //$created_task = Task::with('assignedBy')->where('assigned_by', auth()->user()->id)->latest()->paginate(10);
        //     $assigned_task = auth()->user()->task()
        //         ->with('assignedBy')
        //         ->when($searchTerm, function ($query, $searchTerm) {
        //             $query->where(function ($subQuery) use ($searchTerm) {
        //                 $subQuery->where('title', 'like', "%{$searchTerm}%")
        //                          ->orWhere('description', 'like', "%{$searchTerm}%")
        //                          ->orWhere('status', 'like', "%{$searchTerm}%" )
        //                          ->orWhere('due_date', 'like', "%{$searchTerm}%");
        //             });
        //         })
        //         ->latest()
        //         ->paginate(10)->withQueryString();
            
        //         $assigned_task->getCollection()->transform(function ($task) {
        //             $task->is_assigned = 1;  // Add the attribute
        //             return $task;
        //         });
        // }

        return ['created_task'=>$created_task, 'assigned_task'=>$assigned_task];

    }

    public function index(Request $request){
        //$userworkflows = Workflow::with('task')->where('created_by', auth()->user()->id)->latest()->get();

        $allTask = $this->getBranchTask($request);
        $created_task = $allTask['created_task'];
        $assigned_task = $allTask['assigned_task'];
        
        $created_task = $created_task? $created_task: [];
        $assigned_task = $assigned_task? $assigned_task: [];

        return inertia('Workflow/Workflows', compact('created_task', 'assigned_task'));
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

    public function searchWorkflow(Request $request){
        $allTask = $this->getBranchTask($request);
        return response()->json($allTask);
    }
}
