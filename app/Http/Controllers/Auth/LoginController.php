<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MemberProfile;
use App\Models\BanquetBooking;
use App\Models\RoomBooking;
use App\Models\CardItem;
use App\Models\Card;
use App\Models\User;

class LoginController extends Controller
{
    function login(Request $request) 
    {
        
        return view('auth.sign_in');
        
    }

    function authentication(Request $request) 
    {
        $credentials = $request->only('email', 'password');
       
        if (Auth::attempt($credentials)) {
            if(Auth::user()->status == 'Active'){
                return redirect()->route('dashboard')->with('success', 'Login successful!');
            } else {
                Auth::logout();
                return back()->with('error', 'Your account is inactive. Please contact support.');
            }            
        }

        return back()->with('error', 'Invalid login credentials.');
    }

    function sign_up(Request $request) 
    {

        return view('auth.sign_up');
        
    }

    function registration(Request $request) 
    {
        
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ], [
            'name.required'     => 'Please enter your name.',
            'email.required'    => 'Email is required.',
            'email.email'       => 'Enter a valid email address.',
            'email.unique'      => 'This email is already registered.',
            'password.required' => 'Password is required.',
            'password.min'      => 'Password must be at least 6 characters.',
        ]);

        $params['name'] = $request->name;
        $params['email'] = $request->email;
        $params['password'] = Hash::make($request->password);

        $res = User::create($params);

        if($res){

            return redirect()->route('login')->with('success', 'Register Successfully.');

        } else {

            return back()->with('error', 'Try Again.');

        }       
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('admin')->with('success', 'Logged out successfully.');
    }

    function dashboard(Request $request)
    {
        $data['regs'] = User::where('role', 'Super Admin')->count();
        $data['today_regs'] = MemberProfile::where('role', 'Student')->count();

        if(Auth::guard('web')->user()->role == 'Room Admin') {
            $data['room_booking'] = RoomBooking::where('status', 'Active')->count();
            $data['room_booking_today'] = RoomBooking::where('status', 'Active')->whereDate('created_at', date('Y-m-d'))->count();
        } else {
            $data['room_booking'] = '0';
            $data['room_booking_today'] = '0';
        }

        if(Auth::guard('web')->user()->role == 'Banquet Admin') {
            $data['banquet_booking'] = BanquetBooking::where('status', 'Active')->count();
            $data['banquet_booking_today'] = BanquetBooking::where('status', 'Active')->whereDate('created_at', date('Y-m-d'))->count();
        } else {
            $data['banquet_booking'] = '0';
            $data['banquet_booking_today'] = '0';
        }
    
        return view('backend.dashboard', $data);
    }

    function student_authentication(Request $request) 
    {
        $member = MemberProfile::where('MemberID', $request->username)->where('Password', $request->password)->first();
        
        if($member){
            Auth::guard('student')->login($member);

            $member = Auth::guard('student')->user();
            $cards = Card::where('memberID', $member->MemberID)->get();
            foreach ($cards as $key => $card) {
                CardItem::where('card_id', $card->id)->delete();
                $card->delete();
            }
            return redirect()->route('student.dashboard')->with('success', 'Login successfully.');
        } else {
            return back()->with('error', 'Invalid login credentials.');
        }
    }

    function student_logout(Request $request)
    {
        Auth::guard('student')->logout();
        return redirect('/')->with('success', 'Logged out successfully.');
    }
}
