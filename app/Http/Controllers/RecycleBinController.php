<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Archive;

class RecycleBinController extends Controller
{
    public function index(){
        $deleted_files = Archive::with(['deletedBy', 'archiveDocs'])->onlyTrashed()->get()->map(function($item){
             $item->action_type = 'File deletion';
             return $item;
        });
        return inertia('dashboard/RecycleBin', compact('deleted_files'));
    }
}
