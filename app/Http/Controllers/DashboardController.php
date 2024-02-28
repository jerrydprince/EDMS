<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;

class DashboardController extends Controller
{
    public function index(){
        return inertia('dashboard/index');
    }

    public function BranchesOverview(){
       $branches = Department::with(['folder','division'=>function($query){
            $query->with('unit');
       }])->where('id', auth()->user()->department_id)->get();
      
        return inertia('dashboard/BranchesOverview', compact('branches'));
    }

}
