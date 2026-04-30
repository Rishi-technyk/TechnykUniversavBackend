<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Member;
use App\Rules\CurrentPasswordValidation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class ProfileController extends Controller
{
    public function index()
    {
       
        return view('member.profile');
        
    }

 
	
	public function changePassword(Request $request)
{
    if ($request->isMethod('post')) {
        $randomNumber = $request->session()->get('randomNumber');
        $request->validate([
            'current_password' => ['required'],
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ]);

        $current_password = $request->current_password;
        $user = auth()->user();
        $member = Member::find($user->id);

        // Calculate the hash of the current password with the random number
        $sha256Hash = hash('sha256', $member->Password . $randomNumber);

        // Check if the current password matches the hashed value
        if ($current_password === $sha256Hash) {
            // Check password history
            $passwordHistory = json_decode($member->password_history);

            if (!is_array($passwordHistory)) {
                $passwordHistory = [];
            }

            // Check if the new password matches any of the last three passwords
            foreach ($passwordHistory as $history) {
				if ($request->password === $history) {
					return redirect()->route('member_change_password')->with('error', 'Password must be different from the last three passwords.');
				}
			}

            // Add the current password to the password history
            $passwordHistory[] = $member->Password;

            if (count($passwordHistory) > 3) {
                // Keep only the last three passwords in the history
                $passwordHistory = array_slice($passwordHistory, -3);
            }

            // Update the user's password and password history
            $member->Password = $request->password;
            $member->password_history = json_encode($passwordHistory);
            $member->save();
			Session::regenerate();

            return redirect()->route('member_change_password')->with('success', 'Password changed successfully.');
        } else {
            return redirect()->route('member_change_password')->with('error', 'Current password is incorrect.');
        }
    }

    return view('member.change-password');
}
	
}
