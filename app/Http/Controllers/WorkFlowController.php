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
    public function index(){
        //$userworkflows = Workflow::with('task')->where('created_by', auth()->user()->id)->latest()->get();
        $type = $this->getBranchType();
       
        if($this->isRole('division heads')){
            $division = Division::find(auth()->user()->division_id);
            $created_task = Task::with('assignedByDivision')->where('assigned_by_division', auth()->user()->division_id)->latest()->paginate(10);
            $assigned_task = $division->task()->with('assignedByDepartment')->paginate(10);
        }

        if($this->isRole('unit heads')){
            $unit = Unit::find(auth()->user()->unit_id);
            $created_task = Task::with('assignedBy')->where('assigned_by_unit', auth()->user()->unit_id)->paginate(10);
            $assigned_task = $unit->task()->with('assignedByDivision')->paginate(10);
            //dd($assigned_task);
        }

        if($this->isRole('directors')){
            $department = Department::find(auth()->user()->department_id);
            $created_task = Task::with('assignedByDepartment')->where('assigned_by_department', auth()->user()->department_id)->paginate(10);
            $assigned_task = $department->task()->with('assignedBySuperUser')->paginate(10);
            
        }

        if(auth()->user()->hasRole('super users')){
            
            //$department = Department::find(auth()->user()->department_id);
            $created_task = Task::with('assignedBySuperUser')->where('assigned_by_super_user', auth()->user()->id)->paginate(10);
           // $assigned_task = $department->task()->with('assignedByDepartment')->paginate(10);
            $assigned_task = [];
        }

        if($type == 'unit' && !$this->isRole('unit heads')){
            //ordinary users cannot create task;
            $created_task = [];
            //$created_task = Task::with('assignedBy')->where('assigned_by', auth()->user()->id)->latest()->paginate(10);
            $assigned_task = auth()->user()->task()->with('assignedBy')->paginate(10);
        }

        $created_task = $created_task? $created_task: [];
        $assigned_task = $assigned_task? $assigned_task: [];

        return inertia('Workflow/Workflows', compact('created_task', 'assigned_task'));
    }

    // public function createWorkFlow(Request $request){
    //     $wkflow = new Workflow();
    //     $wkflow->title = $request->title;
    //     $wkflow->emoji = $request->emoji;
    //     $wkflow->created_by = auth()->user()->id;
    //     $wkflow->save();
    //     return redirect()->back();
    // }

    // public function createTask(Request $request){
    //     $request->validate([
    //         'start' => 'required',
    //         'end' => 'required',
    //         'title' => 'required',
    //         'workflow_id' => 'required'
    //     ]);

    //     if($request->file('file')){
    //         $file = $request->file('file');
    //        // $extension = strtolower($file->getClientOriginalExtension());
    //         $fileName = time() . '_' . $file->getClientOriginalName();
    //         $path = "/workflow";
    //         $file->move(public_path($path),$fileName);
    //     }

    //     $task = new Task();
    //     $task->start = $request->start;
    //     $task->end = $request->end;
    //     $task->title = $request->title;
    //     $task->description = $request->description;
    //     $task->workflow_id = $request->workflow_id;
    //     $task->document_type = $request->documentType;
    //     if($request->file('file')){
    //         $task->file_path = $path.'/'.$fileName;
    //     }
    //     $task->save();

    //     $ids = explode(',', $request->userIds);
    //     $task->user()->sync($ids);

    //     //user notification
    //     $users = User::whereIn('id', $ids)->get();
    //     Notification::send($users, new TaskNotification($request->title));

    //     return redirect()->back();
    // }

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
}
