<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Spatie\Browsershot\Browsershot;
use App\Models\Payment;
use App\Models\VoteBook;
use App\Models\VATVoucher;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    public function generateVoucherNumber(){
        $voucher = Voucher::latest()->first();
        if ($voucher) {
            $lastId = $voucher->id;
            $newId = $lastId + 1;
            $ref = 'VOU-' . str_pad($newId, 9, '0', STR_PAD_LEFT);
        } else {
            $ref = 'VOU-000000001'; // Starting reference if no entries exist
        }
        return $ref;
    }

    public function generateVATVoucherNumber($id){
        $voucher = VATVoucher::latest()->first();
        if ($voucher) {
            $lastId = $voucher->id;
            $newId = $lastId + $id;
            $ref = 'VAT-VOU-' . str_pad($newId, 9, '0', STR_PAD_LEFT);
        } else {
            $ref = 'VAT-VOU-00000000'.$id; // Starting reference if no entries exist
        }

        return $ref;
    }

    public function createVoucher(Request $request){
        $request->validate([
           'deptal_no' => 'required',
           'source'=> 'required',
           'date' => 'required|date',
           'head' => 'required',
           'checked_and_passed' => 'required',
           'amount' => 'required|numeric',
           'classification_code' => 'required',
           's_head' => 'required',
           'address' => 'required',
           'data_type' => 'required',
           'payment_id' => 'required|exists:payments,id',
        ]);

        if ($request->hasFile('signature')) {
            $file = $request->file('signature');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filepath = $file->storeAs('vouchers/signatures', $filename, 'public');
            $request->merge(['file_path' => $filepath]);
        }

        //check if this payment has a voucher and delete the record
        $voucher = Voucher::where('payment_id', $request->payment_id)->first();
        if ($voucher) {
            if ($voucher->path && file_exists(public_path($voucher->path))) {
                unlink(public_path($voucher->path));
            }

            if ($voucher->signature && Storage::exists($voucher->signature)) {
                Storage::delete($voucher->signature);
            }

           if(!$request->id){
             $voucher->delete();
           }
        }
        

        // Create a new voucher entry
        $voucher = $request->id? Voucher::find($request->id): new Voucher();
       
        $voucher->deptal_no = $request->deptal_no;
        $voucher->source = $request->source;
        $voucher->date = $request->date;    
        $voucher->head = $request->head['title'];
        $voucher->checked_and_passed = $request->checked_and_passed;
        $voucher->amount = $request->amount;
        $voucher->classification_code = $request->classification_code['title'];
        $voucher->voucher_number = $request->id? $voucher->voucher_number: $this->generateVoucherNumber();
        $voucher->s_head = $request->s_head['title'];
        $voucher->address = $request->address;
        $voucher->data_type = $request->data_type['title'];
        $voucher->payment_id = $request->payment_id;  
        if($request->file_path){
            $voucher->signature = $request->file_path; 
        } 
        $voucher->save();

        $html = view('voucher', compact('voucher'))->render();
                $fileName = "voucher_".$voucher->id.'.pdf';
            $path = public_path('voucher/'.$fileName);
            
         if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
           
             // Generate PDF and save to storage/app/pdfs/voucher.pdf
            Browsershot::html($html)
             //->setChromePath('C:\Program Files\Google\Chrome\Application\chrome.exe')
                ->format('A4')
                ->margins(0, 0, 0, 0) // optional
                ->save($path);
        
            //update voucher
            $voucher->path = 'voucher/'.$fileName;
            $voucher->save();
           

         return back()->with(['voucher'=>$voucher]);
        //return redirect()->to(url()->previous())->with('voucher', $voucher);
    }

    // public function generateVATVoucher(Request $request){
    //      $request->validate([
    //           'vote_book_id' => 'required|exists:vote_books,id',
    //           'payment_id' => 'required|exists:payments,id',
    //       ]);
    //    $payment_id = $request->payment_id;
    //    $payment = Payment::with('ledger')->findOrFail($payment_id);
    //    $votebook = VoteBook::find($request->vote_book_id);
       
    //      if(!$votebook){            
    //         return back()->withErrors(['vote_book_id' => 'Invalid Vote Book selected.']);
    //      }  
    //      if(!$payment || !$payment->ledger){            
    //         return back()->withErrors(['payment_id' => 'Invalid Payment selected.']);
    //      }

    //      $percentage = [
    //         "vat" => $request->vat,   
    //         "wht" => $request->wht,   
    //         "stamp_duty" => $request->stamp_duty,
    //      ];

    //      $vats = VATVoucher::where('payment_id', $payment->id)->get();

    //      foreach($percentage as  $value){
    //         $html = view('vat_voucher', compact('payment'))->render();
    //             $fileName = $value."_".$payment->id.'.pdf';
    //             $path = public_path('vat_voucher/'.$fileName);
                
    //         if (!file_exists(dirname($path))) {
    //             mkdir(dirname($path), 0777, true);
    //         }
            
    //             // Generate PDF and save to storage/app/pdfs/voucher.pdf
    //             Browsershot::html($html)
    //             //->setChromePath('C:\Program Files\Google\Chrome\Application\chrome.exe')
    //                 ->format('A4')
    //                 ->margins(0, 0, 0, 0) // optional
    //                 ->save($path);
    //         $vat = $vats->where('percentage', $value)->first();
    //         if ($vat) {
    //             if ($vat->path && file_exists(public_path($vat->path))) {
    //                 unlink(public_path($vat->path));
    //             }
    //         if(!$request->id){
    //             $vat->delete();
    //         }
    //         }else{
    //             $rate = floatval(str_replace('%', '', $value));
    //             $vat = new VATVoucher();
    //             $vat->payment_id = $payment->id;
    //             $vat->vote_book_id = $votebook->id;
    //             $vat->voucher_number = $this->generateVATVoucherNumber();   
    //             $vat->percentage = $request->vat;
    //             $vat->vote_book_id = $votebook->id;
    //             $vat->amount = $payment->ledger->debit; 
    //             $vat->deduction_amount = ($rate / 100) * $payment->ledger->debit;
    //         }
    //      }

    //      $vouchers = VATVoucher::where('payment_id', $payment->id)->get();
        
    //      return back()->with(['voucher'=>$vouchers]);
    //     //return redirect()->to(url()->previous())->with('voucher', $voucher);
    // }

    public function generateVATVoucher(Request $request)
    {
        // Validate the request
        $request->validate([
            'vote_book_id' => 'required|exists:vote_books,id',
            'payment_id' => 'required|exists:payments,id',
            'vat' => 'required',
            'wht' => 'required',
            'stamp_duty' => 'required',
        ]);

        $payment_id = $request->payment_id;
        $payment = Payment::with('ledger')->findOrFail($payment_id);
        $votebook = VoteBook::find($request->vote_book_id);

        // Validation checks
        if (!$votebook) {
            return back()->withErrors(['vote_book_id' => 'Invalid Vote Book selected.']);
        }
        
        if (!$payment || !$payment->ledger) {
            return back()->withErrors(['payment_id' => 'Invalid Payment selected.']);
        }

        // Define percentage types and their values
        $percentages = [
            'vat' => $request->vat,
            'wht' => $request->wht,
            'stamp_duty' => $request->stamp_duty,
        ];

        if (empty($percentages)) {
            return back()->withErrors(['percentages' => 'At least one percentage (VAT, WHT, or Stamp Duty) must be provided.']);
        }

        // Get existing VAT vouchers for this payment
        $existingVats = VATVoucher::where('payment_id', $payment->id)->get();
        $createdVouchers = [];

        try {
            // Process each percentage type
            DB::beginTransaction();
             $counter = 1;
             $voucher = VATVoucher::latest()->first();
            foreach ($percentages as $type => $percentage) {
                // Generate HTML for the voucher
                $html = view('vat_voucher', compact('payment', 'percentage', 'type'))->render();
                
                // Create filename
                $fileName = $percentage . "_".$type."_" . $payment->id . '.pdf';
                $relativePath = 'vat_voucher/' . $fileName;
                $fullPath = public_path($relativePath);
                
                // Create directory if it doesn't exist
                if (!file_exists(dirname($fullPath))) {
                    mkdir(dirname($fullPath), 0777, true);
                }
               
                // Generate PDF
                Browsershot::html($html)
                    ->format('A4')
                    ->margins(0, 0, 0, 0)
                    ->save($fullPath);
                
                // Find existing VAT voucher for this type
                // $existingVat = $existingVats->where('percentage', $percentage)->first();
                  $rate = floatval(str_replace('%', '', $percentage));
                // if ($existingVat) {
                //     // Delete old file if exists
                //     if ($existingVat->path && file_exists(public_path($existingVat->path))) {
                //         unlink(public_path($existingVat->path));
                //     }
                   
                //     // Update existing voucher
                //     $existingVat->update([
                //         'path' => $relativePath,
                //         'amount' => $payment->ledger->debit,
                //         'deduction_amount' => ($rate / 100) * $payment->ledger->debit,
                //         'updated_at' => now()
                //     ]);
                    
                //     $createdVouchers[] = $existingVat;
                // } else {
                    // Create new VAT voucher
                    // $vatVoucher = VATVoucher::create([
                    //     'payment_id' => $payment->id,
                    //     'vote_book_id' => $votebook->id,
                    //     'voucher_number' => $this->generateVATVoucherNumber(),
                    //     'percentage' => $percentage,
                    //     'amount' => $payment->ledger->debit,
                    //     'deduction_amount' => ($rate / 100) * $payment->ledger->debit,
                    //     'path' => $relativePath
                    // ]);
                    $vatVoucher = [
                        'payment_id' => $payment->id,
                        'vote_book_id' => $votebook->id,
                        'voucher_number' => $this->generateVATVoucherNumber($counter),
                        'percentage' => $percentage,
                        'amount' => $payment->ledger->debit,
                        'deduction_amount' => ($rate / 100) * $payment->ledger->debit,
                        'path' => $relativePath,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $createdVouchers[] = $vatVoucher;
               // }
                ++$counter;
            }

            VATVoucher::insert($createdVouchers);
            DB::commit();
            // Get all vouchers for this payment
            $vouchers = VATVoucher::where('payment_id', $payment->id)->get();
            
            return to_route('view-payment', $request->payment_id)->with([
                'success' => 'VAT vouchers generated successfully!',
                'voucher' => $vouchers
            ]);

        } catch (\Exception $e) {
            // Log the error
            
            \Log::error('Error generating VAT vouchers: ' . $e->getMessage(), ['exception' => $e]);
            DB::rollBack();
           
            return to_route('view-payment', $request->payment_id)->withErrors(['error' => 'An error occurred while generating VAT vouchers. Please try again.']);
        }
    }

}
