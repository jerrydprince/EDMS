<?php

namespace App\Mail;


use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendClientMailNoAttachment extends Mailable
{
    public array $data;
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            subject: $this->data['subject'],
            to: $this->data['recipient'],
        );
        if (isset($this->data['cc'])) {
            $envelope->cc($this->data['cc']);
        }
        if (isset($this->data['bcc'])) {
            $envelope->cc($this->data['bcc']);
        }
        // if(array_key_exists('cc', $this->data)){
        //    $envelope->cc($this->data['cc']);
        // }
        // if(array_key_exists('bcc', $this->data)){
        //     $envelope->bcc($this->data['bcc']);
        //  }
        return $envelope;
    }

    /**
     * Get the message content definition.
     */
    public function content()
    {
        return new Content(
            markdown: 'emails.mailBody',
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
        return [
        ];
    }
}
