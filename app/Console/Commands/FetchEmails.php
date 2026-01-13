<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ddeboer\Imap\Server;
use Ddeboer\Imap\Search\Date\Since;
use DateTime;
use DateInterval;
use App\Jobs\ProcessFetchedEmails;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use App\Models\Mail;

class FetchEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:fetch {--unseen : Fetch only unseen emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches emails from the IMAP server and processes them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
             //$systemUser = User::where('role', 'super admin')->first();
            
          
                $usersWithMailPermission = User::whereNotNull('email_password')->get();

            $this->info("Found " . $usersWithMailPermission->count() . " users with mail permission.");
                        Log::info('Users with mail permission', [
                            'count' => $usersWithMailPermission->count(),
                            'users' => $usersWithMailPermission->pluck('email')->toArray()
                        ]);
            //iterate over the users
             
            foreach ($usersWithMailPermission as $user) {
                $server = new Server(env('IMAP_HOST'));
                $decryptedPassword = Crypt::decrypt($user->email_password);
                $connection = $server->authenticate($user->email, $decryptedPassword);
                $mailbox = $connection->getMailbox('INBOX');
                
                $lastFetched = $user->last_email_fetch_at ?? new DateTime('-60 days');
                //$thirtyDaysAgo = new DateTime( $lastFetched);
                $messages = $mailbox->getMessages(new Since($lastFetched));

                $messageCount = $messages->count();
                $this->info("Found " . $messageCount . " messages in the last 60 days.");

                if ($messageCount > 0) {
                    // Extract message data to avoid serialization issues
                    $emailData = [];
                    $this->info("Extracting email data...");
                    
                    foreach ($messages as $message) {
                        try {
                           // Log::info('headers', ['headers' => $message->getHeaders()]);
                            //get message thread
                            $messageId = $message->getHeaders()->get('message_id');
                            $inReplyTo = $message->getHeaders()->get('in_reply_to');
                            $subject = $message->getSubject();

                            $messageId = $this->cleanMessageId($messageId);
                            $inReplyTo = $this->cleanMessageId($inReplyTo);

                           // $threadId = $this->getThreadId($messageId, $inReplyTo, $subject);

                            $emailData[] = [
                                'message_id' =>  $messageId,
                                'subject' => $message->getSubject() ?? '(No Subject)',
                                'recipient' => $message->getFrom()->getAddress(),
                                'from_name' => $message->getFrom()->getName() ?? '',
                                'body_text' => $message->getBodyText() ?? '',
                                'body_html' => $message->getBodyHtml() ?? '',
                                'received_at' => $message->getDate()->format('Y-m-d H:i:s'),
                                'has_attachments' => $message->hasAttachments(),
                                'user_id' => $user->id,
                                'thread_id' => $inReplyTo ? $inReplyTo: $messageId,
                               // 'size' => $message->getSize(),
                            ];
                        } catch (\Exception $e) {
                            Log::warning('Failed to process message: ' . $e->getMessage(), [
                                'message_number' => $message->getNumber()
                            ]);
                            continue;
                        }
                    }
                    
                    //ProcessFetchedEmails::dispatch($emailData);
                    collect($emailData)->chunk(20)->each(function ($chunk) {
                        Log::info('Dispatching chunk of emails for processing', ['count' => count($chunk)]);
                        ProcessFetchedEmails::dispatch($chunk->toArray());
                    });
                    $this->info("Dispatched " . count($emailData) . " emails for processing.");

                    // Update last fetched timestamp
                   //if (!empty($emailData)) {
                        $user->update(['last_email_fetch_at' => now()]);
                    //}
                } else {
                    $this->info("No new emails to process.");
                }
            }
    
        } catch(\Exception $e) {
            Log::error('IMAP Fetch Error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            $this->error("Failed to connect to IMAP server: " . $e->getMessage());
            return 1;
        }
    }

    private function getThreadId($messageId, $inReplyTo, $subject)
    {
        // If it's a reply, find the thread
        if ($inReplyTo) {
            $parentEmail = Mail::where('message_id', $inReplyTo)->first();
            if ($parentEmail) {
                return $parentEmail->thread_id;
            }
        }
        
        // Check by subject (fallback)
        $cleanSubject = $this->cleanSubject($subject);
        $existingThread = Mail::where('subject', $cleanSubject)
                            ->whereNotNull('thread_id')
                            ->orderBy('received_at', 'asc')
                            ->first();
        
        if ($existingThread) {
            return $existingThread->thread_id;
        }
        
        // New thread - use first message ID
        return $messageId;
    }

private function cleanSubject($subject)
{
    if (empty($subject)) {
        return '';
    }
    
    // Remove Re:, RE:, Fwd:, FW:, etc.
    $subject = preg_replace('/^(re|fwd|fw|fw\s*:):\s*/i', '', trim($subject));
    
    // Remove multiple spaces
    $subject = preg_replace('/\s+/', ' ', $subject);
    
    return trim($subject);
}

    private function cleanMessageId($messageId)
{
    if (empty($messageId)) {
        return null;
    }
    
    // Remove < > and whitespace
    return trim(str_replace(['<', '>'], '', $messageId));
}
}
