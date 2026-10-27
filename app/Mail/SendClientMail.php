<?php

namespace App\Mail;


use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendClientMail extends Mailable implements ShouldQueue
{
    public array $data;
    public $emailattachment;
    public $email;
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct($data, $emailattachment, $email)
    {
        $this->data = $data;
        $this->emailattachment = $emailattachment;
        $this->email = $email;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            subject: $this->data['subject'],
            to: $this->data['recipient'],
            from: $this->email ?? config('mail.from.address'),
        );
        if (isset($this->data['cc'])) {
            $envelope->cc($this->data['cc']);
        }
        if (isset($this->data['bcc'])) {
            $envelope->bcc($this->data['bcc']);
        }
        return $envelope;
    }

    /**
     * Get the message content definition.
     */
    public function content()
    {
        return new Content(
            view: 'emails.mailBody',
            with: [
                'data' => $this->data
            ],



        );

    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if ($this->emailattachment && file_exists($this->emailattachment)) {
            return [
                Attachment::fromPath($this->emailattachment)
            ];
        }
        
        return [];
    }
}
