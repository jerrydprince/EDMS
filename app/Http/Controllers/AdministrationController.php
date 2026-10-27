<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\StaffQuery;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdministrationController extends Controller
{
    /**
     * Get official Nominal Roll list with PSR statutory retirement countdowns & leave entitlements.
     */
    public function nominalRoll(Request $request)
    {
        $query = User::with(['department', 'division', 'unit', 'section', 'designation'])
            ->where('is_approved', 1);

        if ($request->has('department_id') && !empty($request->department_id)) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('ippis_no', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate($request->get('per_page', 20));

        $users->getCollection()->transform(function($user) {
            $gl = (int) ($user->grade_level ?? 8);

            // Public Service Rules (PSR 2021) Leave Entitlement
            if ($gl >= 8) {
                $annualLeaveDays = 30;
            } elseif ($gl == 7) {
                $annualLeaveDays = 21;
            } else {
                $annualLeaveDays = 14;
            }

            // Retirement Rules: 35 years of service or 60 years of age (whichever is earlier)
            $now = Carbon::now();
            $dofa = $user->date_of_first_appointment ? Carbon::parse($user->date_of_first_appointment) : Carbon::now()->subYears(max(1, $gl - 7));
            $dob = $user->date_of_birth ? Carbon::parse($user->date_of_birth) : Carbon::now()->subYears(25 + max(1, $gl - 7));

            $retireByService = $dofa->copy()->addYears(35);
            $retireByAge = $dob->copy()->addYears(60);

            $statutoryRetirementDate = $retireByService->lessThan($retireByAge) ? $retireByService : $retireByAge;
            $daysToRetirement = (int) $now->diffInDays($statutoryRetirementDate, false);

            $user->statutory_data = [
                'annual_leave_entitlement_days' => $annualLeaveDays,
                'statutory_retirement_date' => $statutoryRetirementDate->format('Y-m-d'),
                'years_of_service' => (int) $now->diffInYears($dofa),
                'days_to_retirement' => $daysToRetirement,
                'is_due_retirement' => $daysToRetirement <= 0,
                'is_confirmation_due' => $user->date_of_confirmation ? false : ($now->diffInYears($dofa) >= 2),
            ];

            return $user;
        });

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'status' => 'success',
                'data' => $users
            ]);
        }

        return Inertia::render('Administration/Index', [
            'initialTab' => 'nominal',
            'users' => $users
        ]);
    }

    /**
     * Verify / Update officer in the Nominal Roll.
     */
    public function verifyNominalRoll(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'ippis_no' => 'required|string|max:30',
            'grade_level' => 'required|integer|min:1|max:17',
            'step' => 'nullable|integer|min:1|max:15',
            'date_of_birth' => 'nullable|date',
            'date_of_first_appointment' => 'nullable|date',
            'date_of_confirmation' => 'nullable|date',
        ]);

        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => "Nominal roll records updated and verified for {$user->first_name} {$user->last_name}.",
            'data' => $user
        ]);
    }

    /**
     * Get statutory leaves.
     */
    public function leaves(Request $request)
    {
        $query = Leave::with(['user.department', 'user.division', 'user.unit', 'user.section', 'reliefUser', 'approver'])
            ->orderBy('id', 'desc');

        if ($request->has('status') && !empty($request->status) && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $leaves = $query->paginate($request->get('per_page', 15));

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'status' => 'success',
                'data' => $leaves
            ]);
        }

        return Inertia::render('Administration/Index', [
            'initialTab' => 'leaves',
            'leaves' => $leaves
        ]);
    }

    /**
     * Process statutory leave application (Approval / Rejection).
     */
    public function processLeave(Request $request, $id)
    {
        $leave = Leave::with('user')->findOrFail($id);

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'supervisor_comment' => 'nullable|string'
        ]);

        $status = $validated['action'] === 'approve' ? 'approved' : 'rejected';

        $leave->update([
            'status' => $status,
            'approved_by' => Auth::id() ?? 1,
            'supervisor_comment' => $validated['supervisor_comment'] ?? 'Processed by Administration (Statutory Leaves Section).'
        ]);

        if ($status === 'approved') {
            $leave->user->update(['is_on_leave' => 1]);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Statutory leave request #{$leave->id} successfully {$status}.",
            'data' => $leave->load('approver')
        ]);
    }

    /**
     * Get PSR disciplinary queries.
     */
    public function queries(Request $request)
    {
        $query = StaffQuery::with(['issuer.department', 'recipient.department', 'recipient.section', 'adjudicator'])
            ->orderBy('id', 'desc');

        if ($request->has('verdict') && !empty($request->verdict) && $request->verdict !== 'all') {
            if ($request->verdict === 'pending') {
                $query->whereNull('verdict');
            } else {
                $query->where('verdict', $request->verdict);
            }
        }

        $queries = $query->paginate($request->get('per_page', 15));

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'status' => 'success',
                'data' => $queries
            ]);
        }

        return Inertia::render('Administration/Index', [
            'initialTab' => 'queries',
            'queries' => $queries
        ]);
    }

    /**
     * Issue official PSR 2021 Disciplinary Query.
     */
    public function issuePsrQuery(Request $request)
    {
        $validated = $request->validate([
            'issued_to' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'incident_category' => 'required|string', // Misconduct, Serious Misconduct
            'facts_description' => 'required|string',
            'deadline_hours' => 'required|integer|in:24,48,72',
            'evidence_path' => 'nullable|string'
        ]);

        $year = date('Y');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
        $queryRef = "PSR/QRY/{$year}/{$random}";

        $deadlineAt = Carbon::now()->addHours($validated['deadline_hours']);

        $query = StaffQuery::create([
            'query_ref' => $queryRef,
            'issued_by' => Auth::id() ?? 1,
            'issued_to' => $validated['issued_to'],
            'subject' => $validated['subject'],
            'incident_category' => $validated['incident_category'],
            'facts_description' => $validated['facts_description'],
            'evidence_path' => $validated['evidence_path'] ?? null,
            'deadline_hours' => $validated['deadline_hours'],
            'deadline_at' => $deadlineAt,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Official Disciplinary Query ({$queryRef}) issued under PSR 2021. {$validated['deadline_hours']}hr representation deadline initiated.",
            'data' => $query->load(['issuer', 'recipient'])
        ], 201);
    }

    /**
     * Adjudicate and record Disciplinary Committee Verdict.
     */
    public function resolvePsrQuery(Request $request, $id)
    {
        $query = StaffQuery::findOrFail($id);

        $validated = $request->validate([
            'verdict' => 'required|in:accepted,cautioned,escalated_to_dc,under_review',
            'verdict_comment' => 'required|string'
        ]);

        $query->update([
            'verdict' => $validated['verdict'],
            'verdict_comment' => $validated['verdict_comment'],
            'verdict_by' => Auth::id() ?? 1,
            'verdict_at' => Carbon::now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Disciplinary Committee verdict recorded as [{$validated['verdict']}] for query {$query->query_ref}.",
            'data' => $query->load('adjudicator')
        ]);
    }
}
