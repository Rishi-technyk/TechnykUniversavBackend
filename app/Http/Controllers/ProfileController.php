<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Personality;
use App\Models\User;

class ProfileController extends Controller
{
    function index()
    {
        $data['user'] = Auth::user();

        return view('backend.profile.index', $data);
    }

    function update(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    =>   [
                                'required',
                                'email',
                                Rule::unique('users', 'email')->ignore($request->id),
                            ],
        ], [
            'name.required'     => 'Please enter your name.',
            'email.required'    => 'Email is required.',
            'email.email'       => 'Enter a valid email address.',
            'email.unique'      => 'This email is already registered.',
        ]);

        $params['name'] = $request->name;
        $params['email'] = $request->email;

        User::whereId(Auth::user()->id)->update($params);

        return back()->with('success', 'Profile Updated Successfully.');
        
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    // Check if old password matches current user's password
                    if (!Hash::check($value, auth()->user()->password)) {
                        $fail('Old password is incorrect.');
                    }
                }
            ],

            'new_password' => 'required|string|min:6',

            'confirm_password' => 'required|same:new_password',

        ], [
            'old_password.required'      => 'Please enter your old password.',
            'new_password.required'      => 'Please enter a new password.',
            'new_password.min'           => 'New password must be at least 6 characters.',
            'confirm_password.required'  => 'Please confirm your new password.',
            'confirm_password.same'      => 'Confirm password must match new password.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->old_password, $user->password)) {
            return back()->with('error', 'Old password does not match.');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password updated successfully!');
    }
}
