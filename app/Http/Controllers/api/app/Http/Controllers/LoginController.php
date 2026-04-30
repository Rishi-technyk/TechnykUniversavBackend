<?php



namespace App\Http\Controllers;



use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;

use App\Models\Member;

use App\Models\OtpModel;

use Illuminate\Support\Facades\Validator;

use App\Mail\OtpMail;

use Illuminate\Support\Facades\Mail;

use App\Jobs\SendQueueEmail;

use Illuminate\Support\Str;

use Illuminate\Support\Facades\Session;

use Illuminate\Support\Facades\Crypt;

use Illuminate\Support\Facades\Cache;

use App\Models\RoomBooking;

use App\Models\BanquetBooking;

use Carbon\Carbon;

class LoginController extends Controller

{

   



   public function index(Request $request)

    {      

	   $randomNumber = rand(1000, 9999);

       $request->session()->put('randomNumber', $randomNumber);

       return view('member.login', ['randomNumber' => $randomNumber]);

    }



   

    public function login(Request $request)

    {

        

		$lockoutDuration = 30 * 60; 

		$lockoutTimeKey = 'lockout_time';

		$failedAttemptsKey = 'failed_attempts';

		$maxFailedAttempts = 10;

		$failedAttempts = Session::get($failedAttemptsKey, 0);

		Session::put($failedAttemptsKey, $failedAttempts + 1);

		if (Session::has($lockoutTimeKey) && time() - Session::get($lockoutTimeKey) < $lockoutDuration) {

			// return redirect('/')->with('error', 'You are temporarily locked out. Please try again later');			

		}		

        if ($failedAttempts >= $maxFailedAttempts) {

			Session::put($lockoutTimeKey, time());

			Session::put($failedAttemptsKey, 0); // Reset failed attempts

			// return redirect('/')->with('error', 'You are temporarily locked out. Please try again later');

			

		}			

		$randomNumber = $request->session()->get('randomNumber');

		$request->validate(

            [

                'username' => 'required',

                'password' => 'required',

                'captcha' => 'required|captcha',

            ],

            [

                // 'captcha.captcha' => 'Invalid Captcha.'

            ]

        );	 

		

        $username = $request->username;

        $password = $request->password;

        $member = Member::where(['MemberID' => $username])->first();       

		$pwd=$member->Password ?? '';

		$sha256Hash=$pwd.$randomNumber;

		$sha256Hash = hash('sha256', $sha256Hash);        

		if($pwd === $request->password) {		
    // if($password === $password) {		
		$sessionToken = Str::random(60);    

        session(['session_token' => $sessionToken]);    

        $member->session_token = $sessionToken;

        $member->save();
        \Log::info($member);

        // if ($member && Hash::check($password, $member->Password)) {

        // if ($member && $member->Password === $member->Password) {

            Auth::login($member);
           
			Session::regenerate();			 

            if($member->role=='Admin' || $member->role=='Room Manager' || $member->role=='Banquet Manager'){
                
                return redirect()->route('superadmin.dashboard')->with('message', 'Signed in!');

            } elseif ($member->role=='Super Admin') {
                
                return redirect()->route('main.superadmin.dashboard')->with('message', 'Signed in!');

            } elseif ($member->role=='Student') {
                
                return redirect()->route('member_profile')->with('message', 'Signed in!');

            } else {

                return redirect()->route('home')->with('message', 'Signed in!');

            }

		}else{

			if($pwd){

			$member->increment('failed_login_attempts');

            $member->update(['last_failed_login_at' => now()]);

			}

		}

		

        return redirect()->route('login')->with('error', 'Login details are not valid!');

    }


    public function admin_dashboard()
    {
        

        if(Auth::check() && Auth::user()->role=='Room Manager'){

            $data['today_booking'] = RoomBooking::where('status', 'Active')->whereDate('created_at', Carbon::today())->count();

            return view('superadmin.room_dashboard', $data); 

        } elseif(Auth::check() && Auth::user()->role=='Banquet Manager'){

            $data['member_booking'] = BanquetBooking::where('occupant_type', '1')->count();
            $data['guest_booking']  = BanquetBooking::where('occupant_type', '2')->count();
            $data['today_booking']  = BanquetBooking::whereDate('created_at', Carbon::today())->count();

            return view('superadmin.dashboard', $data); 

        } else {

            return redirect()->back();

        }  
        
    }

    public function super_admin_dashboard()
    {
        $data['member'] = Member::whereIn('role', ['Room Manager', 'Banquet Manager'])->get();

        return view('superadmin.super_admin_dashboard', $data);
    }


    public function dashboard()

    {

        return view('member.dashboard');

    }



    public function forgot_password()

    {

        return view('member.forgot-password');

    }

    // public function otp_send(Request $request)

    // {

    //     $request->validate([

    //         'email' => 'required|email',

    //         'captcha' => 'required|captcha',

    //     ], [

    //         'captcha.captcha' => 'Invalid Captcha.'

    //     ]);

    //     $email = $request->email;

    //     $member = Member::where('Email', $email)->first();

    //     if ($member) {

    //         $cacheKey = 'otp_emails_sent_' . $email;

    //         $emailCount = Cache::get($cacheKey, 0);

    //         if ($emailCount < 3) {

    //             $emailCount++;

    //             Cache::put($cacheKey, $emailCount, now()->addDay());

    //             $otp = mt_rand(100000, 999999);

    //             $otpRecord = OtpModel::updateOrCreate(['MemberId' => $member->MemberID], ['OTP' => $otp]);

    //             $subject = "OTP for password change";

    //             $data = $otp;

    //             $emailType = 'otp';

    //             Mail::to($email)->send(new OtpMail($data, $subject, $emailType));

    //             $emailid = encrypt($email);

    //             return redirect()->route('changpwd', ['emailid' => $emailid]);

    //         } else {

    //             return redirect()->route('forgot_password')->with('error', 'You have exceeded the limit of OTP emails. Try again after 24 hours.');

    //         }

    //     } else {

    //         return redirect()->route('forgot_password')->with('error', 'Email does not exist.');

    //     }

    // }


    public function otp_send(Request $request)
    {
        $request->validate([
            'mobile' => 'required',
            'captcha'  => 'required|captcha',
        ], [
            'captcha.captcha' => 'Invalid Captcha.'
        ]);

        // Get Member
        $member = Member::where('Mobile', $request->mobile)->first();

        if (!$member || !$member->Mobile) {
            return back()->with('error', 'No mobile number linked with this Membership ID.');
        }

        // Generate 6 digit OTP
        $otp = random_int(100000, 999999);

        // Save OTP in database
        OtpModel::updateOrCreate(
            ['MemberId' => $member->MemberID],
            ['OTP' => $otp]
        );

        // ===========================
        // SMS API SETTINGS
        // ===========================
        $authKey    = "135468AwHMDbYRku58e1d959";
        $senderId   = "gviclb";
        $TemplateID = "1207176190107861479";
        $mobile     = $member->Mobile;
               
        $SMSText="Dear Member"; 

        $SMSText = $SMSText."\n"."Your OTP for password reset for GVI Club App is $otp. Valid for 5 minutes. Do not share this OTP with anyone.";
        
        $SMSText = $SMSText."\n"." Regards,";

        $SMSText = $SMSText."\n"." GVI Club";

        $SMSText = $SMSText."\n"." by technyk";
        
            
        $message = urlencode($SMSText);


        $postData = [
            'authkey'     => $authKey,
            'mobiles'     => $mobile,
            'message'     => $message,
            'sender'      => $senderId,
            'route'       => 4,
            'DLT_TE_ID'   => $TemplateID,
            'country'     => 91];

        $ch = curl_init("http://india.msg91.com/sendhttp.php");

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $maskedMobile = "XXXXXX" . substr($mobile, 6);

        $email = $member->Email;

        return redirect()->route('changpwd', ['emailid' => encrypt($email)])
             ->with('success', "OTP has been sent to registered mobile number $maskedMobile");
    }




	public function changpwd($emailid)

    {

	  $email = decrypt($emailid);

	  $member = Member::where(['Email' => $email])->first();

	  return view('member.otp-verify', compact('member'));

    }	

	

    public function otp_verify(Request $request)
    {

        $request->validate([

            'member_id' => 'required',

            'otp' => 'required|min:6',

            'password' => 'required|confirmed',

    		'captcha' => 'required|captcha',

        ],

                [

                    'captcha.captcha' => 'Invalid Captcha.'

                ]);



        $user = OtpModel::where('MemberId', $request->member_id)->first();



        if (!$user) {

    		return redirect()->route('forgot_password')->with('error', 'User not found.');        

        }


        $enteredOtp = $request->otp;

    	

    	$emailid = encrypt($request->email);



        if ($enteredOtp != $user->OTP) {

    		return redirect()->route('changpwd', ['emailid' => $emailid])->with('error', 'Invalid OTP.');

    		

        }



        $member = Member::where('MemberID', $request->member_id)->first();

        if (!$member) {

    		return redirect()->route('changpwd', ['emailid' => $emailid])->with('error', 'Somthing Went worng.');

    		}



        // Check password history

        $passwordHistory = json_decode($member->password_history, true);

        if (!is_array($passwordHistory)) {

            $passwordHistory = [];

        }



        foreach ($passwordHistory as $history) {

            if ($request->password === $history) {

    			return redirect()->route('changpwd', ['emailid' => $emailid])->with('error', 'Password must be different from the last three passwords.');

    			

            }

        }



        // Add the current password to the password history

        $passwordHistory[] = $member->Password;



        if (count($passwordHistory) > 3) {

            // Keep only the last three passwords in the history

            $passwordHistory = array_slice($passwordHistory, -3);

        }



        $member->Password = $request->password;

        $member->password_history = json_encode($passwordHistory);

        $member->save();



        $user->delete();

    	return redirect()->route('index')->with('success', 'Password updated successfully');	    

    }



    public function logout()

    {

        session()->flush();

        Auth::logout();

        return redirect()->away('https://gvicc.in/');

    }



    public function reloadCaptcha()

    {

        return response()->json(['captcha' => captcha_img()]);

    }

}

