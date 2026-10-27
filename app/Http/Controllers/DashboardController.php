<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Division;
use App\Models\Unit;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends Controller
{
    public function index(){
       // if(auth()->user()->hasRole('record manager')){
       $task = [];
        $branch = new LengthAwarePaginator([], 0, 10);

       if(auth()->user()->task()->exists()){
        $task = auth()->user()->task()->with(['workflowVersion'])->where('status', 'pending')->take(10)->get();
       }
    
        if(auth()->user()->unit_id){
            $unit = Unit::find(auth()->user()->unit_id);
            
            if($unit->archive()->exists()){
                $branch= $unit->archive()->with(['filePath', 'archiveDocs'])->latest()->take(10)->get();
            }
    
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $perPage = 10;
            $total = $branch->count();
            $branch = new LengthAwarePaginator($branch, $total, $perPage, $currentPage, [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);
           // dd($paginator);
            return inertia('dashboard/index', compact('branch', 'task'));
        }

        if(auth()->user()->division_id){

            $division = Division::find(auth()->user()->division_id);
            if($division->archive()->exists()){
                $branch= $division->archive()->with(['filePath', 'archiveDocs'])->paginate(10);
            }

             return inertia('dashboard/index', compact('branch', 'task'));
        }

       if(auth()->user()->department_id){
        $department = Department::find(auth()->user()->department_id);
        if($department->archive()->exists()){
            $branch= $department->archive()->with(['filePath', 'archiveDocs'])->paginate(10);
        }
           
        return inertia('dashboard/index', compact('branch', 'task'));
       }
    
       return inertia('dashboard/index', compact('branch', 'task'));
    }

    public function agfDashboard() { return inertia('dashboard/AGFDashboard'); }

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

}
