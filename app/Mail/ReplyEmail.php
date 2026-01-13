<?php

namespace App\Mail;

use BeyondCode\Mailbox\Drivers\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
//use Illuminate\Mail\Mailables\Headers;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Log as FacadeLog;

class ReplyEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $reply;
    public $original;
    public $emailattachment;
    public function __construct($reply, $original, $emailattachment = null)
    {
        $this->reply = $reply;
        $this->original = $original;
        $this->emailattachment = $emailattachment;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // return new Envelope(
        //     subject: 'Re: ' . ($this->original['subject'] ?? 'No Subject'),
        // );

         $envelope = new Envelope(
            subject: 'Re: ' . ($this->original['subject'] ?? ''),
            to: $this->original['recipient'],
            //from: $this->email ?? config('mail.from.address'),
            // from: new Address('jeffrey@example.com', 'Jeffrey Way'),
            // replyTo: [
            //     new Address($this->original['subject'] ?? ''),
            // ],
        );

        return $envelope;

        //  return new Envelope(
        //     to: [new Address($this->recipientEmail, $this->recipientName)],
        //     cc: [new Address('cc@example.com', 'CC Person')],
        //     bcc: [new Address('bcc@example.com')],
        //     subject: 'Re: ' . ($this->originalEmail['subject'] ?? 'Your inquiry'),
        //     replyTo: [new Address('support@example.com', 'Support Team')],
        // );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reply',
        );
    }

    // public function headers(): Headers
    // {
    //     //$headers = new Headers();
    //     //if (isset($this->original['message_id'])) {
    //         // $headers->addTextHeader('In-Reply-To', $this->original['message_id']);
    //         // $references = $this->original['references'] ?? '';
    //         // $references .= ' ' . $this->original['message_id'];
    //         // $headers->addTextHeader('References', trim($references));
    //         FacadeLog::info("original", $this->original);
    //        return new Headers(
    //             messageId: $this->original['message_id'],
    //             references: [$this->original['message_id']],
    //         );
    //     // }
    //     // return new Headers();

    // }



    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        //  return [
        //     Attachment::fromPath($this->emailattachment)
        // ];

         if ($this->emailattachment && file_exists($this->emailattachment)) {
            return [
                Attachment::fromPath($this->emailattachment)
            ];
        }
        
        return [];
    }
}
