<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentType;
use Illuminate\Support\Facades\Storage;
use App\Models\Archive;
use Illuminate\Support\Facades\DB;
use App\Models\Department;
use App\Models\Folder;
use App\Models\Unit;
use App\Models\FilePath;
use App\Models\ArchiveDocs;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\File;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Spatie\PdfToImage\Pdf;
use App\Models\Division;
use App\Models\SubFolder;
use Illuminate\Support\Facades\Log;
use App\Models\FileActivity;
use App\Models\Version;
use App\Models\User;
use App\Models\Share;
use App\Mail\ShareFile;
use Illuminate\Support\Facades\Mail;

class ArchiveController extends Controller
{
    public function Archiving($branch, $id){
        //$doctypes = DocumentType::with('metadata')->get();
  // dd($id);

        $user = auth()->user();
        if($user->hasRole('super admin') || $user->hasRole('admin') || $user->hasRole('super user')){
            $branchId = request()->query('branch_id');
           // dd($branchId);
            if($branch == 'unit'){
                $selected_branch = Unit::find($branchId);
                $branch_id = $selected_branch->id;
            }
            if($branch == 'division'){
                $selected_branch = Division::find($branchId);
                $branch_id = $selected_branch->id;
            }
            if($branch == 'department'){
                $selected_branch = Department::find($branchId);
                $branch_id = $selected_branch->id;
            }
        }else{
            if($branch == 'unit'){
                $branch_id = $user->unit_id;
            }
            if($branch == 'division'){
                $branch_id = $user->division_id;
            }
            if($branch == 'department'){
                $branch_id = $user->department_id;
            }
        }
        if($branch == 'unit'){
             $branches = Unit::with(['folder', 'documentType'=>function($query){
                $query->with('metadata');
            }])->where('id', $branch_id)->first();
        }

        if($branch == 'division'){
                $branches = Division::with(['folder', 'documentType'=>function($query){
                $query->with('metadata');
            }])->where('id', $branch_id)->first();
            //dd($id);
        }

       if($branch == 'department'){
                $branches = Department::with(['folder', 'documentType'=>function($query){
                $query->with('metadata');
            }])->where('id', $branch_id)->first();
        }
  
        $doctypes = isset($branches->documentType)? $branches->documentType : [];
        // if(count($doctypes)==0){
        //    if(auth()->user()->division_id){
        //     $division = Division::with(['documentType'=> function($query){
        //         $query->with('metadata');
        //     }])->find(auth()->user()->division_id);
        //     $doctypes = $division->documentType;
        //    }

        //    if(auth()->user()->unit_id){
        //     $unit = Unit::with(['documentType'=> function($query){
        //        $query->with('metadata');
        //     }])->find(auth()->user()->division_id);
        //     $doctypes = $unit->documentType;
        //    }

        //    if(auth()->user()->department_id){
        //     $unit = Department::with(['documentType'=> function($query){
        //        $query->with('metadata');
        //     }])->find(auth()->user()->division_id);
        //     $doctypes = $unit->documentType;
        //    }
        // }
       
        //$folders = $this->getAllFolders(public_path('/Archives/Department/Division/Unit/'));
        $archives = session()->get('archives');
        $thumbnails = request()->session()->get('thumbnails');
        $branch_type = $branch;
        return inertia('archives/archiving', compact('doctypes', 'archives','thumbnails', 'branches', 'branch_type'));
    }

    public function createFolder(Request $request){
     
        $request->validate([
            'name' => 'required|unique:folder',
            'number' =>'required',
            'year' => 'required',
        ]);
      
        DB::transaction(function () use($request) {
            $folder2 = Folder::find($request->id);

            // if(auth()->user()->hasRole('record manager')){
            //     $department = auth()->user()->department_id;
            //     $division = auth()->user()->division_id;
            //     $unit = auth()->user()->unit_id;
            // }else{
            //     $department = $request->department;
            //     $division = $request->division;
            //     $unit = $request->unit;
            // }

            $department = $request->deptId? $request->deptId: auth()->user()->department_id;
            $division = $request->divId? $request->divId: auth()->user()->division_id;
            $unit = $request->unitId? $request->unitId: auth()->user()->unit_id;

            if(!$department){
                return redirect()->back();
            }

            $fullPath = '';

            if(!$request->id){
                $baseDirectory = "storage/Archives";
                    $subfolders = [$department, $division, $unit, $request->name];
                    if (!is_dir($baseDirectory)) {
                        mkdir($baseDirectory, 0777, true);
                    }

                    foreach ($subfolders as $folderName) {
                        $fullPath = $baseDirectory . "/" . $folderName;
                        if (!is_dir($fullPath)) {
                            mkdir($fullPath, 0777, true);
                        }
                        $baseDirectory = $fullPath;
                    }
            }else{
                $fullPath = $folder2->path."/".$request->name;
               
                if (!is_dir($fullPath)) {
                    mkdir($fullPath, 0777);
                }
            }

            $folder = new Folder();
            $folder->name = $request->name;
            $folder->number = $request->number;
            $folder->year = $request->year;
            $folder->file_type_id = $request->type;
            $folder->department_id=$department;
            $folder->division_id = $division;
            $folder->unit_id = $unit;
            $folder->path = $fullPath;
            $folder->description = $request->description;
            if($request->id){
                $folder->is_sub_folder = 1;
            }else{
                $folder->is_sub_folder = 0;
            }
            $folder->save();

            if($request->id){
               
                $sub = new SubFolder();
                $sub->path = $fullPath;
                $sub->folder_id = $folder2->id;
                $sub->name = $request->name;
                $sub->number = $request->number;
                $sub->year = $request->year;
                $sub->file_type_id = $request->type;
                $sub->department_id=$department;
                $sub->division_id = $division;
                $sub->unit_id = $unit;
                $sub->description = $request->description;
                $sub->my_id = $folder->id;
                $sub->save();
            }

       
        });
        return redirect()->back();
    }

    public function getAllFolders($path){
        $contents = scandir($path);

        // Filter out '.' and '..' entries (current and parent directories)
        $directories = array_filter($contents, function ($item) use ($path) {
            return is_dir($path . '/' . $item) && !in_array($item, ['.', '..']);
        });

        $folders = [];

        foreach ($directories as $directory) {
            $filesInDirectory = glob($path . '/' . $directory . '/*');
        
            $data = [
                'name'=> $directory,
                'files' => count($filesInDirectory)
            ];
            array_push($folders, $data);
        }
        return $folders;
    }

    function formatSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
    
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function createThumbnail($sourcePath, $destinationPath, $thumbnailWidth, $thumbnailHeight, $fileName)
    {
        
        $manager = new ImageManager(Driver::class);
        $image = $manager->read($sourcePath);
        $image->scale(width:480);
        /* $image->save(public_path($destinationPath.$fileName)); */
        $image->save($destinationPath.$fileName);

        return $destinationPath.$fileName;
    }

    public function numberFilter($string){
        return preg_replace("/[a-zA-Z]/", "", $string);
    }

    function convertToBytes($value)
{
    $value = trim($value);
    $unit = strtolower(substr($value, -2));

    switch ($unit) {
        case 'KB':
            return (int)$value * 1024;
        case 'MB':
            return (int)$value * 1024 * 1024;
        case 'GB':
            return (int)$value * 1024 * 1024 * 1024;
        default:
            return 0; // Invalid unit
    }
}

    public function batchUploadFiles(Request $request) {
        $folder = Folder::find($request->folder_id);
        if ($folder && $folder->is_open == 0) {
            return response()->json(['error' => 'This file is closed and documents can no longer be uploaded into it.'], 403);
        }

        $department = $folder ? $folder->department_id : null;
        $division = $folder ? $folder->division_id : null;
        $unit = $folder ? $folder->unit_id : null;

        $documents = $request->input('documents'); // Contains metadata and doctype
        $files = $request->file('documents'); // Contains the actual files

        if (!$documents) {
            return redirect()->back()->withErrors(['error' => 'No documents provided.']);
        }

        foreach ($documents as $index => $doc) {
            // Create Archive
            $archive = new Archive();
            $archive->department_id = $department;
            $archive->division_id = $division;
            $archive->unit_id = $unit;
            $archive->folder_id = $request->folder_id;
            $archive->updated_by = auth()->user()->id;
            $archive->save();

            // Save Document Type ID inside Archive if possible (optional, but handled implicitly)
            // If the system expects it elsewhere, we handle it. But the model doesn't have it natively except if via relation.

            // Metadata
            $doc_types = [];
            if(isset($doc['input_obj']) && is_array($doc['input_obj'])) {
                foreach($doc['input_obj'] as $values){
                    // If JSON string
                    if(is_string($values)) {
                        $values = json_decode($values, true);
                    }
                    if(is_array($values)) {
                        foreach($values as $key => $value){
                            $arr = [
                                'archive_keys' => $key,
                                'archive_values' => $value,
                                'archive_id' => $archive->id,
                                'created_at' => \Carbon\Carbon::now()
                            ];
                            array_push($doc_types, $arr);
                        }
                    }
                }
                ArchiveDocs::insert($doc_types);
            }

            $this->logFileActivity($archive->id, "New file created by ".auth()->user()->first_name.' '.auth()->user()->last_name);

            // File upload
            if(isset($files[$index]['file'])) {
                $file = $files[$index]['file'];
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                $path = $folder->path.'/';
                $thumbnail_path = $folder->path.'/thumbnails/';

                $extension = strtolower($file->getClientOriginalExtension());
                $fileName = time() . '_' . $file->getClientOriginalName();
                $size = $this->formatSize(filesize($file));

                if(!is_dir(public_path($thumbnail_path))){
                    mkdir($thumbnail_path,0777,true);
                }

                $thumbnail = '';
                if (in_array($extension, $allowedExtensions)) {
                        $thumbnail = $this->createThumbnail($file, $thumbnail_path, 480, 520, $fileName);
                }
    
                $file->move(public_path($path),$fileName);
                
                $path = str_replace('storage/', '', $path);
                $thumbnail = str_replace('storage/','', $thumbnail);

                $paths = [
                    'path'=>'/'.$path.$fileName,
                    'created_at'=>\Carbon\Carbon::now(), 
                    'updated_at'=>\Carbon\Carbon::now(),
                    'updated_by' =>auth()->user()->id,
                    'updated_by_name' => auth()->user()->last_name." ".auth()->user()->first_name,
                    'file_name' => $fileName,
                    'file_size' => $size,
                    'thumbnails' => '/'.$thumbnail
                ];
                
                $filePathObj = FilePath::create($paths);
                $archive->filePath()->attach($filePathObj->id);
            }
        }

        return redirect()->back()->with('success', 'Batch uploaded successfully');
    }

    public function uploadFiles(Request $request){
        // $request->validate([
        //     'files.*' => 'required|mimes:jpeg,png,jpg,gif,svg,pdf,doc,docx,xls,xlsx|max:4096',
        // ]);

        $uploadedFiles = [];
        
        if($request->file('files')){
            // $department = auth()->user()->department_id;
            // $division = auth()->user()->division_id;
            // $unit = auth()->user()->unit_id;
            $folder = Folder::find($request->folder_id);
            $request->session()->forget('thumbnails');

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
          
            $filepath = [];
            $thumbnails = [];


            // if((!is_null($department) && $department != 0) && (!is_null($division) && $division != 0) && (!is_null($unit) && $unit !=0)){
            //     $path = "/Archives/".$department."/".$division."/".$unit."/".$folder."/";
            //     $thumbnail_path = "/Archives/".$department."/".$division."/".$unit."/".$folder."/thumbnails/";
            // }
            // if((!is_null($department) && $department != 0) && (!is_null($division) && $division != 0) && !(!is_null($unit) && $unit !=0)){
            //     $path = "/Archives/".$department."/".$division."/".$folder."/";
            //     $thumbnail_path = "/Archives/".$department."/".$division."/".$folder."/thumbnails/";
            // }
            // if((!is_null($department) && $department != 0) && !(!is_null($division) && $division != 0)){
            //     $path = "/Archives/".$department."/".$folder."/";
            //     $thumbnail_path = "/Archives/".$department."/".$folder."/thumbnails/";
            // }

            $total_size = 0;

            try {
                foreach ($request->file('files') as $index=>$file) {
                    $path = $folder->path.'/';
                    $thumbnail_path = $folder->path.'/thumbnails/';

                    $extension = strtolower($file->getClientOriginalExtension());
                 
                    //$path = "/Archives/".$department."/".$division."/".$unit."/".$folder."/";
                    //$thumbnail_path = "/Archives/".$department."/".$division."/".$unit."/".$folder."/thumbnails/";
                    $fileName = time() . '_' . $file->getClientOriginalName();
                   /* $fileName = time() . '_.dat'; */
                    $size = $this->formatSize(filesize($file));
                  
                    $total_size += filesize($file);
                    //create thumbnails
                    if(!is_dir(public_path($thumbnail_path))){
                       /* mkdir(public_path("/Archives/".$department."/".$division."/".$unit."/".$folder.'/thumbnails'),0777,true); */
                       mkdir($thumbnail_path,0777,true);
                    }

                    $thumbnail = '';
                    if (in_array($extension, $allowedExtensions)) {
                         $thumbnail = $this->createThumbnail($file, $thumbnail_path, 480, 520, $fileName);

                        //  $storage_path = Storage::putFileAs(
                        //     'public/'.$path, $file, $fileName
                        //     );
                        //$file->move(public_path($path),$fileName);
                    }
        
                        $file->move(public_path($path),$fileName);
                   
                    //$file->move(public_path($path),$fileName);
                    
                   // $storage_path = Storage::disk('local')->put('public/'.$path.$fileName, encrypt($fileName));
                  // $storage_path = Storage::put('public/'.$path, $file);
                  
                 
                  
                   /*
                   $fileContent = $file->get();

                   // Encrypt the Content
                   $encryptedContent = encrypt($fileContent);
                   
                   // Store the encrypted Content
                   Storage::disk('local')->put('public/'.$path.$fileName, $encryptedContent);
                    */
                  // Storage::disk('local')->put('public/'.$path, encrypt($file));
                    $path = str_replace('storage/', '', $path);
                    $thumbnail = str_replace('storage/','', $thumbnail);

                    $paths = [
                        'path'=>'/'.$path.$fileName,
                        'created_at'=>\Carbon\Carbon::now(), 
                        'updated_at'=>\Carbon\Carbon::now(),
                        'updated_by' =>auth()->user()->id,
                        'updated_by_name' => auth()->user()->last_name." ".auth()->user()->first_name,
                        'file_name' => $fileName,
                        'file_size' => $size,
                        'thumbnails' => '/'.$thumbnail
                    ];
                    array_push($filepath, $paths);
                    array_push($thumbnails,$thumbnail );
                }
                FilePath::insert($filepath);

                //add folder size
                 //$folder = Folder::find($request->folder_id);
                // $folder->folder_size = $total_size? $total_size: 0;
                // $folder->save();
               
                $request->session()->put('file_size', $total_size);

                $ids = FilePath::latest()->limit(count($filepath))->pluck('id');
                $request->session()->put(['ids'=>$ids]);
                $request->session()->put(['thumbnails'=>$thumbnails]);
            } catch (Exception $e) {
                dd($e);
            }
           
        }

       // $request->session()->forget('archives');
        return redirect()->back();
    }

    public function ArchiveFiles(Request $request){
        $user = auth()->user();
        $folder = Folder::find($request->folder_id);
        
        if (!$folder) {
            return redirect()->back()->withErrors(['error' => 'The specified folder could not be found.']);
        }

        if ($folder->is_open == 0) {
            return redirect()->back()->withErrors(['error' => 'This file is closed and documents can no longer be uploaded into it.']);
        }

        $department = $folder->department_id;
        $division = $folder->division_id;
        $unit = $folder->unit_id;

        $archive = new Archive();
        $archive->department_id = $department;
        $archive->division_id = $division;
        $archive->unit_id = $unit;
        //$archive->path =$request->session()->get('archives');
        $archive->folder_id = $request->folder_id;
        $archive->updated_by = auth()->user()->id;
        $archive->save();

        // $doc_types = [
        //     'description'=>$request->description,
        //     'text' => $request->text,
        //     'date' => $request->date,
        //     'number' => $request->number,
        //     'list'=> $request->list,
        //     'archive_id' => $archive->id
        // ];
        $doc_types = [];
        foreach($request->input_obj as $values){
           foreach($values as $key => $value){
                $arr = [
                'archive_keys' => $key,
                'archive_values' => $value,
                'archive_id' => $archive->id,
                'created_at' => \Carbon\Carbon::now()
            ];
           }
          
          array_push($doc_types, $arr);
        }

        ArchiveDocs::insert($doc_types);

        //create an activity
        $archive_id = $archive->id;
        $details = "New file created by ".auth()->user()->first_name.' '.auth()->user()->last_name;

        $this->logFileActivity($archive_id, $details);

        $file_path_ids = [];

        if($request->file('files')) {
            $folder = Folder::find($request->folder_id);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $filepath = [];
            $thumbnails = [];

            try {
                foreach ($request->file('files') as $index=>$file) {
                    $path = $folder->path.'/';
                    $thumbnail_path = $folder->path.'/thumbnails/';

                    $extension = strtolower($file->getClientOriginalExtension());
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $size = $this->formatSize(filesize($file));

                    if(!is_dir(public_path($thumbnail_path))){
                        mkdir($thumbnail_path,0777,true);
                    }

                    $thumbnail = '';
                    if (in_array($extension, $allowedExtensions)) {
                         $thumbnail = $this->createThumbnail($file, $thumbnail_path, 480, 520, $fileName);
                    }
        
                    $file->move(public_path($path),$fileName);
                   
                    $path = str_replace('storage/', '', $path);
                    $thumbnail = str_replace('storage/','', $thumbnail);

                    $paths = [
                        'path'=>'/'.$path.$fileName,
                        'created_at'=>\Carbon\Carbon::now(), 
                        'updated_at'=>\Carbon\Carbon::now(),
                        'updated_by' =>auth()->user()->id,
                        'updated_by_name' => auth()->user()->last_name." ".auth()->user()->first_name,
                        'file_name' => $fileName,
                        'file_size' => $size,
                        'thumbnails' => '/'.$thumbnail
                    ];
                    array_push($filepath, $paths);
                }
                FilePath::insert($filepath);
                $file_path = FilePath::latest()->get()->take(count($filepath));
                $file_path_ids = $file_path->pluck('id');
            } catch (\Exception $e) {
                // Ignore failure
            }
        } else {
            $file_path_ids = $request->session()->get('ids') ?? [];
        }

        if (!empty($file_path_ids)) {
            $archive->filePath()->attach($file_path_ids);
        }

        $request->session()->forget('file_size');

        // $files = $request->session()->get('archives');
        // if($files){
        //     $data = [];
        //     foreach($files as $file){
        //         $temp = [
        //             'path' => $file,
        //             'archive_id' => $archive->id
        //         ];
        //         array_push($data, $temp);
        //     }

        //     DB::table('files')->insert($data);
        //     $request->session()->forget('archives');
        // }

        if($user->hasRole('super admin') || $user->hasRole('admin') || $user->hasRole('super user') || $user->hasRole('director') || $user->hasRole('deputy director') || $user->hasRole('assistant director')){
            return redirect()->route('folder-view2',[$request->branch_type, $request->folder_id, $request->branch_id]);
        }
        return redirect()->route('folder-view',[$request->branch_type, $request->folder_id]);
    }

    public function deleteArchives(Request $request){
        $archive = Archive::find($request->id);
        ArchiveDocs::where('archive_id', $archive->id)->delete();
        $file_path = $archive->filePath()->get();
        $total_size = 0;
        foreach ($file_path as $file) {
            $total_size  += $this->convertToBytes($file->file_size);
           Storage::delete('public'.$file->path);
           Storage::delete('public'.$file->thumbnails);
           //unlink(public_path($file->thumbnails));
        }
        //update folder count
        $folder = Folder::find($archive->folder_id);

        //recalculate the folder size
        $folder_size_byte = $this->convertToBytes($folder->folder_size);
        $folder_size = $this->formatSize($folder_size_byte - $total_size);

        if($folder){
            $folder->file_count = $folder->file_count-1;
            $folder->folder_size  = $folder_size;
            $folder->save();
        }
        

        $archive->filePath()->detach();
        //dd($file_path->pluck('id'));
        //$file_path::whereIn('id', $file_path->pluck('id'))->delete();
        FilePath::whereIn('id',$file_path->pluck('id'))->delete();
        $archive->delete();

        return redirect()->back();
    }

    public function Branches($id){
       $department = Department::withCount(['user','archive'])->with(['folder', 'archive'=>function($query){
            $query->with(['archiveDocs', 'filePath']);
       }])->find($id);
        return inertia('branch/Branches', compact('department'));
    }

    public function downloadFile(Request $request){
        $archive = Archive::with(['filePath'])->find($request->id);
        if (!$archive || $archive->filePath->isEmpty()) {
            abort(404, 'Archive or filePath not found.');
        }
        $this->authorize('view', $archive);

        $archiveFile = $archive->file()->withoutGlobalScopes()->first();
        
        if ($archiveFile && $archiveFile->is_confidential && $archiveFile->created_by !== auth()->id()) {
            abort(403, 'You are not authorized to view this confidential file.');
        }

        $targetFp = ($request->filled('file_path_id'))
            ? ($archive->filePath->firstWhere('id', $request->file_path_id) ?? $archive->filePath[0])
            : $archive->filePath[0];

        $pathString = ltrim($targetFp->path, '/');
        
        // 1. Try public/storage/... (where the upload actually places it)
        $path1 = public_path('storage/' . $pathString);
        $path2 = public_path($pathString);
        $path3 = Storage::path('public/' . $pathString);

        \Log::info("Attempting to download file for Archive ID: {$request->id}, FilePath ID: {$targetFp->id}");
        \Log::info("Path 1: {$path1} - Exists: " . (file_exists($path1) ? 'Yes' : 'No'));
        \Log::info("Path 2: {$path2} - Exists: " . (file_exists($path2) ? 'Yes' : 'No'));
        \Log::info("Path 3: {$path3} - Exists: " . (file_exists($path3) ? 'Yes' : 'No'));

        $path = $path1;
        if (!file_exists($path)) {
            $path = $path2;
            if(!file_exists($path)){
                 $path = $path3;
            }
        }
        
        // If file not found, try stripping 'copied_' prefix in case the file copy logic failed to create physical copies
        if (!file_exists($path) && str_contains($path, 'copied_')) {
            $cleanPathString = str_replace('copied_', '', $pathString);
            $cleanPath1 = public_path('storage/' . $cleanPathString);
            $cleanPath2 = public_path($cleanPathString);
            $cleanPath3 = Storage::path('public/' . $cleanPathString);
            
            if (file_exists($cleanPath1)) {
                $path = $cleanPath1;
            } elseif (file_exists($cleanPath2)) {
                $path = $cleanPath2;
            } elseif (file_exists($cleanPath3)) {
                $path = $cleanPath3;
            }
        }

        if (!file_exists($path)) {
            abort(404, 'File not found on the server.');
        }

        $this->logFileActivity($archive->id, 'Downloaded file: ' . $targetFp->file_name);
        return response()->download($path, $targetFp->file_name);
    }

    public function downloadFileVersion(Request $request){
        $version = Version::with('file')->find($request->id);
        if (!$version) {
            abort(404, 'Version not found.');
        }
        $archive = Archive::where('file_id', $version->file_id)->first();
        if ($archive) {
            $this->authorize('view', $archive);
        }

        if (isset($version->file) && $version->file->is_confidential) {
            $user = auth()->user();
            if (!$user->hasRole('super admin') && !$user->hasRole('super user') && !$user->hasRole('super user')) {
                if ($version->file->created_by !== $user->id) {
                    abort(403, 'Unauthorized access to confidential file.');
                }
            }
        }

        $versionData = json_decode($version->versions, true);

        if (!isset($versionData['file_path']) || empty($versionData['file_path'])) {
            \Log::error("File path not found in version JSON data: " . json_encode($versionData));
            abort(404, 'File not found in version.');
        }

        $pathString = ltrim($versionData['file_path'][0]['path'], '/');
        $fileName = $versionData['file_path'][0]['file_name'];
        
        $path1 = public_path('storage/' . $pathString);
        $path2 = public_path($pathString);
        $path3 = storage_path('app/public/' . $pathString);

        \Log::info("Attempting to download version ID: {$request->id}");
        \Log::info("Version File Path 1: {$path1} - Exists: " . (file_exists($path1) ? 'Yes' : 'No'));
        \Log::info("Version File Path 2: {$path2} - Exists: " . (file_exists($path2) ? 'Yes' : 'No'));
        \Log::info("Version File Path 3: {$path3} - Exists: " . (file_exists($path3) ? 'Yes' : 'No'));

        $path = $path1;
        if (!file_exists($path)) {
            $path = $path2;
            if(!file_exists($path)){
                 $path = $path3;
            }
        }
        
        if (!file_exists($path)) {
            // If file not found, try stripping 'copied_' prefix
            $originalFileName = str_replace('copied_', '', $fileName);
            $originalPathString = str_replace($fileName, $originalFileName, $pathString);
            
            $path1 = public_path('storage/' . $originalPathString);
            $path2 = public_path($originalPathString);
            $path3 = storage_path('app/public/' . $originalPathString);
            
            \Log::info("Fallback Version Path 1: {$path1} - Exists: " . (file_exists($path1) ? 'Yes' : 'No'));
            \Log::info("Fallback Version Path 2: {$path2} - Exists: " . (file_exists($path2) ? 'Yes' : 'No'));
            \Log::info("Fallback Version Path 3: {$path3} - Exists: " . (file_exists($path3) ? 'Yes' : 'No'));

            $path = $path1;
            if (!file_exists($path)) {
                $path = $path2;
                if(!file_exists($path)){
                    $path = $path3;
                }
            }
        }
        
        if (!file_exists($path)) {
            abort(404, 'File not found on the server.');
        }
        
        if ($archive) {
            $this->logFileActivity($archive->id, 'Downloaded version ' . $version->version);
        }
        
        return response()->download($path, $fileName);
    }

    public function DuplicateFile(Request $request){
        DB::transaction(function () use($request) {
            $archive = Archive::with(['archiveDocs', 'filePath'])->where('id', $request->id)->first();
            $new_archive = $archive->replicate();
            $new_archive->updated_by = auth()->user()->id;
            $new_archive->save();
            $archiveDocs = $archive->archiveDocs;
            $file_path = $archive->filePath;
            $tempDocs = [];
            foreach($archiveDocs as $docs){
                $newValue = $docs->archive_values;
                if (stripos($docs->archive_keys, 'title') !== false) {
                    $newValue = $newValue . ' (Copy)';
                }

                $arr = [
                    'archive_id' => $new_archive->id,
                    'archive_keys' => $docs->archive_keys,
                    'archive_values' => $newValue,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ];
               
                array_push($tempDocs, $arr);
            }

            ArchiveDocs::insert($tempDocs);
            
            //create file activity
            $details = "File duplicate was made by ".auth()->user()->first_name." ".auth()->user()->last_name;
            $archive_id = $archive->id;
            $this->logFileActivity($archive_id, $details);
    
            $total_size = 0;
            foreach($file_path as $path){
                 //make a copy of the file
               $path_string = explode('/', $path->path);
               $path_string[count($path_string)-1] = 'copied_'.$path_string[count($path_string)-1];
               $new_path = implode('/',$path_string);
    
               //thumbnails
               $path_string_thumbnails = explode('/', $path->thumbnails);
               $path_string_thumbnails[count($path_string_thumbnails)-1] = 'copied_'.$path_string_thumbnails[count($path_string_thumbnails)-1];
               $new_path_thumbnails = implode('/',$path_string_thumbnails);
              
                $current_path = 'public'.$new_path;
                $current_thumbnail = 'public'.$new_path_thumbnails;
                Storage::copy('public'.$path->path, $current_path);
                Storage::copy('public'.$path->path, $current_thumbnail);
    
                $newpath = $path->replicate();
                $newpath->path = $new_path;
                $newpath->thumbnails = $new_path_thumbnails;
                $newpath->file_name = 'copied_'.$path->file_name;
                $newpath->updated_by = auth()->user()->id;
                $newpath->updated_by_name = auth()->user()->last_name." ".auth()->user()->first_name;
                $newpath->save();
                $new_archive->filePath()->attach($newpath);

                $total_size  += $this->convertToBytes($path->file_size);
            }
    
            $folder = Folder::find($archive->folder_id);
            //recalculate folder 
            $folder_size_byte = $this->convertToBytes($folder->folder_size);
            $folder_size = $this->formatSize($folder_size_byte + $total_size);

            $folder->file_count = $folder->file_count + 1;
           // $folder->folder_size  += $total_size;
            $folder->save();
        });

        return redirect()->back();
    }

    public function imageToPDF($request){
        $archive = Archive::with(['filePath','department', 'division', 'unit'])->find($request->id);
        $department = $archive->department_id;
        $unit = $archive->unit_id;
        $division = $archive->division_id;
        $folder = Folder::find($archive->folder_id);

        foreach($archive->filePath as $path){
            $old_fileName = $path->file_name;
            $name_array = explode('.',$old_fileName);
            $new_fileName = $name_array[0];
            $current_path = "/Archives/".$department."/".$division."/".$unit."/".$folder->name."/convertedFiles/".$new_fileName.".pdf";
            (new TesseractOCR(public_path('storage'.$path->path)))
            ->configFile('pdf')
            ->setOutputFile(public_path("storage".$current_path))
            ->run();

            $path->path = $current_path;
            $path->file_name = $new_fileName.".pdf";
            $path->save();
        }
       
        return true;
    }

    public function PDFToImage($request){
        $archive = Archive::with(['filePath','department', 'division', 'unit'])->find($request->id);
        // $department = $archive->department->first()->id;
        // $unit = isset($archive->unit)?? $archive->unit->first()->id;
        // $division = isset($archive->division)?? $archive->division->first()->id;
        $folder = Folder::find($archive->folder_id);

        foreach($archive->filePath as $path){
            $thumbnail = $path->thumbnails;
            $name_array = explode('/',$thumbnail);
            $new_fileName = $name_array[count($name_array) - 1];
            $current_path = $path->path;
            $name_array = array_slice($name_array, 0, -2);
            $new_path = implode('/',$name_array);
            $old_path = $new_path.'/'.$new_fileName;
            // if($department && $division && $unit){
            //     $current_path = "/Archives/".$department."/".$division."/".$unit."/".$folder->name."/convertedFiles/".$new_fileName;
            //     $old_path = "/Archives/".$department."/".$division."/".$unit."/".$folder->name."/".$new_fileName;
            // }

            // if($department && $division){
            //     $current_path = "/Archives/".$department."/".$division."/".$folder->name."/convertedFiles/".$new_fileName;
            //     $old_path = "/Archives/".$department."/".$division."/".$folder->name."/".$new_fileName;
            // }
            // if($department && $unit){
            //     $current_path = "/Archives/".$department."/".$unit."/".$folder->name."/convertedFiles/".$new_fileName;
            //     $old_path = "/Archives/".$department."/".$unit."/".$folder->name."/".$new_fileName;
            // }
            Storage::copy('public'.$old_path, 'public'.$current_path);
        }

            $path->path = $old_path;
            $path->file_name = $new_fileName;
            $path->save();

        return true;
    }

    public function logFileActivity($archive_id, $details){
        FileActivity::create([
            'archive_id' => $archive_id,
            'details' => $details,
            'date' => \Carbon\Carbon::now()
        ]);
        return true;        
    }

    public function changeFileFormat(Request $request){
        $current = $request->current;
        $to = $request->to;
        if($current == 'Image Format' && $to == 'PDF Format'){
            $this->imageToPDF($request);

            // FileActivity::create([
            //     'archive_id' => $archive->id,
            //     'details' => 'File format changed from '.$current." to ".$to." format",
            //     'date' => \Carbon\Carbon::now()
            // ])
            //$this->logFileActivity($archive_id, $details)
            return redirect()->back();
        }
        if($current == 'PDF Format' && $to == 'Image Format'){
            $this->PDFToImage($request);

            // FileActivity::create([
            //     'archive_id' => $archive->id,
            //     'details' => 'File format changed from '.$current." to ".$to." format",
            //     'date' => \Carbon\Carbon::now()
            // ])
            //$this->logFileActivity($archive_id, $details)
            return redirect()->back();
        }
    }

    public function renameFile(Request $request){
        // $file_path = FilePath::find($request->id);
        // $old_name = 'storage'.$file_path->path;
        // $path_array = explode('/', $file_path->path);
        // $file_name = $path_array[count($path_array) - 1];
        // $ext_array = explode('.',$file_name);
        // $ext_array[0] = $request->name;
        // $new_fileName = implode('.', $ext_array);
        // $path_array[count($path_array) -1] = $new_fileName;
        // $new_file_path = implode('/',$path_array);
        // $new_file_name = 'storage'.implode('/',$path_array);

        //if(file_exists(public_path($new_file_name))){
           // return redirect()->back()->with('error','The file name alread exist');
       // }else{
            // rename($old_name, $new_file_name);
            // $file_path->path = $new_file_path;
            // $file_path->file_name = $new_fileName;
            // $file_path->save();

            $archive = Archive::find($request->id);
            $file_name = $archive->archiveDocs->filter(function($arch){
                if($arch->archive_keys == 'title'){
                    return $arch;
                }
            });
            $old_name = $file_name->first() ? $file_name->first()->archive_values : 'Unknown';
            
            $archive = $archive->archiveDocs()->updateOrCreate(
                ['id'=>$request->archive_docs_id], 
                ['archive_keys'=>'title', 'archive_values' => $request->name]
            );
      
                $archive_id = $request->id;
                $details = "File name changed from '{$old_name}' to '{$request->name}', 
                action carried out by ".auth()->user()->first_name.' '.auth()->user()->last_name;
                $this->logFileActivity($archive_id, $details) ;            

            return redirect()->back();
       // }
        
    }

    // public function userArchiving(){
    //     $doctypes = DocumentType::with('metadata')->get();
    //     //$folders = $this->getAllFolders(public_path('/Archives/Department/Division/Unit/'));
    //     $archives = session()->get('archives');
    //     $folders =auth()->user()->department->folder;
    //     $thumbnails = request()->session()->get('thumbnails');
    //    // return inertia('dashboard/Branches', compact('doctypes', 'folders','archives','thumbnails'));
    // }

    public function isUserPermittedToViewDivision($branch_id){
        //check if the data entry user have access
        $user = auth()->user();
        if(auth()->user()->hasRole('record manager') || $user->hasRole('deputy director')){
            if($user->division_id && $user->division_id == $branch_id){
                return true;
            }else{
                return false;
            }
        }

        //check if the unit head can access the said resources
        if($user->hasRole('assistant director')){
           return false;
        }

        //suepr admin, super users and admin can always access any of these
        if($user->hasRole('super admin') || $user->hasRole('super user') || $user->hasRole('admin') || $user->hasPermissionTo('division view')){
            return true;
        }

        //check if directors/departmental heads can access this
        if($user->hasRole('director')){
            $department_id = $user->department_id;
            $exists = Department::where('id', $department_id)
                        ->whereHas('division', function($query) use($branch_id){
                            $query->where('id', $branch_id);
                        })->exists();
            if($exists){
                return true;
            }else{
                return false;
            }
        }

        //check if divisional head can access this
        // if($user->hasRole('deputy director')){
        //     $division_id = $user->division_id;

        //     $exists = Division::where('id', $division_id)
        //         ->whereHas('unit', function($query) use($branch_id){
        //             $query->where('id', $branch_id);
        //         })->exists();
            
        //     if($exists){
        //         return true;
        //     }else{
        //         return false;
        //     }
        // }
    }

    public function isUserPermittedToViewUnit($branch_id){
        //check if the data entry user have access
        $user = auth()->user();
        if(auth()->user()->hasRole('record manager')){
            if($user->unit_id && $user->unit_id == $branch_id){
                return true;
            }else{
                return false;
            }
        }

        //check if the unit head can access the said resources
        if($user->hasRole('assistant director')){
            if($user->unit_id == $branch_id){
                return true;
            }else{
                return false;
            }
        }

        //suepr admin, super users and admin can always access any of these
        if($user->hasRole('super admin') || $user->hasRole('super user') || $user->hasRole('admin') || $user->hasPermissionTo('unit view')){
            return true;
        }

        //check if directors/departmental heads can access this
        if($user->hasRole('director')){
            $department_id = $user->department_id;
            $exists = Department::where('id', $department_id)
                        ->whereHas('division', function($query) use($branch_id){
                            $query->where('id', $branch_id);
                        })->exists();
            if($exists){
                return true;
            }else{
                return false;
            }
        }

        //check if divisional head can access this
        if($user->hasRole('deputy director')){
            $division_id = $user->division_id;
            if($division_id  == $user->division_id){
                return true;
            }else{
                return false;
            }
        }
    }

    public function isUserPermittedToViewDepartment($branch_id){
        //check if the data entry user have access
        $user = auth()->user();
        if(auth()->user()->hasRole('record manager')){
            if($user->department_id && $user->department_id == $branch_id){
                return true;
            }else{
                return false;
            }
        }

        //check if the unit head can access the said resources
        if($user->hasRole('assistant director')){
            return false;
        }

        //suepr admin, super users and admin can always access any of these
        if($user->hasRole('super admin') || $user->hasRole('super user') || $user->hasRole('admin') || $user->hasPermissionTo('department view')){
            return true;
        }

        //check if directors/departmental heads can access this
        if($user->hasRole('director')){
           if($user->department_id == $branch_id){
            return true;
           }else{
            return false;
           }
        }

        //check if divisional head can access this
        if($user->hasRole('deputy director')){
           return false;
        }
    }

    public function userBranches(Request $request){
     
        $search = request()->search;

          
            
            if(request()->branch){
               
               // $branch_id = request()->id;
                $branch_name = ucfirst(request()->branch);
       
                if($branch_name == 'Unit'){
                    $branch_id =  $request->unitId;
                }else if($branch_name == 'Division'){
                    $branch_id = $request->divId;
                }else if($branch_name == 'Department'){
                    $branch_id = $request->deptId;
                }

                if($branch_id == 0){
                    return redirect()->back();
                }
        
            }else{ 
                
                if(auth()->user()->unit_id){
                    $branch_id = auth()->user()->unit_id;
                    $branch_name = 'Unit';
                }else
                if(auth()->user()->division_id){
                    $branch_id =  auth()->user()->division_id;
                    $branch_name = "Division";
                }else
                if(auth()->user()->department_id){
                    $branch_id =  auth()->user()->department_id;
                    $branch_name = "Department";
                }
            }
            $unitId = filled(request()->unitId)? request()->unitId: auth()->user()->unit_id;
            $divId = filled(request()->divId)? request()->divId: auth()->user()->division_id;
            $deptId = filled(request()->deptId)? request()->deptId: auth()->user()->department_id;

            $folder = Folder::with(['fileType', 'subfolder']);
            $user = auth()->user();

          if($branch_name == 'Unit' && $this->isUserPermittedToViewUnit($branch_id)){
            
            $branch = Unit::with(['folder'=>function($query){
                $query->with(['fileType','subfolder']);
            }, 'fileType'])->find($branch_id);
            //$branch_name = 'Unit';
            $branch = Unit::find($branch_id);
            //$folder = $query->where('unit_id', $branch_id)->latest()->paginate(20);
            $folder->where('unit_id', $branch_id);

            if($search){
                $folder->where('name', 'like', '%' . $search . '%')
                            ->orWhere('number', 'like', '%' . $search . '%')
                            ->orWhere('trace_id', 'like', '%' . $search . '%')
                            ->orWhereHas('fileType', function($query) use($search) {
                                $query->where('name', 'like', '%' . $search . '%');
                            });
            }
          
            $folder = $folder->withCount('archives')->latest()->paginate(20)->withQueryString();
           
            return inertia('dashboard/Branches', compact('branch', 'branch_name','folder', 'unitId', 'divId', 'deptId'));
          }

         // $division = request()->id? request()->id: auth()->user()->division_id;
          if($branch_name == 'Division' && $this->isUserPermittedToViewDivision($branch_id)){
         
            $branch = Division::with(['folder'=>function($query){
                $query->with(['fileType','subfolder']);
            }, 'fileType'])->find($branch_id);
           // $branch_name = 'Division';
            $branch = Division::find($branch_id);
            $folder = $folder->where('division_id', $branch_id);

             if($search){
                $folder->where('name', 'like', '%' . $search . '%')
                            ->orWhere('number', 'like', '%' . $search . '%')
                            ->orWhere('trace_id', 'like', '%' . $search . '%')
                            ->orWhereHas('fileType', function($query) use($search) {
                                $query->where('name', 'like', '%' . $search . '%');
                            });
            }

            $folder = $folder->withCount('archives')->latest()->paginate(20)->withQueryString();

            return inertia('dashboard/Branches', compact('branch', 'branch_name','folder', 'unitId', 'divId', 'deptId'));
          }

          $department = request()->deptId? request()->deptId: auth()->user()->department_id;

           if($department && $this->isUserPermittedToViewDepartment($branch_id)){
             $branch = Department::with(['folder'=>function($query){
                $query->with(['fileType','subfolder']);
            }, 'fileType'])->find($department);
          
            $branch = Department::find($department);
            $folder = $folder->where('department_id', $department);
           }

           if($search){
                $folder->where('name', 'like', '%' . $search . '%')
                            ->orWhere('number', 'like', '%' . $search . '%')
                            ->orWhere('trace_id', 'like', '%' . $search . '%')
                            ->orWhereHas('fileType', function($query) use($search) {
                                $query->where('name', 'like', '%' . $search . '%');
                            });
            }
          
            if(!isset($branch)){
                return redirect()->to('/dashboard');
            }
            $folder = $folder->withCount('archives')->latest()->paginate(20)->withQueryString();
           
            return inertia('dashboard/Branches', compact('branch', 'branch_name','folder' , 'unitId', 'divId', 'deptId'));
    
     }

    public function folderView(Request $request, $branch, $id){

       $archive_id = request()->archive_id;
        $branch = strtolower($branch);
       $folder = Folder::with(['fileType', 'subfolder'=>function($query){
        $query->with('fileType');
       }])->find($id);

       $folder_id = $id;
       $branch_name = $branch;

       $archive = Archive::with(['filePath', 'fileActivity', 'archiveDocs','department','division', 'unit', 'version' => function($query){
                $query->latest();
            }]);

        if($branch == 'unit'){
            $branch_id = request()->branch_id? request()->branch_id: auth()->user()->unit_id;
            $branch = Unit::find($branch_id);
            if(!is_null($archive_id)){
                $archives =        $archive->where('id', 'LIKE', $archive_id."%")->where('folder_id', $id)->where('unit_id',  $branch_id);
                
            }else{
                $archives =        $archive->where('folder_id', $id)->where('unit_id',  $branch_id);
              
            }
            $search = request('search');
            if ($search) {
                $archive->whereHas('archiveDocs', function($q) use ($search) {
                    $q->where('archive_values', 'like', "%{$search}%");
                    // ->orWhere('amount', 'like', "%{$search}%")
                    // ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $archives = $archive->latest()->paginate(20);
            $is_folder_open = $folder->is_open;
            $branch_type = 'unit';
            $folder_name = $folder->name;
           // $document_type = $archives->archiveDocs->unique('archive_keys');
            return inertia('dashboard/FolderView', compact('archives','branch_id', 'is_folder_open', 'branch_type','folder_name', 'folder', 'folder_id', 'branch', 'branch_name'));
        }

        if($branch == 'division'){
            $branch_id = request()->branch_id? request()->branch_id: auth()->user()->division_id;
            $branch = Division::find($branch_id);
            if(request()->archive_id){
                $archives =        $archive->where('id', 'LIKE', $archive_id."%")->where('folder_id', $id)->where('division_id',  $branch_id);
            }else{
                $archives =        $archive->where('folder_id', $id)->where('division_id',  $branch_id);
            }

            $search = request('search');
            if ($search) {
                $archive->whereHas('archiveDocs', function($q) use ($search) {
                    $q->where('archive_values', 'like', "%{$search}%");
                });
            }

            $archives = $archive->latest()->paginate(20);
         
            $is_folder_open = $folder? $folder->is_open : true;
            $branch_type = 'division';
            return inertia('dashboard/FolderView', compact('archives','branch_id', 'is_folder_open', 'branch_type', 'folder', 'folder_id', 'branch', 'branch_name'));
        }

       if($branch == 'department'){
        $branch_id = request()->branch_id? request()->branch_id: auth()->user()->department_id;
        $branch = Department::find($branch_id);
        
        if(request()->archive_id){
              $archive->where('id', 'LIKE', $archive_id."%")->where('folder_id', $id)->where('department_id',  $branch_id);
        }else{
                $archive->where('folder_id', $id)->where('department_id',  $branch_id);
        }

        $search = request('search');
        if ($search) {
            $archive->whereHas('archiveDocs', function($q) use ($search) {
                $q->where('archive_values', 'like', "%{$search}%");
            });
        }
     
        $archives = $archive->latest()->paginate(20);
        $is_folder_open = $folder->is_open;
        $branch_type = 'department';
        return inertia('dashboard/FolderView', compact('archives','branch_id', 'is_folder_open', 'branch_type', 'folder', 'folder_id', 'branch', 'branch_name'));
       }
    }

    public function getDivisionWithUnit(Request $request){
        $division = Division::with('unit')->where('department_id', $request->id)->get();
        return response()->json($division);
    }

    public function closeFolder($id){
        Folder::where('id', $id)->update(['is_open'=>0]);
        return redirect()->back();
    }

    public function openFolder($id){
        Folder::where('id', $id)->update(['is_open'=>1]);
        return redirect()->back();
    }

    public function deleteFolder($id){
        $folder = Folder::find($id);
        $folder->delete();
        if($folder->path && is_dir($folder->path)){
            if(rmdir($folder->path)){
                return redirect()->back();
            }
        }
    }

    public function getBranchFileType(){
       $deptId = request()->deptId? request()->deptId: auth()->user()->department_id;
       $divId = request()->divId? request()->divId: auth()->user()->div_id;
       $unitId = request()->unitId? request()->unitId: auth()->user()->unit_id;
        if($unitId){
            $unit = Unit::with('fileType')->find($unitId);
            if($unit){
                $fileType= $unit->fileType;
                return response()->json($fileType);
            }
        }

        if($divId){
            $division = Division::with('fileType')->find($divId);
            if($division){
                $fileType= $division->fileType;
                return response()->json($fileType);
            }
        }

        if($deptId){
            $department = Department::with('fileType')->find($deptId);
            if($department){
                $fileType= $department->fileType;
                return response()->json($fileType);
            }
        }

        return response()->json([]);
    }

    public function updateBranchMetadatas(Request $request){
        foreach($request->form as $form){
            if (isset($form['id'])) {
                ArchiveDocs::where('id', $form['id'])->update([
                    'archive_values' => $form['archive_values']
                ]);
            } else {
                ArchiveDocs::create([
                    'archive_id' => $form['archive_id'],
                    'archive_keys' => $form['archive_keys'],
                    'archive_values' => $form['archive_values']
                ]);
            }
        }
        return response()->json(['success'=>true]);
    }

    public function uploadNewDocument(Request $request){
        if($request->file('files')){

            $archive = Archive::with(['filePath','archiveDocs'])->find($request->document_id);
        
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $file_path_id = '';

            $file_path = $archive->filePath->first()? $archive->filePath->first()->path: [];
            
            $thumbnail = $archive->filePath->first()? $archive->filePath->first()->thumbnails: [];
            // if($file_path && $thumbnail){
            //      $file_arr = explode("/",$file_path);
            //      array_pop($file_arr);
            //     $path = implode('/', $file_arr);
            //     $extension = strtolower($request->file('files')[0]->getClientOriginalExtension());
            //     if(in_array($extension, $allowedExtensions)){
            //         $thumbnail_path = $path.'/thumbnails/';
            //     }
            // }else{
            //     $folder = Folder::find($request->folder_id);
            //     $path = $folder->path.'/';
            //     $thumbnail_path = $folder->path.'/thumbnails/';
            // }

                $folder = Folder::find($request->folder_id);
               
           
            try {
                $filepath = [];
                $thumbnails = [];

                foreach ($request->file('files') as $index=>$file) {
                    $path = $folder->path.'/';
                    $thumbnail_path = $folder->path.'/thumbnails/';

                    $extension = strtolower($file->getClientOriginalExtension());
                 
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $size = $this->formatSize(filesize($file));
                  
                    //create thumbnails
                    if(!is_dir($thumbnail_path)){
                        mkdir($thumbnail_path,0777,true);
                    }

                    $thumbnail = '';
                    if (in_array($extension, $allowedExtensions)) {
                         $thumbnail = $this->createThumbnail($file, $thumbnail_path, 480, 520, $fileName);
                       
                    }
        
                    $file->move(public_path($path),$fileName);
                   
   
                    $path = str_replace('storage/', '', $path);
                    $thumbnail = str_replace('storage/','', $thumbnail);

                    $paths = [
                        'path'=>'/'.$path.$fileName,
                        'created_at'=>\Carbon\Carbon::now(), 
                        'updated_at'=>\Carbon\Carbon::now(),
                        'updated_by' =>auth()->user()->id,
                        'updated_by_name' => auth()->user()->last_name." ".auth()->user()->first_name,
                        'file_name' => $fileName,
                        'file_size' => $size,
                        'thumbnails' => $thumbnail
                    ];
                   if($paths){
                   
                     array_push($filepath, $paths);
                        //array_push($thumbnails,$thumbnail );
                    }
                    
                }
                if($file_path){
                    foreach($archive->filePath as $path){
                        $path->delete();
                    }
                    //$archive->filePath->delete();
                }
                FilePath::insert($filepath);
                $file_path = FilePath::latest()->get()->take(count($filepath));
                $ids = $file_path->pluck('id');
               
                $archive->filePath()->attach($ids);


                $this->createNewVersions($archive);
                $archive->is_checked_out = false;
                $archive->checked_out_by = null;
                $archive->save();

            } catch (Exception $e) {
                //dd($e);
                return response()->json($e);
            }
           
        }
    }

    public function createNewVersions($archive){
        if($archive->filePath){
            //create versions
           $version = new Version();
           $version->versions = json_encode($archive);
           $version->archive_id = $archive->id;
           $version->save();

           return true;
       }
       return false;
    }

    public function restoreFileVersion(Request $request){

        DB::beginTransaction();
        try {
            $archive = Archive::with(['archiveDocs', 'filePath'])->find($request->id);
            
            //create a new archive
            $this->createNewVersions($archive);

            //delete old entry
            foreach($archive->filePath as $path){
                $path->delete();
            }
            foreach($archive->archiveDocs as $docs){
                $docs->delete();
            }

            //restore file
            foreach($request->input('files') as $file){
                $id = FilePath::create([
                    'path' => $file['path'],
                    'file_name' => $file['file_name'],
                    'file_size' => $file['file_size'],
                    'updated_by' =>$file['updated_by'],
                    'updated_by_name' => $file['updated_by_name'],
                    'restored_by_name' => auth()->user()->first_name." ".auth()->user()->last_name,
                ]);
            
                $archive->filePath()->attach($id);
            }

            foreach($request->archive_docs as $docs){
                $id = ArchiveDocs::create([
                    'archive_id' => $docs['archive_id'],
                    'archive_keys' => $docs['archive_keys'],
                    'archive_values' => $docs['archive_values'],
                ]);

            }

             //delete this version 
             $version = Version::find($request->file_version_id);
             $version->delete();

            DB::commit();

            return redirect()->back()->with(['success'=>true]);
        } catch (\Exception $e) {
           DB::rollback();
           return redirect()->back()->with(['error'=>true]);
        }
      

    }

    public function lockDocument(Request $request){
        $archive = Archive::find($request->id);
        $archive->is_locked = 1;
        $archive->save();
        return redirect()->back()->with(['success'=>true]);
    }

    public function checkOutDocument(Request $request){
        $archive = Archive::find($request->id);
        if ($archive->is_checked_out && $archive->checked_out_by !== auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'File is already checked out by someone else.']);
        }
        $archive->is_checked_out = true;
        $archive->checked_out_by = auth()->id();
        $archive->save();
        $this->logFileActivity($archive->id, 'Checked out file');
        return redirect()->back()->with(['success'=>true]);
    }

    public function checkInDocument(Request $request){
        $archive = Archive::find($request->id);
        if ($archive->is_checked_out && $archive->checked_out_by !== auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'You cannot check in a file checked out by someone else.']);
        }
        $archive->is_checked_out = false;
        $archive->checked_out_by = null;
        $archive->save();
        $this->logFileActivity($archive->id, 'Checked in file');
        return redirect()->back()->with(['success'=>true]);
    }

    public function deleteFileVersion(Request $request){
        $version = Version::find($request->id);
        $version->delete();
        return redirect()->back();
        //return response()->json(['success'=>true], 200);
    }

    public function shareFileSearch(Request $request){
        $unit = auth()->user()->unit_id;
        $division = auth()->user()->division_id;
        $department = auth()->user()->department_id;

        $search = $request->search;

        if($search){
            //  $users = User::where(function($query) use ($search) {
            //     $query->where('first_name', 'LIKE', '%' . $search . '%');
            // })->orWhere(function($query) use ($unit, $division, $department) {
            //     if ($unit !== null) {
            //         $query->orWhere('unit_id', $unit);
            //     }
            //     if ($division !== null) {
            //         $query->orWhere('division_id', $division);
            //     }
            //     if ($department !== null) {
            //         $query->orWhere('department_id', $department);
            //     }
            // })->get();

            if($unit){
                $query = User::where('unit_id', $unit);
            }

            if($division){
                $query = User::where('division_id', $division);
            }

            if($department){
                $query = User::where('department_id', $department);
            }

            $query->where('first_name','LIKE','%'.$search.'%')
                ->orWhere('last_name', 'LIKE', '%'.$search.'%');
            
            $users = $query->get();
        }else{
            $users = [];
        }
       
        
        return response()->json($users);
    }

    public function shareFile(Request $request){

        if($request->ids){
            $data = [];

            foreach($request->ids as $id){
                $arr = [
                    'sharing_id' => auth()->user()->id,
                    'shared_id' => $id,
                    'archive_id' => $request->archive_id,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ];
                array_push($data, $arr);
            }
            Share::insert($data);
        }

        // $user_mails = User::whereIn('id', $request->ids)->pluck('email');
        // $archive = Archive::with('filePath')->find($request->archive_id);

        // foreach ($user_mails as $recipient) {
        //     Mail::to($recipient)->send(new ShareFile($archive->filePath->pluck('path')));
        // }

        return redirect()->back()->with(['success'=>true]);
    }

    public function shareFolder(Request $request){
        if($request->ids){
            $data = [];

            foreach($request->ids as $id){
                $arr = [
                    'sharing_id' => auth()->user()->id,
                    'shared_id' => $id,
                    'folder_id' => $request->folder_id,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ];
                array_push($data, $arr);
            }
            \App\Models\ShareFolder::insert($data);
        }

        return redirect()->back()->with(['success'=>true]);
    }

    public function folderSearch(Request $request) {
        $search = $request->search;
    
        // Build the query
        $folder = Folder::with(['fileType', 'subfolder'])
            ->where(function($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('number', 'like', '%' . $search . '%')
                    ->orWhereHas('fileType', function($query) use($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
            })
            ->latest()
            ->paginate(20);
    
            if ($request->header('X-Inertia')) {
                return redirect()->back()->with('folder',$folder);
            }else{
                return response()->json($folder);
            }
        
    }

   public function sharedFiles(Request $request){
        $user = auth()->user();
        $tab = $request->get('tab', 'shared_with_me');
        if (!in_array($tab, ['shared_with_me', 'shared_by_me'])) {
            $tab = 'shared_with_me';
        }
        $search = $request->search;

        $sharedWithMeCount = Share::where('shared_id', $user->id)->count();
        $sharedByMeCount = Share::where('sharing_id', $user->id)->count();

        $query = Share::with([
            'archive' => function($query) use ($search) {
                $query->with(['filePath', 'fileActivity', 'archiveDocs', 'department', 'division', 'unit']);
                if (!empty($search)) {
                    $query->where(function($q) use ($search) {
                        $q->whereHas('archiveDocs', function($sub) use ($search) {
                            $sub->where('archive_values', 'like', "%{$search}%");
                        })
                        ->orWhereHas('filePath', function($sub) use ($search) {
                            $sub->where('file_name', 'like', "%{$search}%");
                        });
                    });
                }
            },
            'sharingUser:id,first_name,last_name,email',
            'sharedUser:id,first_name,last_name,email'
        ]);

        if ($tab === 'shared_by_me') {
            $query->where('sharing_id', $user->id);
        } else {
            $query->where('shared_id', $user->id);
        }

        $shared = $query->latest()->paginate(30)->withQueryString();
        
        $archives = $shared->getCollection()->flatMap(function($share) use ($tab) {
            return $share->archive->map(function($arch) use ($share, $tab) {
                $arch->share_info = [
                    'id' => $share->id,
                    'tab' => $tab,
                    'sharing_user' => $share->sharingUser,
                    'shared_user' => $share->sharedUser,
                    'shared_at' => $share->created_at ? $share->created_at->format('M d, Y') : null,
                ];
                return $arch;
            });
        });

        return inertia('dashboard/sharedFiles', [
            'shared' => $shared,
            'archives' => $archives,
            'activeTab' => $tab,
            'sharedWithMeCount' => $sharedWithMeCount,
            'sharedByMeCount' => $sharedByMeCount,
            'filters' => [
                'search' => $search,
                'tab' => $tab,
            ]
        ]);
   }

    public function documentManagementIndex(){
        $user = auth()->user();
        $branches = [];
        if($user->hasRole('super admin') || $user->hasRole('admin') || $user->hasPermissionTo('department view') || $user->hasPermissionTo('division view') || $user->hasPermissionTo('unit view')){
            $branches = Department::with(['division'=> function($query){
                $query->with('unit');
            }])->get();
        }

        if($user->hasRole('director')){
            $department_id = $user->department_id;
            $branches = Department::with(['division'=>function($query){
                $query->with('unit');
            }])->where('id', $department_id)->get();
        }

        if($user->hasRole('deputy director')){
            $division_id = $user->division_id;
            $department_id = $user->department_id;
            
            $branches = Department::with(['division' =>function($query) use ($division_id){
                $query->with('unit')->where('id', $division_id);
            }])->where('id', $department_id)->get();
        }

        if($user->hasRole('assistant director')){
            $division_id = $user->division_id;
            $department_id = $user->department_id;
            $unit_id = $user->unit_id;

            $branches = Department::with(['division' =>function($query) use ($division_id, $unit_id){
                $query->with(['unit'=>function($q) use($unit_id){
                    $q->where('id', $unit_id);
                }])->where('id', $division_id);
            }])->where('id', $department_id)->get();
        }
        
        $role = $user->getRoleNames();
        return inertia('DocumentManagement/Index', compact('branches', 'role'));
    }

}
