<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class FcmService
{
    public static function sendFCMMessage($notification, $fcmTokens, $serverKey)
    {
        $url = 'https://fcm.googleapis.com/v1/projects/aepta-edc61/messages:send';

        Log::info("FCM sent to token: $fcmTokens");

        $data = [
            "message" => [
                "token" => $fcmTokens,
                "notification" => [
                    "title" => $notification->title,
                    "body" => $notification->short_descriptions,
                    "image" => 'https://teebooking.aepta.in/get-notification-image/' . $notification->image,
                ],
                "data" => [
                    "type" => "Notification",
                ]
            ]
        ];

        $encodedData = json_encode($data);

        $headers = [
            'Authorization: Bearer ' . $serverKey,
            'Content-Type: application/json; UTF-8',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
