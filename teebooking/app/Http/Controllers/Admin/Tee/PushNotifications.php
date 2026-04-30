<?php

namespace App\Http\Controllers\Admin\tee;

use App\Http\Controllers\Controller;
use App\Jobs\SendFCMNotification;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\Member;
use Carbon\Carbon;
class PushNotifications extends Controller
{
    public function index(Request $request)
    {
        $query_param = [];
        $search = $request->input('search'); // Use input() method to retrieve search query
        if ($request->has('search')) {
            $key = explode(' ', $request->input('search'));
            $notification = Notifications::where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('title', 'like', "%{$value}%"); // Use orWhere() for multiple keywords
                }
            })->latest()->paginate(10); // Paginate the results
            $query_param = ['search' => $request->input('search')];
        } else {
            $notification = Notifications::latest()->paginate(10); // No search, so paginate all records
        }
        $notification->appends($query_param); // Append query parameters to paginator

        return view('admin.tee.notifications.index', compact('notification', 'search'));
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
        $notification->time = Carbon::now();
        $notification->date = Carbon::today();
        $notification->save();
        return redirect()->back()->with('Notification sent successfully!');
    }

    public function edit($id)
    {
        $notification = Notifications::find($id);
        return view('admin.tee.notifications.edit', compact('notification'));
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
        $notification->time = Carbon::now();
        $notification->date = Carbon::today();
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
    
    public static function getAccessToken() {
        $serviceAccountData = json_decode(file_get_contents('https://teebooking.aepta.in/AEPTAServiceAccountKey.json'), true);
    
        $jwtHeader = base64_encode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT'
        ]));
    
        $now = time();
        $jwtPayload = base64_encode(json_encode([
            'iss' => $serviceAccountData['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]));
    
        $dataToSign = $jwtHeader . '.' . $jwtPayload;
    
        $privateKey = openssl_pkey_get_private($serviceAccountData['private_key']);
        openssl_sign($dataToSign, $jwtSignature, $privateKey, 'SHA256');
        $jwtSignature = base64_encode($jwtSignature);
    
        $jwt = $dataToSign . '.' . $jwtSignature;
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        $response = json_decode($response, true);
        return $response['access_token'];
    }
    
    public static function sendFCMMessage($notification, $fcmTokens, $serverKey) {
        $url = 'https://fcm.googleapis.com/v1/projects/aepta-edc61/messages:send';
        
        $data = [
            "message" => [
                "token" => $fcmTokens,
                "notification" => [
                    "title" => $notification->title,
                    "body" => $notification->description,
                    "image" => 'https://admin.club26.org/notification/'.$notification->image
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

    // public function broadcast(Request $request)
    // {
    //     $id = $request->id;
        
    //     $notification = Notifications::find($id);
    //     if (!$notification) {
    //         return redirect()->back()->with('error', 'Notification not found');
    //     }
    //     $serverKey = $this->getAccessToken();
    //     $fcmTokens = Member::whereNotNull('device_id')
    //                         ->where('device_id', '!=', '')
    //                         ->pluck('device_id')->first();
        
    //     $this->sendFCMMessage($notification, $fcmTokens, $serverKey);
    //     // foreach ($fcmTokens as $token) {
    //     //     $this->sendFCMMessage($notification, $token, $serverKey);
    //     // }
    //     return redirect()->back()->with('success', 'Broadcast successfully sent!');
    // }
    public function broadcast(Request $request)
{
    $id = $request->id;
    $notification = Notifications::find($id);

    if (!$notification) {
        return redirect()->back()->with('error', 'Notification not found');
    }

    $serverKey = $this->getAccessToken();

    // Get all FCM tokens
    $allTokens = Member::whereNotNull('device_id')
                        ->where('device_id', '!=', '')
                        ->pluck('device_id')
                        ->toArray();

    // Chunk into batches of 500 (FCM limit)
    $tokenChunks = array_chunk($allTokens, 500);

    foreach ($tokenChunks as $chunk) {
        dispatch(new SendFCMNotification($chunk, $notification, $serverKey));
    }

    return redirect()->back()->with('success', 'Broadcast queued for all users!');
}
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
}
