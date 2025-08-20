<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\FinanceFiles;
use App\Models\Payment;
use App\Models\User;
use App\Models\Division;
use Spatie\Permission\Models\Role;

class FandAController extends Controller
{
    public function payments(Request $request){
        $payment = null;
        if($request->id){
            $payment = Payment::with(['files', 'recipient'])
                ->findOrFail($request->id);
        }
        return inertia('Payment/Finance', ['payment' => $payment]);
    }

    public function getRegistryFiles(){
        $registryfiles = FinanceFiles::with('path')->paginate(10);
        return response()->json($registryfiles);
    }
  
    public function initiatePayment(Request $request){ 
        $data = $request->validate([
            'documents' => 'required|array',
            'title' => 'required|string',
            'dueDate' => 'required|date',
            'recipient' => 'required|array',
        ]);

        try {
            DB::beginTransaction(); 
           $data['dueDate'] = date('Y-m-d H:i:s', strtotime($data['dueDate']));
            $pay = $request->payment_id? Payment::findOrFail($request->payment_id):new Payment();
            $pay->title = $data['title'];
            //$pay->due_date = $data['dueDate'];
            //$pay->recipient_id = $data['recipient'][0];
            //$pay->comments = $request->input('comments', '');
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
                    'assigned_by' => auth()->user()->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            
            if($request->payment_id){
                DB::table('payment_assignment')
                    ->where('recipient_id', $recipient->id)
                    ->where('recipient_role_id', $recipient_role_id)
                    ->update($assignmentData);
                $pay->files()->sync($ids);
            } else {
                DB::table('payment_assignment')->insert($assignmentData);
                $pay->files()->attach($ids);
            }

            DB::commit();
            return to_route('f-and-a');
        } catch (\Exception $e) {
            DB::rollBack();
             //dd($e->getMessage());
             return redirect()->back()->withErrors(['error' => 'Failed to initiate payment ']);
            return redirect()->back()->with('error', 'Failed to initiate payment: ' . $e->getMessage());
        }
       
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
        $division = Division::where('title', 'Finance and Accounts')->first();
   
        if (!$division) {
            return redirect()->back()->with('message', 'Department not found');
        }
        $assistantDirector = User::with('division')->role('unit heads') // Spatie method
        ->where('division_id', $division->id)
        ->select('id','first_name', 'last_name', 'email','phone', 'gender', 'department_id', 'division_id')
        ->get();

        return response()->json($assistantDirector);
    }

    public function getActivePayments()
    {
        if(auth()->user()->hasRole('directors')){
           $payments = Payment::with(['files', 'recipient', 'assignedTo' => function($query) {
                $query->with('user')->where('assigned_by', auth()->user()->id);
            }])
            ->where(function ($query) {
                $query->where('status', 'due')
                      ->orWhere('status', 'overdue')
                      ->orWhere('status', 'reverted');
            })
            ->latest()
            ->paginate(10);
        } else {
            $id = auth()->user()->id;
             $payments = Payment::with(['files','assignedBy'=>function($query){
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
                })
                ->latest()
                ->paginate(10);
        }
        // $payments = Payment::with(['files', 'recipient', 'assignedTo'])
        //     ->where('status', 'due')
        //     ->orWhere('status', 'overdue')
        //     ->orWhere('status', 'reverted')
        //     ->latest()
        //     ->paginate(10);

        return response()->json($payments);
    }

    public function viewPayment($id)
    {
        if(auth()->user()->hasRole('directors')){
            $payment = Payment::with(['files', 'recipient', 'assignedTo' => function($query) {
                $query->with('user')->where('assigned_by', auth()->user()->id);
            }])
            ->findOrFail($id);
            
        } else {
            $payment = Payment::with(['files', 'recipient', 'assignedTo' => function($query) {
                $query->with('user')->where('assigned_by', auth()->user()->id );
            }, 'assignedBy'=>function($query){
                $query->with(['user'])->where('recipient_id', auth()->user()->id);
             }])
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
        ]);

            $payment = Payment::findOrFail($data['payment_id']);
           
            if($request->from == 'Division'){
                $payment->division_head_status = "rejected";
            } else {
                $payment->unit_head_status = "rejected";
            }
            $payment->status = 'reverted';
            if($request->from == 'Division'){
                $payment->division_head_comments = $data['reasonForRejection'];
            } else {
                $payment->unit_head_comments = $data['reasonForRejection'];
            }
            $payment->save();
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
                'created_at' => now(),
                'updated_at' => now(),
            ]);

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
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        return to_route('view-payment', $data['payment_id'])->with('message', 'Payment assigned successfully.');
    }

    public function getAdvancedSectionAndOtherChargesUsers(){
        $other_charges_hos = User::role('other charges(HOS)')->first();
        $advance_section_hos = User::role('advanced section(HOS)')->first();
        return response()->json([
            'other_charges_hos' => $other_charges_hos,
            'advance_section_hos' => $advance_section_hos,
        ]);
    }
        
}
