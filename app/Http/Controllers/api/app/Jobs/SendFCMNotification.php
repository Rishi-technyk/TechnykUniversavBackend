<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;
use App\Models\NotificationUser; 
use Log;

class SendFCMNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tokenMap;
    protected $notification;
    protected $serverKey;

    public function __construct(array $tokenMap, $notification, $serverKey)
{
    $this->tokenMap = $tokenMap;
    $this->notification = $notification;
    $this->serverKey = $serverKey;
}

public function handle()
{
    $url = 'https://fcm.googleapis.com/v1/projects/gvi-club/messages:send';

    foreach ($this->tokenMap as $token => $userId) {
        $message = [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title" => $this->notification->title,
                    "body" => $this->notification->short_descriptions,
                    "image" => 'https://gvicc.in/gvicc/get-notification-image/'. $this->notification->image,
                ],
                "data" => [
                    "type" => "Notification"
                ]
            ]
        ];

        $success = false;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($url, $message);

            $success = $response->successful();
  Log::info('Success', [
                    'success' => $success,
                    
                ]);
            if ($success) {
                Log::info('FCM sent to token.', [
                    'token' => $token,
                    'notification_id' => $this->notification->id,
                    'response' => $response->json()
                ]);
            } else {
                Log::error('FCM failed for token.', [
                    'token' => $token,
                    'notification_id' => $this->notification->id,
                    'response' => $response->body(),
                    'serverkey'=>$this->serverKey
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception while sending FCM.', [
                'token' => $token,
                'error' => $e->getMessage(),
                'notification_id' => $this->notification->id
            ]);
        }

    $existingRecord = NotificationUser::where('notification_id', $this->notification->id)
                                  ->where('user_id', $userId)
                                  ->first();

if (!$existingRecord) {
    NotificationUser::create([
        'notification_id' => $this->notification->id,
        'user_id' => $userId,
        'sent_at' => now(),
        'success' => $success,
    ]);
} elseif (!$existingRecord->success && $success) {
    // Previously failed, now succeeded — update it
    $existingRecord->update([
        'sent_at' => now(),
        'success' => true,
    ]);
}

    }
}

}
