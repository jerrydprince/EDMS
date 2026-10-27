<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServicomReport;
use App\Models\ServicomPanel;
use App\Models\ServicomPanelMember;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ServicomController extends Controller
{
    /**
     * Display the SERVICOM Portal
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) return redirect()->route('login');

        $reports = ServicomReport::with([
            'accused.department',
            'accused.division',
            'accused.unit',
            'complainant',
            'assignedOfficer',
            'panel.chairperson',
            'panel.secretary',
            'panel.members',
        ])
        ->latest('id')
        ->get();

        $staffUsers = User::select('id', 'first_name', 'last_name', 'email', 'department_id', 'division_id', 'unit_id')
            ->orderBy('first_name')
            ->get();

        $stats = [
            'total' => $reports->count(),
            'under_review' => $reports->whereIn('status', ['submitted', 'under_review'])->count(),
            'panels_active' => $reports->where('status', 'panel_convened')->count(),
            'resolved' => $reports->whereIn('status', ['investigation_complete', 'closed'])->count(),
            'dismissed' => $reports->where('status', 'dismissed')->count(),
        ];

        return Inertia::render('Servicom/Index', [
            'reports' => $reports,
            'staffUsers' => $staffUsers,
            'stats' => $stats,
        ]);
    }

    /**
     * Staff submits a grievance / harassment / intimidation report
     */
    public function storeReport(Request $request)
    {
        $request->validate([
            'accused_user_id' => 'required|exists:users,id',
            'category' => 'required|string',
            'complaint_details' => 'required|string',
            'is_anonymous' => 'nullable|boolean',
            'evidence' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,mp3,wav|max:20480',
        ]);

        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $fileName = 'srv_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $destination = public_path('servicom_evidence');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $fileName);
            $evidencePath = '/servicom_evidence/' . $fileName;
        }

        $isAnonymous = $request->boolean('is_anonymous');
        $trackingCode = 'SRV-' . date('Y') . '-' . strtoupper(Str::random(6));

        $report = ServicomReport::create([
            'tracking_code' => $trackingCode,
            'complainant_id' => $isAnonymous ? null : auth()->id(),
            'is_anonymous' => $isAnonymous,
            'accused_user_id' => $request->accused_user_id,
            'category' => $request->category,
            'complaint_details' => $request->complaint_details,
            'evidence_file_path' => $evidencePath,
            'status' => 'submitted',
        ]);

        return redirect()->back()->with('success', "Your report has been lodged securely with SERVICOM under Tracking Code: {$trackingCode}. All details are protected with strict whistleblower confidentiality.");
    }

    /**
     * Update triage status of report
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:submitted,under_review,investigation_complete,closed,dismissed',
        ]);

        $report = ServicomReport::findOrFail($id);
        $report->status = $request->status;
        $report->save();

        return redirect()->back()->with('success', "Report status updated to: {$request->status}");
    }

    /**
     * Constitute an Investigation Panel for a report
     */
    public function constitutePanel(Request $request, $id)
    {
        $report = ServicomReport::findOrFail($id);

        $request->validate([
            'panel_title' => 'required|string|max:255',
            'chairperson_id' => 'required|exists:users,id',
            'secretary_id' => 'required|exists:users,id',
            'hearing_date' => 'nullable|date',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        $panel = ServicomPanel::updateOrCreate(
            ['report_id' => $report->id],
            [
                'panel_title' => $request->panel_title,
                'chairperson_id' => $request->chairperson_id,
                'secretary_id' => $request->secretary_id,
                'hearing_date' => $request->hearing_date,
                'status' => 'constituted',
            ]
        );

        // Sync panel members
        if ($request->has('member_ids')) {
            $panel->members()->sync($request->member_ids);
        }

        $report->status = 'panel_convened';
        $report->save();

        return redirect()->back()->with('success', "Investigation Panel '{$panel->panel_title}' constituted successfully.");
    }

    /**
     * Record Panel Findings & Recommendations
     */
    public function updatePanelFindings(Request $request, $panelId)
    {
        $panel = ServicomPanel::with('report')->findOrFail($panelId);

        $request->validate([
            'findings' => 'required|string',
            'recommendations' => 'required|string',
            'action_taken' => 'nullable|string',
            'status' => 'required|in:in_progress,report_submitted,resolved',
        ]);

        $panel->findings = $request->findings;
        $panel->recommendations = $request->recommendations;
        $panel->action_taken = $request->action_taken;
        $panel->status = $request->status;
        $panel->save();

        if ($request->status === 'resolved') {
            $panel->report->status = 'closed';
            $panel->report->save();
        } elseif ($request->status === 'report_submitted') {
            $panel->report->status = 'investigation_complete';
            $panel->report->save();
        }

        return redirect()->back()->with('success', 'Panel findings and executive recommendations recorded successfully.');
    }
}
