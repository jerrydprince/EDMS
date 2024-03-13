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

class ArchiveController extends Controller
{
    public function Archiving($id){
        //$doctypes = DocumentType::with('metadata')->get();
        $department = Department::with(['folder', 'documentType'=>function($query){
            $query->with('metadata');
        }])->where('id', $id)->first();
       
        $doctypes = $department->documentType;
        if(count($doctypes)==0){
           if(auth()->user()->division_id){
            $division = Division::with(['documentType'=> function($query){
                $query->with('metadata');
            }])->find(auth()->user()->division_id);
            $doctypes = $division->documentType;
           }
        }
        if(count($doctypes)==0){
            if(auth()->user()->unit_id){
             $unit = Unit::with(['documentType'=> function($query){
                $query->with('metadata');
             }])->find(auth()->user()->division_id);
             $doctypes = $unit->documentType;
            }
         }
        //$folders = $this->getAllFolders(public_path('/Archives/Department/Division/Unit/'));
        $archives = session()->get('archives');
        $folders =$department->folder;
        $thumbnails = request()->session()->get('thumbnails');
        return inertia('archives/archiving', compact('doctypes', 'folders','archives','thumbnails', 'department'));
    }

    public function createFolder(Request $request){
        $request->validate([
            'name' => 'required|unique:folder',
            'number' =>'required',
            'year' => 'required',
        ]);

        DB::transaction(function () use($request) {
            if(auth()->user()->hasRole('data entry')){
                $department = auth()->user()->department_id;
                $division = auth()->user()->division_id;
                $unit = auth()->user()->unit_id;
            }else{
                $department = $request->department;
                $division = $request->division;
                $unit = $request->unit;
            }
          

            $folder = new Folder();
            $folder->name = $request->name;
            $folder->number = $request->number;
            $folder->year = $request->year;
            $folder->type = $request->type;
            $folder->department_id=$department;
            $folder->division_id = $division;
            $folder->unit_id = $unit;
            $folder->save();

            //$path = public_path('/Archives/'.$department.'/'.$division.'/'.$unit.'/'.$request->name, 0777);
           
                    $baseDirectory = "Archives";
                    $subfolders = [$department, $division, $unit, $request->name];
                    if (!is_dir($baseDirectory)) {
                        mkdir($baseDirectory, 0777);
                    }

                    foreach ($subfolders as $folderName) {
                        $fullPath = $baseDirectory . "/" . $folderName;
                        if (!is_dir($fullPath)) {
                            mkdir($fullPath, 0777);
                        }
                        $baseDirectory = $fullPath;
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
        $image->save("storage".$destinationPath.$fileName);

        return $destinationPath.$fileName;
    }

    public function numberFilter($string){
        return preg_replace("/[a-zA-Z]/", "", $string);
    }

    public function uploadFiles(Request $request){
        // $request->validate([
        //     'files.*' => 'required|mimes:jpeg,png,jpg,gif,svg,pdf,doc,docx,xls,xlsx|max:4096',
        // ]);

        $uploadedFiles = [];

        if($request->file('files')){
            $department = auth()->user()->department_id;
            $division = auth()->user()->division_id;
            $unit = auth()->user()->unit_id;
            $folder = $request->folder[0];
            $request->session()->forget('thumbnails');
           
            $filepath = [];
            $thumbnails = [];

            $path = "";
            $thumbnail_path ="";

            if((!is_null($department) && $department != 0) && (!is_null($division) && $division != 0) && (!is_null($unit) && $unit !=0)){
                $path = "/Archives/".$department."/".$division."/".$unit."/".$folder."/";
                $thumbnail_path = "/Archives/".$department."/".$division."/".$unit."/".$folder."/thumbnails/";
            }
            if((!is_null($department) && $department != 0) && (!is_null($division) && $division != 0) && !(!is_null($unit) && $unit !=0)){
                $path = "/Archives/".$department."/".$division."/".$folder."/";
                $thumbnail_path = "/Archives/".$department."/".$division."/".$folder."/thumbnails/";
            }
            if((!is_null($department) && $department != 0) && !(!is_null($division) && $division != 0)){
                $path = "/Archives/".$department."/".$folder."/";
                $thumbnail_path = "/Archives/".$department."/".$folder."/thumbnails/";
            }

            $total_size = 0;

            try {
                foreach ($request->file('files') as $index=>$file) {
                  
                    //$path = "/Archives/".$department."/".$division."/".$unit."/".$folder."/";
                    //$thumbnail_path = "/Archives/".$department."/".$division."/".$unit."/".$folder."/thumbnails/";
                    $fileName = time() . '_' . $file->getClientOriginalName();
                   /* $fileName = time() . '_.dat'; */
                    $size = $this->formatSize(filesize($file));
                  
                    $total_size += $this->numberFilter($size);
                    //create thumbnails
                    if(!is_dir(public_path('storage'.$thumbnail_path))){
                       /* mkdir(public_path("/Archives/".$department."/".$division."/".$unit."/".$folder.'/thumbnails'),0777,true); */
                       mkdir("storage/Archives/".$department."/".$division."/".$unit."/".$folder.'/thumbnails',0777,true);
                    }
                    $thumbnail = $this->createThumbnail($file, $thumbnail_path, 480, 520, $fileName);
                    //$file->move(public_path($path),$fileName);
                    
                   // $storage_path = Storage::disk('local')->put('public/'.$path.$fileName, encrypt($fileName));
                  // $storage_path = Storage::put('public/'.$path, $file);
                  
                  $storage_path = Storage::putFileAs(
                    'public/'.$path, $file, $fileName
                    );
                  
                   /*
                   $fileContent = $file->get();

                   // Encrypt the Content
                   $encryptedContent = encrypt($fileContent);
                   
                   // Store the encrypted Content
                   Storage::disk('local')->put('public/'.$path.$fileName, $encryptedContent);
                    */
                  // Storage::disk('local')->put('public/'.$path, encrypt($file));
                    
                    $paths = [
                        'path'=>$path.$fileName,
                        'created_at'=>\Carbon\Carbon::now(), 
                        'updated_at'=>\Carbon\Carbon::now(),
                        'updated_by' =>auth()->user()->id,
                        'updated_by_name' => auth()->user()->last_name." ".auth()->user()->first_name,
                        'file_name' => $fileName,
                        'file_size' => $size,
                        'thumbnails' => $thumbnail
                    ];
                    array_push($filepath, $paths);
                    array_push($thumbnails,$thumbnail );
                }
                FilePath::insert($filepath);

                //add folder size
                $folder = Folder::find($request->folder_id);
                $folder->folder_size = $total_size? $total_size: 0;
                $folder->save();

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
        // $request->validate([
        //     'description' => 'sometimes|required',
        //     'text' => 'sometimes|required',
        //     'date' => 'sometimes|required',
        //     'number' => 'sometimes|required'
        // ]);

        $department = auth()->user()->department_id;
        $division = auth()->user()->division_id;
        $unit = auth()->user()->unit_id;

        $archive = new Archive();
        $archive->department_id = $department;
        $archive->division_id = $division;
        $archive->unit_id = $unit;
        //$archive->path =$request->session()->get('archives');
        $archive->folder_id = $request->folder_id;
        $archive->updated_by = auth()->user()->id;
        $archive->save();

        $doc_types = [
            'description'=>$request->description,
            'text' => $request->text,
            'date' => $request->date,
            'number' => $request->number,
            'list'=> $request->list,
            'archive_id' => $archive->id
        ];

        ArchiveDocs::insert($doc_types);

        $file_path_ids = $request->session()->get('ids');

        $archive->filePath()->attach($file_path_ids);

        $folder = Folder::find($request->folder_id);
        $folder->file_count = $folder->file_count + 1;
        $folder->save();

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

        return redirect()->back();
    }

    public function deleteArchives(Request $request){
        $archive = Archive::find($request->id);
        ArchiveDocs::where('archive_id', $archive->id)->delete();
        $file_path = $archive->filePath()->get();
        $total_size = 0;
        foreach ($file_path as $file) {
            $total_size  += $this->numberFilter($file->file_size);
           Storage::delete('public'.$file->path);
           Storage::delete('public'.$file->thumbnails);
           //unlink(public_path($file->thumbnails));
        }
        //update folder count
        $folder = Folder::find($archive->folder_id);
        $folder->file_count = $folder->file_count-1;
        $folder->folder_size  -= $total_size;
        $folder->save();

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
         $archive = Archive::with('filePath')->find($request->id);
        $path = Storage::path('public/'.$archive->filePath[0]->path);
        return response()->download($path, $archive->filePath[0]->file_name);
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
                $arr = [
                    'description'=> $docs->description,
                    'text' => $docs->text,
                    'date' => $docs->date,
                    'number' => $docs->number,
                    'list' => $docs->list,
                    'archive_id' =>$new_archive->id,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ];
                array_push($tempDocs, $arr);
            }
            ArchiveDocs::insert($tempDocs);
    
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
                $newpath->save();
                $new_archive->filePath()->attach($newpath);
            }
    
            $folder = Folder::find($archive->folder_id);
            $folder->file_count = $folder->file_count + 1;
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
        $department = $archive->department->first()->id;
        $unit = $archive->unit->first()->id;
        $division = $archive->division->first()->id;
        $folder = Folder::find($archive->folder_id);

        foreach($archive->filePath as $path){
            $thumbnail = $path->thumbnails;
            $name_array = explode('/',$thumbnail);
            $new_fileName = $name_array[count($name_array) - 1];
            $current_path = "/Archives/".$department."/".$division."/".$unit."/".$folder->name."/convertedFiles/".$new_fileName;
            $old_path = "/Archives/".$department."/".$division."/".$unit."/".$folder->name."/".$new_fileName;
            Storage::copy('public'.$old_path, 'public'.$current_path);
        }

            $path->path = $current_path;
            $path->file_name = $new_fileName;
            $path->save();

        return true;
    }

    public function changeFileFormat(Request $request){
        $current = $request->current;
        $to = $request->to;
        if($current == 'Image Format' && $to == 'PDF Format'){
            $this->imageToPDF($request);
            return redirect()->back();
        }
        if($current == 'PDF Format' && $to == 'Image Format'){
            $this->PDFToImage($request);
            return redirect()->back();
        }
    }

    // public function userArchiving(){
    //     $doctypes = DocumentType::with('metadata')->get();
    //     //$folders = $this->getAllFolders(public_path('/Archives/Department/Division/Unit/'));
    //     $archives = session()->get('archives');
    //     $folders =auth()->user()->department->folder;
    //     $thumbnails = request()->session()->get('thumbnails');
    //    // return inertia('dashboard/Branches', compact('doctypes', 'folders','archives','thumbnails'));
    // }

    public function userBranches($id){
        $department = Department::withCount(['user','archive'])->with(['folder', 'archive'=>function($query){
             $query->with(['archiveDocs', 'filePath']);
        }])->find($id);
         return inertia('dashboard/Branches', compact('department'));
     }

    public function folderView($id){
        $archives = Archive::with('filePath')->where('folder_id', $id)->where('department_id', auth()->user()->department_id)->get();
        return inertia('dashboard/FolderView', compact('archives'));
    }

    public function getDivisionWithUnit(Request $request){
        $division = Division::with('unit')->where('department_id', $request->id)->get();
        return response()->json($division);
    }
}
