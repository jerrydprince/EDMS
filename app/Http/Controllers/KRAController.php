<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\KPI;
use App\Models\KRA;

class KRAController extends Controller
{
    public function createKRA(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'nullable|integer',
        ]);

        $department_id = $request->department_id ? $request->department_id : auth()->user()->department_id;
        if ($department_id) {
            DB::transaction(function () use ($request, $department_id) {
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
                        'created_at' => \Carbon\Carbon::now(),
                        'description' => 'Timeliness in delivery and task milestone adherence'
                    ],
                    [
                        'key' => 'accuracy',
                        'value' => '5',
                        'string' => 'point',
                        'department_id' => $department_id,
                        'kra_id' => $kra->id,
                        'ordering' => 2,
                        'description' => 'How accurately did the employee perform the assigned tasks and data entries?',
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
                        'description' => 'How effectively does the employee convey information and interact with stakeholders?',
                        'created_at' => \Carbon\Carbon::now()
                    ]
                ];

                KPI::insert($default_kpi);
            });
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back();
    }

    public function updateKRA(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:k_r_a_s,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $kra = KRA::find($request->id);
        $kra->name = $request->name;
        $kra->description = $request->description;
        $kra->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'kra' => $kra]);
        }

        return redirect()->back();
    }

    public function getKRA(Request $request)
    {
        $department_id = $request->id ?: auth()->user()->department_id;
        if (!$department_id) {
            return response()->json([]);
        }

        $query = KRA::with(['kpi' => function($q) {
            $q->orderBy('ordering', 'ASC')->orderBy('id', 'ASC');
        }])->where('department_id', $department_id)->orderBy('id', 'DESC');

        if ($request->has('single') && filter_var($request->single, FILTER_VALIDATE_BOOLEAN)) {
            return response()->json($query->first());
        }

        $kras = $query->get();
        return response()->json($kras);
    }

    public function addKPI(Request $request)
    {
        $request->validate([
            'kra_id' => 'required|exists:k_r_a_s,id',
            'name' => 'required|string|max:255',
            'string' => 'required|string',
        ]);

        $kra = KRA::find($request->kra_id);
        $department_id = $kra ? $kra->department_id : (auth()->user()->department_id ?? null);

        $kpi = $request->kpi_id ? KPI::find($request->kpi_id) : new KPI();
        $kpi->key = $request->name;
        $kpi->string = $request->string;
        $kpi->value = $request->string == 'point' ? ($request->points ?? 5) : $request->string;
        $kpi->kra_id = $request->kra_id;
        $kpi->department_id = $department_id;
        if ($request->has('description')) {
            $kpi->description = $request->description;
        }
        if ($request->has('ordering')) {
            $kpi->ordering = $request->ordering;
        }
        $kpi->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'kpi' => $kpi]);
        }

        return redirect()->back();
    }

    public function deleteKPI(Request $request)
    {
        $kpi = KPI::find($request->id);
        if ($kpi) {
            $kpi->delete();
        }
        return response()->json(['success' => true]);
    }

    public function deleteKRA(Request $request)
    {
        $kra = KRA::with('kpi')->find($request->id);

        if ($kra) {
            if ($kra->kpi) {
                foreach ($kra->kpi as $kpi) {
                    $kpi->delete();
                }
            }
            $kra->delete();
        }

        return response()->json(['success' => true]);
    }
}
