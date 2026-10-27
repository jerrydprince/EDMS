<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Models\Department;
use App\Models\Division;
use App\Models\Unit;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    /**
     * Display a listing of designations for administrative setup.
     */
    public function index(Request $request)
    {
        $query = Designation::with(['department', 'division', 'unit']);

        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('cadre', 'like', '%' . $request->search . '%');
        }

        if ($request->hierarchy_level) {
            $query->where('hierarchy_level', $request->hierarchy_level);
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $designations = $query->latest()->paginate(15)->withQueryString();
        $departments = Department::select('id', 'title')->get();

        return inertia('settings/DesignationsSetup', [
            'designations' => $designations,
            'departments' => $departments,
            'filters' => $request->only(['search', 'hierarchy_level', 'department_id'])
        ]);
    }

    /**
     * API endpoint returning designations available for a given hierarchy placement.
     */
    public function getAvailableDesignations(Request $request)
    {
        $deptId = $request->department_id;
        $divId = $request->division_id;
        $unitId = $request->unit_id;
        $sectionId = $request->section_id;

        // If no department is selected, return empty list (designations are strictly department-specific)
        if (!$deptId) {
            return response()->json([]);
        }

        $query = Designation::active()->where('department_id', $deptId);

        if ($sectionId) {
            $query->where(function ($q) use ($sectionId, $unitId, $divId) {
                $q->where('section_id', $sectionId)
                  ->orWhere(function ($sub) use ($unitId, $divId) {
                      if ($unitId) $sub->where('unit_id', $unitId);
                      elseif ($divId) $sub->where('division_id', $divId);
                      else $sub->whereNull('division_id');
                  });
            });
        } elseif ($unitId) {
            $query->where(function ($q) use ($unitId, $divId) {
                $q->where('unit_id', $unitId)
                  ->orWhere('division_id', $divId)
                  ->orWhereNull('division_id');
            });
        } elseif ($divId) {
            $query->where(function ($q) use ($divId) {
                $q->where('division_id', $divId)
                  ->orWhereNull('division_id');
            });
        }

        $designations = $query->orderBy('grade_level', 'desc')
            ->orderBy('title', 'asc')
            ->get();

        return response()->json($designations);
    }

    /**
     * Store a newly created designation.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'cadre' => 'nullable|string|max:255',
            'grade_level' => 'nullable|integer|min:0|max:20',
            'hierarchy_level' => 'required|string|in:department,division,unit,section',
            'department_id' => 'required|exists:departments,id',
            'division_id' => 'nullable|exists:divisions,id',
            'unit_id' => 'nullable|exists:units,id',
            'description' => 'nullable|string',
            'is_registry' => 'nullable|boolean',
            'is_head_of_office' => 'nullable|boolean',
        ]);

        $dept = Department::find($data['department_id']);
        if ($dept && !str_contains($data['title'], '(' . $dept->title . ')')) {
            $data['title'] = trim($data['title']) . ' (' . $dept->title . ')';
        }

        $data['is_registry'] = $request->boolean('is_registry');
        $data['is_head_of_office'] = $request->boolean('is_head_of_office');
        $data['is_active'] = true;

        Designation::create($data);

        return redirect()->back()->with(['success' => true, 'message' => 'Department-specific designation created successfully']);
    }

    /**
     * Update an existing designation.
     */
    public function update(Request $request, $id)
    {
        $designation = Designation::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'cadre' => 'nullable|string|max:255',
            'grade_level' => 'nullable|integer|min:0|max:20',
            'hierarchy_level' => 'required|string|in:department,division,unit,section',
            'department_id' => 'required|exists:departments,id',
            'division_id' => 'nullable|exists:divisions,id',
            'unit_id' => 'nullable|exists:units,id',
            'description' => 'nullable|string',
            'is_registry' => 'nullable|boolean',
            'is_head_of_office' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $dept = Department::find($data['department_id']);
        if ($dept && !str_contains($data['title'], '(' . $dept->title . ')')) {
            $data['title'] = trim($data['title']) . ' (' . $dept->title . ')';
        }

        $data['is_registry'] = $request->boolean('is_registry');
        $data['is_head_of_office'] = $request->boolean('is_head_of_office');
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $designation->update($data);

        return redirect()->back()->with(['success' => true, 'message' => 'Designation updated successfully']);
    }

    /**
     * Deactivate or toggle status of designation.
     */
    public function toggleStatus($id)
    {
        $designation = Designation::findOrFail($id);
        $designation->is_active = !$designation->is_active;
        $designation->save();

        return redirect()->back()->with(['success' => true]);
    }
}
