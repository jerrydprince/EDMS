<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Division;
use App\Models\File;
use App\Models\FileMovement;
use App\Models\Unit;
use Illuminate\Contracts\Validation\Validator;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as FacadesValidator;
//use PhpOffice\PhpWord\Shared\Validate;
use Illuminate\Support\Facades\DB;
use App\Events\RegistryEvent;
use App\Models\FinanceFilePaths;
use App\Notifications\RegistryNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use PhpParser\Node\Expr\FuncCall;
use App\Models\FinanceFiles;
use App\Models\RegistryFile;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller\CorrespondenceController;

class RegistryController extends Controller
{
    public function adminRegistry()
    {
        //$files = File::with('department')->orderBy('created_at', 'desc')->get();
        $department = Department::get();
        $division = Division::get();
        $unit = Unit::get();

        return inertia('Registry/AdminRegistry', compact('department', 'division', 'unit'));
    }

    public function agfTasks(Request $request)
    {
        return redirect('/dashboard/workflow');
    }

    public function getWorkflows()
    {
        $workflows = \App\Models\WorkflowVersion::with('workflow')
            ->has('workflow')
            ->where('is_published', true)
            ->latest()
            ->get();
            
        return response()->json(['data' => $workflows]);
    }

    public function registry(Request $request)
    {
        $department = Department::get();
        $division = Division::get();
        $unit = Unit::get();
        
        $correspondences = \App\Models\Correspondence::with(['correspondenceFile', 'sentBy.department'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Registry/Registry', compact('department', 'division', 'unit', 'correspondences'));
    }

    public function getCorrespondences(Request $request)
    {
        $search = $request->search;
        $query = \App\Models\Correspondence::with(['correspondenceFile', 'sentBy.department']);
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $correspondences = $query->latest()->paginate(15);
        return response()->json($correspondences);
    }
    // public function login()
    // {
    //     return Inertia::render('Registry/login');
    // }
    public function getDepartment(Request $request)
    {
        if($request->id){
            $department = Department::with(['division.unit'])->find($request->id);
        }else{
            $department = Department::with(['division.unit'])->find(auth()->user()->department_id);
        }
        
        return  response()->json([
            'message' => 'All departments with division and units',
            'data' => $department
        ]);
    }

    public function getFileByName($departmentId, $name)
    {
        $decodedFileName = urldecode($name);
        $file = File::where('name', $decodedFileName)
            // ->where('sent_to_department_id', $departmentId)
            ->first();
        if ($file) {
            return response()->json(['file_id' => $file->id]);
        } else {
            return response()->json(['error' => 'File not found'], 404);
        }
    }

    public function transferFile(Request $request, $id)
    {
        // $validated = FacadesValidator::make(
        //     $request->all(),
        //     [
        //         'to_department' => 'required|exists:departments,id',
        //     ]
        // );

        // if ($validated->fails()) {
        //     // return response()->json([
        //     //     'message' => $validated->errors(),
        //     //     'status' => false
        //     // ], 400);
        //     return redirect()->back()->withErrors();
        // }
        $file = FileMovement::find($id);
        // $file->sent_to_department_id = $request->to_department;
        // $file->sent_from_department_id = auth()->user()->department_id;
        // $file->is_incoming = true;
        // $file->status = 'pending';
        // $file->is_outgoing = false;
        // $file->is_forwarded = 1;
        // $file->save();
       //dd($request->branch);

       $current_branch_id = '';
       $user = auth()->user();
       $loggedInUser = $this->getLogedInUserBranch();
       if($loggedInUser == 'unit'){
        $current_branch_id = $user->unit_id;
       }
       if($loggedInUser == 'division'){
        $current_branch_id = $user->division_id;
       }
       if($loggedInUser == 'department'){
        $current_branch_id = $user->department_id;
       }


        $newfile = $file->replicate();
        if($request->branch['branch_type']==='unit'){
            $newfile->sent_to_unit = $request->branch['id'];
            $newfile->sent_to_division = null;
            $newfile->sent_to_department = null;
        }
        if($request->branch['branch_type']==='division'){
            $newfile->sent_to_division = $request->branch['id'];
            $newfile->sent_to_unit = null;
            $newfile->sent_to_department = null;

        }
        if($request->branch['branch_type']==='department'){
            $newfile->sent_to_department = $request->branch['id'];
            $newfile->sent_to_unit = null;
            $newfile->sent_to_division = null;
        }
        if(auth()->user()->hasAnyPermission(['agf registry view', 'agf registry create'])){
            if ($request->branch['branch_type'] == 'agf') {
                $newfile->sent_to_agf = $request->branch['id'] ?? true;
                $newfile->sent_to_department = null;
            } else {
                $newfile->sent_to_agf = null;
                $newfile->sent_to_department = $request->branch['id'];
            }
            $newfile->sent_to_unit = null;
            $newfile->sent_to_division = null;

            $newfile->sent_from_unit = null;
            $newfile->sent_from_division = null;
            $newfile->sent_from_department = null;
            $newfile->sent_from_agf = true;
            
            $newfile->unit_id = null;
            $newfile->division_id = null;
            $newfile->department_id = null;
        } else {
            if($loggedInUser === 'unit'){
                $newfile->sent_from_unit = $current_branch_id;
                $newfile->sent_from_division = null;
                $newfile->sent_from_department = null;
                $newfile->sent_from_agf = null;

                $newfile->unit_id = $user->unit_id;
                $newfile->division_id = null;
                $newfile->department_id = null;
            }
            if($loggedInUser === 'division'){
                $newfile->sent_from_division = $current_branch_id;
                $newfile->sent_from_unit = null;
                $newfile->sent_from_department = null;
                $newfile->sent_from_agf = null;

                $newfile->unit_id = null;
                $newfile->division_id = $user->division_id;
                $newfile->department_id = null;
            }
            if($loggedInUser === 'department' && $request->branch['branch_type'] !='agf'){
                $newfile->sent_from_department = $current_branch_id;
                $newfile->sent_from_division = null;
                $newfile->sent_from_unit = null;
                $newfile->sent_from_agf = null;

                $newfile->unit_id = null;
                $newfile->division_id = null;
                $newfile->department_id = $user->department_id;
            }
            if($loggedInUser === 'department' && $request->branch['branch_type'] == 'agf'){
                $newfile->sent_from_unit = null;
                $newfile->sent_from_division = null;
                $newfile->sent_from_department = $current_branch_id;
                $newfile->sent_from_agf = null;

                $newfile->unit_id = null;
                $newfile->division_id = null;
                $newfile->department_id = null;

                $newfile->sent_to_agf = true;
                $newfile->sent_to_unit = null;
                $newfile->sent_to_division = null;
                $newfile->sent_to_department = null;
            }
        }
        
        // Ensure AGF task is generated when creating a file sent to AGF
        if ($request->branch['branch_type'] == 'agf') {
            $agf = \App\Models\User::role('super user')->first();
            if($agf){
                $task = new \App\Models\Task();
                $task->title = 'Registry File: ' . $newfile->name;
                $task->file_id = $newfile->id;
                $task->created_by = auth()->id();
                $task->due_date = \Carbon\Carbon::now()->addDays(5)->format('Y-m-d');
                $task->status = 'pending';
                
                $masterWorkflowVersionId = \App\Models\WorkflowVersion::whereHas('workflow', function($q) {
                    $q->where('title', 'Master Routing Workflow');
                })->where('is_published', true)->value('id');
                
                if ($masterWorkflowVersionId) {
                    $task->workflow_version_id = $masterWorkflowVersionId;
                }
                $task->save();
                
                $assignment = new \App\Models\TaskAssignment();
                $assignment->task_id = $task->id;
                $assignment->assigned_to = $agf->id;
                $assignment->assigned_by = auth()->id();
                $assignment->status = 'pending';
                $assignment->save();
                
                $workflowService = app(\App\Services\GenericWorkflowService::class);
                try {
                    $workflowService->start($task, auth()->user(), []);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Generic Workflow Start Error: ' . $e->getMessage());
                }
            }
        }

        $newfile->updated_at = \Carbon\Carbon::now();
        $newfile->status = 'pending';
        $newfile->save();

        $file->is_forwarded = 1;
        $file->status = 'forwarded';
        $file->save();
        return redirect()->back();
    }

   // public function forwardFile($fileId)
    // {

    //     $getfile = File::findOrFail($fileId);
    //     if (!$getfile->is_incoming) {
    //         return response()->json(['message' => 'File is not marked as incoming'], 400);
    //     }
    //     $getfile->is_outgoing  = true;
    //     $getfile->is_incoming  = false;
    //     $getfile->save();
    //     return  response()->json([
    //         'message' => 'file forwarded Successfully',
    //         'data' => $getfile
    //     ]);
    // }
    // public function createDepartment(Request $request)
    // {
    //     $validated = $request->validate([
    //         'name' => 'string|required',
    //     ]);
    //     $newDepartment = new Department();
    //     $newDepartment->name = $request->name;
    //     $newDepartment->save();
    //     return  response()->json([
    //         'message' => 'Department created Successfully',
    //         'data' => $newDepartment
    //     ]);
    // }

    public function getFileMovementUniqueId(){
        $uniqueId = substr(str_shuffle("0123456789"), 0, 9);
        return $uniqueId;
    }

    public function getLogedInUserBranch(){
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

    public function createFile(Request $request)
    {

        if($request->file_type){
            $request->merge(['file_type'=>$request->file_type['name']]);
        }
        if($request->branch){
            $request->merge(['branch'=>$request->branch['id'], 'branch_type'=> $request->branch['branch_type']]);
        }
      
      //dd($request->all());
        $validated = $request->validate([
            'name' => 'required|string',
            'file_type' => 'required|string',
            'branch' => 'required|integer',
            // 'subject' => 'string|required',
            //'document_number' => 'string|required',
        ]);

        $branch_id = null;
        $user = auth()->user();
        if($user->unit_id){
            $branch_id = $user->unit_id;
        }elseif($user->division_id){
            $branch_id = $user->division_id;
        }elseif($user->department_id){
            $branch_id = $user->department_id;
        }elseif(auth()->user()->hasAnyPermission(['agf registry view', 'agf registry create'])){
            $branch_id = $user->id;
        }

        $uploaded_paths = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $fileName = time() . '_' . uniqid() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $path = '/registry/' . date('Y') . '/' . date('m');
                $fullPath = public_path($path);

                if (!file_exists($fullPath)) {
                    mkdir($fullPath, 0755, true);
                }

                $file->move($fullPath, $fileName);
                $uploaded_paths[] = $path . '/' . $fileName;
            }
        }

        // make uploaded paths available inside the transaction closure
        //$request->merge(['uploaded_paths' => $uploaded_paths]);

        DB::transaction(function () use($request, $branch_id, $user, $uploaded_paths) {
            $logedInUserBranch = $this->getLogedInUserBranch();
            
            $newFile = new File();
            $newFile->name = $request->name;
            $newFile->file_type = $request->file_type;
            $newFile->doc_no = $request->document_number;
            $newFile->description = $request->description;
            $newFile->path = !empty($uploaded_paths) ? $uploaded_paths[0] : 'N/A';
            $newFile->archive_id = 1; // Default archive id to satisfy DB schema
            $newFile->created_by = auth()->id();
            if($logedInUserBranch =='unit'){
                $newFile->unit_id = $branch_id;
            }
            if($logedInUserBranch == 'division'){
                $newFile->division_id = $branch_id;
            }
            if($logedInUserBranch == 'department'){
                $newFile->department_id = $branch_id;
            }
            if(auth()->user()->hasAnyPermission(['agf registry view', 'agf registry create'])){
                $newFile->is_agf = true;
            }
            $newFile->save();
    
            // Phase 4: Generic Workflow Engine Integration for Registry
            if ($request->route_to_workflow && $request->workflow_version_id) {
                try {
                    $task = new \App\Models\Task();
                    $task->title = 'Registry File: ' . $newFile->name;
                    $task->workflow_version_id = $request->workflow_version_id;
                    $task->file_id = $newFile->id;
                    $task->created_by = $user->id;
                    $task->due_date = \Carbon\Carbon::now()->addDays(5)->format('Y-m-d');
                    $task->save();

                    $workflowService = app(\App\Services\GenericWorkflowService::class);
                    $workflowService->start($task, $user, []);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Generic Workflow Start Error: ' . $e->getMessage());
                }
            }

            $branchData = is_string($request->branch) ? json_decode($request->branch, true) : $request->branch;
            $branchId = is_array($branchData) ? ($branchData['id'] ?? 0) : ($request->branch ?? 0);
            $branchType = is_array($branchData) ? ($branchData['branch_type'] ?? null) : ($request->branch_type ?? null);

            $fm = new FileMovement();
            $fm->sent_from = $branch_id;
            $fm->sent_to = $branchId;
            $fm->file_id = $newFile->id;
            $fm->status = "pending";
            $fm->file_movement_id = $this->getFileMovementUniqueId();
            $fm->sent_to_branch = $branchType;
            if($branchType == 'unit'){
                $fm->sent_to_unit = $branchId;
            }
            if($branchType == 'division'){
                $fm->sent_to_division = $branchId;
            }
            if($branchType == 'department'){
                $fm->sent_to_department = $branchId;
            }
            if($logedInUserBranch == 'unit'){
                $fm->unit_id = $branch_id;
                $fm->sent_from_unit = $branch_id;
            }
            if($logedInUserBranch == 'division'){
                $fm->division_id = $branch_id;
                $fm->sent_from_division = $branch_id;
            }
            if($logedInUserBranch == 'department'){
                $fm->department_id = $branch_id;
                $fm->sent_from_department = $branch_id;
            }
            if(auth()->user()->hasAnyPermission(['agf registry view', 'agf registry create'])){
                $fm->sent_from_agf = true;
                if($branchType =='agf'){
                    $fm->sent_to_agf = true;
                    
                    // Auto-generate Task for AGF to show up in Workflow Dashboard
                    $agf = \App\Models\User::role('super user')->first();
                    if($agf){
                        $task = new \App\Models\Task();
                        $task->title = 'AGF Registry File: ' . $newFile->name;
                        $task->file_id = $newFile->id;
                        $task->created_by = auth()->id();
                        $task->due_date = \Carbon\Carbon::now()->addDays(5)->format('Y-m-d');
                        $task->status = 'pending';
                        
                        $masterWorkflowVersionId = \App\Models\WorkflowVersion::whereHas('workflow', function($q) {
                            $q->where('title', 'Master Routing Workflow');
                        })->where('is_published', true)->value('id');
                        
                        if ($masterWorkflowVersionId) {
                            $task->workflow_version_id = $masterWorkflowVersionId;
                        }

                        $task->save();
                        
                        $assignment = new \App\Models\TaskAssignment();
                        $assignment->task_id = $task->id;
                        $assignment->assigned_to = $agf->id;
                        $assignment->assigned_by = auth()->id();
                        $assignment->status = 'pending';
                        $assignment->save();
                        
                        if (!empty($uploaded_paths)) {
                            foreach ($uploaded_paths as $path) {
                                \App\Models\TaskDoc::create([
                                    'task_id' => $task->id,
                                    'path' => $path,
                                    'file_name' => basename($path),
                                    'size' => file_exists(public_path($path)) ? filesize(public_path($path)) : 0
                                ]);
                            }
                        }

                        $workflowService = app(\App\Services\GenericWorkflowService::class);
                        try {
                            $workflowService->start($task, auth()->user(), []);
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Generic Workflow Start Error: ' . $e->getMessage());
                        }
                    }
                }
            }

            if (!empty($uploaded_paths)) {
                foreach ($uploaded_paths as $path) {
                    $registryFile = new RegistryFile();
                    $registryFile->file_id = $newFile->id;
                    $registryFile->file_path = $path;
                    //$registryFile->uploaded_by = auth()->id();
                    $registryFile->save();
                }
            }

    
            $fm->save();

        });

        return redirect()->back();
    }
    public function updateFileStatus(Request $request, $fileId)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:rejected,delivered',
        ]);

        //$file = File::findOrFail($fileId);
        $file = FileMovement::findOrFail($fileId);
        $file->status = $request->status;
        $file->save();
     
        return redirect()->back();
    }
    
    public function getDivisionsInDepartment($departmentId)
    {
        $department = Department::findOrFail($departmentId);
        $divisions = $department->division;

        return response()->json(['divisions' => $divisions]);
    }
    public function getUnitsInDivision($divisionId)
    {
        $division = Division::findOrFail($divisionId);
        $units = $division->units;

        return response()->json(['divisions' => $units]);
    }
    public function sendToDivision(Request $request)
    {

        $validated = FacadesValidator::make(
            $request->all(),
            [
                'file_id' => 'required|exists:files,id',
                'division_id' => 'required|exists:divisions,id',
            ]
        );

        if ($validated->fails()) {
            return response()->json([
                'message' => $validated->errors(),
                'status' => false
            ], 400);
        }
        $file = File::findOrFail($request->file_id);
        $division = Division::findOrFail($request->division_id);
        if ($file->sent_to_department_id !== $division->department_id) {
            return response()->json(['error' => 'The file and the division must belong to the same department.'], 400);
        }

        // Record the file movement
        FileMovement::create([
            'file_id' => $request->file_id,
            // 'from_department_id' => $file->sent_from_department_id,
            // 'to_department_id' => $file->sent_to_department_id,
            // 'from_division_id' => $file->sent_from_division_id,
            'division_id' => $request->division_id,
            // 'from_unit_id' => $file->sent_from_unit_id,
            // 'to_unit_id' => $file->unit_id,
        ]);

        $file->division_id = $request->division_id;
        $file->is_incoming = true;
        $file->save();

        return response()->json(['message' => 'File sent to division successfully']);
    }
    public function moveToUnit(Request $request)
    {
        $validated = $request->validate([
            'file_id' => 'required|exists:files,id',
            'unit_id' => 'required|exists:units,id',
        ]);

        $file = File::findOrFail($request->file_id);
        $unit = Unit::findOrFail($request->unit_id);
        if ($file->division_id !== $unit->division_id) {
            return response()->json(['error' => 'The file and the unit must belong to the same division.'], 400);
        }
        $file->unit_id = $request->unit_id;
        $file->is_incoming = true;
        $file->save();

        return response()->json(['message' => 'File moved to unit successfully']);
    }
    public function getFilesInDivision($divisionId)
    {
        $division = Division::with('files')->findOrFail($divisionId);

        return response()->json($division->files);
    }
    public function getFilesInUnit($unitId)
    {
        $unit = Unit::with('files')->findOrFail($unitId);

        return response()->json($unit->files);
    }
    public function getFileMovements($fileId)
    {
        $file = File::findOrFail($fileId);
        $fromDept = $file->sentFromDepartment()->get()->map(function ($file) {
            return [
                "sent_from_department_id" => $file->sentFromDepartment ? $file->sentFromDepartment->name : null,
            ];
        });
        $fileMovements = FileMovement::where('file_id', $fileId)
            ->with(['toDivision', 'toDepartment', 'toUnit', 'fromDivision', 'fromUnit', 'fromDepartment'])
            ->get();
        return response()->json([
            'file_id' => $fileId,
            'file_name' => $file->name,
            'movements' => $fileMovements,
            'from_department' => $fromDept
        ]);
        // return response()->json($file->movements);
    }

    public function branchMap($branch){
      if($branch){
        $new_branch = $branch->getCollection()->map(function($files){
            return $files->fileMovement->map(function($file) use($files){
                return [
                    "id" => $file->id,
                    "name" => $files->name,
                    "file_type" => $files->file_type,
                    "status" => $file->status,
                    "doc_no" => $files->doc_no,
                    "description" => $files->description,
                    "sent_from" => $file->sent_from,
                    "division_id" => $files->division_id,
                    "unit_id" => $files->unit_id,
                    "sent_to" =>$file->sent_to,
                    "is_forwarded" => $file->is_forwarded,
                    "file_movement_id" => $file->file_movement_id,
                    'sent_to_unit' =>$file->sent_to_unit,
                    'sent_to_division' => $file->sent_to_division,
                    'sent_to_department' => $file->sent_to_department,
                    'sent_from_unit' => $file->sent_from_unit,
                    'sent_from_division' => $file->sent_from_division,
                    'sent_from_department' => $file->sent_from_department,
                    'sent_from_agf' => $file->sent_from_agf,
                    'sent_to_agf' => $file->sent_to_agf,
                    "created_by" => $files->created_by,
                    "created_at" => $files->created_at,
                    "updated_at" => $files->updated_at,
    
                ];
            });
        })->collapse();

        return $branch->setCollection($new_branch);
      }else{
        return [];
      }
    }

    public function getUnitFile($id){
        $user = auth()->user();
        $branch = File::whereHas('fileMovement', function($query) use ($user, $id) {
            $query->where(function($query) use ($user, $id) {
                $query->where('unit_id', $id);
                     // ->where('sent_to_branch', 'unit');
            })->orWhere(function($query) use ($user, $id) {
                $query->where('sent_to_unit', $id);
            });
        })
        ->with(['fileMovement' => function($query) use ($user, $id) {
            $query->where(function($query) use ($user, $id) {
                $query->where('unit_id', $id);
                      //->where('sent_to_branch', 'unit');
            })->orWhere(function($query) use ($user, $id) {
                $query->where('sent_to_unit', $id);
            });
        }])
        ->with('task.activeSteps.step')
        ->latest()
        ->paginate(20);
        
        return $branch;
    }

    public function getDivisionFile($id){
        $user = auth()->user();
        $branch = File::whereHas('fileMovement', function($query) use ($user, $id) {
            $query->where(function($query) use ($user, $id) {
                $query->where('division_id', $id);
                     // ->where('sent_to_branch', 'unit');
            })->orWhere(function($query) use ($user, $id) {
                $query->where('sent_to_division', $id);
            });
        })
        ->with(['fileMovement' => function($query) use ($user, $id) {
            $query->where(function($query) use ($user, $id) {
                $query->where('division_id', $id);
                      //->where('sent_to_branch', 'unit');
            })->orWhere(function($query) use ($user, $id) {
                $query->where('sent_to_division', $id);
            });
        }])
        ->with('task.activeSteps.step')
        ->latest()
        ->paginate(20);

        return $branch;
    }

    public function getDepartmentFile($id){
         $user = auth()->user();
    
        // Outgoing Files
        $outgoing = File::whereHas('fileMovement', function ($query) use ($user, $id) {
            $query->where('sent_from_department', $user->department_id);
        })
        ->with(['fileMovement' => function ($query) use ($user, $id) {
            $query->where('sent_from_department', $user->department_id);
        }])
        ->with('task.activeSteps.step')
        ->latest()
        ->paginate(20);

        // Incoming Files
        $incoming = File::whereHas('fileMovement', function ($query) use ($user, $id) {
            $query->where('sent_to_department', $user->department_id);
        })
        ->with(['fileMovement' => function ($query) use ($user, $id) {
            $query->where('sent_to_department', $user->department_id);
        }])
        ->with('task.activeSteps.step')
        ->latest()
        ->paginate(20);

        // return $branch;
        return [
            'outgoing' => $outgoing,
            'incoming' => $incoming,
        ];
    }

    public function getAGFFile(){
        $user = auth()->user();
        $branch = File::whereHas('fileMovement', function($query){
            $query->where(function($query)  {
                $query->where('sent_from_agf', true);
                     // ->where('sent_to_branch', 'unit');
            })->orWhere(function($query) {
                $query->where('sent_to_agf', true);
            });
        })
        ->with(['fileMovement' => function($query) {
            $query->where(function($query)  {
                $query->where('sent_from_agf', 1);
                      //->where('sent_to_branch', 'unit');
            })->orWhere(function($query) {
                $query->where('sent_to_agf', 1);
            });
        }])
        ->with('task.activeSteps.step')
        ->latest()
        ->paginate(20);

        //return $branch;
        return [
            'outgoing' => $branch,
            'incoming' => [],
        ];
    }

    public function getBranchFiles(Request $request)
    { 
        $user = auth()->user();
        $branch = null;

        if($request->branch_type =='unit'){
            $id = $request->branch_id;
            $branch = $this->getUnitFile($id);
            $branch = $this->branchMap($branch);
            return response()->json($branch);
        }

        if($request->branch_type == 'division'){
            $id = $request->branch_id;
            $branch = $this->getDivisionFile($id);
            $branch = $this->branchMap($branch);
            return response()->json($branch);
        }
        if($request->branch_type == 'department'){
            $id = $request->branch_id;
            $branch = $this->getDepartmentFile($id);
            $branch = $this->branchMap($branch);
            return response()->json($branch);
        }
       
        if($user->unit_id || $request->branch_type =='unit'){
            $id = $request->branch_id? $request->branch_id: $user->unit_id;
            $branch = $this->getUnitFile($id);
            $branch = $this->branchMap($branch);
            return response()->json($branch);
        }
        if($user->division_id || $request->branch_type == 'division'){
            $id = $request->branch_id? $request->branch_id: $user->division_id;
            $branch = $this->getDivisionFile($id);
            $branch = $this->branchMap($branch);
            return response()->json($branch);
        }
        if($user->department_id || $request->branch_type == 'department'){
            $id = $request->branch_id? $request->branch_id: $user->department_id;
            $branch = $this->getDepartmentFile($id);
            $incoming = $this->branchMap($branch['incoming']);
            $outgoing = $this->branchMap($branch['outgoing']);
            return response()->json(['incoming'=>$incoming, 'outgoing'=>$outgoing]);
        }
        
        if(auth()->user()->hasAnyPermission(['agf registry view', 'agf registry create'])){
            $branch = $this->getAGFFile();
            // $branch = $this->branchMap($branch);
            // return response()->json($branch);
           // dd(gettype($branch['outgoing']));
            $incoming = $this->branchMap($branch['incoming']);
            $outgoing = $this->branchMap($branch['outgoing']);
            return response()->json(['incoming'=>$incoming, 'outgoing'=>$outgoing]);
        }


    }

    public function getUserBranches(){
        $user = auth()->user();
      
        if($user->unit_id){
            $division = DB::table('divisions')
                ->where('id', $user->division_id)
                ->select('divisions.*') 
                ->first();
            
            $division->branch_type = 'division';

            $units = DB::table('units')
                ->where('division_id', $user->division_id)
                ->where('id', '!=', $user->unit_id)
                ->select('units.*') 
                ->get();

                // $units = $units->map(function($item) {
                //     $item->branch_type = 'unit';  
                //     return $item;
                // });

            // Convert $division to an array and merge with $units collection
            $units = collect([$division]);
            return response()->json($units);
        }

        if($user->division_id){ 
            $department = DB::table('departments')
                ->where('id', $user->department_id)
                ->select('departments.*') 
                ->first();
            
            $department->branch_type = 'department';

            $units = DB::table('units')
                ->where('division_id', $user->division_id)
                ->select('units.*') 
                ->get();

                $units = $units->map(function($item) {
                    $item->branch_type = 'unit';  
                    return $item;
                });

            // Convert $division to an array and merge with $units collection
            $divisions = collect([$department])->merge($units);
            return response()->json($divisions);
        }

        if ($user->department_id) {
            $divisions = DB::table('divisions')
                ->where('department_id', $user->department_id)
                ->select('divisions.*')
                ->get();
            
            // Map over the collection to add 'branch_type' to each item
            $divisions = $divisions->map(function ($item) {
                $item->branch_type = 'division';
                return $item;
            });
        
            // Add a new item to the collection
            $divisions->push([
                'branch_type' => 'agf',
                'created_at' => null,
                'description' => null,
                'head' => null,
                'id' => $divisions->count() + 1, // Increment the ID by 1
                'title' => 'AGF',
                'updated_at' => null,
            ]);
        
            return response()->json($divisions);
        }        

        if(auth()->user()->hasAnyPermission(['agf registry view', 'agf registry create'])){
            $departments = DB::table('departments')
            // ->where('department_id', $user->department_id)
            // ->select('divisions.*') 
            ->get();
        
            $departments = $departments->map(function($item) {
                $item->branch_type = 'department';  
                return $item;
            });

        return response()->json($departments);
        }
    }

    public function getBranchType(){
        $user= auth()->user();
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

    public function TrackFile(Request $request){
        $branch_type = $this->getBranchType();
        $user = auth()->user();
        
        $filesQuery = File::where(function($q) use ($request) {
            $q->where('id', $request->search)
              ->orWhere('name', 'LIKE', '%'.$request->search.'%')
              ->orWhere('doc_no', 'LIKE', '%'.$request->search.'%')
              ->orWhereHas('tasks', function($q) use ($request) {
                  $q->where('tracking_id', 'LIKE', '%'.$request->search.'%');
              });
        });

        if($branch_type == 'unit' && $user->unit_id){
            $filesQuery->whereHas('fileMovement', function($query) use ($user) {
                $query->where('unit_id', $user->unit_id)
                      ->orWhere('sent_to_unit', $user->unit_id);
            })->with(['fileMovement' => function($query) use ($user) {
                $query->where('unit_id', $user->unit_id)
                      ->orWhere('sent_to_unit', $user->unit_id)
                      ->latest()->take(40);
            }]);
        } elseif($branch_type == 'division' && $user->division_id){
            $filesQuery->whereHas('fileMovement', function($query) use ($user) {
                $query->where('division_id', $user->division_id)
                      ->orWhere('sent_to_division', $user->division_id);
            })->with(['fileMovement' => function($query) use ($user) {
                $query->where('division_id', $user->division_id)
                      ->orWhere('sent_to_division', $user->division_id)
                      ->latest()->take(40);
            }]);
        } elseif($branch_type == 'department' && $user->department_id){
            $filesQuery->whereHas('fileMovement', function($query) use ($user) {
                $query->where('department_id', $user->department_id)
                      ->orWhere('sent_to_department', $user->department_id);
            })->with(['fileMovement' => function($query) use ($user) {
                $query->where('department_id', $user->department_id)
                      ->orWhere('sent_to_department', $user->department_id)
                      ->latest()->take(40);
            }]);
        } else {
            $filesQuery->with('fileMovement');
        }
        
        $files = $filesQuery->latest()->take(10)->get()->map(function($file) {
            $arr = $file->toArray();
            $arr['result_type'] = 'file';
            return $arr;
        });

        $tasksQuery = \App\Models\Task::where(function($q) use ($request) {
            $q->where('tracking_id', 'LIKE', '%'.$request->search.'%')
              ->orWhere('title', 'LIKE', '%'.$request->search.'%');
        });

        $tasks = $tasksQuery->latest()->take(10)->get()->map(function($task) {
            $arr = $task->toArray();
            $arr['result_type'] = 'task';
            return $arr;
        });

        $results = $files->concat($tasks);
        return response()->json($results);
    }
    public function getTrackedFile(Request $request){
        if ($request->type == 'task') {
            $task = \App\Models\Task::with(['user.designation', 'assignments.assignedTo.designation', 'assignments.assignedBy.designation'])->find($request->file_id);
            $minutes = \App\Models\TaskMinute::with('user.designation')->where('task_id', $request->file_id)->orderBy('created_at', 'ASC')->get();
            return response()->json(['task' => $task, 'minutes' => $minutes]);
        }
       
        $file = File::find($request->file_id);
        $file_movement = $file->fileMovement()->where('file_movement_id', $request->file_movement_id)->orderBy('id','ASC')->get();
        return response()->json(['file_movement'=> $file_movement, 'file' => $file]);
    }

    public function getAdminRegistryBranch(Request $request){
        $search = $request->search;
        $unit = Unit::where('title', 'LIKE', '%'.$search.'%')->get()->map(function ($unit) {
                $unit->branch_type = 'unit';
                return $unit;
            });
        $division = Division::where('title', 'LIKE', '%'.$search.'%')->get()->map(function ($division) {
            $division->branch_type = 'division';
            return $division;
        });
        $department = Department::where('title', 'LIKE', '%'.$search.'%')->get()->map(function ($department) {
            if (stripos($department->title, 'agf') !== false) {
                $department->branch_type = 'agf';
            } else {
                $department->branch_type = 'department';
            }
            return $department;
        });

        $branches = $department->concat($division)->concat($unit);
        return response()->json($branches->values());
    }

    function formatSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
    
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function setFandARegistryFiles(Request $request){
    
        try {
            DB::beginTransaction();

            if($request->file('files')){
                $files = [];
                $file_name = '';
                $size = 0;
                $paths = [];
                foreach($request->file('files') as $file){

                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $file_name = $fileName;
                    $original_name = $file->getClientOriginalName();
                    $path =  "/finance/".date('Y') . '/' . date('m');
                    //$size = $this->formatSize(filesize($file));
                    $size += filesize($file);
                    $fullPath = public_path($path);

                    if (!file_exists($fullPath)) {
                        mkdir($fullPath, 0777, true);
                    }
                    $file->move(public_path($path),$fileName);
                    $paths[] = $path.'/'.$fileName;

                    $name = $original_name? $original_name: 'Unknown';

                    $newFile = new File();
                    $newFile->name = $name;
                    $newFile->file_type = 'File';
                    $newFile->doc_no = null;
                // $newFile->description = $request->description;
                    $newFile->created_by = auth()->user()->id;
                
                    if(auth()->user()->hasAnyPermission(['agf registry view', 'agf registry create'])){
                        $newFile->is_agf = true;
                    }
                    $newFile->save();
                }

                //DB::table('finance_files')->insert($files);
                $ff = new FinanceFiles();
                $ff->file_name = $file_name;
                $ff->size = $this->formatSize($size);
                $ff->uploaded_by = auth()->user()->id;
                $ff->uploaded_by_name = auth()->user()->last_name." ".auth()->user()->first_name;
                $ff->save();

                //save in finance files path
                $data = [];
                foreach($paths as $path){
                    $data[] = [
                        'path' => $path,
                        'finance_files_id' => $ff->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                FinanceFilePaths::insert($data);

                $superUsers = User::role('super user')->first();
                if($superUsers){
                    $fm = new FileMovement();
                    $fm->sent_from = auth()->user()->id;
                    //edit this section
                    $fm->sent_to = $superUsers->id;
                    $fm->file_id = $newFile->id;
                    $fm->status = "pending";
                    $fm->file_movement_id = $this->getFileMovementUniqueId();
                    $fm->sent_to_branch = 'finance';
                }
            
                if(auth()->user()->hasAnyPermission(['agf registry view', 'agf registry create'])){
                    $fm->sent_to_agf = true;
                }

        
                $fm->save();
                DB::commit();
                return to_route('registry');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return to_route('registry')->withErrors('Error', 'An error occured');
        }
    }
        
    public function acknowledgeFileMovement(Request $request, $movementId)
    {
        $movement = FileMovement::findOrFail($movementId);
        $user = auth()->user();

        // Security Validation: Only the intended destination (branch) can acknowledge the file.
        $isAuthorized = false;
        
        if ($movement->sent_to_branch === 'unit' && $user->unit_id == $movement->sent_to_unit) {
            $isAuthorized = true;
        } elseif ($movement->sent_to_branch === 'division' && $user->division_id == $movement->sent_to_division) {
            $isAuthorized = true;
        } elseif ($movement->sent_to_branch === 'department' && $user->department_id == $movement->sent_to_department) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            return response()->json(['message' => 'Unauthorized. You do not belong to the destination branch of this file.'], 403);
        }

        if ($movement->status === 'delivered') {
            return response()->json(['message' => 'File movement already acknowledged.'], 400);
        }

        DB::transaction(function () use ($movement, $user) {
            // Update movement status
            $movement->status = 'delivered';
            $movement->save();

            // Find the associated task
            $task = \App\Models\Task::where('file_id', $movement->file_id)->latest()->first();

            // Log Audit Material Action
            if ($task) {
                \App\Models\WorkflowLog::create([
                    'task_id' => $task->id,
                    'action_by' => $user->id,
                    'action_type' => 'file_received',
                    'comments' => 'Physical file received and acknowledged.'
                ]);
            }
        });

        return response()->json(['message' => 'File movement acknowledged successfully.']);
    }

    public function editFile(Request $request, $id)
    {
        $validated = $request->validate([
            'branch' => 'required',
        ]);

        $file = \App\Models\File::findOrFail($id);
        
        $branchId = null;
        $branchType = null;
        
        if (is_array($request->branch)) {
            $branchId = $request->branch['id'] ?? null;
            $branchType = $request->branch['branch_type'] ?? null;
        } else {
            $branchId = $request->branch;
            $branchType = $request->branch_type;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($file, $branchId, $branchType) {
            $logedInUserBranch = $this->getLogedInUserBranch();
            $user = auth()->user();
            
            $senderBranchId = null;
            if ($logedInUserBranch == 'unit') {
                $senderBranchId = $user->unit_id;
            } elseif ($logedInUserBranch == 'division') {
                $senderBranchId = $user->division_id;
            } elseif ($logedInUserBranch == 'department') {
                $senderBranchId = $user->department_id;
            } else {
                $senderBranchId = $user->id;
            }

            // Find the most recent pending file movement
            $fm = \App\Models\FileMovement::where('file_id', $file->id)
                ->where('status', 'pending')
                ->latest()
                ->first();
                
            if (!$fm) {
                // if there is no pending file movement, we probably shouldn't edit.
                // But let's create one if needed, or just update the latest.
                $fm = \App\Models\FileMovement::where('file_id', $file->id)->latest()->first();
            }
            
            if ($fm) {
                $fm->sent_to = $branchId;
                $fm->sent_to_branch = $branchType;
                
                $fm->sent_to_unit = null;
                $fm->sent_to_division = null;
                $fm->sent_to_department = null;
                $fm->sent_to_agf = null;
                
                if ($branchType == 'unit') {
                    $fm->sent_to_unit = $branchId;
                }
                if ($branchType == 'division') {
                    $fm->sent_to_division = $branchId;
                }
                if ($branchType == 'department') {
                    $fm->sent_to_department = $branchId;
                }
                
                if (auth()->user()->hasAnyPermission(['agf registry view', 'agf registry create'])) {
                    if ($branchType == 'agf') {
                        $fm->sent_to_agf = true;
                        
                        // Auto-generate Task for AGF
                        $agf = \App\Models\User::role('super user')->first();
                        if($agf){
                            // check if task already exists
                            $existingTask = \App\Models\Task::where('file_id', $file->id)
                                ->whereHas('assignments', function($q) use ($agf) {
                                    $q->where('assigned_to', $agf->id);
                                })->first();
                                
                            if (!$existingTask) {
                                $task = new \App\Models\Task();
                                $task->title = 'AGF Registry File: ' . $file->name;
                                $task->file_id = $file->id;
                                $task->created_by = auth()->id();
                                $task->due_date = \Carbon\Carbon::now()->addDays(5)->format('Y-m-d');
                                $task->status = 'pending';
                                
                                $masterWorkflowVersionId = \App\Models\WorkflowVersion::whereHas('workflow', function($q) {
                                    $q->where('title', 'Master Routing Workflow');
                                })->where('is_published', true)->value('id');
                                
                                if ($masterWorkflowVersionId) {
                                    $task->workflow_version_id = $masterWorkflowVersionId;
                                }

                                $task->save();
                                
                                $assignment = new \App\Models\TaskAssignment();
                                $assignment->task_id = $task->id;
                                $assignment->assigned_to = $agf->id;
                                $assignment->assigned_by = auth()->id();
                                $assignment->status = 'pending';
                                $assignment->save();
                                
                                $workflowService = app(\App\Services\GenericWorkflowService::class);
                                try {
                                    $workflowService->start($task, auth()->user(), []);
                                } catch (\Exception $e) {
                                    \Illuminate\Support\Facades\Log::error('Generic Workflow Start Error: ' . $e->getMessage());
                                }
                            }
                        }
                    } else {
                        $fm->sent_from_agf = true;
                    }
                }
                
                $fm->save();
            }
        });

        return redirect()->back();
    }

}