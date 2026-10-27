<?php

namespace App\Http\Controllers;

use App\Models\Documentation;
use App\Models\Faq;
use App\Models\SupportInfo;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\AuditTrailService;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SupportController extends Controller
{
    /**
     * Render the unified Support & Helpdesk Hub.
     */
    public function support(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user && ($user->hasRole('super admin') 
            || $user->hasRole('admin') 
            || $user->hasRole('super user') 
            || $user->hasRole('Accountant-General')
            || $user->can('support dispatch'));

        // Knowledge Base FAQs & Documentation
        $faqs = Faq::latest('id')->get();
        $documentations = Documentation::latest('id')->get();
        $supportInfo = SupportInfo::first();

        // My submitted tickets
        $myTickets = SupportTicket::where('user_id', $user->id)
            ->with(['assignedTo'])
            ->latest('id')
            ->get();

        // Admin Helpdesk Queue & Triage Data
        $adminTickets = [];
        if ($isAdmin) {
            $ticketQuery = SupportTicket::with(['user.department', 'assignedTo']);

            if ($request->filled('ticket_status') && $request->ticket_status !== 'all') {
                $ticketQuery->where('status', $request->ticket_status);
            }
            if ($request->filled('ticket_priority') && $request->ticket_priority !== 'all') {
                $ticketQuery->where('priority', $request->ticket_priority);
            }
            if ($request->filled('ticket_category') && $request->ticket_category !== 'all') {
                $ticketQuery->where('category', $request->ticket_category);
            }
            if ($request->filled('ticket_search')) {
                $s = $request->ticket_search;
                $ticketQuery->where(function ($q) use ($s) {
                    $q->where('ticket_no', 'LIKE', "%{$s}%")
                      ->orWhere('subject', 'LIKE', "%{$s}%")
                      ->orWhere('description', 'LIKE', "%{$s}%")
                      ->orWhereHas('user', function ($uq) use ($s) {
                          $uq->where('first_name', 'LIKE', "%{$s}%")
                             ->orWhere('last_name', 'LIKE', "%{$s}%")
                             ->orWhere('email', 'LIKE', "%{$s}%");
                      });
                });
            }

            $adminTickets = $ticketQuery->latest('id')->get();
        }

        // Helpdesk Telemetry & Stats
        $ticketStats = [
            'open' => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'resolved' => SupportTicket::where('status', 'resolved')->count(),
            'total' => SupportTicket::count(),
            'my_open' => SupportTicket::where('user_id', $user->id)->whereIn('status', ['open', 'in_progress'])->count(),
            'my_total' => $myTickets->count(),
        ];

        // IT Officers available for ticket assignment
        $itStaff = $isAdmin 
            ? User::where(function($query) {
                $query->whereHas('roles', function($q) {
                    $q->whereIn('name', ['super admin', 'admin', 'super user']);
                })->orWhereHas('permissions', function($q) {
                    $q->where('name', 'support dispatch');
                });
            })->select('id', 'first_name', 'last_name', 'email')->distinct()->get()
            : [];

        return Inertia::render('Support/Support', [
            'faqs' => $faqs,
            'documentations' => $documentations,
            'supportInfo' => $supportInfo,
            'myTickets' => $myTickets,
            'adminTickets' => $adminTickets,
            'ticketStats' => $ticketStats,
            'itStaff' => $itStaff,
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Submit an internal support ticket with optional attachment.
     */
    public function storeTicket(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:64',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'attachment' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $user = auth()->user();

        // Generate sequential unique ticket reference: TKT-YYYYMM-XXXX
        $countToday = SupportTicket::whereDate('created_at', Carbon::today())->count() + 1;
        $ticketNo = 'TKT-' . date('Ymd') . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $dir = public_path('documents/support');
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0777, true, true);
            }
            $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $attachmentName);
            $file->move($dir, $fileName);
            $attachmentPath = '/documents/support/' . $fileName;
        }

        $ticket = SupportTicket::create([
            'ticket_no' => $ticketNo,
            'user_id' => $user->id,
            'category' => $request->category,
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => $request->priority ?: 'medium',
            'status' => 'open',
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        AuditTrailService::logRecord(
            'support_ticket_created',
            "Support Ticket #{$ticket->ticket_no} submitted by {$user->first_name} {$user->last_name}: '{$ticket->subject}'",
            $ticket,
            $user,
            [
                'ticket_no' => $ticket->ticket_no,
                'category' => $ticket->category,
                'priority' => $ticket->priority,
            ],
            'notice'
        );

        return redirect()->back()->with('success', "Support ticket {$ticket->ticket_no} submitted successfully. Our IT helpdesk will investigate shortly.");
    }

    /**
     * Triage, assign, or resolve a support ticket (Admin only).
     */
    public function updateTicketStatus(Request $request, $id)
    {
        $user = auth()->user();
        $isAdmin = $user && ($user->hasRole('super admin') || $user->hasRole('admin') || $user->hasRole('super user') || $user->can('support dispatch'));

        if (!$isAdmin) {
            return redirect()->back()->withErrors(['error' => 'Unauthorized: Only IT administrators or dispatchers can update ticket status.']);
        }

        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'assigned_to' => 'nullable|exists:users,id',
            'resolution_notes' => 'nullable|string',
        ]);

        $ticket = SupportTicket::findOrFail($id);
        $oldStatus = $ticket->status;

        $ticket->status = $request->status;
        if ($request->filled('assigned_to')) {
            $ticket->assigned_to = $request->assigned_to;
        }
        if ($request->filled('resolution_notes')) {
            $ticket->resolution_notes = $request->resolution_notes;
        }
        if ($request->status === 'resolved' && !$ticket->resolved_at) {
            $ticket->resolved_at = now();
        }

        $ticket->save();

        AuditTrailService::logRecord(
            'support_ticket_updated',
            "Support Ticket #{$ticket->ticket_no} updated to '{$ticket->status}' by {$user->first_name} {$user->last_name}",
            $ticket,
            $user,
            [
                'ticket_no' => $ticket->ticket_no,
                'old_status' => $oldStatus,
                'new_status' => $ticket->status,
                'assigned_to' => $ticket->assigned_to,
            ],
            'routine'
        );

        return redirect()->back()->with('success', "Ticket {$ticket->ticket_no} status updated to " . ucfirst(str_replace('_', ' ', $ticket->status)) . ".");
    }

    /**
     * Update FAQ item (Admin only).
     */
    public function updateFaq(Request $request, $id)
    {
        $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'category' => 'nullable|string|max:64',
        ]);

        $faq = Faq::findOrFail($id);
        $faq->update([
            'question' => $request->question,
            'answer' => $request->answer,
            'category' => $request->category ?: ($faq->category ?: 'General'),
        ]);

        return redirect()->back()->with('success', 'FAQ updated successfully!');
    }

    /**
     * Delete FAQ item (Admin only).
     */
    public function deleteFaq($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();

        return redirect()->back()->with('success', 'FAQ deleted successfully!');
    }

    /**
     * Update Documentation item (Admin only).
     */
    public function updateDoc(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'link' => 'required|url',
            'category' => 'nullable|string|max:64',
        ]);

        $doc = Documentation::findOrFail($id);
        $doc->update([
            'name' => $request->name,
            'link' => $request->link,
            'category' => $request->category ?: ($doc->category ?: 'Guides & SOPs'),
        ]);

        return redirect()->back()->with('success', 'Documentation updated successfully!');
    }

    /**
     * Delete Documentation item (Admin only).
     */
    public function deleteDoc($id)
    {
        $doc = Documentation::findOrFail($id);
        $doc->delete();

        return redirect()->back()->with('success', 'Documentation link deleted successfully!');
    }

    /**
     * Configure organization support details.
     */
    public function sendSupportDetails(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone_number' => 'required|string',
            'location' => 'required|string',
            'start_day' => 'required|string',
            'end_day' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $startTime = str_contains($request->start_time, ' ') 
            ? Carbon::createFromFormat('h:i A', $request->start_time)->format('H:i:s')
            : $request->start_time;

        $endTime = str_contains($request->end_time, ' ')
            ? Carbon::createFromFormat('h:i A', $request->end_time)->format('H:i:s')
            : $request->end_time;

        $supportInfo = SupportInfo::first();
        if ($supportInfo) {
            $supportInfo->update([
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'location' => $request->location,
                'start_day' => $request->start_day,
                'end_day' => $request->end_day,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
        } else {
            SupportInfo::create([
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'location' => $request->location,
                'start_day' => $request->start_day,
                'end_day' => $request->end_day,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
        }

        return redirect()->back()->with('success', 'Support details saved successfully!');
    }

    public function getSupportDetails()
    {
        $supportInfo = SupportInfo::first();
        return response()->json([
            'message' => $supportInfo ? 'Support details retrieved successfully' : 'No support details found',
            'data' => $supportInfo
        ], $supportInfo ? 200 : 404);
    }

    public function SaveFaqs(Request $request)
    {
        $request->validate([
            'faqs.*.question' => 'required|string',
            'faqs.*.answer' => 'required|string',
            'faqs.*.category' => 'nullable|string',
        ]);

        foreach ($request->input('faqs', []) as $faqData) {
            Faq::create([
                'category' => $faqData['category'] ?? 'General',
                'question' => $faqData['question'],
                'answer' => $faqData['answer'],
            ]);
        }

        return redirect()->back()->with('success', 'FAQs saved successfully!');
    }

    public function getFaqs()
    {
        return response()->json(Faq::all());
    }

    public function saveDocumentation(Request $request)
    {
        $request->validate([
            'docs.*.name' => 'required|string',
            'docs.*.link' => 'required|url',
            'docs.*.category' => 'nullable|string',
        ]);

        foreach ($request->input('docs', []) as $docData) {
            Documentation::create([
                'category' => $docData['category'] ?? 'Guides & SOPs',
                'name' => $docData['name'],
                'link' => $docData['link'],
            ]);
        }

        return redirect()->back()->with('success', 'Documentation saved successfully!');
    }

    public function getDocumentations()
    {
        return response()->json(Documentation::all());
    }
}
