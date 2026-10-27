<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\FinanceFiles;
use App\Models\Payment;
use App\Models\User;
use App\Models\Division;
use App\Models\Ledger;
use Spatie\Permission\Models\Role;
use App\Models\PaymentComment;
use App\Notifications\PaymentAssignedNotification;
use App\Events\UserNotificationEvent;
use App\Models\PaymentReceipt;
use App\Models\PaymentAssignment;
use App\Notifications\CommentNotification;
use App\Events\PaymentCommentEvent;
use App\Models\AdvancesLedger;
use App\Models\PaymentAuditQuery;
use App\Services\AdvancesManagementService;

class FandAController extends Controller
{
    public function payments(Request $request){
    //    $department = Department::where('title', 'Finance and Accounts')->first();
    //     if (!$department) {
    //         return redirect()->back()->with('message', 'Department not found');
    //     }
    //     if(auth()->user()->department_id != $department->id){
    //         return redirect()->back()->with('message', 'You are not authorized to access this section');
    //     }
        $payment = null;
        if($request->id){
            $payment = Payment::with(['files', 'recipient'])
                ->find($request->id);
        }
        return inertia('Payment/Finance', ['payment' => $payment]);
    }

    public function getRegistryFiles(){
        if(request('search')){
            $registryfiles = FinanceFiles::with('path')
            ->where('file_name', 'like', '%'.request('search').'%')
            ->latest()
            ->paginate(10)
            ->appends(['search' => request('search')]);
            return response()->json($registryfiles);
        }
        $registryfiles = FinanceFiles::with('path')->latest()->paginate(10);
        return response()->json($registryfiles);
    }

    public function paymentComment($request, $payment_id, $payment_assignment_id){
        $recipient = null;
        if(is_array($request->recipient)){
           $recipient = $request->recipient[0];
        } else {
            $recipient = $request->recipient;
        }
        if($request->comment){
            $request->merge(['comments' => $request->comment]);
        }


        $unique_id = $request->unique_id? $request->unique_id: uniqid();
      //  dd($request->all());
        $comment = new PaymentComment();
        $comment->comment = $request->comments;
        $comment->payment_id = $payment_id;
        $comment->recipient_id = $recipient;
        $comment->sender_id = auth()->user()->id;
        $comment->unique_id = $unique_id;
        $comment->payment_assignment_id = $payment_assignment_id;
        $comment->save();

        //send notification
        $user = User::find($recipient);
        $user->notify(new CommentNotification($request->comment, auth()->user()));
        //send a broadcast
       // broadcast(new PaymentCommentEvent($request->comments, $recipient))->toOthers();
    }

    public function makeComment(Request $request){
        $data = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'comment' => 'required|string',
        ]);
        
        $payment_assignment_id = $request->payment_assignment_id;
        $this->paymentComment($request, $data['payment_id'], $payment_assignment_id);

        return to_route('view-payment', $data['payment_id'])->with('message', 'Comment added successfully.');
    }
  
    public function initiatePayment(Request $request){ 
      $data =  $request->validate([
            'documents' => 'required',
            'title' => 'required',
            'dueDate' => 'required',
            'recipient' => 'required',
            'comments' => 'required',
        ]);
       
         DB::transaction(function () use ($request, $data) {
        try {
           $data['dueDate'] = date('Y-m-d H:i:s', strtotime($data['dueDate']));
            $pay = $request->payment_id? Payment::findOrFail($request->payment_id):new Payment();
            $pay->title = $data['title'];
            $pay->save();

             // Attach the files to the payment
               $doc = collect($data['documents']);
               $ids = $doc->pluck('id')->toArray();

               $recipient = User::findOrFail($data['recipient'][0]);
               $recipient_role = $recipient->roles->first()->name;
               $recipient_role_id = $recipient->roles->first()->id;
   
               $assignmentData = [
                    'due_date' => $data['dueDate'],
                    'recipient_id' => $recipient->id,
                    'status' => 'pending',
                    'comments' => $request->input('comments', ''),
                    'role' => $recipient_role,
                    'role_id' => $recipient_role_id,
                    'payment_id' => $pay->id,
                   // 'is_rejected' => 1,
                    'assigned_by' => auth()->user()->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            
            if($request->payment_id){
                $payment_assignment = DB::table('payment_assignment')
                    ->where('recipient_id', $recipient->id)
                    ->where('role_id', $recipient_role_id)
                    ->first();
                $payment_assignment_id = $payment_assignment->id;

                    $payment_assignment->update($assignmentData);
                $pay->files()->sync($ids);

                //update payment
                $payment = Payment::findOrFail($request->payment_id);
                $payment->status = 'due';
                $payment->save();
            } else {
                $payment_assignment_id = DB::table('payment_assignment')->insertGetId($assignmentData);
                $pay->files()->attach($ids);
            }

            //insert the comment
            $this->paymentComment($request, $pay->id, $payment_assignment_id);

            //notify user
            $recipient->notify(new PaymentAssignedNotification($pay->comments, 'A new payment has been assigned to you by '.$recipient->first_name." ".$recipient->last_name, $recipient));
            broadcast(new UserNotificationEvent('A new payment has been assigned to you by '.$recipient->first_name." ".$recipient->last_name, $recipient->id))->toOthers();

            return to_route('f-and-a');
        } catch (\Exception $e) {
            dd($e);
             //dd($e->getMessage());
             return redirect()->back()->withErrors(['error' => 'Failed to initiate payment '.$e->getMessage()]);
            //return redirect()->back()->with('error', 'Failed to initiate payment: ' . $e->getMessage());
        }
    });
        //return response()->json(['message' => 'Payment initiated successfully.']);
    }

    public function getDivisionHeads()
    {   
        $deputyDirectors = User::where('department_id', 3)
            ->where(function($q) {
                $q->where('division_id', 5)
                  ->orWhereHas('roles', function($r) {
                      $r->where('name', 'deputy director');
                  });
            })
            ->select('id','first_name', 'last_name', 'email','phone', 'gender', 'department_id', 'division_id')
            ->get();

        if ($deputyDirectors->isEmpty()) {
            $deputyDirectors = User::role('deputy director')
                ->select('id','first_name', 'last_name', 'email','phone', 'gender', 'department_id', 'division_id')
                ->get();
        }

        return response()->json($deputyDirectors);
    }

    public function getUnitHeads()
    {
        $unitHeads = User::where('department_id', 3)
            ->where(function ($query) {
                $query->where('is_unit_head', 1)
                    ->orWhere('unit_id', 61)
                    ->orWhereHas('roles', function ($r) {
                        $r->where('name', 'assistant director');
                    });
            })
            ->with('unit')
            ->select('id', 'first_name', 'last_name', 'email', 'phone', 'gender', 'department_id', 'division_id', 'unit_id')
            ->get();

        if ($unitHeads->isEmpty()) {
            $unitHeads = User::role('assistant director')
                ->with('unit')
                ->select('id', 'first_name', 'last_name', 'email', 'phone', 'gender', 'department_id', 'division_id', 'unit_id')
                ->get();
        }

        return response()->json($unitHeads);
    }

    public function getActivePayments(Request $request)
    {
        $payments = Payment::query();
        $user_id = auth()->user()->id;
        if(auth()->user()->hasRole('director')){
           $payments = $payments->with(['files'=>function($query) use($user_id){
            $query->with('path');
           }, 'recipient', 'assignedTo' => function($query) use($user_id) {
                $query->with('user')->where('assigned_by', $user_id);
            }])
            ->where(function ($query) {
                $query->where('status', 'due')
                      ->orWhere('status', 'overdue')
                      ->orWhere('status', 'reverted');
            });
            if($request->search) {
                $payments = $payments->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('status', $request->search);
            }
            $payments = $payments->latest()
            ->paginate(10);
        } else {
            $id = auth()->user()->id;
             $payments = $payments->with(['files', 'assignedBy'=>function($query){
                $query->with(['user'])->where('recipient_id', auth()->user()->id);
             }, 'assignedTo'=> function($query) {
                    $query->with('user')->where('assigned_by', auth()->user()->id);
                }])
                ->where(function ($query) {
                    $query->where('status', 'due')
                        ->orWhere('status', 'overdue')
                        ->orWhere('status', 'reverted');
                })
                ->whereHas('assignedTo', function ($q) use ($id) {
                    $q->where('recipient_id', $id);
                       // ->where('status', 'pending'); 
                });

                if($request->search) {
                    $payments = $payments->where('title', 'like', '%' . $request->search . '%')
                        ->orWhere('status', $request->search);
                }
                $payments = $payments->latest()
                ->paginate(10);
        }

        return response()->json($payments);
    }

    public function viewPayment($id)
    {
        // $comment = PaymentComment::where(function($q)use($id){
        //     $q->where('payment_id',  $id)
        //     ->orWhere('sender_id', auth()->user()->id)
        //       ->orWhere('recipient_id', auth()->user()->id);
        //      })
        //     ->latest()
        //     ->first();
       // dd($comment);
       
       //mark notification as read
       if(request()->notification){
        $notification = auth()->user()->notifications()->where('id', request()->notification)->first();
        if($notification){
            $notification->markAsRead();
        }
       }

       $parent_unique_id = PaymentComment::where('payment_id', $id)
                ->where('recipient_id', auth()->user()->id)
                ->first();
        $parent_unique_id = isset($parent_unique_id->unique_id)? $parent_unique_id->unique_id: [''];

        $my_unique_id = PaymentComment::where('payment_id', $id)
                ->where('sender_id', auth()->user()->id)
                ->first();
        $my_unique_id = isset($my_unique_id->unique_id)? $my_unique_id->unique_id: [''];
        

        $user = auth()->user();
        $isPrivileged = $user->hasRole('director') || $user->hasRole('super admin') || $user->hasRole('admin') || ($user->department_id == 3) || $user->can('payment view');

        $query = Payment::with([
            'vatVoucher',
            'Receipt',
            'files' => function($q) { $q->with('path'); },
            'voucher.checkingOfficer',
            'voucher.auditOfficer',
            'recipient',
            'advancesLedger.retirements',
            'auditQueries.auditor',
            'auditQueries.respondent',
            'currentSection',
            'assignedTo.user',
            'assignedTo.comments.sender',
            'assignedTo.comments.receiver',
            'assignedBy.user',
            'assignedBy.comments.sender',
            'assignedBy.comments.receiver',
            'comments.sender',
            'comments.receiver'
        ])->withCount('ledger');

        if (!$isPrivileged) {
            $query->where(function($q) use ($user) {
                $q->whereHas('assignedTo', function($sub) use ($user) {
                    $sub->where('recipient_id', $user->id);
                })->orWhere('recipient_id', $user->id);
            });
        }

        $payment = $query->find($id);

        if (!$payment) {
            return redirect()->route('f-and-a')->with('error', 'The requested payment was not found or you do not have permission to access it.');
        }

        $ebewe_instance = \Illuminate\Support\Facades\DB::table('ebewe_instances')
            ->where('document_type', 'App\\Models\\Payment')
            ->where('document_id', $payment->id)
            ->first();
            
        $ebewe_stamps = [];
        if ($ebewe_instance) {
            $ebewe_stamps = \Illuminate\Support\Facades\DB::table('ebewe_stamps')
                ->where('instance_id', $ebewe_instance->id)
                ->get();
        }

        return inertia('Payment/ViewPayment', [
            'payment' => $payment,
            'ebewe_instance' => $ebewe_instance,
            'ebewe_stamps' => $ebewe_stamps
        ]);
    }

    public function rejectPayment(Request $request)
    {
       
        $data = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'reasonForRejection' => 'required|string',
            'recipient_id' => 'required',
            'assigned_by' => 'required',
        ]);

        if(auth()->user()->hasRole('deputy director')){
            $payment = Payment::findOrFail($data['payment_id']);
            $payment->status = 'reverted';
            $payment->save();
        }

        if(auth()->user()->hasRole('internal audit(HOS)')){
            DB::transaction(function() use($data){
                 $payment = Payment::findOrFail($data['payment_id']);
                $payment->status = 'reverted';
                $payment->save();

                // DB::table('payment_assignment')
                //     ->where('payment_id', $data['payment_id'])
                //     ->update(['status'=>'rejected', 'is_rejected'=>1]);
            });
        }

        DB::table('payment_assignment')
        ->where(function ($query) use ($data) {
            $query->where('recipient_id', auth()->user()->id)
                ->where('payment_id', $data['payment_id'])
                ->where('assigned_by', $data['assigned_by']);
        })
        ->orWhere(function ($query) use ($data) {
            $query->where('recipient_id', $data['assigned_by'])
             ->where('payment_id', $data['payment_id']);
        })
        ->update(['status' => 'rejected']);

        //update the is_rejected
        DB::table('payment_assignment')
            ->where('recipient_id', $data['assigned_by'])
             ->where('payment_id', $data['payment_id'])
             ->update(['is_rejected'=>1]);

            return to_route('view-payment',$data['payment_id'])->with('message', 'Payment rejected successfully.');
          
    }

    /**
     * Unified EBEWE Routing Handler for all specific assignments
     */
    private function processEbeweRouting(Request $request, $actionStr)
    {
        $data = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'dueDate' => 'required|date',
            'recipient' => 'required',
            'generalComment' => 'nullable|string',
        ]);

        $recipient_id = is_array($data['recipient']) ? $data['recipient'][0] : $data['recipient'];

        try {
            $ebewe = new \App\Services\EbeweEngine();
            // In a fully migrated system, we'd pass the actual instance ID. 
            // For now, we find the EBEWE instance mapped to this payment.
            $instance = \Illuminate\Support\Facades\DB::table('ebewe_instances')
                ->where('document_type', 'App\\Models\\Payment')
                ->where('document_id', $data['payment_id'])
                ->first();

            if (!$instance) {
                // Fallback for legacy payments not migrated or freshly created
                throw new \Exception("EBEWE Instance not found for this payment.");
            }

            $ebewe->advance($instance->id, auth()->user(), $data['generalComment'] ?? '', $recipient_id);

            // Trigger notifications (using legacy logic)
            $recipient = User::find($recipient_id);
            if ($recipient) {
                $recipient->notify(new PaymentAssignedNotification($data['generalComment'] ?? '', $recipient));
                broadcast(new UserNotificationEvent('A new payment has been assigned to you via EBEWE.', $recipient->id))->toOthers();
            }

            return to_route('view-payment', $data['payment_id'])->with('message', 'Payment routed successfully via EBEWE Engine.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'EBEWE Routing Error: ' . $e->getMessage()]);
        }
    }

    public function acceptDivisionPayment(Request $request)
    {
        return $this->processEbeweRouting($request, 'Division Accepted');
    }

    public function assignToAdvanceOtherSection(Request $request)
    {
        return $this->processEbeweRouting($request, 'Assigned to Advance/Other');
    }

    public function getAdvancedSectionAndOtherChargesUsers(){
        $other_charges_hos = User::where('section_id', 81)->where('is_section_head', 1)->first() 
            ?? User::where('section_id', 81)->first()
            ?? User::role('other charges(HOS)')->first();

        $advance_section_hos = User::where('section_id', 82)->where('is_section_head', 1)->first() 
            ?? User::where('section_id', 82)->first()
            ?? User::role('advance section(HOS)')->first();

        return response()->json([
            'other_charges_hos' => $other_charges_hos,
            'advance_section_hos' => $advance_section_hos,
        ]);
    }

    public function getAdvanceSectionUsers(){
        $users = User::where('section_id', 82)->select('id','first_name', 'last_name', 'email', 'phone')->get();
        if ($users->isEmpty()) {
            $users = User::role('advance section')->select('id','first_name', 'last_name')->get();
        }
        return response()->json($users);
    }

    public function getOtherChargesUsers(){
        $users = User::where('section_id', 81)->select('id','first_name', 'last_name', 'email', 'phone')->get();
        if ($users->isEmpty()) {
            $users = User::role('other charges')->select('id','first_name', 'last_name')->get();
        }
        return response()->json($users);
    }

    public function getCheckingUsers(){
        $users = User::where('section_id', 83)->select('id','first_name', 'last_name', 'email', 'phone')->get();
        if ($users->isEmpty()) {
            $users = User::role('checking')->select('id','first_name', 'last_name')->get();
        }
        return response()->json($users);
    }

    public function getOtherChargesHOS(){
        $user = User::where('section_id', 81)->where('is_section_head', 1)->first()
            ?? User::where('section_id', 81)->first()
            ?? User::role('other charges(HOS)')->first();
        return response()->json($user);
    }

    public function getInternalAuditUsers(){
        $users = User::where('section_id', 84)->select('id','first_name', 'last_name', 'email', 'phone')->get();
        return response()->json($users);
    }

    public function getCashOfficeUsers(){
        $users = User::where('section_id', 85)->select('id','first_name', 'last_name', 'email', 'phone')->get();
        return response()->json($users);
    }

    public function getFinalAccountsUsers(){
        $users = User::where('unit_id', 11)->select('id','first_name', 'last_name', 'email', 'phone')->get();
        return response()->json($users);
    }

    public function getSectionHeads(){
        $heads = [
            'other_charges' => User::where('section_id', 81)->where('is_section_head', 1)->first() ?? User::where('section_id', 81)->first(),
            'advances' => User::where('section_id', 82)->where('is_section_head', 1)->first() ?? User::where('section_id', 82)->first(),
            'checking' => User::where('section_id', 83)->where('is_section_head', 1)->first() ?? User::where('section_id', 83)->first(),
            'internal_audit' => User::where('section_id', 84)->where('is_section_head', 1)->first() ?? User::where('section_id', 84)->first(),
            'cash_office' => User::where('section_id', 85)->where('is_section_head', 1)->first() ?? User::where('section_id', 85)->first(),
            'final_accounts' => User::where('unit_id', 11)->where('is_unit_head', 1)->first() ?? User::where('unit_id', 11)->first(),
        ];
        return response()->json($heads);
    }

    /**
     * Assistant Director Classification & Routing (Task 2.3)
     * Classifies payment into Advances (Track 1) or Capital/Overhead (Track 2)
     * Enforces FR 1420 guard on Advances
     */
    public function classifyPayment(Request $request)
    {
        $data = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'payment_type' => 'required|in:advances,capital,overhead',
            'comments' => 'nullable|string',
            'recipient_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date'
        ]);

        $payment = Payment::findOrFail($data['payment_id']);
        $payment->payment_type = $data['payment_type'];

        // Determine destination section
        // Track 1: Advances -> Section ID 82
        // Track 2: Capital / Overhead -> Section ID 81
        $targetSectionId = ($data['payment_type'] === 'advances') ? 82 : 81;
        $payment->current_section_id = $targetSectionId;

        // If recipient was passed, or fallback to section head
        $sectionHead = User::where('section_id', $targetSectionId)
            ->where('is_section_head', 1)
            ->first() ?? User::where('section_id', $targetSectionId)->first();

        $targetUserId = $data['recipient_id'] ?? ($sectionHead ? $sectionHead->id : null);

        // If classifying as Advances, enforce FR 1420 guard
        if ($data['payment_type'] === 'advances') {
            $advService = new AdvancesManagementService();
            $beneficiaryId = $payment->recipient_id ?? $targetUserId;
            if ($beneficiaryId) {
                $frCheck = $advService->checkFR1420Compliance($beneficiaryId);
                if (!$frCheck['allowed'] && !$request->boolean('bypass_fr1420')) {
                    return back()->withErrors(['fr1420' => $frCheck['message']]);
                }
            }
        }

        $payment->save();

        // Create assignment record to the section officer
        if ($targetUserId) {
            $senderId = auth()->id() ?? 1;
            $assignmentId = DB::table('payment_assignment')->insertGetId([
                'payment_id' => $payment->id,
                'recipient_id' => $targetUserId,
                'assigned_by' => $senderId,
                'role' => ($targetSectionId === 82) ? 'advances_head' : 'other_charges_head',
                'status' => 'pending',
                'comments' => $data['comments'] ?? 'Classified as ' . strtoupper($data['payment_type']) . ' payment.',
                'due_date' => $data['due_date'] ?? now()->addDays(3),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Save comment
            $comment = new PaymentComment();
            $comment->comment = "Classified as " . strtoupper($data['payment_type']) . " by Assistant Director. Directed to Section Head.";
            $comment->payment_id = $payment->id;
            $comment->recipient_id = $targetUserId;
            $comment->sender_id = $senderId;
            $comment->payment_assignment_id = $assignmentId;
            $comment->unique_id = uniqid();
            $comment->save();
        }

        return to_route('view-payment', $payment->id)->with('message', 'Payment classified as ' . strtoupper($data['payment_type']) . ' and dispatched to Section Head.');
    }

    /**
     * FR 1420 Compliance Check Endpoint
     */
    public function checkFR1420($userId)
    {
        $advService = new AdvancesManagementService();
        return response()->json($advService->checkFR1420Compliance((int)$userId));
    }

    /**
     * Get Advances Subsidiary Ledger Records
     */
    public function getAdvancesLedger(Request $request)
    {
        $query = AdvancesLedger::with(['beneficiary', 'creator', 'retirements', 'payment']);
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('purpose', 'like', "%{$request->search}%")
                  ->orWhere('destination', 'like', "%{$request->search}%")
                  ->orWhereHas('beneficiary', function($b) use ($request) {
                      $b->where('first_name', 'like', "%{$request->search}%")
                        ->orWhere('last_name', 'like', "%{$request->search}%");
                  });
            });
        }
        return response()->json($query->latest()->paginate(15));
    }

    /**
     * Process Advance Retirement
     */
    public function retireAdvance(Request $request)
    {
        $advancesLedgerId = $request->advances_ledger_id;
        if (!$advancesLedgerId && $request->payment_id) {
            $ledger = AdvancesLedger::where('payment_id', $request->payment_id)->latest()->first();
            $advancesLedgerId = $ledger?->id;
        }

        if (!$advancesLedgerId) {
            return back()->withErrors(['advances_ledger_id' => 'Active Advances Ledger entry not found for this payment.']);
        }

        $data = $request->validate([
            'total_expended' => 'required|numeric|min:0',
            'refund_amount' => 'nullable|numeric|min:0',
            'refund_rrr' => 'nullable|string',
            'retirement_voucher_no' => 'nullable|string',
            'status' => 'nullable|string'
        ]);

        $advService = new AdvancesManagementService();
        $retirement = $advService->processRetirement($advancesLedgerId, $data, auth()->user()->id);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Advance retirement submitted successfully (Retirement Ref: ' . $retirement->retirement_voucher_no . ').',
                'data' => $retirement
            ]);
        }

        return back()->with('message', 'Advance retirement submitted successfully (Retirement Ref: ' . $retirement->retirement_voucher_no . ').');
    }

    public function assignToAdvanceSection(Request $request)
    {
        return $this->processEbeweRouting($request, 'Assigned to Advance');
    }

    public function assignToChecking(Request $request)
    {
        return $this->processEbeweRouting($request, 'Assigned to Checking');
    }

    public function assignToOtherChargesByHOS(Request $request)
    {
        return $this->processEbeweRouting($request, 'Assigned to Other Charges');
    }

    public function assignToSectionUsers(Request $request)
    {
        return $this->processEbeweRouting($request, 'Assigned to Section');
    }

    // Unified Submit Task
    private function processEbeweSubmit(Request $request)
    {
        try {
            $ebewe = new \App\Services\EbeweEngine();
            $instance = \Illuminate\Support\Facades\DB::table('ebewe_instances')
                ->where('document_type', 'App\\Models\\Payment')
                ->where('document_id', $request->payment_id)
                ->first();

            if ($instance) {
                // In a purely automated map, advance goes to the next configured target
                // For a raw submit, we just let EBEWE auto-resolve next target
                $ebewe->advance($instance->id, auth()->user(), 'Task Submitted');
            }
            return to_route('view-payment', $request->payment_id);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'EBEWE Submission Error: ' . $e->getMessage()]);
        }
    }

    public function advanceTaskSubmit(Request $request) { return $this->processEbeweSubmit($request); }
    public function otherChargesTaskSubmit(Request $request) { return $this->processEbeweSubmit($request); }
    public function submitTask(Request $request) { return $this->processEbeweSubmit($request); }

    public function getSectionUsers(Request $request){
        if(strpos($request->role, '(HOS)') !== false){
            $user = User::role($request->role)->select('id','first_name', 'last_name')->first();
        } else {
            $user = User::role($request->role)->select('id','first_name', 'last_name')->get();
        }
        
        return response()->json($user);
    }

    public function getPaymentAssignments(Request $request){
        $assignments = DB::table('payment_assignment')
            ->where('payment_id', $request->payment_id)
            ->get();
        return response()->json($assignments);
    }

    public  function formatSizeUnits($bytes)
    {
                if ($bytes >= 1073741824) {
                    $size = number_format($bytes / 1073741824, 2) . ' GB';
                } elseif ($bytes >= 1048576) {
                    $size = number_format($bytes / 1048576, 2) . ' MB';
                } elseif ($bytes >= 1024) {
                    $size = number_format($bytes / 1024, 2) . ' KB';
                } elseif ($bytes > 1) {
                    $size = $bytes . ' bytes';
                } elseif ($bytes == 1) {
                    $size = $bytes . ' byte';
                } else {
                    $size = '0 bytes';
                }
                return $size;
    }

    public function paymentReceiptUpload(Request $request){
        $data = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf', // max 2MB
        ]);

        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('receipts', $filename, 'public');
            $fileSize = $file->getSize(); // size in bytes
            // Format file size to human readable (e.g., 20 KB, 20 MB)
           
            $fileSizeFormatted = $this->formatSizeUnits($fileSize);
            // You can use $fileSizeFormatted as needed (e.g., save to DB, return in response, etc.)
            // Save the file path to the payment record
            $payment = new PaymentReceipt();
            $payment->payment_id = $data['payment_id'];
            $payment->path = $filePath;
            $payment->size = $fileSizeFormatted;
            $payment->uploaded_by = auth()->user()->id;
            $payment->save();

            return to_route('view-payment', $data['payment_id'])->with('message', 'Receipt uploaded successfully.');
        }

        return redirect()->back()->withErrors(['error' => 'Failed to upload receipt.']);
    }

    public function removeUploadedReceipt(Request $request){

        $receipt = PaymentReceipt::findOrFail($request->id);
        // Delete the file from storage
        if (Storage::disk('public')->exists($receipt->path)) {
            Storage::disk('public')->delete($receipt->path);
        }
        // Delete the record from the database
        $receipt->delete();

        return to_route('view-payment', $request->payment_id)->with('message', 'Receipt removed successfully.');
    }   

    public function finalisePayment(Request $request){
        $data = $request->validate([
            'payment_id' => 'required|exists:payments,id',
        ]);

        $payment = Payment::findOrFail($data['payment_id']);
        $payment->status = 'paid';
        $payment->save();

        return to_route('view-payment', $data['payment_id'])->with('message', 'Payment finalized successfully.');
    }

     public function getResolvedPayments(Request $request)
    {
        $payments = Payment::query();
       // if(auth()->user()->hasRole('director')){
           $payments = $payments->with(['files'=>function($query){
            $query->with('path');
           }, 'recipient', 'assignedTo' => function($query) {
                $query->with('user')->where('assigned_by', auth()->user()->id);
            }])
            ->where(function ($query) {
                $query->where('status', 'paid');
            });

            if($request->search) {
                $payments = $payments->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('status', $request->search);
            }

            $payments = $payments->latest()
            ->paginate(10);
       // }

        return response()->json($payments);
    }

    public function reSubmitPayment(Request $request){
        $request->validate([
            'assigned_by' => 'required',
            'recipient' => 'required',
        ]);

        DB::transaction(function() use($request){
             //change the status of the assigned to user 
            PaymentAssignment::where('recipient_id', $request->recipient)
                ->where('assigned_by', $request->assigned_by)
                ->update(['status'=>'pending', 'is_rejected' => 0]);

             //change the parent assigned by status
            PaymentAssignment::where('recipient_id', $request->assigned_by)
                ->where('assigned_by', $request->parent_assigned_by)
                ->update(['is_rejected'=>0, 'status'=>'pending',]);

            return redirect()->back();
        });
    }

    public function affixDigitalStamp(Request $request)
    {
        $data = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'stamp_type' => 'required|string',
            'label' => 'required|string',
            'color' => 'required|string',
            'comments' => 'nullable|string'
        ]);

        $instance = \Illuminate\Support\Facades\DB::table('ebewe_instances')
            ->where('document_type', 'App\\Models\\Payment')
            ->where('document_id', $data['payment_id'])
            ->first();

        if (!$instance) {
            return redirect()->back()->withErrors(['error' => 'No active EBEWE instance for this document.']);
        }

        // Save Stamp
        \Illuminate\Support\Facades\DB::table('ebewe_stamps')->insert([
            'instance_id' => $instance->id,
            'stage_id' => $instance->current_stage_id,
            'applied_by' => auth()->user()->id,
            'stamp_type' => $data['stamp_type'],
            'label' => $data['label'],
            'color' => $data['color'],
            'design_pattern' => 'circular',
            'comments' => $data['comments'],
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Auto-advance EBEWE logic after stamping
        try {
            $ebewe = new \App\Services\EbeweEngine();
            $ebewe->advance($instance->id, auth()->user(), 'Digital Stamp Affixed: ' . $data['label']);
        } catch (\Exception $e) {
            // Ignore advance errors for manual routings
        }

        return redirect()->back()->with('message', 'Digital Stamp Affixed Successfully!');
    }
}
