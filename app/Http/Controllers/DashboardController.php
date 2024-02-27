<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;

class DashboardController extends Controller
{
    public function index(){
        return inertia('dashboard/index');
    }

    public function Branch(){
       $branches = Department::with(['division'=>function($query){
            $query->with('unit');
       }])->find(auth()->user()->department_id);
      
        return inertia('dashboard/BranchesOverview', compact('branches'));
    }
}
