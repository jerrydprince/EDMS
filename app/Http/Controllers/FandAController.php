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
use Illuminate\Support\Str;
use App\Notifications\CommentNotification;
use App\Events\PaymentCommentEvent;

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
                ->findOrFail($request->id);
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
        $department = Department::where('title', 'Finance and Accounts')->first();
   
        if (!$department) {
            return redirect()->back()->with('message', 'Department not found');
        }
        $deputyDirectors = User::role('division heads') // Spatie method
        ->where('department_id', $department->id)
        ->select('id','first_name', 'last_name', 'email','phone', 'gender', 'department_id', 'division_id')
        ->get();

        return response()->json($deputyDirectors);
    }

    public function getUnitHeads()
    {   
        // $division = Division::where('title', 'Finance and Accounts')->first();
   
        // if (!$division) {
        //     return redirect()->back()->withErrors(['message'=> 'Division not found']);
        // }
        // $assistantDirector = User::with('unit')->role('unit heads') // Spatie method
        // ->where('division_id', $division->id)
        // ->select('id','first_name', 'last_name', 'email','phone', 'gender', 'department_id', 'division_id')
        // ->get();
    $department = Department::where('title', 'Finance and Accounts')
            ->with('division.unit.users')
            ->first();

        if (!$department) {
            return redirect()->back()->with('message', 'Department not found');
        }

        // collect all users from all divisions & units in this department
        $users = $department->division
            ->flatMap(function ($division) {
                return $division->unit->flatMap(function ($unit) {
                    return $unit->users()
                        ->role('unit heads') // filter by role
                        ->with('unit')       // eager load unit
                        ->select('id','first_name','last_name','email','phone','gender','department_id','division_id', 'unit_id')
                        ->get();
                });
            });

        if ($users->isEmpty()) {
        return redirect()->back()->with('message', 'No unit heads found in this department');
    }

    return response()->json($users);

       // return response()->json($assistantDirector);
    }

    public function getActivePayments(Request $request)
    {
        $payments = Payment::query();
        $user_id = auth()->user()->id;
        if(auth()->user()->hasRole('directors')){
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
        

        if(auth()->user()->hasRole('directors')){
            $payment = Payment::with(['vatVoucher', 'Receipt','files'=>function($query){
                $query->with('path');
            }, 'voucher', 'recipient', 'assignedTo' => function($query) {
                $query->with(['user','comments'=>function($q){
                    $q->with('sender', 'receiver')->latest();
                }])->where('assigned_by', auth()->user()->id);
            } ,'comments'=>function($query){
                $query->with('sender','receiver')
                ->where('sender_id', 'assigned_by.id')
                ->orWhere('recipient_id', auth()->user()->id)
                ->latest();
            }])
            ->withCount('ledger')
            ->findOrFail($id);
            
        } else {
            $payment = Payment::with(['vatVoucher', 'Receipt','files'=>function($query){
                $query->with('path');
            }, 'voucher', 'recipient', 
                'assignedTo' => function($query) {
                $query->with(['user', 'comments'=>function($q){
                    $q->with(['sender', 'receiver'])->latest();
                }])->where('assigned_by', auth()->user()->id );
            }, 'assignedBy'=>function($query){
                $query->with(['user', 'comments'=>function($q){
                    $q->with(['sender', 'receiver'])->latest();
                }])->where('recipient_id', auth()->user()->id);
             },'comments'=>function($query){
                $query->with('sender','receiver')
               ->where(function($q){
                    $q->where('sender_id', 'assignedBy.id')
                    ->orWhere('recipient_id', auth()->user()->id)
                   // ->orWhere('sender_id', auth()->user()->id)
                    ;
                })
                ->latest();
            }])
             ->withCount('ledger')
            ->whereHas('assignedTo', function ($q) {
                $q->where('recipient_id', auth()->user()->id);
            })
            ->findOrFail($id);


        }

        return inertia('Payment/ViewPayment', [
            'payment' => $payment,
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

        if(auth()->user()->hasRole('division heads')){
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

    public function acceptDivisionPayment(Request $request)
    {
        $data = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'dueDate' => 'required|date',
            'recipient' => 'required',
            'generalComment' => 'nullable|string',
        ]);

        // $payment = Payment::findOrFail($data['payment_id']);
        // $payment->due_date = date('Y-m-d H:i:s', strtotime($data['dueDate']));
        // $payment->division_head_status = "accepted";
        // $payment->division_head_comments = $data['generalComment'] ?? '';
        // $payment->status = 'due';
        // $payment->save();

            //$user = User::with('roles')->findOrFail($data['recipient']);
            $recipient = User::findOrFail($data['recipient'][0]);
               $recipient_role = $recipient->roles->first()->name;
               $recipient_role_id = $recipient->roles->first()->id;

        $payment_assignment_id = DB::table('payment_assignment')
           ->insertGetId([
                'due_date' => $data['dueDate'],
                'status' => 'pending',
                'comments' => $data['generalComment'] ?? '',
                'role'=> $recipient_role,
                'role_id' => $recipient_role_id,
                'recipient_id' => $recipient->id,
                'payment_id' => $data['payment_id'],
               // 'is_finished' => 1,
                'assigned_by' => auth()->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $request->merge(['comment' => $data['generalComment'] ?? '']);
        $this->paymentComment($request, $data['payment_id'], $payment_assignment_id);

        return to_route('view-payment', $data['payment_id'])->with('message', 'Payment accepted successfully.');
    }

    public function assignToAdvanceOtherSection(Request $request)
    {
        $data = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'dueDate' => 'required|date',
            'recipient' => 'required',
            'generalComment' => 'nullable|string',
        ]);

        // $payment = Payment::findOrFail($data['payment_id']);
        // $payment->due_date = date('Y-m-d H:i:s', strtotime($data['dueDate']));
        // $payment->unit_head_status = "accepted";
        // $payment->unit_head_comments = $data['generalComment'] ?? '';
        // $payment->status = 'due';
        // $payment->save();

            //$user = User::with('roles')->findOrFail($data['recipient']);
            $recipient = User::findOrFail($data['recipient']);
               $recipient_role = $recipient->roles->first()->name;
               $recipient_role_id = $recipient->roles->first()->id;

        $payment_assignment_id = DB::table('payment_assignment')
           ->insertGetId([
                'due_date' => $data['dueDate'],
                'status' => 'pending',
                'comments' => $data['generalComment'] ?? '',
                'role'=> $recipient_role,
                'role_id' => $recipient_role_id,
                'recipient_id' => $recipient->id,
                'payment_id' => $data['payment_id'],
                'assigned_by' => auth()->user()->id,
                //'is_finished' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        $request->merge(['comment' => $data['generalComment'] ?? '']);
        $this->paymentComment($request, $data['payment_id'], $payment_assignment_id);
        //$recipient->notify(new PaymentAssignedNotification($data['generalComment'] ?? '', $recipient));
       // broadcast(new UserNotificationEvent('A new payment has been assigned to you.', $recipient->id))->toOthers();
        return to_route('view-payment', $data['payment_id'])->with('message', 'Payment assigned successfully.');
    }

    public function getAdvancedSectionAndOtherChargesUsers(){
        $other_charges_hos = User::role('other charges(HOS)')->first();
        $advance_section_hos = User::role('advance section(HOS)')->first();
        return response()->json([
            'other_charges_hos' => $other_charges_hos,
            'advance_section_hos' => $advance_section_hos,
        ]);
    }

    public function getAdvanceSectionUsers(){
        $users = User::role('advance section')->select('id','first_name', 'last_name')->get();
        return response()->json($users);
    }

    public function getOtherChargesUsers(){
        $users = User::role('other charges')->select('id','first_name', 'last_name')->get();
        return response()->json($users);
    }

    public function getCheckingUsers(){
        $users = User::role('checking')->select('id','first_name', 'last_name')->get();
        return response()->json($users);
    }

    public function getOtherChargesHOS(){
        $user = User::role('other charges(HOS)')->select('id','first_name', 'last_name')->first();
        return response()->json($user);
    }

     public function assignToAdvanceSection(Request $request)
    {
        $data = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'dueDate' => 'required|date',
            'recipient' => 'required',
            'generalComment' => 'nullable|string',
        ]);

            //$user = User::with('roles')->findOrFail($data['recipient']);
            $recipient = User::findOrFail($data['recipient'][0]);
               $recipient_role = $recipient->roles->first()->name;
               $recipient_role_id = $recipient->roles->first()->id;

        $payment_assignment_id = DB::table('payment_assignment')
           ->insertGetId([
                'due_date' => $data['dueDate'],
                'status' => 'pending',
                'comments' => $data['generalComment'] ?? '',
                'role'=> $recipient_role,
                'role_id' => $recipient_role_id,
                'recipient_id' => $recipient->id,
                'payment_id' => $data['payment_id'],
                'assigned_by' => auth()->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
             $request->merge(['comment' => $data['generalComment'] ?? '']);
            $this->paymentComment($request, $data['payment_id'], $payment_assignment_id);
           // $recipient->notify(new PaymentAssignedNotification($data['generalComment'] ?? '', $recipient));
           // broadcast(new UserNotificationEvent('A new payment has been assigned to you.', $recipient->id))->toOthers();
        return to_route('view-payment', $data['payment_id'])->with('message', 'Payment assigned successfully.');
    }

    public function advanceTaskSubmit(Request $request){
        DB::table('payment_assignment')->where('id', $request->id)->update(['status'=>'done']);
        return to_route('view-payment', $request->payment_id);
    }

    public function otherChargesTaskSubmit(Request $request){
        $assignment = DB::table('payment_assignment')->where('id', $request->id)->first();
        DB::table('payment_assignment')->where('id', $request->id)->update(['status'=>'done']);

        if ($assignment) {
            $recipient = User::find($assignment->assigned_by);
            if ($recipient) {
                //$recipient->notify(new PaymentAssignedNotification('Your assigned payment task has been submitted.', $recipient));
                //broadcast(new UserNotificationEvent('Your assigned payment task has been submitted.', $recipient->id))->toOthers();
            }
        }

        return to_route('view-payment', $request->payment_id);
    }
    

     public function assignToChecking(Request $request)
    {
        $data = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'dueDate' => 'required|date',
            'recipient' => 'required',
            'generalComment' => 'nullable|string',
        ]);

            //$user = User::with('roles')->findOrFail($data['recipient']);
            $recipient = User::findOrFail($data['recipient'][0]);
               $recipient_role = $recipient->roles->first()->name;
               $recipient_role_id = $recipient->roles->first()->id;

        $payment_assignment_id = DB::table('payment_assignment')
           ->insertGetId([
                'due_date' => $data['dueDate'],
                'status' => 'pending',
                'comments' => $data['generalComment'] ?? '',
                'role'=> $recipient_role,
                'role_id' => $recipient_role_id,
                'recipient_id' => $recipient->id,
                'payment_id' => $data['payment_id'],
                'assigned_by' => auth()->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
             $request->merge(['comment' => $data['generalComment'] ?? '']);
        $this->paymentComment($request, $data['payment_id'], $payment_assignment_id);
       // $recipient->notify(new PaymentAssignedNotification($data['generalComment'] ?? '', $recipient));
       // broadcast(new UserNotificationEvent('A new payment has been assigned to you.', $recipient->id))->toOthers();
        return to_route('view-payment', $data['payment_id'])->with('message', 'Payment assigned successfully.');
    }

    public function assignToOtherChargesByHOS(Request $request)
    {
        $data = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'dueDate' => 'required|date',
            'recipient' => 'required',
            'generalComment' => 'nullable|string',
        ]);

            //$user = User::with('roles')->findOrFail($data['recipient']);
            $recipient = User::findOrFail($data['recipient'][0]);
               $recipient_role = $recipient->roles->first()->name;
               $recipient_role_id = $recipient->roles->first()->id;

        DB::table('payment_assignment')
           ->insert([
                'due_date' => $data['dueDate'],
                'status' => 'pending',
                'comments' => $data['generalComment'] ?? '',
                'role'=> $recipient_role,
                'role_id' => $recipient_role_id,
                'recipient_id' => $recipient->id,
                'payment_id' => $data['payment_id'],
                'assigned_by' => auth()->user()->id,
                //'is_finished' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        return to_route('view-payment', $data['payment_id'])->with('message', 'Payment assigned successfully.');
    }

    /**
     * Streamlined functions below
     * These function was used for checkings down to final accounts
     * assistant directors to other charges to be reviewed
     */
     public function assignToSectionUsers(Request $request)
    {
        $data = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'dueDate' => 'required|date',
            'recipient' => 'required',
            'generalComment' => 'nullable|string',
        ]);

            //$user = User::with('roles')->findOrFail($data['recipient']);
            $recipient = User::findOrFail($data['recipient'][0]);
               $recipient_role = $recipient->roles->first()->name;
               $recipient_role_id = $recipient->roles->first()->id;

        $payment_assignment_id = DB::table('payment_assignment')
           ->insertGetId([
                'due_date' => $data['dueDate'],
                'status' => 'pending',
                'comments' => $data['generalComment'] ?? '',
                'role'=> $recipient_role,
                'role_id' => $recipient_role_id,
                'recipient_id' => $recipient->id,
                'payment_id' => $data['payment_id'],
                'assigned_by' => auth()->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
             $request->merge(['comment' => $data['generalComment'] ?? '']);
        $this->paymentComment($request, $data['payment_id'], $payment_assignment_id);
        $recipient->notify(new PaymentAssignedNotification($data['generalComment'] ?? '', $recipient));
        broadcast(new UserNotificationEvent('A new payment has been assigned to you.', $recipient->id))->toOthers();
        return to_route('view-payment', $data['payment_id'])->with('message', 'Payment assigned successfully.');
    }

    public function submitTask(Request $request){
        DB::table('payment_assignment')->where('id', $request->id)->update(['status'=>'done']);
        return to_route('view-payment', $request->payment_id);
    }

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
       // if(auth()->user()->hasRole('directors')){
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

        
}
