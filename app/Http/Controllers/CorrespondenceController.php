<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Correspondence;
use Illuminate\Support\Facades\DB;
use App\Models\FileMovement;
use App\Models\File;
use App\Models\User;
use App\Models\Department;

class CorrespondenceController extends Controller
{
    public function correspondence(){
        $correspondences = Correspondence::with('correspondenceFile', 'sentBy')->latest()->get();
        return inertia('correspondence/correspondence', compact('correspondences'));
    }

    function formatSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
    
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function createCorrespondence(Request $request){
        $corr = new Correspondence();
        $corr->description = $request->description;
        $corr->file_name = $request->name;
        $corr->document_number = $request->document_number;
        $corr->created_by = auth()->user()->id;
        $corr->save();

        if($request->file('files')){
            $files = [];
            foreach($request->file('files') as $file){

                $fileName = time() . '_' . $file->getClientOriginalName();
                $original_name = $file->getClientOriginalName();
                $path =  "/correspondence/".date('Y') . '/' . date('m');
                $size = $this->formatSize(filesize($file));
                $fullPath = public_path($path);

                if (!file_exists($fullPath)) {
                    mkdir($fullPath, 0777, true);
                }
                $file->move(public_path($path),$fileName);

                $arr = [
                    'path' => $path.'/'.$fileName,
                    'correspondence_id' =>$corr->id,
                    // 'name' => $original_name,
                    // 'size'=>$size
                ];

                array_push($files, $arr);
            }

            $name = $original_name? $original_name: 'Unknown';

            DB::table('correspondence_file')->insert($files);

        }

            $newFile = new File();
            $newFile->name = $request->name;
            $newFile->file_type = isset($request->file_type['name']) ? $request->file_type['name'] : null ;
            $newFile->doc_no = null;
            $newFile->description = $request->description;
            $newFile->created_by = auth()->user()->id;
           
            if(auth()->user()->hasAnyPermission(['agf registry view', 'agf registry edit'])){
                $newFile->is_agf = true;
            }
            $newFile->save();

            $superUsers = User::role('super users')->first();
            if($superUsers){
                $fm = new FileMovement();
                $fm->sent_from = auth()->user()->id;
                $fm->sent_to = $superUsers->id;
                $fm->file_id = $newFile->id;
                $fm->status = "pending";
                $fm->file_movement_id = $this->getFileMovementUniqueId();
                $fm->sent_to_branch = 'agf registry';
            }
           
            if(auth()->user()->hasAnyPermission(['agf registry view', 'agf registry edit'])){
                $fm->sent_to_agf = true;
            }

    
            $fm->save();
    }

    public function getFileMovementUniqueId(){
        $uniqueId = substr(str_shuffle("0123456789"), 0, 9);
        return $uniqueId;
    }

    public function viewFile($id){
        $correspondence = Correspondence::with('correspondenceFile')->find($id);
        return inertia('correspondence/viewFile', compact('correspondence'));
    }

    public function getDepartments(Request $request){
        $department = Department::where('title','like', '%'.$request->search. '%')->get(); 
        return response()->json($department);
    }
}
