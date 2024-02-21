<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Metadata;

class MetadataController extends Controller
{
     //create metadata
     public function createMetaData(Request $request){
        $request->validate([
            'name'=>'required',
            'metadata_string' => 'required',
        ]);

        $meta = $request->id? Metadata::find($request->id)  : new Metadata();
        $meta->name = $request->name;
        $meta->string = $request->metadata_string;
        $meta->is_default = $request->is_default;
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
   
      //returns part of metadata
    public function getMetadataPart(){
       $metadata = Metadata::get()->take(7);
       return response()->json($metadata);
    }

    //make default
    //public function makeDe
}
