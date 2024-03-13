<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;

class DashboardController extends Controller
{
    public function index(){
        if(auth()->user()->hasRole('data entry'))
        $userDepartment = auth()->user()->department()->with(['archive'=>function($query){
            $query->with('filePath');
        }])->first();
        return inertia('dashboard/index', compact('userDepartment'));
    }

    public function BranchesOverview(){
       $branches = Department::with(['folder','division'=>function($query){
            $query->with('unit');
       }])->where('id', auth()->user()->department_id)->get();
      
        return inertia('dashboard/BranchesOverview', compact('branches'));
    }

    public function support(){
        return inertia('dashboard/Support');
    }

    public function settings(){
        $user = auth()->user();
        return inertia('dashboard/settings/Settings', compact('user'));
    }

}
