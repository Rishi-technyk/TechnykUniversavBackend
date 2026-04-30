<?php

namespace App\Http\Controllers\Admin\Tee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoomPrice;
use App\Models\CategoryMaster;
use App\Models\OccupantType;
use Illuminate\Support\Facades\Auth;


class SheetController extends Controller
{
    public function index()
    {
      
        //print_r($roomPrices);die();
        return view('admin.tee.add-sheet');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        
        if (Auth::guard('admin')->attempt($credentials)) {
            return redirect()->route('admin.dashboard')
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
