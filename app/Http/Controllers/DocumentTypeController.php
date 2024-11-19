<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentType;
use App\Models\Metadata;

class DocumentTypeController extends Controller
{
    public function createDocumentType(Request $request){
        $request->validate([
            'name'=>'required'
        ]);

        $doctype = $request->id? DocumentType::find($request->id) : new DocumentType();
        $doctype->name = $request->name;
        $doctype->description = $request->description;
        $doctype->save();

        if($request->metadata){
            $metadata = array_unique($request->metadata);
            if($request->id){
                $doctype->metadata()->sync($metadata,['created_at'=>\Carbon\Carbon::now(), 'updated_at'=>\Carbon\Carbon::now()]);
            }else{
                $doctype->metadata()->attach($metadata,['created_at'=>\Carbon\Carbon::now(), 'updated_at'=>\Carbon\Carbon::now()]);
            }
        }else{
            if(!$request->id){
                $metadata = Metadata::where('is_default',1)->get();
                $ids = $metadata->pluck('id')->toArray();
                $doctype->metadata()->attach($ids,['created_at'=>\Carbon\Carbon::now(), 'updated_at'=>\Carbon\Carbon::now()]);
            }
        }

        return redirect()->to("/dashboard/setup-document-type");
    }

    public function SetupDocumentType(){
        $documentTypes = DocumentType::with('metadata')->get();

        $documentTypes->each(function ($documentType) {
            $documentType->updated_at_formatted = $documentType->updated_at->diffForHumans();
            $documentType->metadata_count = count($documentType->metadata);
        });
        return inertia('admin/SetupDocumentType', compact('documentTypes'));
    }

    public function configureMetaData(Request $request){
        DocumentType::find($request->id)->metadata()->sync($request->metadata);
        return redirect()->to('/dashboard/setup-document-type');
    }

    public function duplicateDocumentType(Request $request){
        $document_type = DocumentType::with('metadata')->find($request->id);
        $new_document_type = $document_type->replicate();
        $new_document_type->save();
        $new_document_type->metadata()->sync($document_type->metadata);
        return redirect()->back();
    }

    public function deleteDocumentType(Request $request){
        $document_type = DocumentType::with('metadata')->find($request->id);
        //$document_type->metadata()->delete();
        $document_type->delete();
        return redirect()->back();
    }

    public function getDocumentType(){
        $doctype = DocumentType::get();
        return response()->json($doctype);
    }

    public function getBranchDocumentType(){
        if(auth()->user()->unit_id){
            $doctype = DocumentType::whereHas('unit', function($query){
                $query->where('unit_id', auth()->user()->unit_id);
            })->get();
            return response()->json($doctype);
        }

        if(auth()->user()->division_id){
            $doctype = DocumentType::with('metadata')->whereHas('unit', function($query){
                $query->where('division_id', auth()->user()->division_id);
            })->get();
            return response()->json($doctype);
        }

        if(auth()->user()->department_id){
            $doctype = DocumentType::whereHas('unit', function($query){
                $query->where('department_id', auth()->user()->department_id);
            })->get();
            return response()->json($doctype);
        }
    }
}
