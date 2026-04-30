<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\SendFCMNotification;
use App\Models\Notifications;
use App\Models\Member;
use App\Models\NotificationUser; 
use Carbon\Carbon;
use App\Services\FCMService;
use App\Models\MemberReceipt;

use Log;
class PushNotifications extends Controller
{
  public function index(Request $request)
{
    if (!auth()->guard('admin')->user()) {
        return redirect()->route('admin.login');
    }

    $permittedUserCount = Member::where('has_notification_permission', 1)->count();

    $query_param = [];
    $search = $request->input('search');

    if ($request->has('search')) {
        $key = explode(' ', $search);

        $notification = Notifications::withCount([
            'notificationUsers as sent_user_count' => function ($query) {
                $query->where('success', true);
            }
        ])->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('title', 'like', "%{$value}%");
            }
        })->latest()->paginate(10);

        $query_param = ['search' => $search];
    } else {
        $notification = Notifications::withCount([
            'notificationUsers as sent_user_count' => function ($query) {
                $query->where('success', true);
            }
        ])->latest()->paginate(10);
    }

    $notification->appends($query_param);

    return view('admin.notifications.config.index', compact('notification', 'search', 'permittedUserCount'));
}



    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required'
        ], [
            'title.required' => 'title is required!',
        ]);

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            $image = $request->file('banner');
            $extension = $image->getClientOriginalExtension();
            $imageName = time() . '_' . uniqid() . '.' . $extension;
            $image->move(public_path() . "/notification", $imageName);
            // $image->storeAs('public/admin/assets/notification', $imageName); // Save the image to the storage folder
        } else {

            if (!$request->hasFile('banner')) {

            } elseif (!$request->file('banner')->isValid()) {

            }
            $imageName = null;
        }

        $notification = new Notifications;
        $notification->title = $request->title;
        $notification->description = $request->description;
        $notification->image = $imageName;
        $notification->active_status = 1;
        $notification->address = $request->address;
        $notification->short_descriptions = $request->short_descriptions;
        $notification->time = $request->time;
        $notification->date = $request->date;
        $notification->save();
        return redirect()->back()->with('Notification sent successfully!');
    }

    public function edit($id)
    {
        $notification = Notifications::find($id);
        return view('admin.notifications.config.edit', compact('notification'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required'
        ], [
            'title.required' => 'Title is required!',
        ]);

        $notification = Notifications::findOrFail($id); // Find the existing notification by its ID

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            // Handle file upload and update the image if a new one is provided
            $image = $request->file('banner');
            // $imageName = time() . '_' . $image->getClientOriginalName();
            $extension = $image->getClientOriginalExtension();
            $imageName = time() . '_' . uniqid() . '.' . $extension;
            $image->move(public_path() . "/notification", $imageName);
            // $image->storeAs('public/admin/assets/notification', $imageName);
            $notification->image = $imageName; // Update the image name
        }
        $notification->short_descriptions = $request->short_descriptions;
        $notification->time = $request->time;
        $notification->date = $request->date;
        $notification->title = $request->title;
        $notification->description = $request->description;
        $notification->save();
        return redirect()->route('notifications')->with('success','Notification updated successfully!');
    }

    public function status(Request $request)
    {
        $id = $request->id;
        $status = $request->status; // Change active_status to status

        $notification = Notifications::find($id);
        if ($notification) {
            $notification->active_status = $status;
            $notification->save();
        }else{
            return redirect()->back()->with('message','notification not found');
        }
    }

    public function delete($id)
    {
        $notification = Notifications::findOrFail($id);
        $notification->delete();
        return redirect()->route('notifications')->with('success','Notification delete successfully!');
    }
    // public function broadcast(Request $request)
    // {
    //     $id = $request->id;
    //     $status = $request->status;
    //     $notification = Notifications::find($id);

    //     if ($notification) {
    //         $notification->broadcast = $status;
    //         $notification->save();
    //     }
    //      $fcm_token = Member::whereNotNull('device_id')->pluck('device_id')->all();
    //         if($notification->broadcast == 1){
    //             $url = 'https://fcm.googleapis.com/fcm/send';
    //             $serverKey = 'AAAAk3NTDVY:APA91bF_bQcQejndlXRTI6ctRVTSt2nGFdUk4uYJLYg4iRWaJI942VUNBfpnrEOxSERZdojPj3lpHBpncFw9C0rMWMhzVDrvcVUpU335crRT1zok7LajNkdCBNvY0PrN9gFl0hu-3rDA'; // ADD SERVER KEY HERE PROVIDED BY FCM
    //             $data = [
    //                 "registration_ids" =>$fcm_token,
    //                 "notification" => [
    //                     "title" => $notification->title,
    //                     "body" => $notification->description, // This should be the body text of the notification
    //                     "image" => "https://t3.ftcdn.net/jpg/02/77/69/26/360_F_277692680_b65wdSQDuWZRrKwIUmGQo0zwND6n0MZR.jpg",
    //                 ]
    //             ];
    //             $encodedData = json_encode($data);
    //             $headers = [
    //                 'Authorization:key=' . $serverKey,
    //                 'Content-Type: application/json',
    //             ];
    //             $ch = curl_init();
    //             curl_setopt($ch, CURLOPT_URL, $url);
    //             curl_setopt($ch, CURLOPT_POST, true);
    //             curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //             curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    //             curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    //             // Disabling SSL Certificate support temporarly
    //             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //             curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
    //             // Execute post
    //             $result = curl_exec($ch);
    //             if ($result === FALSE) {
    //                 die('Curl failed: ' . curl_error($ch));
    //             }
    //             // Close connection
    //             curl_close($ch);
    //             $message = 'Broadcast successfully sent!' ;
    //         }else{
    //             $message = 'Your new success message here!' ;
    //         }
    //     return redirect()->back()->with('message',$message);
    // }
    
      public static function getAccessToken()
{
    // $serviceAccountPath = env('FIREBASE_CREDENTIALS_PATH'); // load from .env
$serviceAccountPath = storage_path('app/firebase/holidayclub-service-account.json');

    if (!file_exists($serviceAccountPath)) {
        throw new \Exception("Firebase credentials file not found at: {$serviceAccountPath}");
    }

    $serviceAccountData = json_decode(file_get_contents($serviceAccountPath), true);

    if (!$serviceAccountData || !isset($serviceAccountData['client_email'], $serviceAccountData['private_key'])) {
        throw new \Exception("Invalid Firebase credentials JSON.");
    }

    // JWT Header
    $jwtHeader = rtrim(strtr(base64_encode(json_encode([
        'alg' => 'RS256',
        'typ' => 'JWT'
    ])), '+/', '-_'), '=');

    $now = time();

    // JWT Payload
    $jwtPayload = rtrim(strtr(base64_encode(json_encode([
        'iss'   => $serviceAccountData['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud'   => 'https://oauth2.googleapis.com/token',
        'exp'   => $now + 3600,
        'iat'   => $now
    ])), '+/', '-_'), '=');

    $dataToSign = $jwtHeader . '.' . $jwtPayload;

    // Sign with private key
    $privateKey = openssl_pkey_get_private($serviceAccountData['private_key']);
    openssl_sign($dataToSign, $jwtSignature, $privateKey, 'SHA256');
    $jwtSignature = rtrim(strtr(base64_encode($jwtSignature), '+/', '-_'), '=');

    $jwt = $dataToSign . '.' . $jwtSignature;

    // Exchange JWT for access token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion'  => $jwt
    ]));

    $response = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($response, true);

    if (isset($response['access_token'])) {
        return $response['access_token'];
    }

    throw new \Exception("Failed to fetch Firebase access token: " . json_encode($response));
}
    // public static function getAccessToken() {
    //     $url = 'https://holidayclub.in/holidayclub/HolidayClubServiceAccountKey.json';
    //     Log::info("FCM sent to token: 1");
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     $response = curl_exec($ch);
        
    //     if (curl_errno($ch)) {
    //         echo 'Error:' . curl_error($ch);
    //     }
        
    //     curl_close($ch);
        
    //     $serviceAccountData = json_decode($response, true);
    //     // dd($serviceAccountData);
    //     // $serviceAccountData = json_decode(file_get_contents('https://club26.org/mobileAPI/serviceAccountKey.json'), true);
    
    //     $jwtHeader = base64_encode(json_encode([
    //         'alg' => 'RS256',
    //         'typ' => 'JWT'
    //     ]));
    
    //     $now = time();
    //     $jwtPayload = base64_encode(json_encode([
    //         'iss' => $serviceAccountData['client_email'],
    //         'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
    //         'aud' => 'https://oauth2.googleapis.com/token',
    //         'exp' => $now + 3600,
    //         'iat' => $now
    //     ]));
    
    //     $dataToSign = $jwtHeader . '.' . $jwtPayload;
    
    //     $privateKey = openssl_pkey_get_private($serviceAccountData['private_key']);
    //     openssl_sign($dataToSign, $jwtSignature, $privateKey, 'SHA256');
    //     $jwtSignature = base64_encode($jwtSignature);
    
    //     $jwt = $dataToSign . '.' . $jwtSignature;
    
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    //     curl_setopt($ch, CURLOPT_POST, 1);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    //         'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
    //         'assertion' => $jwt
    //     ]));
    
    //     $response = curl_exec($ch);
    //     curl_close($ch);
    
    //     $response = json_decode($response, true);
    //     return $response['access_token'];
    // }
    
    public static function sendFCMMessage($notification, $fcmTokens, $serverKey) {
        $url = 'https://fcm.googleapis.com/v1/projects/gvi-club/messages:send';
        Log::info("FCM sent to token: $notification");
        $data = [
            "message" => [
                "token" => $fcmTokens,
                "notification" => [
                    "title" => $notification->title,
                    "body" => $notification->short_descriptions,
                    "image" =>  'https://gvicc.in/gvicc/get-notification-image/'.$notification->image
                ],
                "data" => [
                    "type" => "Notification"
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

public function broadcast(Request $request)
{
    $notificationId = $request->id;

    // Fetch notification
    $notification = Notifications::find($notificationId);
    if (!$notification) {
        return redirect()->back()->with('error', 'Notification not found');
    }

    // Generate Firebase Access Token
    $serverKey = $this->getAccessToken();
    Log::info("Firebase Access Token Generated Successfully");

    /**
     * STEP 1:
     * Fetch all members who can receive notifications
     */
    $members = Member::where('has_notification_permission', true)
        ->whereNotNull('device_id')
        ->get(['id', 'device_id']);

    if ($members->isEmpty()) {
        return redirect()->back()->with('error', 'No members found with Device ID');
    }

    /**
     * STEP 2:
     * List of users who already received this notification
     */
    $alreadySent = NotificationUser::where('notification_id', $notificationId)
        ->where('success', true)
        ->pluck('user_id')
        ->toArray();

    /**
     * STEP 3:
     * Filter users → send notification only to new users
     */
    $pendingDelivery = $members->reject(function ($member) use ($alreadySent) {
        return in_array($member->id, $alreadySent);
    });

    if ($pendingDelivery->isEmpty()) {
        return redirect()->route('notifications')->with('success', 'All users already received this notification.');
    }

    /**
     * STEP 4:
     * Chunk tokens into batches of 500 (FCM limit)
     */
    $chunks = $pendingDelivery->chunk(500);

    

    foreach ($chunks as $chunk) {
        // Convert to arr → [device_id => user_id]
        $mappedTokens = $chunk->pluck('id', 'device_id')->toArray();

        dispatch(new SendFCMNotification(
            $mappedTokens,
            $notification,
            $serverKey
        ));
    }

    return redirect()->route('notifications')->with(
        'success',
        'Broadcast started → Sending only to NEW users who did not receive earlier!'
    );
}

// public function broadcast(Request $request)
// {
//     $id = $request->id;
//     $notification = Notifications::find($id);

//     if (!$notification) {
//         return redirect()->back()->with('error', 'Notification not found');
//     }

//     $serverKey = $this->getAccessToken();

// \Log::info("sccetss token   is    -------$serverKey");

//     // ✅ Step 1: Get all user-device_id pairs
//     $allMembers = Member::where('has_notification_permission', true)
//                         ->whereNotNull('device_id')
//                         ->get(['id', 'device_id']);

//     // Create array: [device_id => user_id]
//     $allTokens = $allMembers->pluck('id', 'device_id')->toArray();



//     // ✅ Step 2: Get list of user_ids who already received this notification
//     $alreadySentUserIds = NotificationUser::where('notification_id', $notification->id)
//                                           ->where('success', true)
//                                           ->pluck('user_id')
//                                           ->toArray();

//     // ✅ Step 3: Filter tokens — exclude already sent users
//     $filteredTokens = array_filter($allTokens, function ($userId) use ($alreadySentUserIds) {
//         return !in_array($userId, $alreadySentUserIds);
//     });

//     // ✅ Step 4: Chunk and dispatch
//     $tokenChunks = array_chunk($filteredTokens, 500, true); // preserve key => value

//     Log::info('Dispatching token chunks:', [
//         'chunks_count' => count($tokenChunks),
//     ]);

//     foreach ($tokenChunks as $chunk) {
//         dispatch(new SendFCMNotification($chunk, $notification, $serverKey));
//     }

//     return redirect()->route('notifications')->with('success', 'Broadcast queued for new users only!');
// }


    private function sendFcmNotification($notification, $fcmTokens, $serverKey)
    {
        // dd($fcmTokens);
        $url = 'https://fcm.googleapis.com/fcm/send';
        // $serverKey = 'AAAAk3NTDVY:APA91bF_bQcQejndlXRTI6ctRVTSt2nGFdUk4uYJLYg4iRWaJI942VUNBfpnrEOxSERZdojPj3lpHBpncFw9C0rMWMhzVDrvcVUpU335crRT1zok7LajNkdCBNvY0PrN9gFl0hu-3rDA'; // Replace with your server key
        $data = [
            "registration_ids" => $fcmTokens,
            "notification" => [
                "title" => $notification->title,
                "body" => $notification->description,
                'time'=> $notification->time,
                'date'=> $notification->date,
                "address"=> $notification->address,
                "image" => "https://www.shutterstock.com/image-vector/banner-mega-offer-260nw-760648000.jpg", // Replace with your image URL
            ]
        ];

        $encodedData = json_encode($data);
        $headers = [
            'Authorization:key=' . $serverKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => $encodedData,
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result !== false;
    }
    public function showSentUsers($id)
{
    $notification = Notifications::findOrFail($id);

    // Get users who received this notification successfully
    $sentUsers = NotificationUser::with('user') // assuming relation is set
        ->where('notification_id', $id)
        ->where('success', true)
        ->get();
   
    
    return view('admin.notifications.config.sent_users', compact('notification', 'sentUsers'));
}
public function resend($id)
{
    $notificationUser = NotificationUser::with('notification')->find($id);

    if (!$notificationUser) {
        return response()->json([
            'success' => false,
            'message' => 'Notification user not found.',
        ]);
    }

    $userId = $notificationUser->user_id;

    $memberProfile = Member::where('id', $userId)->first();

    if (!$memberProfile || !$memberProfile->device_id) {
        return response()->json([
            'success' => false,
            'message' => 'Device ID not found for this user.',
        ]);
    }

    $deviceId = $memberProfile->device_id;

    // Get the notification data
    $notification  = Notifications::find($notificationUser->notification_id);

    if (!$notification) {
        return response()->json([
            'success' => false,
            'message' => 'Notification details not found.',
        ]);
    }

    // Get your server key from Firebase
    $serverKey = $this->getAccessToken();

    // Send FCM
    $response = FCMService::sendFCMMessage($notification, $deviceId, $serverKey);


    return response()->json([
        'success' => true,
        'message' => 'Notification resent successfully.',
        'fcm_response' => json_decode($response, true),
    ]);
}
public function reminders(Request $request)
{
    $query = MemberReceipt::query();

    // Filter by PayStatus if selected
    if ($request->has('status') && in_array($request->status, ['Success', 'pending'])) {
        $query->where('PayStatus', $request->status);
    }

    // Search by Member ID
    if ($request->filled('search')) {
        $query->where('Mem_Id', 'like', '%' . $request->search . '%');
    }

    // Paginate results (10 per page)
    $member = $query->paginate(10)->appends($request->all());

    return view('admin.notifications.config.reminders', compact('member'));
}

public function billPayment(Request $request,FCMService $fcm)
{
    Log::info( $request->mem_id);
    // Get member profile
    $memberProfile = Member::where('SC_ID', $request->mem_id)->first();
      Log::info( $memberProfile);
    if (!$memberProfile) {
        return response()->json(['message' => 'Member not found'], 404);
    }

    // Get receipt details
    $receipt = MemberReceipt::where('Mem_Id', $memberProfile->SC_ID)->first();
    if (!$receipt) {
        return response()->json(['message' => 'Bill not found'], 404);
    }

    // Convert month number to full month name
    $monthName = Carbon::createFromDate(null, $receipt->BillMonth, 1)->format('F');
 $BillAmt = $receipt->BillAmt;
            
            $PaymentReceived = $receipt->PaymentReceived;

            $amount_payable = number_format($BillAmt - $PaymentReceived, 2);
    // Professional notification title & body
    $title = "Bill Payment Reminder";
    $body = "Your bill Rs. {$amount_payable} for {$monthName} is due. Please tap below to make your payment.";

  
 // Assuming you have an FCM service
    $response = $fcm->sendNotification(
        $memberProfile->device_id,
        $title,
        $body,
        [
            'id' => $receipt->id,
            'screen' => 'Invoice',
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'type' => 'BILL_PAYMENT',
        ]
    );

    return response()->json([
        'message' => 'Notification sent successfully',
        'fcm_response' => $response
    ]);
}

public function billPaymentReminderToAll(FCMService $fcm)
{
    // Get all unpaid receipts
    $unpaidReceipts = MemberReceipt::where('PayStatus', 'pending')->get();

    if ($unpaidReceipts->isEmpty()) {
        return response()->json(['message' => 'No unpaid bills found.'], 404);
    }

    // Get unique member IDs
    $memberIds = $unpaidReceipts->pluck('Mem_Id')->unique();

    // Fetch members who can receive notifications
    $members = Member::whereIn('SC_ID', $memberIds)
        ->where('has_notification_permission', true)
        ->whereNotNull('device_id')
        ->get(['MemberID', 'device_id']);

    if ($members->isEmpty()) {
        return response()->json(['message' => 'No members eligible for notifications.'], 404);
    }

    $notifications = [];

    foreach ($unpaidReceipts as $receipt) {
        $member = $members->firstWhere('MemberID', $receipt->Mem_Id);
        if (!$member) {
            continue;
        }

        $monthName = Carbon::createFromDate(null, $receipt->BillMonth, 1)->format('F');
        $BillAmt = $receipt->BillAmt;
        $PaymentReceived = $receipt->PaymentReceived;
         $amount_payable = number_format($BillAmt - $PaymentReceived, 2);

        $title = "Bill Payment Reminder";
        $body = "Your bill Rs. {$amount_payable} for {$monthName} is due. Please tap below to make your payment.";

        $notifications[] = [
            'device_id' => $member->device_id,
            'title'     => $title,
            'body'      => $body
        ];
    }

    // Send in chunks (max 100 per batch)
    foreach (array_chunk($notifications, 100) as $chunk) {
        
        foreach ($chunk as $notif) {
            try {
              $fcm->sendNotification(
                $notif['device_id'],
                'Bill Payment Reminder',
                $notif['body'],
                [
                    'screen' => 'Invoice',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]
            );
                // $this->fcm->sendNotification($notif['device_id'], $notif['title'], $notif['body']);
            } catch (\Exception $e) {
                Log::error('Notification failed for device: ' . $notif['device_id'], ['error' => $e->getMessage()]);
            }
        }
    }

    return response()->json(['message' => 'Notifications sent successfully']);
}

}
