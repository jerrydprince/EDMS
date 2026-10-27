<?php

namespace App\Http\Controllers;

use App\Models\AperSubmission;
use App\Models\DigitalStamp;
use App\Models\FileMovementCard;
use App\Models\StaffConfirmation;
use App\Models\User;
use App\Models\WelfareClaim;
use App\Services\DigitalStampService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdministrationDepartmentController extends Controller
{
    /**
     * Authorize Department 14 access or relevant Spatie permissions.
     */
    protected function authorizeAdminDeptAccess()
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        if ($user->hasRole('super admin') || $user->hasRole('super user')) {
            return true;
        }

        if ($user->department_id == 14) {
            return true;
        }

        // Check explicit permissions
        if ($user->can('admin confirmation manage') || $user->can('admin aper manage') || $user->can('admin welfare manage') || $user->can('admin file card manage')) {
            return true;
        }

        abort(403, 'Access Restricted: You lack authorization for Department 14 (Administration & HR) statutory desks.');
    }

    // ==========================================
    // 1. 24-MONTH CONFIRMATION LIFECYCLE (PSR 020301)
    // ==========================================

    public function confirmations(Request $request)
    {
        $this->authorizeAdminDeptAccess();

        $query = StaffConfirmation::with('user.department')
            ->orderBy('confirmation_due_date', 'asc');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('user', function($uq) use ($s) {
                $uq->where('first_name', 'like', "%{$s}%")
                   ->orWhere('last_name', 'like', "%{$s}%")
                   ->orWhere('ippis_no', 'like', "%{$s}%");
            });
        }

        $confirmations = $query->paginate($request->get('per_page', 15));

        // Append real-time countdown / urgency flag
        $now = Carbon::now();
        $confirmations->getCollection()->transform(function($c) use ($now) {
            $dueDate = Carbon::parse($c->confirmation_due_date);
            $c->days_until_due = (int) $now->diffInDays($dueDate, false);
            $c->is_advance_alert = ($c->days_until_due <= 90 && $c->days_until_due > 0 && $c->status === 'probation');
            $c->is_overdue = ($c->days_until_due <= 0 && $c->status === 'probation');
            return $c;
        });

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'status' => 'success',
                'data' => $confirmations
            ]);
        }

        return Inertia::render('Administration/Index', [
            'initialTab' => 'confirmations',
            'confirmations' => $confirmations
        ]);
    }

    public function recommendConfirmation(Request $request, $id)
    {
        $this->authorizeAdminDeptAccess();

        $confirmation = StaffConfirmation::with('user')->findOrFail($id);

        $validated = $request->validate([
            'action' => 'required|in:recommend,defer,approve',
            'recommendation_notes' => 'required|string',
            'gazette_reference' => 'nullable|string',
        ]);

        $status = match ($validated['action']) {
            'recommend' => 'recommended',
            'defer' => 'deferred',
            'approve' => 'approved',
        };

        $confirmation->update([
            'status' => $status,
            'recommendation_notes' => $validated['recommendation_notes'],
            'gazette_reference' => $validated['gazette_reference'] ?? $confirmation->gazette_reference,
            'approved_by' => ($status === 'approved') ? Auth::id() : $confirmation->approved_by,
            'approved_at' => ($status === 'approved') ? Carbon::now() : $confirmation->approved_at,
        ]);

        if ($status === 'approved') {
            $confirmation->user->update([
                'date_of_confirmation' => Carbon::now()->format('Y-m-d')
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Confirmation status for {$confirmation->user->first_name} {$confirmation->user->last_name} updated to [{$status}].",
            'data' => $confirmation
        ]);
    }

    // ==========================================
    // 2. APER CAMPAIGN & LEDGER (APPENDIX D)
    // ==========================================

    public function aperSubmissions(Request $request)
    {
        $this->authorizeAdminDeptAccess();

        $query = AperSubmission::with(['user.department', 'reportingOfficer', 'countersigningOfficer'])
            ->orderBy('year', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('year') && $request->year !== 'all') {
            $query->where('year', $request->year);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('user', function($uq) use ($s) {
                $uq->where('first_name', 'like', "%{$s}%")
                   ->orWhere('last_name', 'like', "%{$s}%")
                   ->orWhere('ippis_no', 'like', "%{$s}%");
            });
        }

        $submissions = $query->paginate($request->get('per_page', 15));

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'status' => 'success',
                'data' => $submissions
            ]);
        }

        return Inertia::render('Administration/Index', [
            'initialTab' => 'confirmations',
            'submissions' => $submissions
        ]);
    }

    public function evaluateAper(Request $request, $id)
    {
        $this->authorizeAdminDeptAccess();

        $aper = AperSubmission::with('user')->findOrFail($id);

        $validated = $request->validate([
            'overall_score' => 'required|numeric|min:0|max:100',
            'rating_category' => 'required|in:Outstanding,Very Good,Good,Fair,Poor',
            'recommendation' => 'required|in:Accelerated Promotion,Promotable,Normal,Not Promotable',
            'is_checklist_verified' => 'required|boolean',
        ]);

        $aper->update(array_merge($validated, [
            'status' => 'vetted',
            'countersigning_officer_id' => Auth::id(),
        ]));

        return response()->json([
            'status' => 'success',
            'message' => "APER appraisal for {$aper->user->first_name} {$aper->user->last_name} scored and verified under Appendix D checklist.",
            'data' => $aper
        ]);
    }

    // ==========================================
    // 3. STAFF WELFARE CLAIMS (48-HOUR SLA)
    // ==========================================

    public function welfareClaims(Request $request)
    {
        $this->authorizeAdminDeptAccess();

        $query = WelfareClaim::with(['user.department', 'recommendedBy', 'approvedBy'])
            ->orderBy('id', 'desc');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('claim_type') && $request->claim_type !== 'all') {
            $query->where('claim_type', $request->claim_type);
        }

        $claims = $query->paginate($request->get('per_page', 15));

        // Add real-time SLA countdown
        $now = Carbon::now();
        $claims->getCollection()->transform(function($c) use ($now) {
            $deadline = Carbon::parse($c->sla_deadline);
            $c->hours_remaining = round($now->diffInHours($deadline, false), 1);
            $c->is_sla_breached = ($c->hours_remaining <= 0 && in_array($c->status, ['pending', 'recommended']));
            return $c;
        });

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'status' => 'success',
                'data' => $claims
            ]);
        }

        return Inertia::render('Administration/Index', [
            'initialTab' => 'welfare',
            'claims' => $claims
        ]);
    }

    public function storeWelfareClaim(Request $request)
    {
        $this->authorizeAdminDeptAccess();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'claim_type' => 'required|in:burial,medical,compassionate,nhis,housing_loan',
            'amount_requested' => 'required|numeric|min:1',
            'justification' => 'required|string',
            'evidence_path' => 'nullable|string',
        ]);

        $slaDeadline = Carbon::now()->addHours(48);

        $claim = WelfareClaim::create([
            'user_id' => $validated['user_id'],
            'claim_type' => $validated['claim_type'],
            'amount_requested' => $validated['amount_requested'],
            'amount_approved' => 0.00,
            'justification' => $validated['justification'],
            'evidence_path' => $validated['evidence_path'] ?? null,
            'sla_deadline' => $slaDeadline,
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Staff welfare claim submitted. 48-Hour SLA countdown initiated.",
            'data' => $claim->load('user')
        ], 201);
    }

    public function processWelfareClaim(Request $request, $id)
    {
        $this->authorizeAdminDeptAccess();

        $claim = WelfareClaim::with('user')->findOrFail($id);

        $validated = $request->validate([
            'action' => 'required|in:recommend,approve,reject,disburse',
            'amount_approved' => 'nullable|numeric|min:0',
        ]);

        $status = match ($validated['action']) {
            'recommend' => 'recommended',
            'approve' => 'approved',
            'reject' => 'rejected',
            'disburse' => 'disbursed',
        };

        $updateData = ['status' => $status];
        if (isset($validated['amount_approved'])) {
            $updateData['amount_approved'] = $validated['amount_approved'];
        }

        if ($status === 'recommended') {
            $updateData['recommended_by'] = Auth::id();
        } elseif ($status === 'approved' || $status === 'disbursed') {
            $updateData['approved_by'] = Auth::id();
            if ($claim->amount_approved == 0 && isset($validated['amount_approved'])) {
                $updateData['amount_approved'] = $validated['amount_approved'];
            }
        }

        $claim->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => "Welfare claim #{$claim->id} processed to status [{$status}].",
            'data' => $claim
        ]);
    }

    // ==========================================
    // 4. SUB-REGISTRY & FILE MOVEMENT CARDS (APPENDIX A)
    // ==========================================

    public function fileMovementCards(Request $request)
    {
        $this->authorizeAdminDeptAccess();

        $query = FileMovementCard::with(['releasingOfficer', 'receivingOfficer'])
            ->orderBy('id', 'desc');

        if ($request->filled('is_treated') && $request->is_treated !== 'all') {
            $query->where('is_treated', $request->is_treated === '1');
        }

        if ($request->filled('sla_breached') && $request->sla_breached !== 'all') {
            $query->where('sla_breached', $request->sla_breached === '1');
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('file_no', 'like', "%{$s}%")
                  ->orWhere('subject', 'like', "%{$s}%")
                  ->orWhere('from_office', 'like', "%{$s}%")
                  ->orWhere('to_office', 'like', "%{$s}%");
            });
        }

        $cards = $query->paginate($request->get('per_page', 15));

        // Recalculate holding hours and 24-hr table SLA status dynamically
        $now = Carbon::now();
        $cards->getCollection()->transform(function($card) use ($now) {
            if (!$card->is_treated) {
                $startTime = Carbon::parse($card->date_time_out);
                $card->holding_hours = round($now->diffInHours($startTime), 1);
                $card->sla_breached = ($card->holding_hours > 24);
            }
            return $card;
        });

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'status' => 'success',
                'data' => $cards
            ]);
        }

        return Inertia::render('Administration/Index', [
            'initialTab' => 'sub_registry',
            'cards' => $cards
        ]);
    }

    public function storeFileMovement(Request $request)
    {
        $this->authorizeAdminDeptAccess();

        $validated = $request->validate([
            'file_no' => 'required|string|max:100',
            'subject' => 'required|string|max:255',
            'from_office' => 'required|string|max:150',
            'to_office' => 'required|string|max:150',
            'receiving_officer_id' => 'nullable|exists:users,id',
            'remarks' => 'nullable|string',
        ]);

        $card = FileMovementCard::create([
            'file_no' => $validated['file_no'],
            'subject' => $validated['subject'],
            'from_office' => $validated['from_office'],
            'to_office' => $validated['to_office'],
            'releasing_officer_id' => Auth::id(),
            'receiving_officer_id' => $validated['receiving_officer_id'] ?? null,
            'date_time_out' => Carbon::now(),
            'is_treated' => false,
            'holding_hours' => 0,
            'sla_breached' => false,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Appendix A File Movement Card registered. Transferred to {$validated['to_office']}. 24-hour table SLA started.",
            'data' => $card
        ], 201);
    }

    public function markFileTreated(Request $request, $id)
    {
        $this->authorizeAdminDeptAccess();

        $card = FileMovementCard::findOrFail($id);
        $now = Carbon::now();
        $startTime = Carbon::parse($card->date_time_out);
        $holdingHours = round($now->diffInHours($startTime), 1);

        $card->update([
            'is_treated' => true,
            'treated_at' => $now,
            'date_time_in' => $now,
            'holding_hours' => $holdingHours,
            'sla_breached' => ($holdingHours > 24),
            'remarks' => $request->remarks ?? $card->remarks,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "File {$card->file_no} marked as treated after {$holdingHours} hours.",
            'data' => $card
        ]);
    }

    // ==========================================
    // 5. DIGITAL STAMPS REGISTRY & SVG ENGINE
    // ==========================================

    public function getDigitalStamps()
    {
        $this->authorizeAdminDeptAccess();

        $stamps = DigitalStamp::with(['user', 'department'])
            ->orderBy('is_default', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $stamps
        ]);
    }

    public function previewSvgStamp(Request $request)
    {
        $this->authorizeAdminDeptAccess();

        $designation = $request->get('designation', 'DIRECTOR ADMINISTRATION');
        $date = $request->get('date');
        $inkColor = $request->get('ink_color', '#1e3a8a');
        $departmentName = $request->get('department_name', 'OFFICE OF THE ACCOUNTANT-GENERAL OF THE FEDERATION');

        $svg = DigitalStampService::generateSvgStamp($designation, $date, $inkColor, $departmentName);

        return response()->json([
            'status' => 'success',
            'data' => [
                'svg' => $svg,
                'designation' => $designation,
                'ink_color' => $inkColor,
            ]
        ]);
    }

    public function storeDigitalStamp(Request $request)
    {
        $this->authorizeAdminDeptAccess();

        $validated = $request->validate([
            'designation_title' => 'required|string|max:150',
            'stamp_type' => 'required|in:svg_generated,uploaded_png',
            'ink_color' => 'required|string|max:20',
            'custom_stamp_file' => 'nullable|file|mimes:png,svg|max:2048',
            'is_default' => 'nullable|boolean',
        ]);

        $customPath = null;
        if ($request->hasFile('custom_stamp_file')) {
            $file = $request->file('custom_stamp_file');
            $filename = 'stamp_' . time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('stamps', $filename, 'public');
            $customPath = 'storage/' . $path;
        }

        if (!empty($validated['is_default'])) {
            DigitalStamp::where('designation_title', $validated['designation_title'])
                ->update(['is_default' => false]);
        }

        $stamp = DigitalStamp::create([
            'user_id' => Auth::id(),
            'department_id' => Auth::user()->department_id ?? 14,
            'designation_title' => $validated['designation_title'],
            'stamp_type' => $validated['stamp_type'],
            'custom_stamp_path' => $customPath,
            'ink_color' => $validated['ink_color'],
            'is_default' => $validated['is_default'] ?? true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Digital stamp profile created for {$stamp->designation_title}.",
            'data' => $stamp
        ], 201);
    }
}
