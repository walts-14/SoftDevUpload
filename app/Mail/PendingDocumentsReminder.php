<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PendingDocumentsReminder extends Mailable
{
    use Queueable, SerializesModels;

        public $user;

    public function __construct()
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Pending Document Submission')
                    ->view('emails.pending_documents')
                    ->with(['user' => $this->user]);
    }
    
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pending Documents Reminder',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
