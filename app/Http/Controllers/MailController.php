<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\mail_cc;
use App\Models\mail_bcc;
use App\Models\Attachment;
use App\Models\DraftModel;
use App\Mail\SendClientMail;
use App\Mail\SendClientMailNoAttachment;
use Illuminate\Http\Request;

use App\Models\Mail as MailsModel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;

class MailController extends Controller
{
    public function mail()
    {
        $mails = MailsModel::orderBy('created_at', 'desc')->get();
        return Inertia::render('Mail/Mails', [
            'mails' =>  $mails
        ]);
        // return inertia('Mail/Mails', compact('mails'));

        // return Inertia::render('Mail/Mails', [
        //     'mails' => MailsModel::all()
        // ]);
        // return $mails;

    }

    public function createMail(Request $request)
    {
        $validated = $request->validate([
            'recipient' => 'email|required',
            'subject' => 'string|required',
            'body' => 'string|required',
            'bcc' => 'email|nullable',
            'cc' => 'email|nullable',
            'doc' => 'file|nullable|mimes:jpg,png,pdf,doc,docx',
        ]);
        $newMail = new MailsModel();
        $newMail->recipient = $request->recipient;
        $newMail->subject = $request->subject;
        $newMail->body = $request->body;
        $newMail->sender = auth()->user()->first_name." ".auth()->user()->last_name;
        $newMail->sender_id = auth()->user()->id;
        $newMail->save();
        if($request->hasFile('doc')){
            $file = $request->file('doc');
            $path = $file->store('public');
            $attach_path = storage_path('app/' . $path);
            // return $attach_path;
            $attachment = new Attachment();
            $attachment->file_path = $path;
            $attachment->mail_id = $newMail->id;
            $attachment->save();
        }
        if ($request->cc !== null) {
            $mailcc = new mail_cc();
            $mailcc->mail_id = $newMail->id;
            $mailcc->recipient_mail = $request->cc;
            $mailcc->save();
        }
        if ($request->bcc !== null) {
            $mailbcc = new mail_bcc();
            $mailbcc->mail_id = $newMail->id;
            $mailbcc->recipient_mail = $request->bcc;
            $mailbcc->save();
        }
        if($request->hasFile('doc')){
        $send = Mail::send(new SendClientMail($request->all(), $attach_path));
        }else{
        $send = Mail::send(new SendClientMailNoAttachment($request->all()));
        }
        // return Redirect::route('mails.Mails');
        if ($send) {
            return redirect()->back()->with($newMail);
        } else {
            response()->json([
                'message' => 'An error occured'
            ]);
        }
    }


    public function readMail($id)
    {
        return inertia::render('Mail/ReadMail', [
            'id' => $id
        ]);
    }
    public function draftMail($id)
    {
        return inertia::render('Mail/DraftMail', [
            'id' => $id
        ]);
    }
    public function deleteMail($id)
    {
        $mail = MailsModel::where('id', $id)->delete();
        return  response()->json([
            'message' => 'Mail deleted Successfully',
            'data' => $mail
        ]);
    }
    public function getArchiveMails()
    {
        $data = MailsModel::where('is_archived', true)->get();
        return  response()->json([
            'message' => 'Archived Mail Fecthed Successfully',
            'data' => $data
        ]);
    }
    public function getSingleMail($id)
    {
        $data = MailsModel::where('id', $id)->with('attachments')->get();
        return  response()->json([
            'message' => ' Mail Fecthed Successfully',
            'data' => $data
        ]);
    }
    public function getSingleDraftMail($id)
    {
        $data = DraftModel::where('id', $id)->get();
        return  response()->json([
            'message' => ' Draft Mail Fecthed Successfully',
            'data' => $data
        ]);
    }
    public function isDraft(Request $request)
    {
        $savedraft = new DraftModel();
        $savedraft->body = $request->body;
        $savedraft->subject = $request->subject;
        $savedraft->recipient = $request->recipient;
        $savedraft->save();
        return  response()->json([
            'message' => ' sent as drafts',
            'data' => $savedraft
        ]);
    }
    public function getDrafts()
    {
        $allsavedrafts = DraftModel::orderBy('created_at', 'desc')->get();
        return  response()->json([
            'message' => 'drafts fetched successfully',
            'data' => $allsavedrafts
        ]);
    }
    public function archiveMail($id)
    {
        $mail = MailsModel::where('id', $id)->first();
        $mail->is_archived = !$mail->is_archived;
        $mail->save();
        if ($mail->is_archived) {
            $message = 'Mail has ben archived successfully';
        } else {
            $message = 'Mail has been Unarchived successfully';
        }

        return  response()->json([
            'message' => $message,
        ]);
    }



    public function getStarredMails()
    {
        $data = MailsModel::where('is_starred', true)->get();
        return  response()->json([
            'message' => 'Starred Mail Fecthed Successfully',
            'data' => $data
        ]);
    }

    public function starMail($id)
    {
        $mail = MailsModel::where('id', $id)->first();
        $mail->is_starred = !$mail->is_starred;
        $mail->save();
        if ($mail->is_starred) {
            $message = 'Mail has ben starred successfully';
        } else {
            $message = 'Mail has been Unstarred successfully';
        }

        return  response()->json([
            'message' => $message,
        ]);
    }
}
