<?php
namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\BookingCancelledMail;

class BookingNotificationService
{
    public static function sendCancellationMail($member, $booking, $type)
    {
        // if (!$member || !$member->Email) {
        //     return;
        // }

        Mail::to('rajrishisharma12125@gmail.com')
            ->queue(new BookingCancelledMail($booking, $type));
    }
}