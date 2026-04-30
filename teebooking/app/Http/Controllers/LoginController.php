<?php



namespace App\Http\Controllers;



use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;

use App\Models\Member;

use App\Models\Card;

use App\Models\CardItem;

use App\Models\OtpModel;

use App\Models\RoomBooking;

use App\Models\BanquetBooking;

use Illuminate\Support\Facades\Validator;

use App\Mail\OtpMail;

use Illuminate\Support\Facades\Mail;

use App\Jobs\SendQueueEmail;

use Carbon\Carbon;



class LoginController extends Controller

{

    public function index()

    {

        return view('member.login');

    }



    public function login(Request $request, $member_id)

    {

        $member = Member::where(['MemberID' => $member_id])->first();

        Auth::login($member);

        return redirect()->route('home')->with('message', 'Signed in!'); 

    }

    public function login_admin(Request $request)
    {
        
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

            $member->Password = Hash::make($request->input('password'));

            $member->save();



            $user->delete();



            return response()->json(['success' => 'Password updated successfully']);

        } else {

            return response()->json(['error' => 'Invalid OTP'], 400);

        }











    }



    public function logout()

    {
        $member = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();

        $card = Card::where('memberID', $member->MemberID)->first();

        if($card){

            CardItem::where('card_id', $card->id)->delete();

            Card::where('id', $card->id)->delete();

        }

        

        session()->flush();

        Auth::logout();

        return redirect()->route('login');

    }



    public function reloadCaptcha()

    {

        return response()->json(['captcha' => captcha_img()]);

    }

}

