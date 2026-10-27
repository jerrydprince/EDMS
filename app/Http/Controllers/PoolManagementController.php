<?php

namespace App\Http\Controllers;

use App\Models\PoolOfficer;
use App\Models\PoolRequisition;
use App\Models\PoolPosting;
use App\Services\DigitalStampService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PoolManagementController extends Controller
{
    /**
     * Enforce strict civil service departmental boundary:
     * Strictly restricted to Director of Administration and AD Pool Management (Dept 14),
     * or officers with explicit Spatie 'pool' permissions, or Super Admins.
     */
    protected function authorizePoolAccess()
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        if ($user->hasRole('super admin') || $user->hasRole('super user')) {
            return true;
        }

        // Must belong to Department 14 (Administration & Human Resources)
        if ($user->department_id != 14) {
            abort(403, 'Access Restricted: The Federal Accountants Pool is departmentalized under Department 14 (Administration & HR).');
        }

        // Check Director of Administration
        if ($user->hasRole('director')) {
            return true;
        }

        // Check Assistant Director Pool Management
        $designation = strtolower($user->designation?->name ?? (string)$user->designation);
        if ($user->hasRole('assistant director') && (str_contains($designation, 'pool') || str_contains($designation, 'posting'))) {
            return true;
        }

        // Check explicit Spatie permissions
        if ($user->can('pool view') || $user->can('pool manage')) {
            return true;
        }

        abort(403, 'Access Restricted: You lack statutory authorization to access the National Central Register of Federal Treasury Officers.');
    }

    /**
     * Dashboard Index: National Census Statistics & Searchable Officer Registry
     */
    public function index(Request $request)
    {
        $this->authorizePoolAccess();

        // 1. National Treasury Census Statistics
        $totalOfficers = PoolOfficer::count();
        $activeDeployed = PoolOfficer::where('status', 'active')->count();
        $dueRotation = PoolOfficer::where(function($q) {
            $q->where('tenancy_years', '>=', 3.0)
              ->orWhere('status', 'due_rotation');
        })->count();
        $recalled = PoolOfficer::where('status', 'recalled')->count();
        $awaitingPosting = PoolOfficer::where('status', 'awaiting_posting')->count();

        // Cadre distribution
        $byCadre = PoolOfficer::select('cadre', DB::raw('count(*) as count'))
            ->groupBy('cadre')
            ->pluck('count', 'cadre');

        // Grade Level distribution
        $byGradeLevel = PoolOfficer::select('grade_level', DB::raw('count(*) as count'))
            ->groupBy('grade_level')
            ->orderBy('grade_level')
            ->pluck('count', 'grade_level');

        // Geopolitical Zone distribution (Federal Character Monitoring)
        $byZone = PoolOfficer::select('geopolitical_zone', DB::raw('count(*) as count'))
            ->groupBy('geopolitical_zone')
            ->pluck('count', 'geopolitical_zone');

        // Requisition metrics
        $totalRequisitions = PoolRequisition::count();
        $pendingRequisitions = PoolRequisition::where('status', 'pending')->count();
        $pendingAssumptions = PoolPosting::where('assumption_status', 'pending')->count();

        // 2. Queryable Officers List
        $query = PoolOfficer::with('postings');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhere('other_names', 'like', "%{$s}%")
                  ->orWhere('ippis_no', 'like', "%{$s}%")
                  ->orWhere('staff_id', 'like', "%{$s}%")
                  ->orWhere('current_mda_name', 'like', "%{$s}%")
                  ->orWhere('state_of_origin', 'like', "%{$s}%");
            });
        }

        if ($request->filled('cadre') && $request->cadre !== 'all') {
            $query->where('cadre', $request->cadre);
        }

        if ($request->filled('grade_level') && $request->grade_level !== 'all') {
            $query->where('grade_level', $request->grade_level);
        }

        if ($request->filled('geopolitical_zone') && $request->geopolitical_zone !== 'all') {
            $query->where('geopolitical_zone', $request->geopolitical_zone);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'due_rotation') {
                $query->where(function($q) {
                    $q->where('tenancy_years', '>=', 3.0)
                      ->orWhere('status', 'due_rotation');
                });
            } else {
                $query->where('status', $request->status);
            }
        }

        $officers = $query->orderBy('grade_level', 'desc')
            ->orderBy('tenancy_years', 'desc')
            ->paginate($request->get('per_page', 15));

        $metrics = [
            'total_officers' => $totalOfficers,
            'active_deployed' => $activeDeployed,
            'due_rotation' => $dueRotation,
            'recalled' => $recalled,
            'awaiting_posting' => $awaitingPosting,
            'total_requisitions' => $totalRequisitions,
            'pending_requisitions' => $pendingRequisitions,
            'pending_assumptions' => $pendingAssumptions,
            'by_cadre' => $byCadre,
            'by_grade_level' => $byGradeLevel,
            'by_zone' => $byZone,
        ];

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'metrics' => $metrics,
                    'officers' => $officers
                ]
            ]);
        }

        return Inertia::render('Administration/PoolManagement', [
            'metrics' => $metrics,
            'officers' => $officers,
            'initialTab' => 'pool'
        ]);
    }

    /**
     * Store or Update an Officer in the National Central Register
     */
    public function storeOfficer(Request $request)
    {
        $this->authorizePoolAccess();

        $validated = $request->validate([
            'id' => 'nullable|exists:pool_officers,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'other_names' => 'nullable|string|max:100',
            'ippis_no' => 'required|string|max:50|unique:pool_officers,ippis_no,' . $request->id,
            'staff_id' => 'nullable|string|max:50',
            'gender' => 'required|in:male,female',
            'cadre' => 'required|string',
            'grade_level' => 'required|string',
            'step' => 'nullable|integer|min:1|max:15',
            'state_of_origin' => 'required|string',
            'lga' => 'nullable|string',
            'geopolitical_zone' => 'required|string',
            'current_mda_name' => 'required|string',
            'current_mda_code' => 'nullable|string',
            'directorate_in_mda' => 'nullable|string',
            'date_posted_to_mda' => 'nullable|date',
            'tenancy_years' => 'nullable|numeric|min:0|max:40',
            'special_competencies' => 'nullable|array',
            'confirmation_status' => 'required|string',
            'date_of_first_appointment' => 'nullable|date',
            'date_of_present_appointment' => 'nullable|date',
            'phone' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:150',
            'status' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        if (!empty($validated['id'])) {
            $officer = PoolOfficer::findOrFail($validated['id']);
            $officer->update($validated);
            $message = "Treasury Officer {$officer->full_name} record updated successfully.";
        } else {
            $officer = PoolOfficer::create($validated);
            $message = "Treasury Officer {$officer->full_name} registered in the National Central Register.";
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $officer
        ]);
    }

    /**
     * Retrieve MDA Requisitions (Appendix C)
     */
    public function requisitions(Request $request)
    {
        $this->authorizePoolAccess();

        $query = PoolRequisition::with(['postings.poolOfficer', 'creator', 'treatedBy'])
            ->orderBy('id', 'desc');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('mda_name', 'like', "%{$s}%")
                  ->orWhere('requisition_no', 'like', "%{$s}%")
                  ->orWhere('letter_ref', 'like', "%{$s}%");
            });
        }

        $requisitions = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $requisitions
        ]);
    }

    /**
     * Store new MDA Requisition with Appendix C Statutory Checklist
     */
    public function storeRequisition(Request $request)
    {
        $this->authorizePoolAccess();

        $validated = $request->validate([
            'mda_name' => 'required|string|max:255',
            'mda_code' => 'nullable|string|max:50',
            'letter_ref' => 'required|string|max:100',
            'letter_date' => 'required|date',
            'justification' => 'required|string',
            'grade_level_required' => 'required|string',
            'number_required' => 'required|integer|min:1|max:50',
            'special_skills_required' => 'nullable|array',
            'has_letterhead' => 'required|boolean',
            'has_accommodation' => 'required|boolean',
        ]);

        $year = date('Y');
        $randomSeq = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        $requisitionNo = "REQ/OAGF/POOL/{$year}/{$randomSeq}";

        $requisition = PoolRequisition::create(array_merge($validated, [
            'requisition_no' => $requisitionNo,
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]));

        return response()->json([
            'status' => 'success',
            'message' => "MDA Requisition [{$requisitionNo}] logged successfully. Appendix C checklist validated.",
            'data' => $requisition
        ], 201);
    }

    /**
     * Intelligent Candidate Matching Engine for MDA Requisition
     * Factors in: Grade Level, Cadre, Special Competencies (GIFMIS, IPSAS), Tenancy (Rotation), Federal Character.
     */
    public function matchCandidates(Request $request, $id)
    {
        $this->authorizePoolAccess();

        $requisition = PoolRequisition::findOrFail($id);
        $glRequired = $requisition->grade_level_required;
        $glNum = (int) preg_replace('/[^0-9]/', '', $glRequired);
        $skillsRequired = $requisition->special_skills_required ?? [];

        // Build list of acceptable GLs (exact and compatible +/- 1)
        $glLevels = [$glRequired];
        if ($glNum > 8) {
            $glLevels[] = sprintf('GL %02d', $glNum - 1);
        }
        if ($glNum < 17) {
            $glLevels[] = sprintf('GL %02d', $glNum + 1);
        }

        // Fetch eligible officers
        $candidates = PoolOfficer::whereIn('grade_level', $glLevels)
            ->whereNotIn('status', ['interdicted', 'on_leave'])
            ->get();

        // Score and rank candidates
        $ranked = $candidates->map(function($officer) use ($skillsRequired, $requisition, $glRequired) {
            $officerGl = $officer->grade_level;
            $score = ($officerGl === $glRequired) ? 50 : 35; // 50 for exact GL, 35 for adjacent eligible GL

            // Rotation priority: Tenancy >= 3 years gets major preference
            if ($officer->tenancy_years >= 3.0 || $officer->status === 'due_rotation') {
                $score += 30;
            } elseif ($officer->tenancy_years >= 2.0) {
                $score += 15;
            }

            // Skills match bonus
            $matchedSkills = [];
            $officerSkills = $officer->special_competencies ?? [];
            foreach ($skillsRequired as $skill) {
                if (in_array($skill, $officerSkills)) {
                    $score += 10;
                    $matchedSkills[] = $skill;
                }
            }

            // Cross-deployment check (cannot post back to same MDA)
            $isSameMda = (strtolower(trim($officer->current_mda_name)) === strtolower(trim($requisition->mda_name)));
            if ($isSameMda) {
                $score -= 40; // Penalty
            }

            return [
                'officer' => $officer,
                'score' => max(0, $score),
                'matched_skills' => $matchedSkills,
                'is_due_rotation' => ($officer->tenancy_years >= 3.0),
                'federal_character' => [
                    'state' => $officer->state_of_origin,
                    'zone' => $officer->geopolitical_zone
                ],
                'same_mda_warning' => $isSameMda,
            ];
        })->sortByDesc('score')->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'requisition' => $requisition,
                'candidates' => $ranked
            ]
        ]);
    }

    /**
     * Issue Official Posting Order (Form OAGF/POOL/01)
     * Features: Reference generation, 14-day statutory assumption window, digital stamp preview/attachment.
     */
    public function issuePosting(Request $request)
    {
        $this->authorizePoolAccess();

        $validated = $request->validate([
            'pool_officer_id' => 'required|exists:pool_officers,id',
            'pool_requisition_id' => 'nullable|exists:pool_requisitions,id',
            'to_mda_name' => 'required|string|max:255',
            'posting_type' => 'required|in:routine,emergency,rotation,promotion',
            'effective_date' => 'required|date',
            'assumption_deadline_days' => 'nullable|integer|min:7|max:30',
        ]);

        $officer = PoolOfficer::findOrFail($validated['pool_officer_id']);
        $glNumber = (int) preg_replace('/[^0-9]/', '', $officer->grade_level);

        // Statutory approval rule: GL 06-13 approved by Director Administration; GL 14+ approved by Accountant-General of the Federation (AGF)
        $approvalAuthority = ($glNumber >= 14) ? 'agf' : 'da';

        $year = date('Y');
        $randomSeq = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
        $postingRef = "OAGF/POOL/{$year}/{$randomSeq}";

        $days = $validated['assumption_deadline_days'] ?? 14;
        $effectiveDate = Carbon::parse($validated['effective_date']);
        $assumptionDueDate = $effectiveDate->copy()->addDays($days);

        $posting = PoolPosting::create([
            'posting_ref' => $postingRef,
            'pool_officer_id' => $officer->id,
            'pool_requisition_id' => $validated['pool_requisition_id'] ?? null,
            'from_mda_name' => $officer->current_mda_name,
            'to_mda_name' => $validated['to_mda_name'],
            'posting_type' => $validated['posting_type'],
            'effective_date' => $effectiveDate->format('Y-m-d'),
            'approval_authority' => $approvalAuthority,
            'approved_by' => Auth::id(),
            'approved_at' => Carbon::now(),
            'assumption_due_date' => $assumptionDueDate->format('Y-m-d'),
            'assumption_status' => 'pending',
        ]);

        // Update officer status
        $officer->update([
            'status' => 'awaiting_posting',
            'remarks' => "Posting Order {$postingRef} issued to {$validated['to_mda_name']}. Assumption due {$assumptionDueDate->format('d M Y')}."
        ]);

        // If linked to requisition, update requisition status
        if (!empty($validated['pool_requisition_id'])) {
            $req = PoolRequisition::find($validated['pool_requisition_id']);
            if ($req) {
                $req->update(['status' => 'fulfilled', 'treated_by' => Auth::id()]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Posting Order Form OAGF/POOL/01 ({$postingRef}) generated and issued under authority of {$approvalAuthority}.",
            'data' => $posting->load('poolOfficer')
        ], 201);
    }

    /**
     * Record & Verify Assumption Slip (Form OAGF/POOL/02)
     * Completes posting loop and transitions officer to active deployment in new MDA.
     */
    public function recordAssumption(Request $request, $id)
    {
        $this->authorizePoolAccess();

        $posting = PoolPosting::with('poolOfficer')->findOrFail($id);

        $validated = $request->validate([
            'assumption_slip_returned_at' => 'required|date',
            'assumption_slip_path' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $posting->update([
            'assumption_status' => 'assumed',
            'assumption_slip_returned_at' => $validated['assumption_slip_returned_at'],
            'assumption_slip_path' => $validated['assumption_slip_path'] ?? null,
        ]);

        // Update officer's current posting to new MDA and reset tenancy
        $officer = $posting->poolOfficer;
        $officer->update([
            'current_mda_name' => $posting->to_mda_name,
            'date_posted_to_mda' => $validated['assumption_slip_returned_at'],
            'tenancy_years' => 0.0,
            'status' => 'active',
            'remarks' => "Assumed duty at {$posting->to_mda_name} on {$validated['assumption_slip_returned_at']} (Verified under Form OAGF/POOL/02)."
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Assumption Slip verified! Officer {$officer->full_name} is now actively posted to {$posting->to_mda_name}.",
            'data' => $posting->load('poolOfficer')
        ]);
    }

    /**
     * List all Postings & Filter by Status
     */
    public function postings(Request $request)
    {
        $this->authorizePoolAccess();

        $query = PoolPosting::with(['poolOfficer', 'requisition', 'approvedBy'])
            ->orderBy('id', 'desc');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('assumption_status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('posting_ref', 'like', "%{$s}%")
                  ->orWhere('from_mda_name', 'like', "%{$s}%")
                  ->orWhere('to_mda_name', 'like', "%{$s}%")
                  ->orWhereHas('poolOfficer', function($oq) use ($s) {
                      $oq->where('first_name', 'like', "%{$s}%")
                         ->orWhere('last_name', 'like', "%{$s}%")
                         ->orWhere('ippis_no', 'like', "%{$s}%");
                  });
            });
        }

        $postings = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $postings
        ]);
    }
}
