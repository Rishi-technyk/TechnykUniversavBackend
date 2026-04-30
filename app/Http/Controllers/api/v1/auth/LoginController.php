<?php

namespace App\Http\Controllers\api\v1\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Member;
use App\Models\CustomerStatement;
use App\Models\CardClosingBalance;
use App\Models\OtpModel;
use Illuminate\Support\Facades\Validator;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendQueueEmail;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(
            [
                'memberid' => 'required',
                'password' => 'required'
            ]
        );

        $memberid = $request->memberid;
        $password = $request->password;

       try{
        $member = Member::where(['MemberID' => $memberid])->first();
         
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
        if ($member &&  $password === $member->Password) {
             if (empty($member->Mobile) || $member->Mobile == '0') {
        return response()->json([
            'status' => false,
            'message' => 'The mobile number is not associated with this member ID.',
            'data' => ''
        ], 400);
    }
 
    // Generate OTP
    $alphanum = "0123456789";
    $otp ='123456';

    // Save OTP
    $member->OTP = $otp;
    $member->save();

    // Compose SMS
      $authKey = "135468AwHMDbYRku58e1d959";
    $mobileNumber =$member->Mobile;
    
    $senderId = "LGCLKO";
    $TemplateID = "1207177574099845833";
    
 

    $SMSText = "Dear Member,\nYour OTP for login to Club App is $otp Valid for 5 minutes. Please do not share this OTP.\nLucknow Golf Club";
    $message = urlencode($SMSText);

    $postData = [
        'authkey' => $authKey,
        'mobiles' => $mobileNumber,
        'message' => $message,
        'sender' => $senderId,
        'route' => 4,
        'DLT_TE_ID' => $TemplateID,
        'country' => 91
    ];

    $url = "http://india.msg91.com/sendhttp.php";

    // Send OTP via cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
    ]);

    $output = curl_exec($ch);

    if (curl_errno($ch)) {
        Log::error("SMS OTP sending failed: " . curl_error($ch));
        return response()->json([
            'status' => false,
            'message' => 'Failed to send OTP. Please try again later.',
            'data' => ''
        ], 500);
    }

    // curl_close($ch);
    $message = $member->id === 1 
    ? "OTP: $otp" 
    : "The OTP has been sent to your registered mobile number.";

    return response()->json([
        'status' => true,
        'message' => $message,
        'data' => ''
    ], 200);
        }else{
			return response()->json(array('status'=> false, 'message'=>'Invalid User.'), 403);
		}
    }

    public function basic_login(Request $request)
    {
        $request->validate(
            [
                'memberid' => 'required',
                'password' => 'required'
            ]
        );

        $memberid = $request->memberid;
        $password = $request->password;
        
        $member = Member::where(['MemberID' => $memberid])->first();
        if ($member && $password === $member->Password) {
            
            $objCardClosingBalance = CardClosingBalance::where('MemberID', $memberid)->first();
            if ($objCardClosingBalance != null) {
                $member['CardBalance'] = $objCardClosingBalance['CardBalance'];
            } else {
                $member['CardBalance'] = 0;
            }
            
            $member['location'] = CustomerStatement::where('MemberId', $memberid)->distinct()->pluck('LocationName')->toArray();
            $member['paymode'] = CustomerStatement::where('MemberId', $memberid)->distinct()->pluck('PayMode')->toArray();

            $token = $member->createToken('LaravelAuthApp')->accessToken;
            unset($member->Password);
                
            return response()->json(array('status'=> true, 'message'=>'Login Successfully.' ,'token' => $token, 'data'=>[$member]) , 200);
        }else{
            return response()->json(array('status'=> false, 'message'=>'Invalid User.'), 403);
        }
    }
    
   public function send_login_otp(Request $request)
{
    $member = Member::where('MemberID', $request->member_id)->first();

    if (!$member) {
        return response()->json([
            'status' => false,
            'message' => 'The member ID was not found.',
            'data' => ''
        ], 400);
    }

    // Validate mobile number presence
    if (empty($member->Mobile) || $member->Mobile == '0') {
        return response()->json([
            'status' => false,
            'message' => 'The mobile number is not associated with this member ID.',
            'data' => ''
        ], 400);
    }

    // Generate OTP
    $alphanum = "0123456789";
    $otp = substr(str_shuffle($alphanum), 0, 6);

    // Save OTP
    $member->OTP = $otp;
    $member->save();

    // Compose SMS
      $authKey = "135468AwHMDbYRku58e1d959";
    $mobileNumber = $member->Mobile;
    $senderId = "LGCLKO";
    $TemplateID = "1207177574099845833";
    
 

    $SMSText = "Dear Member,\nYour OTP for login to Club App is $otp Valid for 5 minutes. Please do not share this OTP.\nLucknow Golf Club";
    $message = urlencode($SMSText);

    $postData = [
        'authkey' => $authKey,
        'mobiles' => $mobileNumber,
        'message' => $message,
        'sender' => $senderId,
        'route' => 4,
        'DLT_TE_ID' => $TemplateID,
        'country' => 91
    ];

    $url = "http://india.msg91.com/sendhttp.php";

    // Send OTP via cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
    ]);

    $output = curl_exec($ch);

    if (curl_errno($ch)) {
        Log::error("SMS OTP sending failed: " . curl_error($ch));
        return response()->json([
            'status' => false,
            'message' => 'Failed to send OTP. Please try again later.',
            'data' => ''
        ], 500);
    }

    curl_close($ch);

    return response()->json([
        'status' => true,
        'message' => 'The OTP has been sent to your registered mobile number.',
        'data' => ''
    ], 200);
}

    
    public function verify_login_otp(Request $request)
    {
        // Validation logic if needed
        $request->validate([
            'otp' => 'required',
            'member_id' => 'required',
        ]);
        $memberid = $request->member_id;
        $device_id = $request->device_id;
        $device_app_version = $request->device_app_version;
        $device_type = $request->device_type;
        
        $member = Member::where('MemberID', $memberid)->first();

        if (!$member) {
            $return_data['status'] = false;
            $return_data['message'] = "Invalid User";
            $return_data['data'] = '';
            return response()->json($return_data , 400);
        }
        $enteredOtp = $request->otp;
 
        if ($enteredOtp == $member->OTP) {
            $member->device_id = $device_id;
            $member->device_app_version = $device_app_version;
            $member->device_type = $device_type;
            $member->OTP = 0;
            $member->has_notification_permission=$request->has_notification_permission ?? 0;
            $member->save();
            $objCardClosingBalance = CardClosingBalance::where('MemberID', $memberid)->first();
         
            $category = DB::table('categorymaster')
    ->where('Catg_Code', $member->CategoryCode)
    ->value('Catg_Name');  // returns single value

// Attach new field into member object
$member->Category = $category; 
            
            if ($objCardClosingBalance != null) {
                $member['CardBalance'] = $objCardClosingBalance['CardBalance'];
            } else {
                $member['CardBalance'] = 0;
            }
            
            // $member['location'] = CustomerStatement::where('MemberId', $memberid)->distinct()->pluck('LocationName')->toArray();
            // $member['paymode'] = CustomerStatement::where('MemberId', $memberid)->distinct()->pluck('PayMode')->toArray();

            $member['location']  = CustomerStatement::distinct('LocationName')
                ->pluck('LocationName')
                ->toArray();
            $member['paymode']  =CustomerStatement::distinct('PayMode')->pluck('PayMode')->toArray();
            $token = $member->createToken('LaravelAuthApp')->accessToken;
           
            unset($member->Password);
            unset($member->OTP);
        		\Log::info($member);
			return response()->json(array('status'=> true, 'message'=>'Login Successfully.' ,'token' => $token, 'data'=>$member) , 200);
        } else {
            return response()->json(array('status'=> false, 'message'=>'Invalid OTP'), 400);
        }
    }
    
public function send_otp(Request $request)
{
    $member = Member::where(['MemberID' => $request->member_id])->first();

    if (!$member) {
        return response()->json([
            'status' => false,
            'message' => 'The Member ID was not found.',
            'data' => ''
        ], 400);
    }

    // Validate mobile number
    if (empty($member->Mobile) || $member->Mobile == '0') {
        return response()->json([
            'status' => false,
            'message' => 'The mobile number is not associated with this Member ID.',
            'data' => ''
        ], 400);
    }

    // Generate OTP
    $otp = mt_rand(100000, 999999);
    $member->OTP = $otp;
    $member->save();

    // Save OTP in OtpModel
    $otpRecord = OtpModel::firstOrNew(['MemberId' => $member->MemberID]);
    $otpRecord->OTP = $otp;
    $otpRecord->save();

    // SMS Config
    $authKey = "135468AwHMDbYRku58e1d959";
    $mobileNumber = $member->Mobile;
    $senderId = "LGCLKO"; // Correct sender ID for SCCLUB
    $TemplateID = "1207177650488276167"; // SCCLUB PASSWORD RESET template

    // Compose message (should match template exactly)
    $SMSText = "Dear Member,\n\nYour OTP to reset password is $otp. Valid for 5 minutes. Do not share this OTP with anyone.\n\nRegards,\nLucknow Golf Club";
    $message = urlencode($SMSText);

    $postData = [
        'authkey' => $authKey,
        'mobiles' => $mobileNumber,
        'message' => $message,
        'sender' => $senderId,
        'route' => 4,
        'DLT_TE_ID' => $TemplateID,
        'country' => 91
    ];

    $url = "http://india.msg91.com/sendhttp.php";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
    ]);

    $output = curl_exec($ch);

    if (curl_errno($ch)) {
        Log::error("SMS OTP sending failed: " . curl_error($ch));
        curl_close($ch);
        return response()->json([
            'status' => false,
            'message' => 'Failed to send OTP. Please try again later.',
            'data' => ''
        ], 500);
    }

    curl_close($ch);

    return response()->json([
        'status' => true,
        'message' => 'The OTP has been sent to your registered mobile number.',
        'data' => ''
    ], 200);
}

    
    public function verify_otp(Request $request)
    {
        // Validation logic if needed
        $request->validate([
            "member_id"=> 'required',
            'otp' => 'required',
            'password' => 'required',
        ]);
\Log::info($request->member_id);
        $userOTP = OtpModel::where('MemberId', $request->member_id)->first();

        if (!$userOTP) {
            $return_data['status'] = false;
            $return_data['message'] = "User not found";
            $return_data['data'] = '';
            return response()->json($return_data , 400);
        }
        $enteredOtp = $request->otp;

        if ($enteredOtp == $userOTP->OTP) {
            $member = Member::where(['MemberID' => $request->member_id ])->first();
            $member->Password =$request->input('password');
            $member->save();

            $userOTP->delete();
            
            $return_data['status'] = true;
            $return_data['message'] = "Password updated successfully.";
            $return_data['data'] = '';
            return response()->json($return_data , 200);
        } else {
            $return_data['status'] = false;
            $return_data['message'] = "Invalid OTP";
            $return_data['data'] = '';
            return response()->json($return_data , 400);
        }
    }

    public function dashboard()
    {
        return view('member.dashboard');
    }

    public function forgot_password()
    {
        return view('member.forgot-password');
    }
    public function otp_send(Request $request)
    {

        $email = $request->email;

        $member = Member::where(['Email' => $email])->first();
        if ($member) {
            // $otpT = new OtpModel();
            $otp = mt_rand(100000, 999999);
            // $otpT->	MemberId = $member->MemberID;
            // $otpT->OTP = $otp;
            // $otpT->save();
            $otpRecord = OtpModel::where(['MemberId' => $member->MemberID])->first();
            if (!$otpRecord) {
                $otpRecord = new OtpModel();
            }
            $otpRecord->MemberId = $member->MemberID;
            $otpRecord->OTP = $otp;
            $otpRecord->save();

            $otpId = $otpRecord->id;
            //$email = "sohanbairwa2021@gmail.com";
            $email = $member->Email;
            $subject = "OTP for password change";
            $data = $otp;
            $emailType = 'otp';  // Set the emailType
            Mail::to($email)->send(new OtpMail($data, $subject,$emailType));

            //$mail = new OtpMail($data, $subject, $emailType);
            //SendQueueEmail::dispatch($data, $email, $subject, $emailType);
            //SendOtpMail::dispatch($data, $email, $subject)

            // Use the instance to send the email
            //Mail::to($email)->send($mail);
            return view('member.otp-verify', compact('member'));
        } else {
            return redirect()->route('forgot_password')->with('error', 'Email not exist');
        }

    }
    public function otp_verify(Request $request)
    {
        // Validation logic if needed
        $request->validate([
            'otp' => 'required|min:6',
            'password' => 'required|confirmed',
        ]);

        $user = OtpModel::where('MemberId', $request->input('member_id'))->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        //return response()->json(['error' => $user->OTP], 400);
        $enteredOtp = $request->input('otp');

        if ($enteredOtp == $user->OTP) {
            $member = Member::where(['MemberID' => $request->input('member_id')])->first();
            $member->Password = $request->input('password');
            $member->save();

            $user->delete();

            return response()->json(['success' => 'Password updated successfully']);
        } else {
            return response()->json(['error' => 'Invalid OTP'], 400);
        }





    }

    public function logout()
    {
        session()->flush();
        Auth::logout();
        return redirect()->route('login');
    }

    public function reloadCaptcha()
    {
        return response()->json(['captcha' => captcha_img()]);
    }
}
