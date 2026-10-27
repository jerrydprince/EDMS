<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkflowRule;
use App\Models\Department;

class WorkflowSettingsController extends Controller
{
    public function index(Request $request)
    {
        $rules = WorkflowRule::with('department')->latest()->get();
        $departments = Department::all();

        return inertia('admin/WorkflowSettings', [
            'rules' => $rules,
            'departments' => $departments
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'workflow_type' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'rule_key' => 'required|string',
            'rule_value' => 'required|string',
            'description' => 'nullable|string'
        ]);

        WorkflowRule::create($validated);

        return redirect()->back()->with('message', 'Workflow rule created successfully.');
    }

    public function update(Request $request, $id)
    {
        $rule = WorkflowRule::findOrFail($id);

        $validated = $request->validate([
            'workflow_type' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'rule_key' => 'required|string',
            'rule_value' => 'required|string',
            'description' => 'nullable|string'
        ]);

        $rule->update($validated);

        return redirect()->back()->with('message', 'Workflow rule updated successfully.');
    }

    public function destroy($id)
    {
        $rule = WorkflowRule::findOrFail($id);
        $rule->delete();

        return redirect()->back()->with('message', 'Workflow rule deleted successfully.');
    }
}
