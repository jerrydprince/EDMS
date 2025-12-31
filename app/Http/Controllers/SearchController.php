<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Archive;
use App\Models\RecentSearch;
use App\Models\Folder;
use App\Models\Department;
use App\Models\Division;
use App\Models\Unit;

class SearchController extends Controller
{
    // public function search(Request $request){

    //     if($request->doctype == 'Document Types'){
    //        $request_arr = $request->except('doctype');
    //        $request = new Request;
    //        $request->merge($request_arr);
    //     }

    //     if($request->modified == 'Date Modified'){
    //         $request_arr = $request->except('modified');
    //         $request = new Request;
    //         $request->merge($request_arr);
    //     }

    //     $date = '';
    //     if($request->modified){
    //         if($request->modified == 'Today'){
    //             $date = now()->format('Y-m-d');
    //         }else if($request->modified == 'Yesterday'){
    //             $date = now()->subDay()->format('Y-m-d');
    //         }else if($request->modified == 'Last 7 days'){
    //             $date = now()->subDays(6)->format('Y-m-d');
    //         }else if($request->modified == 'Last 30 days'){
    //             $date =  now()->subDays(29)->format('Y-m-d');
    //         }else if($request->modified == 'Last 90 days'){
    //             $date = now()->subDays(89)->format('Y-m-d');
    //         }
    //     }

    //   if($request->file_name){
    //     $department = [];
    //     $division = [];
    //     $unit = [];
    //     $branch = '';

    //     if(auth()->user()->hasRole('data entry')){
           
    //         $branch_id = '';
    //         if(auth()->user()->unit_id){
    //             $branch = 'unit';
    //             $branch_id = auth()->user()->unit_id;
    //         }else if(auth()->user()->division_id){
    //             $branch = 'division';
    //             $branch_id = auth()->user()->division_id;
    //         }else{
    //             $branch = 'department';
    //             $branch_id = auth()->user()->department_id;
    //         }

    //         if($request->doctype != 'Document Types'){
    //             $userBranch = $branch=='unit'? Unit::find($branch_id): 
    //             (($branch=='division')? Division::find($branch_id): Department::find($branch_id));
    //         }else{
    //             $userBranch = $branch=='unit'? Unit::find($branch_id): (($branch=='division')? Division::find($branch_id): Department::find($branch_id));
    //         }
    
    //         $archives = $userBranch->archive()
    //         ->with(['filePath', 'archiveDocs', 'department', 'division', 'unit'])
    //         ->where(function ($query) use ($request) {
    //             $query->whereHas('filePath', function ($query) use ($request) {
    //                 $query->where('file_name', 'LIKE', '%' . $request->file_name . '%')
    //                     ->orWhere('updated_by_name', 'LIKE', '%' . $request->file_name . '%');
    //             })
    //             ->orWhereHas('archiveDocs', function ($query) use ($request) {
    //                 $query->where('archive_values', 'LIKE', '%' . $request->file_name . '%');
    //             });
    //         })
           
    //         ->when($request->modified, function ($query) use ($date) {
    //             return $query->whereHas('archiveDocs', function($q) use($date){
    //                 $q->whereDate('archive_values', '>=', $date);
    //             });
    //         })->get();

    //         $archives = $archives->map(function ($item) {
    //            // $item->archiveDocs->each(function ($doc) use ($item) {
    //                 // if ($doc->archive_keys == 'title') {
    //                 //     $item->title = $doc->archive_values;
    //                 // }else{
    //                 //     $item->title = '';
    //                 // }
                   
    //                 //$item->title = $title->archive_values;
    //             //});
    //             $title = $item->archiveDocs->filter(function($item){
    //                 return $item->archive_keys ==='title';
    //             });
    //             //$item->title = isset($title->archive_values)? $title->archive_values: '';
    //             $archive = $title->pop();
    //             $item->title = $archive? $archive->archive_values: '';
    //             return $item;
    //         });

               

    //          $folders = $userBranch->folder()->with(['division','unit','department'])
    //                 ->where(function($query) use ($request) {
    //                     $query->orWhere('name', 'LIKE' ,'%'.$request->file_name.'%')
    //                         ->orWhere('number', 'LIKE', '%'.$request->file_name.'%');
    //                        // ->orWhere('type', $request->file_name);
    //                 })
    //                 ->get();

           
            
    //     }else{
    //         $archives = Archive::with(['filePath', 'archiveDocs', 'department', 'division', 'unit'])
    //         ->whereHas('filePath', function($query) use($request) {
    //             $query->where('file_name', 'LIKE', '%' . $request->file_name . '%')
    //                 ->orWhere('updated_by_name', 'LIKE', '%' . $request->file_name . '%');
    //         })->orWhereHas('archiveDocs', function($query) use ($request){
    //             $query->where('archive_values', 'LIKE', "%".$request->file_name."%");
    //         })
    //         // ->when($request->modified, function ($query) use ($date) {
    //         //     return $query->whereDate('updated_at','>=', $date);
    //         // });
    //         ->when($request->modified, function ($query) use ($date) {
    //             return $query->whereHas('archiveDocs', function($q) use($date){
    //                 $q->whereDate('archive_values', '>=', $date);
    //             });
    //         });

    //         if ($request->doctype) {
    //             $archives->whereHas('unit.documentType', function($q) use ($request) {
    //                 $q->where('name', $request->doctype);
    //             })
    //             ->orWhereHas('division.documentType', function($q) use ($request) {
    //                 $q->where('name', $request->doctype);
    //             })->orWhereHas('department.documentType', function($q) use ($request) {
    //                 $q->where('name', $request->doctype);
    //             });
    //         }
            
    //         $archives = $archives->get();


    //         $folders =Folder::with(['division','unit','department'])->where(function($query) use ($request) {
    //             $query->orWhere('name', 'LIKE' ,'%'.$request->file_name.'%')
    //                 ->orWhere('number', 'LIKE', '%'.$request->file_name.'%');
    //                // ->orWhere('type', $request->file_name);
    //         })->get();
            


    //          $department = Department::where('id', auth()->user()->department_id)
    //                     ->where('title',$request->file_name)->get();
    //         $division = Division::where('id', auth()->user()->division_id)->where('title', $request->file_name);
    //         $unit = Unit::where('id', auth()->user()->unit_id)->where('title', $request->file_name);;
    //      }
         
    //      RecentSearch::updateOrCreate(['query_string'=>$request->query_string], 
    //                 ['query'=>$request->file_name,'user_id'=>auth()->user()->id]);
    //      $recent_searches = RecentSearch::where('user_id',auth()->user()->id)->latest()->get()->take(4);
 
    //      return response()->json(['archive'=>$archives, 'recent_searches'=>$recent_searches->unique('query'), 
    //                     'folder'=>$folders, 'department'=>$department, 'division'=>$division, 'unit'=>$unit, 'user_branch'=>$branch]);
    //   }
    // }

    //  public function search(Request $request){

    //     if($request->doctype == 'Document Types'){
    //        $request_arr = $request->except('doctype');
    //        $request = new Request;
    //        $request->merge($request_arr);
    //     }

    //     if($request->modified == 'Date Modified'){
    //         $request_arr = $request->except('modified');
    //         $request = new Request;
    //         $request->merge($request_arr);
    //     }

    //     $date = '';
    //     if($request->modified){
    //         if($request->modified == 'Today'){
    //             $date = now()->format('Y-m-d');
    //         }else if($request->modified == 'Yesterday'){
    //             $date = now()->subDay()->format('Y-m-d');
    //         }else if($request->modified == 'Last 7 days'){
    //             $date = now()->subDays(6)->format('Y-m-d');
    //         }else if($request->modified == 'Last 30 days'){
    //             $date =  now()->subDays(29)->format('Y-m-d');
    //         }else if($request->modified == 'Last 90 days'){
    //             $date = now()->subDays(89)->format('Y-m-d');
    //         }
    //     }

    //   if($request->file_name){
    //     $department = [];
    //     $division = [];
    //     $unit = [];
    //     $branch = '';

    //     if(auth()->user()->hasRole('data entry')){
           
    //         $branch_id = '';
    //         if(auth()->user()->unit_id){
    //             $branch = 'unit';
    //             $branch_id = auth()->user()->unit_id;
    //         }else if(auth()->user()->division_id){
    //             $branch = 'division';
    //             $branch_id = auth()->user()->division_id;
    //         }else{
    //             $branch = 'department';
    //             $branch_id = auth()->user()->department_id;
    //         }

    //         if($request->doctype != 'Document Types'){
    //             $userBranch = $branch=='unit'? Unit::find($branch_id): 
    //             (($branch=='division')? Division::find($branch_id): Department::find($branch_id));
    //         }else{
    //             $userBranch = $branch=='unit'? Unit::find($branch_id): (($branch=='division')? Division::find($branch_id): Department::find($branch_id));
    //         }
    
    //         $archives = $userBranch->archive()
    //         ->with(['filePath', 'archiveDocs', 'department', 'division', 'unit'])
    //         ->where(function ($query) use ($request) {
    //             $query->whereHas('filePath', function ($query) use ($request) {
    //                 $query->where('file_name', 'LIKE', '%' . $request->file_name . '%')
    //                     ->orWhere('updated_by_name', 'LIKE', '%' . $request->file_name . '%');
    //             })
    //             ->orWhereHas('archiveDocs', function ($query) use ($request) {
    //                 $query->where('archive_values', 'LIKE', '%' . $request->file_name . '%');
    //             });
    //         })
           
    //         ->when($request->modified, function ($query) use ($date) {
    //             return $query->whereHas('archiveDocs', function($q) use($date){
    //                 $q->whereDate('archive_values', '>=', $date);
    //             });
    //         })->get();

    //         $archives = $archives->map(function ($item) {
              
    //             $title = $item->archiveDocs->filter(function($item){
    //                 return $item->archive_keys ==='title';
    //             });
    //             //$item->title = isset($title->archive_values)? $title->archive_values: '';
    //             $archive = $title->pop();
    //             $item->title = $archive? $archive->archive_values: '';
    //             return $item;
    //         });

               

    //          $folders = $userBranch->folder()->with(['division','unit','department'])
    //                 ->where(function($query) use ($request) {
    //                     $query->orWhere('name', 'LIKE' ,'%'.$request->file_name.'%')
    //                         ->orWhere('number', 'LIKE', '%'.$request->file_name.'%');
    //                        // ->orWhere('type', $request->file_name);
    //                 })
    //                 ->get();

           
            
    //     }else{
    //         $archives = Archive::with(['filePath', 'archiveDocs', 'department', 'division', 'unit'])
    //         ->whereHas('filePath', function($query) use($request) {
    //             $query->where('file_name', 'LIKE', '%' . $request->file_name . '%')
    //                 ->orWhere('updated_by_name', 'LIKE', '%' . $request->file_name . '%');
    //         })->orWhereHas('archiveDocs', function($query) use ($request){
    //             $query->where('archive_values', 'LIKE', "%".$request->file_name."%");
    //         })
          
    //         ->when($request->modified, function ($query) use ($date) {
    //             return $query->whereHas('archiveDocs', function($q) use($date){
    //                 $q->whereDate('archive_values', '>=', $date);
    //             });
    //         });

    //         if ($request->doctype) {
    //             $archives->whereHas('unit.documentType', function($q) use ($request) {
    //                 $q->where('name', $request->doctype);
    //             })
    //             ->orWhereHas('division.documentType', function($q) use ($request) {
    //                 $q->where('name', $request->doctype);
    //             })->orWhereHas('department.documentType', function($q) use ($request) {
    //                 $q->where('name', $request->doctype);
    //             });
    //         }
            
    //         $archives = $archives->get();


    //         $folders =Folder::with(['division','unit','department'])->where(function($query) use ($request) {
    //             $query->orWhere('name', 'LIKE' ,'%'.$request->file_name.'%')
    //                 ->orWhere('number', 'LIKE', '%'.$request->file_name.'%');
    //                // ->orWhere('type', $request->file_name);
    //         })->get();
            


    //          $department = Department::where('id', auth()->user()->department_id)
    //                     ->where('title',$request->file_name)->get();
    //         $division = Division::where('id', auth()->user()->division_id)->where('title', $request->file_name);
    //         $unit = Unit::where('id', auth()->user()->unit_id)->where('title', $request->file_name);;
    //      }
         
    //      RecentSearch::updateOrCreate(['query_string'=>$request->query_string], 
    //                 ['query'=>$request->file_name,'user_id'=>auth()->user()->id]);
    //      $recent_searches = RecentSearch::where('user_id',auth()->user()->id)->latest()->get()->take(4);
 
    //      return response()->json(['archive'=>$archives, 'recent_searches'=>$recent_searches->unique('query'), 
    //                     'folder'=>$folders, 'department'=>$department, 'division'=>$division, 'unit'=>$unit, 'user_branch'=>$branch]);
    //   }
    // }


//private function getUserBranch()
// {
//     $user = auth()->user();

//     if ($user->unit_id) {
//         return ['name' => 'unit', 'model' => Unit::find($user->unit_id)];
//     }

//     if ($user->division_id) {
//         return ['name' => 'division', 'model' => Division::find($user->division_id)];
//     }

//     return ['name' => 'department', 'model' => Department::find($user->department_id)];
// }

// private function resolveModifiedDate($modified)
// {
//     return match ($modified) {
//         'Today' => now()->format('Y-m-d'),
//         'Yesterday' => now()->subDay()->format('Y-m-d'),
//         'Last 7 days' => now()->subDays(6)->format('Y-m-d'),
//         'Last 30 days' => now()->subDays(29)->format('Y-m-d'),
//         'Last 90 days' => now()->subDays(89)->format('Y-m-d'),
//         default => null,
//     };
// }


public function search(Request $request)
{
    // Normalize request inputs
    if ($request->doctype === 'Document Types') {
        $request->merge(['doctype' => null]);
    }
    
    if ($request->modified === 'Date Modified') {
        $request->merge(['modified' => null]);
    }

    // Calculate date range
    $dateFrom = $this->getDateFromModifiedFilter($request->modified);

    if (!$request->file_name) {
        return response()->json([]);
    }

    $user = auth()->user();
    $isDataEntry = $user->hasRole('data entry');

    // Build queries
    $archives = $this->searchArchives($request, $dateFrom, $isDataEntry);
    $folders = $this->searchFolders($request, $isDataEntry);
    
    // Search organizational units (only for non-data-entry users)
    [$department, $division, $unit] = $isDataEntry 
        ? [collect(), collect(), collect()]
        : $this->searchOrganizationalUnits($request);

    // Save recent search
    RecentSearch::updateOrCreate(
        ['query_string' => $request->query_string],
        ['query' => $request->file_name, 'user_id' => $user->id]
    );

    $recent_searches = RecentSearch::where('user_id', $user->id)
        ->latest()
        ->limit(4)
        ->get()
        ->unique('query');

    return response()->json([
        'archive' => $archives,
        'recent_searches' => $recent_searches,
        'folder' => $folders,
        'department' => $department,
        'division' => $division,
        'unit' => $unit,
        'user_branch' => $isDataEntry ? $this->getUserBranchType() : null
    ]);
}

private function getDateFromModifiedFilter($modified)
{
    $dateMap = [
        'Today' => 0,
        'Yesterday' => 1,
        'Last 7 days' => 6,
        'Last 30 days' => 29,
        'Last 90 days' => 89,
    ];

    if (isset($dateMap[$modified])) {
        return now()->subDays($dateMap[$modified])->format('Y-m-d');
    }

    return null;
}

private function getUserBranchType()
{
    $user = auth()->user();
    
    if ($user->unit_id) return 'unit';
    if ($user->division_id) return 'division';
    return 'department';
}

private function getUserBranchModel()
{
    $user = auth()->user();
    $branchType = $this->getUserBranchType();
    
    $models = [
        'unit' => Unit::class,
        'division' => Division::class,
        'department' => Department::class,
    ];
    
    $idField = $branchType . '_id';
    return $models[$branchType]::find($user->$idField);
}

private function searchArchives(Request $request, $dateFrom, $isDataEntry)
{
    $query = $isDataEntry
        ? $this->getUserBranchModel()->archive()
        : Archive::query();

    $archives = $query->with(['filePath', 'archiveDocs', 'department', 'division', 'unit'])
        ->where(function ($q) use ($request) {
            $q->whereHas('filePath', function ($query) use ($request) {
                $query->where('file_name', 'LIKE', '%' . $request->file_name . '%')
                    ->orWhere('updated_by_name', 'LIKE', '%' . $request->file_name . '%');
            })->orWhereHas('archiveDocs', function ($query) use ($request) {
                $query->where('archive_values', 'LIKE', '%' . $request->file_name . '%');
            });
        })
        ->when($dateFrom, function ($query) use ($dateFrom) {
            return $query->whereHas('archiveDocs', function ($q) use ($dateFrom) {
                $q->whereDate('archive_values', '>=', $dateFrom);
            });
        })
        ->when($request->doctype && !$isDataEntry, function ($query) use ($request) {
            return $query->where(function ($q) use ($request) {
                $q->whereHas('unit.documentType', fn($sq) => $sq->where('name', $request->doctype))
                  ->orWhereHas('division.documentType', fn($sq) => $sq->where('name', $request->doctype))
                  ->orWhereHas('department.documentType', fn($sq) => $sq->where('name', $request->doctype));
            });
        })
        ->limit(20)
        ->get();

    // Extract titles efficiently
    return $archives->map(function ($item) {
        $title = $item->archiveDocs->firstWhere('archive_keys', 'title');
        $item->title = $title->archive_values ?? '';
        return $item;
    });
}

private function searchFolders(Request $request, $isDataEntry)
{
    $query = $isDataEntry
        ? $this->getUserBranchModel()->folder()
        : Folder::query();

    return $query->with(['division', 'unit', 'department'])
        ->where(function ($q) use ($request) {
            $q->where('name', 'LIKE', '%' . $request->file_name . '%')
              ->orWhere('number', 'LIKE', '%' . $request->file_name . '%');
        })
        ->get();
}

private function searchOrganizationalUnits(Request $request)
{
    $user = auth()->user();
    
    $department = Department::where('id', $user->department_id)
        ->where('title', $request->file_name)
        ->get();
        
    $division = Division::where('id', $user->division_id)
        ->where('title', $request->file_name)
        ->get();
        
    $unit = Unit::where('id', $user->unit_id)
        ->where('title', $request->file_name)
        ->get();

    return [$department, $division, $unit];
}

    public function deleteFromRecentSearch(Request $request){
        RecentSearch::find($request->id)->delete();
        return response()->json(['success'=>true]);
    }
}
