<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkflowDelegation;
use App\Models\User;
use Inertia\Inertia;

class WorkflowDelegationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $query = WorkflowDelegation::with(['delegator', 'delegatee']);
        
        // If not admin, only show own delegations
        if (!$user->hasRole('super admin') && !$user->hasRole('super user')) {
            $query->where('delegator_user_id', $user->id);
        }

        $delegations = $query->orderBy('created_at', 'desc')->get();
        $users = User::select('id', 'first_name', 'last_name', 'email', 'department_id')->get();

        return Inertia::render('admin/WorkflowBuilder/Delegations', [
            'delegations' => $delegations,
            'users' => $users
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'delegatee_user_id' => 'required|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $user = auth()->user();
        $delegatorId = $request->delegator_user_id;

        // Admins can set delegations for others
        if ($delegatorId && ($user->hasRole('super admin') || $user->hasRole('super user'))) {
            // keep $delegatorId
        } else {
            $delegatorId = $user->id;
        }

        WorkflowDelegation::create([
            'delegator_user_id' => $delegatorId,
            'delegatee_user_id' => $request->delegatee_user_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Delegation created successfully.');
    }

    public function destroy($id)
    {
        $delegation = WorkflowDelegation::findOrFail($id);
        
        $user = auth()->user();
        if (!$user->hasRole('super admin') && !$user->hasRole('super user') && $delegation->delegator_user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $delegation->delete();

        return redirect()->back()->with('success', 'Delegation removed successfully.');
    }
}
