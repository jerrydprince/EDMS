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
use Webklex\IMAP\Facades\Client; 
use Ddeboer\Imap\Server;
use Ddeboer\Imap\Search\Date\Since;
use DateTime;
use DateInterval;

class MailController extends Controller
{
    public function mail()
    {
        //$mails = MailsModel::orderBy('created_at', 'desc')->get();
        $mails = auth()->user()->mails()->orderBy('created_at', 'desc')->paginate(20);
        return inertia('Mail/Mails', [
            'mails' =>  $mails
        ]);
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
        $newMail->user_id = auth()->user()->id;
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
        $send = Mail::send(new SendClientMailNoAttachment($request->all(), auth()->user()->email));
        }
        // return Redirect::route('mails.Mails');
        if ($send) {
            return redirect()->back()->with(['success'=>true]);
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
        // return  response()->json([
        //     'message' => 'Mail deleted Successfully',
        //     'data' => $mail
        // ]);
        return redirect()->back();
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

        // return  response()->json([
        //     'message' => $message,
        // ]);
        return redirect()->back();
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

        // return  response()->json([
        //     'message' => $message,
        // ]);
        return redirect()->back();
    }

    // public function getAllMailsFolders(){
    //     $client = Client::account("default");  
    //     $client->connect();  
  
    //     /** @var \Webklex\PHPIMAP\Support\FolderCollection $folders */  
    //     $folders = $client->getFolders(false);  
     
    //     $foldersData = [];
    //     $inboxMessages = [];
        
    //     /** @var \Webklex\PHPIMAP\Folder $folder */  
    //     foreach($folders as $folder){  
    //         $folderData = [
    //             'path' => $folder->path,
    //             'name' => $folder->name,
    //             'full_name' => $folder->full_name,
    //         ];

    //         if(strtoupper($folder->name) === 'INBOX'){
    //             $messages = $folder->messages()->all()->limit(10, 0)->get();
    //             //dd($messages);
    //             foreach ($messages as $message) {
    //                 $inboxMessages[] = [
    //                     'uid' => $message->uid,
    //                     'message_id' => $message->message_id,
    //                     'subject' => (string) $message->getSubject(),
    //                     'from' => $message->from ? $message->from->toArray() : [],
    //                     'date' => $message->date ? (string) $message->date : null,
    //                     'flags' => $message->flags,
    //                 ];
                   
    //             }
                
    //             $folderData['messages'] = $inboxMessages;
    //             //dd($folderData);
    //         }
            
    //     }


    //     return response()->json($folderData);
    // }

  public function getAllMailsFolders(){

    $server = new Server('mail.govmail.gbb.com.ng');
    $connection = $server->authenticate('aakinmade@oagf.gov.ng', 'kPzJA3SynW*');
    $mailbox = $connection->getMailbox('INBOX');

  $today = new DateTime();
        $thirtyDaysAgo = $today->sub(new DateInterval('P30D'));
        
        $messages = $mailbox->getMessages(new Since($thirtyDaysAgo));

foreach ($messages as $message) {
    // Get message details
    $subject = $message->getSubject();
    $from = $message->getFrom(); // Returns EmailAddress object
    $date = $message->getDate();
    $bodyText = $message->getBodyText(); // Plain text
    $bodyHtml = $message->getBodyHtml(); // HTML
    
    // Example: Display the data
    echo "Subject: " . $subject . "\n";
    echo "From: " . $from->getAddress() . "\n";
    echo "Date: " . $date->format('Y-m-d H:i:s') . "\n";
    echo "Body: " . $bodyText . "\n\n";
    echo "Body: " . $bodyHtml . "\n\n";
    
    // Get attachments
    $attachments = $message->getAttachments();
    foreach ($attachments as $attachment) {
        $filename = $attachment->getFilename();
        $content = $attachment->getDecodedContent();
        // Save attachment
        file_put_contents(storage_path('app/attachments/' . $filename), $content);
    }
    
    // Mark as read (optional)
    // $message->markAsSeen();
}

    // $mailbox = $connection->getMailbox('INBOX');
    // $messages = $mailbox->getMessages();

    // foreach ($messages as $message) {
    //     echo $message->getSubject();
    //     echo $message->getBodyText();
    // }
    // $client = Client::account("default");  
    // $client->connect();  
  
    // $inbox = $client->getFolder('INBOX');
    
    // $inboxData = [
    //     'path' => $inbox->path,
    //     'name' => $inbox->name,
    //     'full_name' => $inbox->full_name,
    //     'messages' => []
    // ];
    
    // // Count total messages
    // $totalMessages = $inbox->all()->count();
    // dd($totalMessages);
    // // Calculate offset to get last 10 messages
    // $offset = max(0, $totalMessages - 10);
    // dd($offset);
    // // Get the last 10 messages
    // $messages = $inbox->messages()->all()->limit(10, $offset)->get();
    
    // // Reverse to show newest first
    // $latestMessages = $messages->reverse();
    
    // foreach ($latestMessages as $message) {
    //     $inboxData['messages'][] = [
    //         'uid' => $message->uid,
    //         'message_id' => $message->message_id,
    //         'subject' => (string) $message->getSubject(),
    //         'from' => $message->from ? $message->from->toArray() : [],
    //         'date' => $message->date ? (string) $message->date : null,
    //         'flags' => $message->flags,
    //     ];
    // }
    
    return response()->json($inboxData);
}


    public function getFolderMessages($folderName){
        $client = Client::account("default");  
        $client->connect();  
        
        $folder = $client->getFolder($folderName);
        
        if (!$folder) {
            return response()->json(['error' => 'Folder not found'], 404);
        }
        
        $messages = $folder->messages()->all()->get();  
        
        $messagesData = [];
        
        /** @var \Webklex\PHPIMAP\Message $message */  
        foreach ($messages as $message) {  
            $messagesData[] = [
                'uid' => $message->uid,
                'message_id' => $message->message_id,
                'subject' => (string) $message->subject,
                'from' => $message->from ? $message->from->toArray() : [],
                'date' => $message->date ? (string) $message->date : null,
                'flags' => $message->flags,
            ];
        }
        
        return response()->json($messagesData);
    }

    public function getMessage($folderName, $uid){
        $client = Client::account("default");  
        $client->connect();  
        
        $folder = $client->getFolder($folderName);
        
        if (!$folder) {
            return response()->json(['error' => 'Folder not found'], 404);
        }
        
        $message = $folder->messages()->getMessageByUid($uid);
        
        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }
        
        $messageData = [
            'uid' => $message->uid,
            'message_id' => $message->message_id,
            'subject' => (string) $message->subject,
            'from' => $message->from ? $message->from->toArray() : [],
            'to' => $message->to ? $message->to->toArray() : [],
            'cc' => $message->cc ? $message->cc->toArray() : [],
            'date' => $message->date ? (string) $message->date : null,
            'body' => $message->getHTMLBody() ?: $message->getTextBody(),
            'attachments' => $message->getAttachments()->map(function($attachment) {
                return [
                    'name' => $attachment->name,
                    'size' => $attachment->size,
                    'type' => $attachment->type,
                ];
            })->toArray(),
            'flags' => $message->flags,
        ];
        
        return response()->json($messageData);
    }
}
