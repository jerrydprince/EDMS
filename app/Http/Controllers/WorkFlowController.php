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

    public function getBranchTask($request){

        $type = $this->getBranchType();
        $searchTerm = $request->search; 
        $created_task = [];
        $assigned_task = [];

        $created_task = Task::where('created_by', auth()->user()->id)->latest()->paginate(10)->withQueryString();
        $assigned_task = Task::withWhereHas('assignments', function($query){
            $query->where('assigned_to', auth()->user()->id);
        })->latest()->paginate(10)->withQueryString();
     
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
