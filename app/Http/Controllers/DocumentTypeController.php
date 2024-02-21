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
            if($request->id){
                $doctype->metadata()->sync($request->metadata,['created_at'=>\Carbon\Carbon::now(), 'updated_at'=>\Carbon\Carbon::now()]);
            }else{
                $doctype->metadata()->attach($request->metadata,['created_at'=>\Carbon\Carbon::now(), 'updated_at'=>\Carbon\Carbon::now()]);
            }
        }else{
            $metadata = Metadata::where('is_default',1)->get();
            $ids = $metadata->pluck('id')->toArray();
            $doctype->metadata()->attach($ids,['created_at'=>\Carbon\Carbon::now(), 'updated_at'=>\Carbon\Carbon::now()]);
        }

        return redirect()->to("/admin/setup-document-type");
    }

    public function configureMetaData(Request $request){
        DocumentType::find($request->id)->metadata()->sync($request->metadata);
        return redirect()->to('/admin/setup-document-type');
    }
}
