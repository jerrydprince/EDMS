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
use PhpOffice\PhpWord\Shared\Validate;

class RegistryController extends Controller
{
    public function registryConfig()
    {
        $files = File::with('department')->orderBy('created_at', 'desc')->get();
        return Inertia::render('Registry/RegistryConfig', [
            'files' =>  $files
        ]);
    }
    public function registryEntry()
    {
        return Inertia::render('Registry/Registry');
    }
    public function login()
    {
        return Inertia::render('Registry/login');
    }
    public function getDepartment()
    {
        $department = Department::with(['division.unit'])->get();
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
        $validated = FacadesValidator::make(
            $request->all(),
            [
                'to_department' => 'required|exists:departments,id',
            ]
        );

        if ($validated->fails()) {
            return response()->json([
                'message' => $validated->errors(),
                'status' => false
            ], 400);
        }
        $file = File::find($id);
        // if (!$file->is_outgoing) {
        //     return response()->json([
        //         'message' => 'Can only move outgoing files',
        //     ], 400);
        // }
        $file->sent_to_department_id = $request->to_department;
        $file->is_incoming = true;
        $file->status = 'pending';
        $file->is_outgoing = false;
        $file->save();
        return  response()->json([
            'message' => 'file transferred Successfully',
            'data' => $file
        ]);
    }
    public function forwardFile($fileId)
    {

        $getfile = File::findOrFail($fileId);
        if (!$getfile->is_incoming) {
            return response()->json(['message' => 'File is not marked as incoming'], 400);
        }
        $getfile->is_outgoing  = true;
        $getfile->is_incoming  = false;
        $getfile->save();
        return  response()->json([
            'message' => 'file forwarded Successfully',
            'data' => $getfile
        ]);
    }
    public function createDepartment(Request $request)
    {
        $validated = $request->validate([
            'name' => 'string|required',
        ]);
        $newDepartment = new Department();
        $newDepartment->name = $request->name;
        $newDepartment->save();
        return  response()->json([
            'message' => 'Department created Successfully',
            'data' => $newDepartment
        ]);
    }
    public function createFile(Request $request, $departmentId)
    {
        $validated = $request->validate([
            'name' => 'string|required',
            'file_type' => 'string|required',
            'sent_to_department_id' => 'string|required|exists:departments,id',
            'subject' => 'string|required',
            'doc_no' => 'string|required',
        ]);

        // Find the department
        // $sentFromDepartment = Department::findOrFail($departmentId);
        // $sentToDepartment = Department::findOrFail($request->sent_to_department_id);

        $newFile = new File();
        $newFile->name = $request->name;
        $newFile->file_type = $request->file_type;
        $newFile->doc_no = $request->doc_no;
        $newFile->subject = $request->subject;
        $newFile->is_incoming = true;
        // $newFile->is_outgoing = true;
        $newFile->sent_from_department_id = $departmentId; // Assign the sending department_id
        $newFile->sent_to_department_id = $request->sent_to_department_id; // Assign the receiving department_id
        $newFile->save();
        return  response()->json([
            'message' => 'File sent Successfully',
            'data' => $newFile
        ]);
    }
    public function updateFileStatus(Request $request, $fileId)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:rejected,delivered',
        ]);

        $file = File::findOrFail($fileId);
        $file->status = $request->status;
        $file->save();
        return  response()->json([
            'message' => 'File updated Successfully',
            'data' => $file
        ]);
    }
    public function createDivision(Request $request, $deptId)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
        ]);

        $department = Department::findOrFail($deptId);
        $division = new Division([
            'title' => $request->title,
            'description' => $request->description,
            'department_id' => $department->id,
        ]);
        $division->save();
        return response()->json([
            'message' => 'Division created successfully',
            'division' => $division,
        ]);
    }
    public function createUnit(Request $request, $divisionId)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
        ]);

        $division = Division::findOrFail($divisionId);

        $unit = new Unit([
            'title' => $request->title,
            'description' => $request->description,
            'division_id' => $division->id,
        ]);

        $unit->save();

        return response()->json(['message' => 'Unit created successfully', 'unit' => $unit]);
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
    public function getFilesByDept($departmentId)
    {
        $department = Department::findOrFail($departmentId);
        // $files = File::where('sent_to_department_id', $departmentId)
        // ->with('department', 'sentToDepartment')
        // ->get();
        // $sentFiles = $department->sentFiles()->get();
        $sentFiles = $department->sentFiles()->get()->map(function ($file) {
            return [
                "id" => $file->id,
                "name" => $file->name,
                "file_type" => $file->file_type,
                "status" => $file->status,
                "doc_no" => $file->doc_no,
                "subject" => $file->subject,
                "is_incoming" => $file->is_outgoing,
                "sent_from_department_id" => $file->sent_from_department_id,
                "division_id" => $file->division_id,
                "unit_id" => $file->unit_id,
                "sent_from_division_id" => $file->sent_from_division_id,
                "sent_from_unit_id" => $file->sent_from_unit_id,
                'sent_to_department_id' => $file->sentToDepartment ? $file->sentToDepartment->name : null,
                "created_at" => $file->created_at,
                "updated_at" => $file->updated_at,

            ];
        });
        $receivedFiles = $department->receivedFiles()->get()->map(function ($file) {
            return [
                "id" => $file->id,
                "name" => $file->name,
                "file_type" => $file->file_type,
                "status" => $file->status,
                "doc_no" => $file->doc_no,
                "subject" => $file->subject,
                "is_incoming" => $file->is_outgoing,
                "sent_from_department_id" => $file->sent_from_department_id,
                "division_id" => $file->division_id,
                "unit_id" => $file->unit_id,
                "sent_from_division_id" => $file->sent_from_division_id,
                "sent_from_unit_id" => $file->sent_from_unit_id,
                'sent_to_department_id' => $file->sentToDepartment ? $file->sentToDepartment->name : null,
                "created_at" => $file->created_at,
                "updated_at" => $file->updated_at,

            ];
        });
        // $receivedFiles = $department->receivedFiles;
        return response()->json([
            'sent_files' => $sentFiles,
            'received_files' => $receivedFiles
        ], 200);
    }
}
