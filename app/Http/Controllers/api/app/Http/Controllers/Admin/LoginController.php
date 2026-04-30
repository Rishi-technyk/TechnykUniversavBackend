<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
    public function index()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        if (Auth::guard('admin')->attempt($credentials)) {
            return redirect()->route('notifications')
                ->with('success', 'Signed in!');
        }

        return redirect()->route('admin.login')->with('error', 'Login credentials are not valid!');
    }

    public function logout()
    {
        session()->flush();

        Auth::logout();

        return redirect()->route('admin.login');
    }
}
