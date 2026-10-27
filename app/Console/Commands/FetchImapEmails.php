<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemConfiguration;
use App\Models\Mail as MailsModel;
use App\Models\User;

class FetchImapEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch external emails via IMAP using dynamic configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting IMAP email fetch...');

        $configs = SystemConfiguration::pluck('value', 'key');
        
        $host = $configs['IMAP_HOST'] ?? $configs['imap.host'] ?? null;
        $port = $configs['IMAP_PORT'] ?? $configs['imap.port'] ?? 993;
        $encryption = $configs['IMAP_ENCRYPTION'] ?? $configs['imap.encryption'] ?? 'ssl';
        $username = $configs['IMAP_USERNAME'] ?? $configs['imap.username'] ?? null;
        $password = $configs['IMAP_PASSWORD'] ?? $configs['imap.password'] ?? null;

        if (!$host || !$username || !$password) {
            $this->error('IMAP configuration is incomplete. Please add IMAP_HOST, IMAP_USERNAME, and IMAP_PASSWORD to System Configurations.');
            return;
        }

        try {
            $client = \Webklex\IMAP\Facades\Client::make([
                'host'          => $host,
                'port'          => $port,
                'encryption'    => strtolower($encryption),
                'validate_cert' => false, // Set to false to avoid cert errors with testing servers
                'username'      => $username,
                'password'      => $password,
                'protocol'      => 'imap'
            ]);
            $client->connect();
            
            // Usually Inbox is what we want
            $folder = $client->getFolder('INBOX');
            
            // Get all messages (Ethereal sometimes marks them as seen immediately)
            $messages = $folder->messages()->all()->get();
            $this->info('Found ' . $messages->count() . ' messages in INBOX');

            // According to requirements: "only the AGF registry user and super admin should be able to receive external mail"
            // We route it to the registry user, or fallback to super admin.
            $registryUser = User::role('agf registry')->first();
            if (!$registryUser) {
                $registryUser = User::role('super admin')->first();
            }

            foreach($messages as $message) {
                $newMail = new MailsModel();
                $newMail->recipient = $registryUser ? $registryUser->email : 'admin@example.com';
                $newMail->subject = $message->getSubject();
                $newMail->body = $message->getHTMLBody(true) ?: $message->getTextBody();
                
                $from = $message->getFrom();
                $newMail->sender = isset($from[0]) ? $from[0]->mail : 'Unknown';
                
                $newMail->sender_id = $registryUser ? $registryUser->id : 1; // external origin but DB requires int
                $newMail->user_id = $registryUser ? $registryUser->id : 1;
                $newMail->is_sent = 0; // Received mail
                $newMail->save();
                
                // Mark as read on IMAP server
                $message->setFlag(['Seen']);
                $this->info('Saved external message: ' . $message->getSubject());
            }
            
            $client->disconnect();
            $this->info('Finished fetching emails.');

        } catch (\Exception $e) {
            $this->error('Error fetching emails: ' . $e->getMessage());
        }
    }
}
