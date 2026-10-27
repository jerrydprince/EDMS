<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StaffQuery;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Str;

class StaffQueryController extends Controller
{
    /**
     * Check if a user has authority to issue queries under PSR
     */
    public function canUserIssueQuery($user)
    {
        if (!$user) return false;
        return $user->hasAnyRole([
            'Accountant-General', 'agf', 'super user', 'super admin', 'admin',
            'director', 'deputy director', 'assistant director'
        ]);
    }

    /**
     * Display a listing of queries relevant to current user
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) return redirect()->route('login');

        $isHrAdmin = $user->hasAnyRole(['super user', 'super admin', 'admin', 'Accountant-General', 'director']);
        $canIssueQuery = $this->canUserIssueQuery($user);

        $receivedQueries = StaffQuery::with(['issuer.unit', 'issuer.division', 'issuer.department', 'adjudicator'])
            ->where('issued_to', $user->id)
            ->latest('id')
            ->get();

        $issuedQueries = StaffQuery::with(['recipient.unit', 'recipient.division', 'recipient.department', 'adjudicator'])
            ->where('issued_by', $user->id)
            ->latest('id')
            ->get();

        $allQueries = collect([]);
        if ($isHrAdmin) {
            $allQueries = StaffQuery::with(['issuer', 'recipient', 'adjudicator'])
                ->latest('id')
                ->take(100)
                ->get();
        }

        return Inertia::render('PMS/Queries/Index', [
            'receivedQueries' => $receivedQueries,
            'issuedQueries' => $issuedQueries,
            'allQueries' => $allQueries,
            'isHrAdmin' => $isHrAdmin,
            'canIssueQuery' => $canIssueQuery,
        ]);
    }

    /**
     * Get allowed subordinates that the logged-in supervisor can issue queries to
     */
    public function getSubordinates(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$this->canUserIssueQuery($user)) {
            return response()->json(['can_issue' => false, 'subordinates' => []]);
        }

        $isAgfOrAdmin = $user->hasAnyRole(['Accountant-General', 'agf', 'super user', 'super admin', 'admin']);
        $isDirector = $user->hasRole('director');
        $isDeputyDirector = $user->hasRole('deputy director');
        $isAssistantDirector = $user->hasRole('assistant director');

        $query = User::select('id', 'first_name', 'last_name', 'email', 'department_id', 'division_id', 'unit_id')
            ->with(['department', 'division', 'unit'])
            ->where('id', '!=', $user->id);

        if ($isAgfOrAdmin) {
            // AGF can query all staff
            $subordinates = $query->orderBy('first_name')->get();
        } elseif ($isDirector) {
            // Director can query DD, AD, and Unit staff in their department
            if ($user->department_id) {
                $query->where('department_id', $user->department_id);
            }
            $subordinates = $query->whereDoesntHave('roles', function($q) {
                $q->whereIn('name', ['Accountant-General', 'agf', 'director', 'super user', 'super admin']);
            })->orderBy('first_name')->get();
        } elseif ($isDeputyDirector) {
            // Deputy Director can query AD and Unit staff in their division
            if ($user->division_id) {
                $query->where('division_id', $user->division_id);
            } elseif ($user->department_id) {
                $query->where('department_id', $user->department_id);
            }
            $subordinates = $query->whereDoesntHave('roles', function($q) {
                $q->whereIn('name', ['Accountant-General', 'agf', 'director', 'deputy director', 'super user', 'super admin']);
            })->orderBy('first_name')->get();
        } elseif ($isAssistantDirector) {
            // Assistant Director can query Unit staff in their unit
            if ($user->unit_id) {
                $query->where('unit_id', $user->unit_id);
            } elseif ($user->division_id) {
                $query->where('division_id', $user->division_id);
            }
            $subordinates = $query->whereDoesntHave('roles', function($q) {
                $q->whereIn('name', ['Accountant-General', 'agf', 'director', 'deputy director', 'assistant director', 'super user', 'super admin']);
            })->orderBy('first_name')->get();
        } else {
            $subordinates = collect([]);
        }

        return response()->json([
            'can_issue' => true,
            'subordinates' => $subordinates,
        ]);
    }

    /**
     * Superior issues formal query to subordinate
     */
    public function store(Request $request)
    {
        $issuer = auth()->user();
        if (!$this->canUserIssueQuery($issuer)) {
            abort(403, 'Regular users and unit staff cannot issue queries. You can only respond to queries.');
        }

        $request->validate([
            'issued_to' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'incident_category' => 'required|string',
            'facts_description' => 'required|string',
            'deadline_hours' => 'required|integer|in:24,48,72',
            'evidence' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $recipient = User::findOrFail($request->issued_to);

        // Disallow self-querying
        if ($issuer->id === $recipient->id) {
            return redirect()->back()->withErrors(['issued_to' => 'You cannot issue a query to yourself.']);
        }

        // Subordinate check: Non-AGF/Admin cannot query superiors or peers
        $isAgfOrAdmin = $issuer->hasAnyRole(['Accountant-General', 'agf', 'super user', 'super admin', 'admin']);
        if (!$isAgfOrAdmin) {
            if ($recipient->hasAnyRole(['Accountant-General', 'agf', 'super user', 'super admin'])) {
                abort(403, 'Cannot issue query to the Accountant-General or executive administrator.');
            }
            if ($issuer->hasRole('director') && $recipient->hasRole('director')) {
                abort(403, 'A Director cannot issue a query to another Director.');
            }
            if ($issuer->hasRole('deputy director') && $recipient->hasAnyRole(['director', 'deputy director'])) {
                abort(403, 'A Deputy Director cannot issue a query to a Director or another Deputy Director.');
            }
            if ($issuer->hasRole('assistant director') && $recipient->hasAnyRole(['director', 'deputy director', 'assistant director'])) {
                abort(403, 'An Assistant Director can only issue queries to Unit staff.');
            }
        }

        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $fileName = 'qry_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $destination = public_path('query_files');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $fileName);
            $evidencePath = '/query_files/' . $fileName;
        }

        $year = date('Y');
        $randomSeq = strtoupper(Str::random(5));
        $ref = "OAGF/DISC/QRY/{$year}/{$randomSeq}";

        $deadlineHours = (int) $request->deadline_hours;
        $deadlineAt = Carbon::now()->addHours($deadlineHours);

        $query = StaffQuery::create([
            'query_ref' => $ref,
            'issued_by' => $issuer->id,
            'issued_to' => $recipient->id,
            'subject' => $request->subject,
            'incident_category' => $request->incident_category,
            'facts_description' => $request->facts_description,
            'evidence_path' => $evidencePath,
            'deadline_hours' => $deadlineHours,
            'deadline_at' => $deadlineAt,
            'verdict' => 'pending_response',
        ]);

        return redirect()->back()->with('success', "Formal Disciplinary Query {$ref} has been issued successfully with a {$deadlineHours}-hour response deadline.");
    }

    /**
     * Subordinate responds to query
     */
    public function respond(Request $request, $id)
    {
        $query = StaffQuery::findOrFail($id);

        if (auth()->id() !== $query->issued_to) {
            abort(403, 'Unauthorized to respond to this query.');
        }

        $request->validate([
            'response_text' => 'required|string',
            'response_evidence' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $responseEvidencePath = null;
        if ($request->hasFile('response_evidence')) {
            $file = $request->file('response_evidence');
            $fileName = 'resp_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $destination = public_path('query_files');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $fileName);
            $responseEvidencePath = '/query_files/' . $fileName;
        }

        $query->response_text = $request->response_text;
        $query->response_evidence_path = $responseEvidencePath;
        $query->responded_at = Carbon::now();
        $query->verdict = 'under_review';
        $query->save();

        return redirect()->back()->with('success', 'Your formal written response to the query has been submitted.');
    }

    /**
     * Superior decides on verdict
     */
    public function verdict(Request $request, $id)
    {
        $query = StaffQuery::findOrFail($id);
        $user = auth()->user();

        $canAdjudicate = ($user->id === $query->issued_by) || $user->hasAnyRole(['super user', 'super admin', 'admin', 'Accountant-General', 'director']);
        if (!$canAdjudicate) {
            abort(403, 'Unauthorized to adjudicate this query.');
        }

        $request->validate([
            'verdict' => 'required|in:accepted,cautioned,escalated_to_dc',
            'verdict_comment' => 'required|string',
        ]);

        $query->verdict = $request->verdict;
        $query->verdict_comment = $request->verdict_comment;
        $query->verdict_by = $user->id;
        $query->verdict_at = Carbon::now();
        $query->save();

        $verdictLabels = [
            'accepted' => 'Explanation Accepted (Exonerated)',
            'cautioned' => 'Official Letter of Advice / Caution Issued',
            'escalated_to_dc' => 'Escalated to Staff Disciplinary Committee / SERVICOM',
        ];

        return redirect()->back()->with('success', "Query verdict recorded: " . ($verdictLabels[$request->verdict] ?? $request->verdict));
    }

    /**
     * Get queries for PMS ViewProfile
     */
    public function getUserQueries(Request $request, $userId)
    {
        $queries = StaffQuery::with(['issuer', 'adjudicator'])
            ->where('issued_to', $userId)
            ->latest('id')
            ->get();

        return response()->json($queries);
    }
}
