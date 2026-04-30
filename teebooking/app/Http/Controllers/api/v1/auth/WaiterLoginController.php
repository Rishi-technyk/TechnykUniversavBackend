<?php

namespace App\Http\Controllers\api\v1\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\AC_UserMaster;
use App\Models\CustomerStatement;
use App\Models\CardClosingBalance;
use App\Models\OtpModel;
use App\Models\ACHomeMenu;
use Illuminate\Support\Facades\Validator;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendQueueEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WaiterLoginController extends Controller{
    
     
     public function login(Request $request)
    {
       $validator = Validator::make($request->all(), [
    'username' => 'required|string',
    'location' => 'required|exists:IM_LocationMaster,code',
]);
        // Check validation failure
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $username = $request->username;
        $location = $request->location;
        
       $staff = AC_UserMaster::where('UserName', $username)->first();

    if (!$staff) {
        return response()->json([
            'status' => false,
            'message' => 'User not found',
        ], 404);
    }

    // Update location
    $staff->LocationCode = $location;
    $staff->save();
     
        if($staff){  
          
            
             if (empty($staff->Mobile) || $staff->Mobile == '0') {
        return response()->json([
            'status' => false,
            'message' => 'The mobile number is not associated with this Staff ID.',
            'data' => ''
        ], 400);
             }
    
    // Generate OTP
    $alphanum = "0123456789";
    $otp = substr(str_shuffle($alphanum), 0, 6);

    // Save OTP
    $staff->OTP = $otp;
    $staff->save();

    // Compose SMS
    //   $authKey = "135468AwHMDbYRku58e1d959";
    // $mobileNumber = $staff->Mobile;
    // $senderId = "AEPTAD";
    // $TemplateID = "1207175086773161988";
    

    // $SMSText = "Dear Member,\n\nYour OTP for login to AEPTA App is $otp. Valid for 5 minutes. Please do not share this OTP.\n\nRegards,\n AEPTA\nby Technyk";
    // $message = urlencode($SMSText);

    // $postData = [
    //     'authkey' => $authKey,
    //     'mobiles' => $mobileNumber,
    //     'message' => $message,
    //     'sender' => $senderId,
    //     'route' => 4,
    //     'DLT_TE_ID' => $TemplateID,
    //     'country' => 91
    // ];

    // $url = "http://india.msg91.com/sendhttp.php";

    // // Send OTP via cURL
    // $ch = curl_init();
    // curl_setopt_array($ch, [
    //     CURLOPT_URL => $url,
    //     CURLOPT_RETURNTRANSFER => true,
    //     CURLOPT_POST => true,
    //     CURLOPT_POSTFIELDS => http_build_query($postData),
    //     CURLOPT_SSL_VERIFYHOST => 0,
    //     CURLOPT_SSL_VERIFYPEER => 0,
    // ]);

    // $output = curl_exec($ch);

    // if (curl_errno($ch)) {
    //     Log::error("SMS OTP sending failed: " . curl_error($ch));
    //     return response()->json([
    //         'status' => false,
    //         'message' => 'Failed to send OTP. Please try again later.',
    //         'data' => ''
    //     ], 500);
    // }

    // curl_close($ch);
     $message = "OTP: $otp" ;

    return response()->json([
        'status' => true,
        'message' => $message,
        'data' => ''
    ], 200);
       
        }else{
			return response()->json(array('status'=> false, 'message'=>'Invalid User.'), 403);
		}
    }


public function verifyOTP(Request $request)
{
    // 1. Validate request
    $validator = Validator::make($request->all(), [
        'member_id' => 'required|string|exists:AC_UserMaster,UserName',
        'otp' => 'required',
        'device_id' => 'nullable|string',
        'device_app_version' => 'nullable|string',
        'has_notification_permission' => 'nullable|boolean',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    // 2. Fetch user
    $user = AC_UserMaster::where('UserName', $request->member_id)->first();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'User not found',
        ], 404);
    }

Log::info($user);
    // 3. Check OTP
    if ($user->OTP != $request->otp) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid OTP',
        ], 401);
    }

    

    // 5. Generate new access token
    $accessToken = Str::random(60);

    // 6. Update user record
    $user->access_token = $accessToken;
    $user->device_id = $request->device_id ?? $user->device_id;
    $user->device_app_version = $request->device_app_version ?? $user->device_app_version;
    $user->has_notification_permission = $request->has_notification_permission ?? $user->has_notification_permission;
    $user->save();
     $user->role='staff';
    // 7. Return response
    return response()->json([
        'status' => true,
        'message' => 'OTP verified successfully',
        'data' =>  $user,
        'token'=>$accessToken,
    ], 200);
}
public function resendOTP(Request $request)
{
    // 1. Validate request
    $validator = Validator::make($request->all(), [
        'username' => 'required|string|exists:AC_UserMaster,UserName',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    // 2. Fetch user
    $user = AC_UserMaster::where('UserName', $request->username)->first();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'User not found',
        ], 404);
    }

    // 3. Generate new OTP
    $alphanum = "0123456789";
    $otp = substr(str_shuffle($alphanum), 0, 6);

    $user->OTP = $otp;
    $user->save();

    // 4. Send OTP via SMS
    // $authKey = "135468AwHMDbYRku58e1d959"; // replace with your actual key
    // $mobileNumber = $user->Mobile;
    // $senderId = "AEPTAD";
    // $TemplateID = "1207175086773161988";

    // $SMSText = "Dear Member,\n\nYour OTP for login to AEPTA App is $otp. Valid for 5 minutes. Please do not share this OTP.\n\nRegards,\nAEPTA";
    // $message = urlencode($SMSText);

    // $postData = [
    //     'authkey' => $authKey,
    //     'mobiles' => $mobileNumber,
    //     'message' => $message,
    //     'sender' => $senderId,
    //     'route' => 4,
    //     'DLT_TE_ID' => $TemplateID,
    //     'country' => 91
    // ];

    // $url = "http://india.msg91.com/sendhttp.php";

    // $ch = curl_init();
    // curl_setopt_array($ch, [
    //     CURLOPT_URL => $url,
    //     CURLOPT_RETURNTRANSFER => true,
    //     CURLOPT_POST => true,
    //     CURLOPT_POSTFIELDS => http_build_query($postData),
    //     CURLOPT_SSL_VERIFYHOST => 0,
    //     CURLOPT_SSL_VERIFYPEER => 0,
    // ]);

    // $output = curl_exec($ch);

    // if (curl_errno($ch)) {
    //     Log::error("SMS OTP sending failed: " . curl_error($ch));
    //     return response()->json([
    //         'status' => false,
    //         'message' => 'Failed to send OTP. Please try again later.',
    //     ], 500);
    // }

    // curl_close($ch);

    return response()->json([
        'status' => true,
        'message' => 'OTP has been resent successfully.',
        'data' => [
            'username' => $user->UserName,
            'OTP' => $otp, // optional, can omit for security
        ],
    ], 200);
}
}