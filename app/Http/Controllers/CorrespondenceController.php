<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Correspondence;
use Illuminate\Support\Facades\DB;

class CorrespondenceController extends Controller
{
    public function correspondense(){
        return inertia('correspondence/index');
    }

    public function createCorrespondence(Request $request){
        
        $corr = new Correspondence();
        $corr->description = $request->description;
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

            DB::table('correspondence_file')->insert($files);

            $fm = new FileMovement();
            $fm->sent_from = null;
            $fm->sent_to = null;
            $fm->file_id = $newFile->id;
            $fm->status = "pending";
            $fm->file_movement_id = $this->getFileMovementUniqueId();
            $fm->sent_to_branch = $request->branch_type;
            if($request->branch_type == 'unit'){
                $fm->sent_to_unit = $request->branch;
               
            }
            if($request->branch_type == 'division'){
                $fm->sent_to_division = $request->branch;
                
            }
            if($request->branch_type == 'department'){
                $fm->sent_to_department = $request->branch;
                
            }
            if($logedInUserBranch == 'unit'){
                $fm->unit_id = $branch_id;
                $fm->sent_from_unit = $request->branch;
            }
            if($logedInUserBranch == 'division'){
                $fm->division_id = $branch_id;
                $fm->sent_from_division = $request->branch;
            }
            if($logedInUserBranch == 'department'){
                $fm->department_id = $branch_id;
                $fm->sent_from_department = $request->branch;
            }
            if(auth()->user()->hasAnyPermission(['agf registry view', 'agf registry edit'])){
                $fm->sent_from_agf = true;
            }

    
            $fm->save();

        }
    }
}
