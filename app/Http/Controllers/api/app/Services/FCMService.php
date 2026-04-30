<?php

namespace App\Services;

use Illuminate\Support\Facades\Log; 
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Http;

class FCMService
{
    protected $messaging;

   public function __construct()
{
    $credentialsPath = config('services.firebase.credentials');

    // Log the credentials path to see if it's correct
    Log::info('Firebase credentials path:', ['path' => $credentialsPath]);

    if (is_null($credentialsPath)) {
        Log::error('Firebase credentials path is null. Please check the configuration.');
    }

    $factory = (new Factory)
        ->withServiceAccount($credentialsPath);

    $this->messaging = $factory->createMessaging();
}

    public function sendNotification($deviceToken, $title, $body, $data = [])
    {
            try {
            if (!$deviceToken) {
                Log::warning('FCM skipped: empty token');
                return false;
            }

            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification(
                    Notification::create($title, $body)
                )
                ->withData(array_map('strval', $data)); // 🔥 important

            $this->messaging->send($message);

            Log::info('FCM sent', ['token' => $deviceToken]);

            return true;

        } catch (\Throwable $e) {

            Log::error('FCM send error', [
                'error' => $e->getMessage(),
                'token' => $deviceToken
            ]);

            return false;
        }
    
    }
    
    
    public static function sendFCMMessage($notification, $fcmTokens, $serverKey)
    {
        $url = 'https://fcm.googleapis.com/v1/projects/gvi-club/messages:send';

        Log::info("FCM sent to token: $fcmTokens");

        $data = [
            "message" => [
                "token" => $fcmTokens,
                "notification" => [
                    "title" => $notification->title,
                    "body" => $notification->short_descriptions,
                    "image" => 'https://gvicc.in/gvicc/get-notification-image/' . $notification->image,
                ],
                "data" => [
                    "type" => "Notification",
                     'id' => (string) $notification->id,
                    'screen' => 'Notification',   
                     'click_action' => 'FLUTTER_NOTIFICATION_CLICK', 
                ]
            ]
        ];
        Log::info($data);
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
    
     public static function sendFCMMessageV1(
        string $deviceToken,
        string $title,
        string $body,
        array $data = []
    ) {
        try {
            // ðŸ”¹ Path to service account JSON
            $jsonPath = storage_path(
                'app/firebase/holidayclub-service-account.json'
            );

            if (!file_exists($jsonPath)) {
                Log::error('Firebase JSON not found', ['path' => $jsonPath]);
                return false;
            }

            // ðŸ”¹ Load credentials
            $credentials = json_decode(file_get_contents($jsonPath), true);

            // ðŸ”¹ Get OAuth access token
            $accessToken = self::getAccessToken($credentials);

            if (!$accessToken) {
                Log::error('Failed to get Firebase access token');
                return false;
            }

            // ðŸ”¹ Project ID from JSON
            $projectId = $credentials['project_id'];

            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            // ðŸ”¹ Payload
            $payload = [
                "message" => [
                    "token" => $deviceToken,
                    "notification" => [
                        "title" => $title,
                        "body"  => $body,
                    ],
                    "data" => array_merge($data, [
                        "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                    ]),
                ],
            ];

            Log::info('Sending FCM payload', $payload);

            // ðŸ”¹ Send request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ])->post($url, $payload);

            return $response->json();

        } catch (\Throwable $e) {
            Log::error('FCM V1 Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate OAuth Access Token from Service Account JSON
     */
    private static function getAccessToken(array $credentials)
    {
        $now = time();

        $jwtHeader = base64_encode(json_encode([
            "alg" => "RS256",
            "typ" => "JWT"
        ]));

        $jwtClaim = base64_encode(json_encode([
            "iss"   => $credentials['client_email'],
            "scope" => "https://www.googleapis.com/auth/firebase.messaging",
            "aud"   => $credentials['token_uri'],
            "iat"   => $now,
            "exp"   => $now + 3600,
        ]));

        $signatureInput = $jwtHeader . '.' . $jwtClaim;

        openssl_sign(
            $signatureInput,
            $signature,
            $credentials['private_key'],
            'sha256'
        );

        $jwt = $signatureInput . '.' . base64_encode($signature);

        // ðŸ”¹ Request token
        $response = Http::asForm()->post($credentials['token_uri'], [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]);

        return $response->json()['access_token'] ?? null;
    }
}
