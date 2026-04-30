<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailHelper
{
    /**
     * Send plain text email
     */
    public static function sendPlainMail($to, $subject, $message)
    {
        try {
            Mail::raw($message, function ($msg) use ($to, $subject) {
                $msg->to($to)
                    ->subject($subject);
            });

            return true;

        } catch (\Throwable $e) {

            Log::error('Mail sending failed', [
                'error' => $e->getMessage(),
                'to' => $to
            ]);

            return false;
        }
    }

    /**
     * Banquet cancellation mail
     */
    public static function sendBanquetCancellation($member, $booking, $deductionAmt, $gstAmt)
    {
        $message = "
BANQUET BOOKING CANCELLED

Member: {$member->DisplayName}
Member ID: {$member->MemberID}

Function Date: {$booking->funDate}

Charges: ₹ {$booking->charges}
Deduction: ₹ {$deductionAmt}
GST: ₹ {$gstAmt}
Total Deduction: ₹ " . ($deductionAmt + $gstAmt) . "

Cancelled On: " . now()->format('d M Y h:i A') . "

If you need help, please contact club office.

Regards,
GVI Club
";

        return self::sendPlainMail(
            $member->Email,
            'Banquet Booking Cancelled',
            $message
        );
    }
}