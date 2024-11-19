<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    public function index(Request $request){
        if($request->search){
            $search = $request->search;
        //     $activities = Activity::whereRaw("JSON_UNQUOTE(properties->'$.event') LIKE ?", ['%' . $search['event'] . '%'])
        //     //->whereRaw("JSON_UNQUOTE(properties->'$.user') LIKE ?", ['%' . $search['user'] . '%'])
        //    // ->whereRaw("JSON_UNQUOTE(properties->'$.source') LIKE ?", ['%' . $search['source'] . '%'])
        //     ->whereDate('created_at', $search['date'])
        //     //->latest()
        //     ->paginate(20);

        $activities = Activity::query();

            // Check and apply each filter conditionally
            if (!empty($search['event'])) {
                $activities->whereRaw("JSON_UNQUOTE(properties->'$.event') LIKE ?", ['%' . $search['event'] . '%']);
            }

            if (!empty($search['user'])) {
                $activities->whereRaw("JSON_UNQUOTE(properties->'$.user') LIKE ?", ['%' . $search['user'] . '%']);
            }

            if (!empty($search['device'])) {
                $activities->whereRaw("JSON_UNQUOTE(properties->'$.device') LIKE ?", ['%' . $search['device'] . '%']);
            }

            // Apply the date filter if provided
            if (!empty($search['date'])) {
                $activities->whereDate('created_at', $search['date']);
            }

            // Order and paginate the results
            $activities = $activities->latest()->paginate(20);

         
        }else{
            $activities = Activity::latest()->paginate(20);
        }
        
        $search = $request->search? $request->search: [];
        
        return inertia('dashboard/AuditLogs', compact('activities', 'search'));
    }

}
