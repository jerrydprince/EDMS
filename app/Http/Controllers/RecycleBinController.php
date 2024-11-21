<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Archive;
use App\Models\User;
use App\Models\Unit;
use App\Models\Division;
use App\Models\Department;
use App\Models\Metadata;
use App\Models\Mail;

class RecycleBinController extends Controller
{
    public function index(Request $request){
        $per_item = 10;
        if($request->type == 'Deleted Users'){
            $deleted_files = User::with('deletedBy')->onlyTrashed()->paginate($per_item)->withQueryString();
            $type = $request->type;
            return inertia('dashboard/RecycleBin', compact('deleted_files', 'type'));
        }

        if($request->type == 'Deleted Task'){
            //dd('workflow');
        }

        if($request->type == 'Deleted Units'){
            $deleted_files = Unit::onlyTrashed()->paginate($per_item)->withQueryString();
            $type = $request->type;
            return inertia('dashboard/RecycleBin', compact('deleted_files', 'type'));
        }

        if($request->type == 'Deleted Divisions'){
            $deleted_files = Division::onlyTrashed()->paginate($per_item)->withQueryString();
            $type = $request->type;
            return inertia('dashboard/RecycleBin', compact('deleted_files', 'type'));
        }

        if($request->type == 'Deleted Departments'){
            $deleted_files = Department::onlyTrashed()->paginate($per_item)->withQueryString();
            $type = $request->type;
            return inertia('dashboard/RecycleBin', compact('deleted_files', 'type'));
        }

        if($request->type == 'Deleted Metadata'){
            $deleted_files = Metadata::onlyTrashed()->paginate($per_item)->withQueryString();
            $type = $request->type;
            return inertia('dashboard/RecycleBin', compact('deleted_files', 'type'));
        }

        if($request->type == 'Deleted Mails'){
            $deleted_files = Mail::onlyTrashed()->paginate($per_item)->withQueryString();
            $type  = $request->type;
            return inertia('dashboard/RecycleBin', compact('deleted_files', 'type'));
        }
       
        if (!$request->type || $request->type == 'Deleted Files') {
            $deleted_files = Archive::onlyTrashed()
                ->with(['deletedBy', 'archiveDocs' => function($query){
                    $query->onlyTrashed();
                }]) // Eagerly load relationships
                ->paginate($per_item)->withQueryString();
        
            $type = $request->type;
            return inertia('dashboard/RecycleBin', compact('deleted_files', 'type'));
        }
    }

    public function restoreRecycleBin(Request $request){
        if($request->type == 'Deleted Users'){
           $user = User::onlyTrashed()->find($request->id);
           if($user){
            $user->restore();
           }
           return redirect()->back();
        }

        if($request->type == 'Deleted Task'){
            //dd('workflow');
        }

        if($request->type == 'Deleted Units'){
           $unit = Unit::onlyTrashed()->find($request->id);
           if($unit){
            $unit->restore();
           }
           return redirect()->back();
        }

        if($request->type == 'Deleted Divisions'){
           $division = Division::onlyTrashed()->find($request->id);
           if($division){
            $division->restore();
           }
           return redirect()->back();
        }

        if($request->type == 'Deleted Departments'){
           $department = Department::onlyTrashed()->find($request->id);
           if($department){
            $department->restore();
           }
           return redirect()->back();
        }

        if($request->type == 'Deleted Metadata'){
            $meta = Metadata::onlyTrashed()->find($request->id);
            if($meta){
                 $meta->restore();
            }
            return redirect()->back();
        }

        if($request->type == 'Deleted Mails'){
            $mail = Mail::onlyTrashed()->find($request->id);
            if($mail){
               $mail->restore(); 
            }
            return redirect()->back();
        }
       
        if ($request->type == 'Deleted Files') {
            $file = Archive::onlyTrashed()->with(['archiveDocs'=>function($query){
                $query->onlyTrashed();
            }, 'filePath'=>function($query){
                $query->onlyTrashed();
            }])->onlyTrashed()->find($request->id);
            
            if($file){
                 $file->restore();
            }
           
            if($file->archiveDocs){
                foreach($file->archiveDocs as $docs){
                    $docs->restore();
                }
            }

            if($file->filePath){
                foreach($file->filePath as $path){
                    $path->restore();
                }
            }
            return redirect()->back();
        }
    }

    public function deleteRecyleBinFile(Request $request){
        //ensures only the super admin can delete a file
        if(!auth()->user()->hasRole('super admin')){
            return redirect()->back();
        }

        if($request->type == 'Deleted Users'){
            $user = User::onlyTrashed()->find($request->id);
            if($user){
             $user->forceDelete();
            }
            return redirect()->back();
         }
 
         if($request->type == 'Deleted Task'){
             //dd('workflow');
         }
 
         if($request->type == 'Deleted Units'){
            $unit = Unit::onlyTrashed()->find($request->id);
            if($unit){
             $unit->forceDelete();
            }
            return redirect()->back();
         }
 
         if($request->type == 'Deleted Divisions'){
            $division = Division::onlyTrashed()->find($request->id);
            if($division){
             $division->forceDelete();
            }
            return redirect()->back();
         }
 
         if($request->type == 'Deleted Departments'){
            $department = Department::onlyTrashed()->find($request->id);
            if($department){
             $department->forceDelete();
            }
            return redirect()->back();
         }
 
         if($request->type == 'Deleted Metadata'){
             $meta = Metadata::onlyTrashed()->find($request->id);
             if($meta){
                  $meta->forceDelete();
             }
             return redirect()->back();
         }
 
         if($request->type == 'Deleted Mails'){
             $mail = Mail::onlyTrashed()->find($request->id);
             if($mail){
                $mail->forceDelete(); 
             }
             return redirect()->back();
         }
        
         if ($request->type == 'Deleted Files') {
             $file = Archive::onlyTrashed()->with(['archiveDocs'=>function($query){
                 $query->onlyTrashed();
             }, 'filePath'=>function($query){
                 $query->onlyTrashed();
             }])->onlyTrashed()->find($request->id);
             
             if($file){
                  $file->forceDelete();
             }
            
             if($file->archiveDocs){
                 foreach($file->archiveDocs as $docs){
                     $docs->forceDelete();
                 }
             }
 
             if($file->filePath){
                 foreach($file->filePath as $path){
                     $path->forceDelete();
                 }
             }
             return redirect()->back();
         }
    }
}
