<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\CardItem;
use App\Models\Member;
use App\Models\Card;
use Session;

class OTPController extends Controller
{
    function otp_login(Request $request)
    {
        return view('auth.otp_login');        
    }

    function send_otp(Request $request)
    {
        // Implementation for sending OTP goes here
        // For example, you can generate an OTP, save it to the database, and send it via email or SMS
        $data['request'] = $request;

        $member = Member::where('memberID', $request->username)->first();

        if($member && $member->Mobile){

            $otp = sprintf("%06d", mt_rand(1, 999999));

            $authKey = "135468AwHMDbYRku58e1d959";

            $mobileNumber = $member->Mobile;

            $senderId = "LGCLKO";

            $TemplateID="1207177574099845833";

            // EXACT SAME as approved template
            $SMSText = "Dear Member, Your OTP for login to Club App is $otp. Valid for 5 minutes. Please do not share this OTP. Lucknow Golf Club";

            $message = urlencode($SMSText);

            //Define route 

            $route = "4";

            //Prepare you post parameters

            $postData = array(

                'authkey' => $authKey,

                'mobiles' => $mobileNumber,

                'message' => $message,

                'sender' => $senderId,

                'route' => $route,

                'DLT_TE_ID' => $TemplateID,

                'country' => 91

            );

            //API URL

            $url="http://india.msg91.com/sendhttp.php";

            // init the resource

            $ch = curl_init();

            curl_setopt_array($ch, array(

                CURLOPT_URL => $url,

                CURLOPT_RETURNTRANSFER => true,

                CURLOPT_POST => true,

                CURLOPT_POSTFIELDS => $postData

                //,CURLOPT_FOLLOWLOCATION => true

            ));

            //Ignore SSL certificate verification

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            //get response

            $output = curl_exec($ch);  

            curl_close($ch);

            $mm['OTP'] = $otp;

            Member::where('memberID', $request->username)->update($mm);

            $error="OTP has been sent to the registered mobile No. XXXXXX".substr($mobileNumber,6);    

            Session::put('msg', $error);

            Session::put('type', $request->type);

            Session::put('username', $request->username);

            if($request->type == 'forgot_password'){

                return redirect()->route('student.forgot.password.sended')->with('success', $error);

            } else {

                return redirect()->route('otp.sended')->with('success', $error);
            
            }

        } else {

            return redirect()->back()->with('error', 'No Mobile No linked with entered Membership No.');

        }
    }

    function otp_sended() {
        return view('auth.otp_sended');
    }

    function verify_otp(Request $request)
    {
        $member = Member::where('memberID', Session::get('username'))->first();

        if($member && $member->OTP == $request->otp){

            Auth::guard('student')->login($member);

            $member = Auth::guard('student')->user();
            $cards = Card::where('memberID', $member->MemberID)->get();
            foreach ($cards as $key => $card) {
                CardItem::where('card_id', $card->id)->delete();
                $card->delete();
            }
            return redirect()->route('student.dashboard')->with('success', 'Logged in successfully.');
        } else {
            // OTP is incorrect, show an error message
            return redirect()->back()->with('error', 'Invalid OTP. Please try again.');
        }        
    }

    function forgot_password(Request $request) {
        return view('auth.forgot_password');
    }

    function forgot_password_sended() {
        return view('auth.forgot_password_sended');
    }

    function forgot_password_verify_otp(Request $request)
    {
        $member = Member::where('memberID', Session::get('username'))->first();

        if($member && $member->OTP == $request->otp){

            return redirect()->route('student.forgot.password.make')->with('success', 'OTP verified successfully. Please set your new password.');
        } else {
            // OTP is incorrect, show an error message
            return redirect()->back()->with('error', 'Invalid OTP. Please try again.');
        }        
    }

    function make_forgot_password(Request $request) 
    {

        return view('auth.forgot_password_make');
        
    }

    function update_forgot_password(Request $request) 
    {

        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $member = Member::where('memberID', Session::get('username'))->first();

        if(!$member) {
            return redirect()->back()->with('error', 'Member not found.');
        }

        $params['Password'] = $request->password;
        $member->update($params);

        Auth::guard('student')->login($member);

        $member = Auth::guard('student')->user();
        $cards = Card::where('memberID', $member->MemberID)->get();
        foreach ($cards as $key => $card) {
            CardItem::where('card_id', $card->id)->delete();
            $card->delete();
        }

        return redirect()->route('student.dashboard')->with('success', 'Password updated successfully.');
        
    }
}
