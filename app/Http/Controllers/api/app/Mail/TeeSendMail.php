<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeeSendMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $subject;  // Added dynamic subject property

    public function __construct($data, $subject)
    {
        $this->data = $data;
        $this->subject = $subject;  // Assign dynamic subject
    }

    public function envelope()
    {
        return new Envelope(
            subject: $this->subject);
    }
    public function content()
    {
        return new Content(
            markdown: 'emails.tee_mail',
        );
    }

}
