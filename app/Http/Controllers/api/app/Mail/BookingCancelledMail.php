<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;

class BookingCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $type;

    public function __construct($booking, $type)
    {
        $this->booking = $booking;
        $this->type = $type;
    }

    public function build()
    {
        return $this->subject($this->type . ' Booking Cancelled')
                    ->view('emails.booking_cancelled')
                    ->with([
                        'booking' => $this->booking,
                        'type' => $this->type
                    ]);
    }
}

// namespace App\Mail;

// use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Mail\Mailable;
// use Illuminate\Mail\Mailables\Content;
// use Illuminate\Mail\Mailables\Envelope;
// use Illuminate\Queue\SerializesModels;

// class BookingCancelledMail extends Mailable
// {
//     use Queueable, SerializesModels;

//     /**
//      * Create a new message instance.
//      *
//      * @return void
//      */
//     public function __construct()
//     {
//         //
//     }

//     /**
//      * Get the message envelope.
//      *
//      * @return \Illuminate\Mail\Mailables\Envelope
//      */
//     public function envelope()
//     {
//         return new Envelope(
//             subject: 'Booking Cancelled Mail',
//         );
//     }

//     /**
//      * Get the message content definition.
//      *
//      * @return \Illuminate\Mail\Mailables\Content
//      */
//     public function content()
//     {
//         return new Content(
//             view: 'view.name',
//         );
//     }

//     /**
//      * Get the attachments for the message.
//      *
//      * @return array
//      */
//     public function attachments()
//     {
//         return [];
//     }
// }

