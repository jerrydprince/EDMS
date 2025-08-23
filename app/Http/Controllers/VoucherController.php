<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Spatie\Browsershot\Browsershot;

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
        $voucher->voucher_number = $this->generateVoucherNumber();
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
}
