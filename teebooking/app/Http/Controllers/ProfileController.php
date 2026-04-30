<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Member;
use App\Rules\CurrentPasswordValidation;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
       
        return view('member.profile');
        
    }

    public function changePassword(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'current_password' => ['required', new CurrentPasswordValidation()],
                'password' => 'required|confirmed',
                'password_confirmation' => 'required',
            ]);

            Member::whereId(auth()->user()->id)->update([
                'Password' => Hash::make($request->password)
            ]);
            return response()->json(['success' => 'Password changed successfully.']);
        }
        return view('member.change-password ');
    }
}
