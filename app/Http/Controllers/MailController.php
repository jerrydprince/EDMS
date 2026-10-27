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
use App\Mail\ReplyEmail;

class MailController extends Controller
{
    public function mail(Request $request)
    {
       if($request->is_archive == 'true'){
        return $this->getArchivedMails();
       }else
       if($request->is_read == 'false'){
        return $this->getUnreadMails();
       }else if($request->is_starred == 'true'){
        return $this->getStarredMails();
       }else if($request->is_draft == 'true'){
        return $this->getDraftEmails();
       }else if($request->is_sent == 'true'){
        return $this->getSentMails();
        }else{
             // $mails = auth()->user()->mails()->where('is_sent', false)->orderBy('created_at', 'desc')->paginate(20);
            //  $mails = auth()->user()
            //     ->mails()
            //     ->where('is_sent', false)
            //     ->whereIn('id', function ($query) {
            //         $query->selectRaw('MAX(id)')
            //             ->from('mails')
            //             ->groupBy('thread_id');
            //     })
            //     ->orderBy('created_at', 'desc')
            //     ->paginate(20);

                $mails = MailsModel::with('attachments')->where('user_id', auth()->user()->id)
                ->where('is_sent', false)
                ->whereIn('id', function ($query) {
                    $query->selectRaw('MAX(id)')
                        ->from('mails')
                        ->groupBy('thread_id');
                })
                ->orderBy('created_at', 'desc')
                ->paginate(20);
            return inertia('Mail/Mails', [
                'mails' =>  $mails,
                'buttonToggle' => 1
            ]);
        }
    }

    public function getSentMails()
    {
        $sentmails = auth()->user()->mails()->where('is_sent', 1)->orderBy('created_at', 'desc')->paginate(20);
        //  $sentmails = MailsModel::with('attachments')->where('user_id', auth()->user()->id)
        //         ->where('is_sent', true)
        //         // ->whereIn('id', function ($query) {
        //         //     $query->selectRaw('MAX(id)')
        //         //         ->from('mails')
        //         //         ->groupBy('thread_id');
        //         // })
        //         ->orderBy('created_at', 'desc')
        //         ->paginate(20);
            return inertia('Mail/Mails', [
                'mails' =>  $sentmails,
                'buttonToggle' => 6
            ]);
    }

    public function getDraftEmails()
    {
        //$draftmails = auth()->user()->drafts()->orderBy('created_at', 'desc')->paginate(20);
        $draftmails = DraftModel::with('attachments')->where('user_id', auth()->user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
            return inertia('Mail/Mails', [
                'mails' =>  $draftmails,
                'buttonToggle' => 5
            ]);
    }

    public function getStarredMails()
    {
        $mails = auth()->user()->mails()->with('attachments')->where('is_starred', true)->where('is_sent', false)->orderBy('created_at', 'desc')->paginate(20);
            return inertia('Mail/Mails', [
                'mails' =>  $mails,
                'buttonToggle' => 4
            ]);
    }

    public function getUnreadMails()
    {
         $mails = auth()->user()->mails()->with('attachments')->where('is_read', false)->where('is_sent', false)->orderBy('created_at', 'desc')->paginate(20);
            return inertia('Mail/Mails', [
                'mails' =>  $mails,
                'buttonToggle' => 3
            ]);
    }

    public function getArchivedMails()
    {
        $mails = auth()->user()->mails()->with('attachments')->where('is_archived', true)->where('is_sent', false)->orderBy('created_at', 'desc')->paginate(20);
        return inertia('Mail/Mails', [
            'mails' =>  $mails,
            'buttonToggle' => 2
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
            //'file' => 'file|nullable|mimes:jpg,png,pdf,doc,docx',
        ]);

        // Dynamically set SMTP configuration
        $configs = \App\Models\SystemConfiguration::pluck('value', 'key');
        if (isset($configs['MAIL_HOST']) || isset($configs['mail.host'])) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $configs['MAIL_HOST'] ?? $configs['mail.host'],
                'mail.mailers.smtp.port' => $configs['MAIL_PORT'] ?? $configs['mail.port'] ?? 587,
                'mail.mailers.smtp.encryption' => $configs['MAIL_ENCRYPTION'] ?? $configs['mail.encryption'] ?? 'tls',
                'mail.mailers.smtp.username' => $configs['MAIL_USERNAME'] ?? $configs['mail.username'] ?? '',
                'mail.mailers.smtp.password' => $configs['MAIL_PASSWORD'] ?? $configs['mail.password'] ?? '',
                'mail.from.address' => $configs['MAIL_FROM_ADDRESS'] ?? $configs['mail.from.address'] ?? ($configs['MAIL_USERNAME'] ?? $configs['mail.username'] ?? 'hello@example.com'),
                'mail.from.name' => 'EDMS System',
            ]);
            
            // Force Laravel to re-read the configuration and rebuild the mailer
            app('mail.manager')->purge('smtp');
        }

        $file = [];
        if($request->hasFile('file')){
            $file = $request->file('file')[0];
        }

        $attach_path = null;
        if($file){
            $path = $file->store('public/attachments');
            $attach_path = storage_path( $path);
        }
     
        if($file){
         Mail::send(new SendClientMail($request->except('file'), $attach_path, auth()->user()->email));
        }else{
         Mail::send(new SendClientMailNoAttachment($request->except('file'), auth()->user()->email));
        }
    
        // return Redirect::route('mails.Mails');
       // if ($send == 0) {
            $newMail = new MailsModel();
            $newMail->recipient = $request->recipient;
            $newMail->subject = $request->subject;
            $newMail->body = $request->body;
            $newMail->sender = auth()->user()->first_name." ".auth()->user()->last_name;
            $newMail->sender_id = auth()->user()->id;
            $newMail->user_id = auth()->user()->id;
            $newMail->is_sent = 1;
            $newMail->save();
            if($file && $attach_path){
                // return $attach_path;
                $attachment = new Attachment();
                $attachment->file_path = str_replace('public/', '', $path);
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

            return redirect()->back()->with(['success'=>true]);
        // } else {
        //     response()->json([
        //         'message' => 'An error occured'
        //     ]);
        // }
    }

    public function sendReply(Request $request){
        $validated = $request->validate([
            'recipient' => 'email|required',
            'body' => 'string|required',
            'original_message_id' => 'required',
             'bcc' => 'email|nullable',
            'cc' => 'email|nullable',
            'file' => 'file|nullable|mimes:jpg,png,pdf,doc,docx',
        ]);

        $attach_path = '';
        if($request->hasFile('file')){
            $file = $request->file('file');
            $path = $file->store('public');
            $attach_path = storage_path('app/' . $path);
        }

         $originalEmail = [
            'subject' => $request->original_subject ?? '',
            'from' => $request->original_from ?? '',
            'date' => $request->original_date ?? '',
            'body' => $request->original_body ?? '',
            'message_id' => $request->original_message_id ?? '',
            'recipient' => $request->recipient ?? ''
        ];

         //$send = Mail::send(new ReplyEmail($validated['body'], $originalEmail, $attach_path));
         $send = Mail::to($request->recipient)->send(new ReplyEmail($validated['body'], $originalEmail, $attach_path));
         
        // return Redirect::route('mails.Mails');
        if ($send) {
            $newMail = new MailsModel();
            $newMail->recipient = $request->recipient;
            $newMail->subject = 'Re: '.$request->original_subject;
            $newMail->body = $request->body;
            $newMail->sender = auth()->user()->first_name." ".auth()->user()->last_name;
            $newMail->sender_id = auth()->user()->id;
            $newMail->user_id = auth()->user()->id;
            $newMail->thread_id = $request->original_message_id;
            $newMail->message_id = $request->original_message_id;
            $newMail->is_sent = 1;
            $newMail->save();
            if($request->hasFile('doc') && $attach_path){
                // return $attach_path;
                $attachment = new Attachment();
                $attachment->file_path = str_replace('public/', '', $path);
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
        $data = MailsModel::with('attachments')->find($id);
        $data->is_read = true;
        $data->save();

        return  response()->json([
            'message' => 'Mail Fetched Successfully',
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
        if($request->body){
             $savedraft = new DraftModel();
            $savedraft->body = $request->body;
            $savedraft->subject = $request->subject??'No Subject';
            $savedraft->recipient = $request->recipient;
            $savedraft->user_id = auth()->user()->id;
            $savedraft->save();
            return  response()->json([
                'message' => ' sent as drafts',
                'data' => $savedraft
            ]);
        }
       
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



    // public function getStarredMails()
    // {
    //     $data = MailsModel::where('is_starred', true)->get();
    //     return  response()->json([
    //         'message' => 'Starred Mail Fecthed Successfully',
    //         'data' => $data
    //     ]);
    // }

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

    public function markUnread($id)
    {
        $mail = MailsModel::where('id', $id)->first();
        if ($mail) {
            $mail->is_read = 0;
            $mail->save();
        }
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

//     $server = new Server('mail.govmail.gbb.com.ng');
//     $connection = $server->authenticate('aakinmade@oagf.gov.ng', 'kPzJA3SynW*');
//     $mailbox = $connection->getMailbox('INBOX');

// // Process emails in chunks for memory efficiency

 
//     $thirtyDaysAgo = new DateTime('-30 days');
//     $messages = $mailbox->getMessages(new Since($thirtyDaysAgo));
    
//     // Get all existing message IDs in one query
//     $existingMessageIds = MailsModel::pluck('message_id')->toArray();
    
//     $emailsToInsert = [];
    
//     foreach ($messages as $message) {
//         $emailMessageId = $message->getHeaders()->get('message-id');
        
//         // Skip if already exists
//         // if (in_array($emailMessageId, $existingMessageIds)) {
//         //     continue;
//         // }
        
//         $emailsToInsert[] = [
//             'message_id' => $message->getNumber(),
//             'subject' => $message->getSubject(),
//             'recipient' => $message->getFrom()->getAddress(),
//             'from_name' => $message->getFrom()->getName(),
//             'body_text' => $message->getBodyText(),
//             'body' => $message->getBodyHtml(),
//             'received_at' => $message->getDate(),
//             'has_attachments' => $message->hasAttachments(),
//             'user_id' => auth()->user()->id,
//             'created_at' => now(),
//             'updated_at' => now(),
//         ];
//     }
    
//     // Insert all at once (much faster!)
//     if (!empty($emailsToInsert)) {
//         MailsModel::insert($emailsToInsert);
//     }
    
//     return response()->json([
//         'new_emails' => count($emailsToInsert),
//         'total_processed' => count($messages),
//         'emails_to_insert' => $emailsToInsert,
//     ]);
    
//     return response()->json('true');
}

    public function submitToWorkflow(Request $request, $id)
    {
        $mail = MailsModel::findOrFail($id);
        $user = auth()->user();

        // Find the default Mail Incoming Workflow
        $workflowVersion = \Illuminate\Support\Facades\DB::table('workflow_versions')
            ->join('workflows', 'workflow_versions.workflow_id', '=', 'workflows.id')
            ->where('workflows.title', 'Mail Incoming')
            ->where('workflow_versions.is_published', true)
            ->select('workflow_versions.id')
            ->first();

        if (!$workflowVersion) {
            return response()->json(['message' => 'No active Mail Incoming workflow found.'], 404);
        }

        $task = new \App\Models\Task();
        $task->title = 'Incoming Mail: ' . $mail->subject;
        $task->workflow_version_id = $workflowVersion->id;
        $task->mail_id = $mail->id;
        $task->created_by = $user->id;
        
        if ($request->has('allow_step_splitting')) {
            $task->allow_step_splitting = filter_var($request->allow_step_splitting, FILTER_VALIDATE_BOOLEAN);
        }
        
        $task->due_date = \Carbon\Carbon::now()->addDays(5)->format('Y-m-d');
        $task->save();

        $workflowService = app(\App\Services\GenericWorkflowService::class);
        $workflowService->start($task, $user, [$user->id]);

        return redirect()->back()->with(['success' => 'Mail successfully submitted to Workflow']);
    }

    public function jacketAsFileAndSubmit(Request $request, $id)
    {
        $mail = MailsModel::with('attachments')->findOrFail($id);
        $user = auth()->user();

        // 1. Create a Registry File
        $newFile = new \App\Models\File();
        $newFile->name = $mail->subject;
        $newFile->file_type = 'External Mail';
        $newFile->doc_no = $mail->message_id;
        $newFile->description = substr(strip_tags($mail->body), 0, 200);
        $newFile->created_by = $user->id;
        $newFile->department_id = $user->department_id;
        $newFile->path = '';
        $newFile->archive_id = 1;
        $newFile->save();

        // 2. Attach mail attachments to registry file
        foreach ($mail->attachments as $attachment) {
            $registryFile = new \App\Models\RegistryFile();
            $registryFile->file_id = $newFile->id;
            $registryFile->file_path = $attachment->file_path;
            $registryFile->save();
        }

        // 3. Find the Registry Incoming Workflow
        $workflowVersion = \Illuminate\Support\Facades\DB::table('workflow_versions')
            ->join('workflows', 'workflow_versions.workflow_id', '=', 'workflows.id')
            ->where('workflows.title', 'Registry Incoming')
            ->where('workflow_versions.is_published', true)
            ->select('workflow_versions.id')
            ->first();

        if (!$workflowVersion) {
            return response()->json(['message' => 'No active Registry workflow found.'], 404);
        }

        // 4. Create Task
        $task = new \App\Models\Task();
        $task->title = 'Registry File: ' . $newFile->name;
        $task->workflow_version_id = $workflowVersion->id;
        $task->file_id = $newFile->id;
        $task->created_by = $user->id;

        if ($request->has('allow_step_splitting')) {
            $task->allow_step_splitting = filter_var($request->allow_step_splitting, FILTER_VALIDATE_BOOLEAN);
        }

        $task->due_date = \Carbon\Carbon::now()->addDays(5)->format('Y-m-d');
        $task->save();

        $workflowService = app(\App\Services\GenericWorkflowService::class);
        $workflowService->start($task, $user, [$user->id]);

        // Also submit to AGF correspondence
        $corr = new \App\Models\Correspondence();
        $corr->description = substr(strip_tags($mail->body ?? 'Jacked and submitted external mail'), 0, 200);
        $corr->file_name = $mail->subject ?? 'Jacketed Mail';
        $corr->document_number = $newFile->doc_no;
        $corr->size = '0 KB';
        $corr->created_by = $user->id;
        $corr->save();

        if ($mail->attachments && $mail->attachments->count() > 0) {
            $files_corr = [];
            foreach ($mail->attachments as $attachment) {
                $arr = [
                    'path' => $attachment->file_path,
                    'correspondence_id' => $corr->id,
                ];
                array_push($files_corr, $arr);
            }
            \Illuminate\Support\Facades\DB::table('correspondence_file')->insert($files_corr);
        }

        return redirect()->back()->with(['success' => 'Mail successfully jacketed as a File and submitted to Workflow and Correspondence']);
    }

    public function forwardToAgf($emailId)
    {
        $mail = \App\Models\Mail::with('attachments')->findOrFail($emailId);

        $corr = new \App\Models\Correspondence();
        $corr->description = substr(strip_tags($mail->body ?? 'Forwarded external mail'), 0, 200);
        $corr->file_name = $mail->subject ?? 'Forwarded Mail';
        $corr->document_number = null;
        $corr->size = '0 KB';
        $corr->created_by = auth()->user()->id;
        $corr->save();

        if ($mail->attachments && $mail->attachments->count() > 0) {
            $files = [];
            foreach ($mail->attachments as $attachment) {
                $arr = [
                    'path' => $attachment->file_path,
                    'correspondence_id' => $corr->id,
                ];
                array_push($files, $arr);
            }
            \Illuminate\Support\Facades\DB::table('correspondence_file')->insert($files);
        }

        $newFile = new \App\Models\File();
        $newFile->name = $corr->file_name;
        $newFile->file_type = 'External Mail';
        $newFile->doc_no = $mail->message_id;
        $newFile->description = $corr->description;
        $newFile->created_by = auth()->user()->id;
        $newFile->is_agf = true;
        $newFile->path = '';
        $newFile->archive_id = 1;
        $newFile->save();

        $agfUser = \App\Models\User::role(['Accountant-General', 'super user'])->first();
        if ($agfUser) {
            $fm = new \App\Models\FileMovement();
            $fm->sent_from = auth()->user()->id;
            $fm->sent_to = $agfUser->id;
            $fm->file_id = $newFile->id;
            $fm->status = "pending";
            $fm->file_movement_id = substr(str_shuffle("0123456789"), 0, 9);
            $fm->sent_to_branch = 'accountant general';
            $fm->sent_to_agf = true;
            $fm->save();
        }

        return redirect()->back()->with('success', 'Mail forwarded to AGF Correspondence successfully');
    }
}
