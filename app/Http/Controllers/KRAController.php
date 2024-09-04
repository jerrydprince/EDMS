<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\KPI;
use App\Models\KRA;

class KRAController extends Controller
{
    public function createKRA(Request $request){
        $department_id = auth()->user()->department_id;
        if($department_id){

            DB::transaction(function () use ($request, $department_id) {
                 //create KRA
            $kra = new KRA();
            $kra->name = $request->name;
            $kra->description = $request->description;
            $kra->department_id = $department_id;
            $kra->save();

            $default_kpi = [
                    [
                    'key' => 'timeliness',
                    'value' => 'time',
                    'string' => 'time',
                    'department_id' => $department_id,
                    'kra_id' => $kra->id,
                    'ordering' => 1,
                    'created_at' => \Carbon\Carbon::now()
                    ],
                    [
                        'key' => 'accuracy',
                        'value' => '5',
                        'string' => 'point',
                        'department_id' => $department_id,
                        'kra_id' => $kra->id,
                        'ordering' => 2,
                        'description' =>'How accurately did the employee perform the assigned tasks and data entries? ',
                        'created_at' => \Carbon\Carbon::now()
                    ],
                    [
                        'key' => 'productivity',
                        'value' => '5',
                        'string' => 'point',
                        'department_id' => $department_id,
                        'kra_id' => $kra->id,
                        'ordering' => 3,
                        'description' => 'How well did the employee complete the task within the assigned time frame?',
                        'created_at' => \Carbon\Carbon::now()
                    ],
                    [
                        'key' => 'communication skills',
                        'value' => '100',
                        'string' => 'percentage',
                        'department_id' => $department_id,
                        'kra_id' => $kra->id,
                        'ordering' => 4,
                        'communication' =>'How effectively does the employee convey information and interact with others?',
                        'created_at' => \Carbon\Carbon::now()
                    ]
                ];

              KPI::insert($default_kpi);  

            });
        }

        return redirect()->back();
    }

    public function getKRA(){
        $department_id = auth()->user()->department_id;
        if($department_id){
            $kra = KRA::with('kpi')->where('department_id', $department_id)->first();
            return response()->json($kra);
        }
    }

    public function addKPI(Request $request){
        $department_id = auth()->user()->department_id;
        if($department_id){
            $kpi = $request->kpi_id? KPI::find($request->kpi_id): new KPI();
            $kpi->key = $request->name;
            $kpi->string = $request->string;
            $kpi->value = $request->string == 'point'? $request->points: $request->string;
            $kpi->kra_id = $request->kra_id;
            $kpi->ordering = 0;
            $kpi->department_id = $department_id;
            $kpi->save();
        }

        return redirect()->back();
    }

    public function deleteKPI(Request $request){
        $kpi = KPI::find($request->id);
        $kpi->delete();
        return response()->json(['success'=>true]);
    }

    public function deleteKRA(Request $request){
        $kra = KRA::with('kpi')->find($request->id);

        if ($kra) {
            if ($kra->kpi) {
                foreach ($kra->kpi as $kpi) {
                    $kpi->delete();
                }
            }
            $kra->delete(); 
        }
    }
}
