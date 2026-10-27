<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Metadata;
use App\Models\Unit;
use App\Models\Division;
use App\Models\Department;
use App\Models\DocumentType;

class MetadataController extends Controller
{
     //create metadata
     public function createMetaData(Request $request){
        $request->validate([
            'name'=>'required',
            'metadata_string' => 'required',
        ]);

        if($request->metadata_value){
           $metadata = $request->metadata_value;
            $metadata = array_pop($metadata);
        
            $pattern = '/<label[^>]*>(.*?)<\/label>/i';

            // Replacement text for the <label> tag
            $replacement = "<label for='".strtolower($request->metadata_string)."'>$request->name</label>";

            $name = explode(' ', $request->name);
            $name = implode("_", $name);

            // Replace the content inside the <label> tag
            $newHtml = preg_replace($pattern, $replacement, $metadata);  
            $newHtml = preg_replace('/name="[^"]*"/', 'name="'.strtolower($name).'"', $newHtml);
            $newHtml = preg_replace('/v-model="[^"]*"/', 'v-model="form.'.strtolower($name).'"', $newHtml);
           // $newHtml = preg_replace('/:value="[^"]*"/', ':value="form.'.strtolower($name).'"', $newHtml);
            $newHtml = preg_replace('/id="[^"]*"/', 'id="'.strtolower($name).'"', $newHtml);

        }
       

        $meta = $request->id? Metadata::find($request->id)  : new Metadata();
        $meta->name = $request->name;
        $meta->string = $request->metadata_string;
        $meta->is_default = $request->is_default;
        if($request->metadata_value){
             $meta->metadata_value = $newHtml;
        }
       
        $meta->save();

        return redirect()->back()->with(['success'=>true]);
    }

//delete metadata
    public function deleteMetaData(Request $request){
        Metadata::find($request->id)->delete();
        return redirect()->back();
    }

    //returns metadata
    public function getMetadata(){
        $metadata = Metadata::get();
        return response()->json($metadata);
    }
   
   public function getBranchMetadata(Request $request){
    $metadata = [];
    if(auth()->user()->unit_id){
        $unit = Unit::with(['documentType'=>function($query){
            $query->with('metadata');
        }])->find(auth()->user()->unit_id);
        $metadata = isset($unit->documentType)? $unit->documentType: [];
    }else

    if(auth()->user()->division_id){
        $division = Division::with(['documentType'=>function($query){
            $query->with('metadata');
        }])->find(auth()->user()->division_id);
        $metadata = isset($division->documentType)?$division->documentType: [];
    }else

    if(auth()->user()->department_id){
        $deparment = Department::with(['documentType'=>function($query){
            $query->with('metadata');
        }])->find(auth()->user()->department_id);
        $metadata = isset($deparment->documentType)? $deparment->documentType: [];
    }else{
        $metadata = DocumentType::with('metadata')->get();
    }

    return response()->json($metadata);
   }

   //set default metadata
   public function setDefaultMetadata(Request $request){
    $metadata = Metadata::find($request->id);
    $metadata->is_default = 1;
    $metadata->save();
   }
}
