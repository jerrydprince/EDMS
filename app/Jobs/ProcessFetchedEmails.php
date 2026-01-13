<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Mail as MailsModel;

class ProcessFetchedEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // in seconds
    /**
     * Create a new job instance.
     */
    public function __construct(public array $emailData)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Processing email data', ['count' => count($this->emailData)]);
            
        $emailsToInsert = [];
        $existingMessageIds = MailsModel::pluck('message_id')->toArray();
            
            foreach ($this->emailData as $emailData) {
                
                if (in_array($emailData['message_id'], $existingMessageIds)) {
                    continue;
                }
                // Data is already extracted from IMAP messages
                $emailsToInsert[] = [
                    'message_id' => $emailData['message_id'],
                    'subject' => $emailData['subject'],
                    'recipient' => $emailData['recipient'],
                    'from_name' => $emailData['from_name'],
                    'body_text' => $emailData['body_text'],
                    'body' => $emailData['body_html'],
                    'received_at' => $emailData['received_at'],
                    'has_attachments' => $emailData['has_attachments'],
                    'user_id' => $emailData['user_id'],
                    'thread_id' => $emailData['thread_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

            }
            
            // Insert all at once (much faster!)
            if (!empty($emailsToInsert)) {
                
                MailsModel::insert($emailsToInsert);
                Log::info('Successfully inserted emails', ['count' => count($emailsToInsert)]);
            }
            
        } catch (\Exception $e) {
            // Handle exceptions, log errors, etc.
            Log::error('Error processing fetched emails: ' . $e->getMessage());
            throw $e; // Rethrow to trigger retry mechanism
        }
    }

    public function failed(\Throwable $exception)
    {
        // Handle job failure (after all retries)
        Log::error("Job failed permanently: {$exception->getMessage()}", [
            //'message_id' => $this->emailData['message_id'],
        ]);

        // Optionally notify admin
    }
}
