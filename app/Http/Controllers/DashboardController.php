<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Division;
use App\Models\Unit;

class DashboardController extends Controller
{
    public function index(){
       // if(auth()->user()->hasRole('data entry')){
       
        if(auth()->user()->unit_id){
            // $branch = auth()->user()->unit()->with(['archive'=>function($query){
            //     $query->with(['filePath', 'archiveDocs'])->paginate(10);
            // }])->first();
       

            $unit = Unit::find(auth()->user()->unit_id);
            $branch= $unit->archive()->with(['filePath', 'archiveDocs'])->paginate(10);
            return inertia('dashboard/index', compact('branch'));
        }

        if(auth()->user()->division_id){
            // $branch = auth()->user()->division()->with(['archive'=>function($query){
            //             $query->with(['filePath', 'archiveDocs']);
            //         }])->first();

            $division = Division::find(auth()->user()->division_id);
            $branch= $division->archive()->with(['filePath', 'archiveDocs'])->paginate(10);

             return inertia('dashboard/index', compact('branch'));
        }
        
        // $branch = auth()->user()->department()->with(['archive'=>function($query){
        //     $query->with(['filePath', 'archiveDocs']);
        // }])->first();

        $department = Department::find(auth()->user()->department_id);
        $branch= $department->archive()->with(['filePath', 'archiveDocs'])->paginate(10);
           
        return inertia('dashboard/index', compact('branch'));
       // }
       
    }

    public function BranchesOverview(){
    //    $branches = Department::with(['folder','division'=>function($query){
    //         $query->with('unit');
    //    }])->where('id', auth()->user()->department_id)->get();

    $department = Department::with('folder')->withCount('archive')->where('id', auth()->user()->department_id)->get();
    $division = '';
    $unit = '';
    if(isset(auth()->user()->division_id)){
        $division = Division::where('id', auth()->user()->division_id)->withCount('archive')->get();
    }
    
    if(isset(auth()->user()->unit_id)){
        $unit = Unit::where('id', auth()->user()->unit_id)->withCount('archive')->first();
    }
        return inertia('dashboard/BranchesOverview', compact('department','division','unit'));
    }

    public function support(){
        return inertia('dashboard/Support');
    }

    public function settings(){
        $user = auth()->user();
        return inertia('dashboard/settings/Settings', compact('user'));
    }

}
