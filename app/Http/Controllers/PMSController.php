<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\User;
use App\Models\Certificate;

class PMSController extends Controller
{
    public function index(){
        $user = auth()->user();
      
        if($user->hasRole('directors')){
            $users = User::with('roles')->where('department_id', $user->department_id)->get();
            $departments = [];
        }else
        if($user->hasRole('unit heads')){
            $users = User::with('roles')->where('unit_id', $user->unit_id)->get();
            $departments = [];
        }else
        if($user->hasRole('division heads')){
            $users = User::with('roles')->where('division_id', $user->division_id)->get();
            $departments = [];
        }else
        if($user->hasRole('super users') || $user->hasRole('super admin') || $user->hasRole('admin')){
            // $departments = Department::orderBy('title', 'ASC')->get();
            // if($departments){
            //     $users = User::with('roles')->where('department_id', $departments->first()->id)->get();
            // }else{
            //     $users = [];
            // }
            $users = [];
            $departments = [];
        }else{
            $users = [];
            $departments = [];
        }

        return inertia('PMS/index', compact('departments', 'users'));
    }

    public function overview(){
       
        return inertia('PMS/Overview');
    }

    public function settings(){
        return inertia('PMS/Settings');
    }

    public function getDepartmentUsers(Request $request){
        $users = User::with('roles')->where('department_id', $request->id)->get();
        return response()->json($users);
    }

    public function completionRate($task){
        if($task){
            //  // Count tasks by status
            // $completedTasks = Task::where('status', 'completed')->count();
            // $uncompletedTasks = Task::where('status', 'uncompleted')->count();
            // $pendingTasks = Task::where('status', 'pending')->count();

            $completedTasks = $task->filter(function($value){
                return $value->status == 'completed';
            });

            $uncompletedTasks = $task->filter(function($value){
                return $value->status == 'uncompleted';
            });

            $pendingTasks = $task->filter(function($value){
                return $value->status == 'pending';
            });

            $totalTasks = $task->count();
            

            // // Calculate rates
            $completionRate = $totalTasks > 0 ? (count($completedTasks) / $totalTasks) * 100 : 0;
            $uncompletedRate = $totalTasks > 0 ? (count($uncompletedTasks) / $totalTasks) * 100 : 0;
            $pendingRate = $totalTasks > 0 ? (count($pendingTasks) / $totalTasks) * 100 : 0;

            // // Return the rates
            return [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'uncompleted_tasks' => $uncompletedTasks,
                'pending_tasks' => $pendingTasks,
                'completion_rate' => $completionRate,
                'uncompleted_rate' => $uncompletedRate,
                'pending_rate' => $pendingRate,
            ];
        }
    }

    public function viewPMSUserProfile($id){
        $user = User::with(['unit', 'leave'=>function($query){
            $query->latest()->first();
        }, 'rating', 'division', 'certificates', 'roles', 'department', 'task'=>function($query){
            $query->withCount('comments')->with('assignedBy');
        }, 'attendance'=>function($query){
            $query->orderBy('created_at', 'DESC');
        }])->find($id);
        $task = $user->task;
        $ratings = $user->rating;
        $ratings = $ratings->groupBy('key');
        $taskCompletionRate = $this->completionRate($task);
       
        return inertia('PMS/ViewProfile', compact('user','ratings', 'taskCompletionRate'));
    }

    public function viewPMSCurrentUserProfile(){
        $user = User::with(['unit', 'leave'=>function($query){
            $query->latest()->first();
        }, 'rating', 'division', 'certificates', 'roles', 'department', 'task'=>function($query){
            $query->withCount('comments')->with('assignedBy');
        }, 'attendance'=>function($query){
            $query->orderBy('created_at', 'DESC');
        }])->find(auth()->user()->id);
        $task = $user->task;
        $ratings = $user->rating;
        $ratings = $ratings->groupBy('key');
        $taskCompletionRate = $this->completionRate($task);
       
        return inertia('PMS/ViewProfile', compact('user','ratings', 'taskCompletionRate'));
    }

    public function getAttendance($id){
        $user = User::with([ 'attendance'=>function($query){
            $query->orderBy('created_at', 'DESC');
        }])->find($id);
        return inertia('PMS/Attendance', compact('user'));
    }

    public function saveCertificate(Request $request){
        $request->validate([
            'title' => 'required',
            'starting_date'=> 'required',
            'completion_date' => 'required',
            'organization' =>'required',
            'file' => 'required'
        ]);

        if($request->file('file')){
            $file = $request->file('file');
            // $extension = strtolower($file->getClientOriginalExtension());
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = "/certificate/".auth()->user()->id;
            $file->move(public_path($path),$fileName);
        }

        $cert = new Certificate();
        $cert->title = $request->title;
        $cert->starting_date = $request->starting_date;
        $cert->completion_date = $request->completion_date;
        $cert->organization = $request->organization;
        $cert->file_path = $path.'/'.$fileName;
        $cert->user_id = $request->user_id;
        $cert->save();
    }

    public function deleteCertificate(Request $request){
        $cert = Certificate::find($request->id);
        $cert->delete();
        return redirect()->back();
    }
}

