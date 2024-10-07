<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Division;
use App\Models\Unit;
use App\Models\Metadata;
use App\Models\DocumentType;
use App\Models\User;
use App\Models\Folder;
use App\Models\FileType;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use App\Models\Task;

class AdminController extends Controller
{
    public function index(){
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('admin')){
             $departments = Department::with(['division'=>function($query){
                $query->with('unit');
            }])->get()->take(4);
            return inertia('admin/index', compact('departments'));
        }

        if($user->hasRole('data entry')){
            return redirect()->to('/edms');
        }
      
        if($user->hasRole('super users') || $user->hasRole('directors') || $user->hasRole('division heads')
         || $user->hasRole('unit heads') || $user->hasRole('regular users')){

            if($user->hasRole('division heads')){
                $division = Division::find(auth()->user()->division_id);
                $created_task = Task::with('assignedByDivision')
                    ->where('assigned_by_division', auth()
                    ->user()->division_id)
                    ->latest()
                    ->paginate(10);
                  
                    $created_task->getCollection()->transform(function ($task) {
                        $task->is_assigned = 2;  // Add the attribute
                        return $task;
                    });
            }
            else
            if($user->hasRole('unit heads')){
                $unit = Unit::find(auth()->user()->unit_id);
                $created_task = Task::with('assignedBy')
                    ->where('assigned_by_unit', auth()->user()->unit_id)
                    ->latest()
                    ->paginate(10);
                    
                    $created_task->getCollection()->transform(function ($task) {
                        $task->is_assigned = 2;  // Add the attribute
                        return $task;
                    });
            }
            else
            if($user->hasRole('directors')){
                $department = Department::find(auth()->user()->department_id);
                $created_task = Task::with('assignedByDepartment')
                    ->where('assigned_by_department', auth()->user()->department_id)
                    ->latest()
                    ->paginate(10);
                    
                    $created_task->getCollection()->transform(function ($task) {
                        $task->is_assigned = 2;  // Add the attribute
                        return $task;
                    });
                
            }
            else
            if(auth()->user()->hasRole('super users')){
                
                //$department = Department::find(auth()->user()->department_id);
                $created_task = Task::with('assignedBySuperUser')
                    ->where('assigned_by_super_user', auth()->user()->id)
                    ->latest()
                    ->paginate(10);
    
                $created_task->getCollection()->transform(function ($task) {
                    $task->is_assigned = 2;  // Add the attribute
                    return $task;
                });
            }else{
                $created_task = [];
            }

            $mails = auth()->user()->mails()->orderBy('created_at', 'desc')->get();

            return inertia('dashboard/Dashboard', compact('created_task', 'mails'));
        }
    }

    public function BranchSetup(){
        $departments = Department::with(['documentType','fileType:id,id','division'=>function($query){
            $query->with('unit');
        }])->withSum('folder','file_count')->withCount('user','division')->get();

        $department = $departments->count();
        // $division = $departments->pluck('division')->flatten()->count();
        // $unit = $departments->pluck('division.unit')->flatten()->count();
        $division = $departments->sum('division_count');
        $unit = $departments->pluck('division')->flatten()->pluck('unit')->flatten()->count();
        $users = User::count();
        $departments->each(function ($dept) {
            $dept->updated_at_formatted = $dept->updated_at->diffForHumans();
        });
        $documentTypes = DocumentType::get();
       
        return inertia('admin/BranchSetup', compact('department','division','unit', 'users', 'departments','documentTypes'));
    }

    public function deleteDepartment(Request $request){
        Department::find($request->id)->delete();
        return redirect()->back();
    }

    // public function duplicateDepartment(Request $request){
    //     $dept = Department::with('documentType')->find($request->id);
    //     $new_dept = $dept->replicate();
    //     $new_dept->title = $new_dept->title."_duplicate";
    //     $new_dept->save();

    //     $id= $dept->documentType->pluck('id');
    //     $new_dept->documentType()->attach($id);
    //     return redirect()->back();
    // }

    public function MetaDataSetup(){
        $metadatas = Metadata::get();
        
        return inertia('admin/MetaDataSetup', compact('metadatas'));
    }

    public function CreateDepartmentDivisionUnit(Request $request){
       
        DB::transaction(function () use($request) {
            //create department
            $dept = new Department();
            $dept->title = $request->department['title'];
            $dept->description = $request->department['description'];
            $dept->head = 0;
            $dept->save();

            //set the heads
           if($request->heads){
            $ids = collect($request->heads)->pluck('id');
            User::whereIn('id', $ids)->update(['is_department_head'=>1]);
           }

            //create division
            // $div = new Division();
            // $div->title = $request->division['title'];
            // $div->description = $request->division['description'];
            // $div->department_id = $dept->id;
            // $div->head = 0;
            // $div->save();

            //create unit
            // $unit = new Unit();
            // $unit->title = $request->unit['title'];
            // $unit->description = $request->unit['description'];
            // $unit->head = 0;
            // $unit->division_id = $div->id;
            // $unit->save();
        });

        //return redirect()->to('/admin/branch-setup');
        return redirect()->back();
    }

    public function editDepartment(Request $request){
        $dept = Department::find($request->id);
        $dept->description = $request->description;
        $dept->title = $request->title;
        $dept->save();
        return redirect()->back();
    }
    
    public function getDivision(Request $request){
        $department = Department::with(['division'=>function($query){
            $query->with(['documentType','fileType'])->withSum('folder', 'file_count')->withCount('users','unit');
        }])->where('id', $request->id)->first();
        $department_name = $department->title;
        $division = $department->division;
        $division->each(function ($div) {
            $div->updated_at_formatted = $div->updated_at->diffForHumans();
        });
       
        $documentTypes = DocumentType::get();
        return inertia('branch/Divisions', compact('division', 'department_name','documentTypes', 'department'));
    }

    public function deleteDivision(Request $request){
        Division::find($request->id)->delete();
        return redirect()->back();
    }

    // public function duplicateDivision(Request $request){
    //     $div = Division::with('documentType')->find($request->id);
    //     $new_div = $div->replicate();
    //     $new_div->title = $new_div->title."_duplicate";
    //     $new_div->save();

    //     $id = $div->documentType->pluck('id');
    //     $new_div->documentType()->attach($id);
    // }

    public function getUnits(Request $request){
        $div = Division::with(['unit'=>function($query){
            $query->with(['documentType', 'fileType'])->withSum('folder','file_count')->withCount('users');
        }])->where('id', $request->id)->first();
        $division_name = $div->title;
        $units = $div->unit;
        $units->each(function($unit){
            $unit->updated_at_formatted = $unit->updated_at->diffForHumans();
        });
        $documentTypes = DocumentType::get();
        return inertia('branch/Units', compact('units', 'division_name','documentTypes'));
    }

    public function deleteUnit(Request $request){
        Unit::find($request->id)->delete();
        return redirect()->back();
    }

    // public function duplicateUnit(Request $request){
    //     $unit = Unit::with('documentType')->find($request->id);
    //     DB::transaction(function () use($unit) {
    //         $new_unit = $unit->replicate();
    //         $new_unit->title = $new_unit->title."_duplicate";
    //         $new_unit->save();

    //         $ids = $unit->documentType->pluck('id');
    //         $new_unit->documentType()->attach($ids);
    //     });
        
    // }

    public function addDocumentTypeToDepartment(Request $request){

        $dept = Department::find($request->id);
        $dept->documentType()->sync($request->doctypes);
        return redirect()->back();
    }

    public function addDocumentTypeToDivision(Request $request){

        $div = Division::find($request->id);
        $div->documentType()->sync($request->doctypes);
        return redirect()->back();
    }

    public function addDocumentTypeToUnit(Request $request){

        $unit = Unit::find($request->id);
        $unit->documentType()->sync($request->doctypes);
        return redirect()->back();
    }

    public function AddDivisionToDepartment(Request $request){

        $request->validate([
            'title'=>'required',
            'department_id'=>'required'
        ]);
        //create division
        $div = new Division();
        $div->title = $request->title;
        $div->description = $request->description;
        $div->department_id = $request->department_id;
        $div->head = 0;
        $div->save();

        if($request->head){
            $ids = collect($request->head)->pluck('id');
            User::whereIn('id', $ids)->update(['is_division_head'=>1]);
        }

        return redirect()->back();
    }

    public function addUnitToDivision(Request $request){
        $request->validate([
            'title' =>'required',
            'division_id' => 'required'
        ]);

        $unit = new Unit();
        $unit->title = $request->title;
        $unit->description = $request->description;
        $unit->division_id = $request->division_id;
        $unit->head = 0;
        $unit->save();
        
        return redirect()->back();
    }

    public function editDivision(Request $request){
        $request->validate([
            'title'=> 'required'
        ]);

        $div = Division::find($request->id);
        $div->title = $request->title;
        $div->description = $request->description;
        $div->save();
        return redirect()->back();
    }

    public function editUnit(Request $request){
        $request->validate([
            'title'=>'required'
        ]);
        $unit = Unit::find($request->id);
        $unit->title = $request->title;
        $unit->description = $request->description;
        $unit->save();
        return redirect()->back();
    }

    public function searchDepartmentHead(Request $request){
 
             $department_head = User::where('is_department_head', '<>',1)->where('first_name','LIKE', "%".$request->name)
                ->orWhere('last_name','LIKE', "%". $request->name."%")
                ->orWhere('email','LIKE', "%". $request->name."%")
                ->get();
        return response()->json($department_head);
       
    }

    public function searchDivisionHead(Request $request){
 
        $division = User::where('is_division_head', '<>',1)->where('first_name','LIKE', "%".$request->name)
            ->orWhere('last_name','LIKE', "%". $request->name."%")
            ->orWhere('email','LIKE', "%". $request->name."%")
            ->get();
    return response()->json($division);
    
    }

    public function searchUnitHead(Request $request){
 
        $unit = User::where('is_unit_head', '<>',1)->where('first_name','LIKE', "%".$request->name)
            ->orWhere('last_name','LIKE', "%". $request->name."%")
            ->orWhere('email','LIKE', "%". $request->name."%")
            ->get();
    return response()->json($unit);
    
    }

    public function assignRoleToSuperAdmin(){
        auth()->user()->assignRole('super admin');
       echo "success";
    }

    //get the file type
    public function fileType(){
        $filetypes = FileType::get();
        $filetypes->each(function ($filetyp) {
            $filetyp->updated_at_formatted = $filetyp->updated_at->diffForHumans();
        });
        return inertia('admin/FileType', compact('filetypes'));
    }

    public function createFileType(Request $request){
        $request->validate([
            'name'=>'required'
        ]);

        $filetype = $request->id? FileType::find($request->id) : new FileType();
        $filetype->name = $request->name;
        $filetype->description = $request->description;
        $filetype->save();
        return redirect()->back();
    }

    public function deleteFileType(Request $request){
        FileType::find($request->id)->delete();
        return redirect()->back();
    }

    public function assignFileTypesToBranch(Request $request){
        if($request->branch == 'department'){
            $department = Department::find($request->id);
            $department->fileType()->sync($request->filetypes);
            return redirect()->back();
        }

        if($request->branch == 'division'){
            $division = Division::find($request->id);
            $division->fileType()->sync($request->filetypes);
            return redirect()->back();
        }

        if($request->branch == 'unit'){
            $unit = Unit::find($request->id);
            $unit->fileType()->sync($request->filetypes);
            return redirect()->back();
        }
    }

    public function getFileType(){
        $fileTypes = FileType::get();
        return response()->json($fileTypes);
    }
   
}
