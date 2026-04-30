<?php

namespace App\Http\Controllers;

use App\Models\MMRRegistrationSetting;
use App\Models\MMRRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MMRRegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['data'] = MMRRegistrationSetting::first();
        $data['member'] = Auth::guard('student')->user();
        return view('frontend.mmr_registration.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $member = Auth::guard('student')->user();

        $data = MMRRegistrationSetting::where('start_date', '<=', now())->where('end_date', '>=', now())->first();

        if (!$data) {
            return redirect()->route('mmr.registration')->with('error', 'Registration is not open at this time.');
        }

        if(MMRRegistration::where('member_SC_ID', $member->SC_ID)->exists()) {
            return redirect()->back()->with('error', 'You have already registered.');
        } else {
            $registration = new MMRRegistration();
        }
        
        $registration->member_SC_ID = $member->SC_ID;
        $registration->member_id = $member->id;
        $registration->start_date = $data->start_date;
        $registration->end_date = $data->end_date;
        $registration->save();

        return redirect()->route('mmr.registration')->with('success', 'Registration successful!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MMRRegistration  $mMRRegistration
     * @return \Illuminate\Http\Response
     */
    public function show(MMRRegistration $mMRRegistration)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MMRRegistration  $mMRRegistration
     * @return \Illuminate\Http\Response
     */
    public function edit(MMRRegistration $mMRRegistration)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MMRRegistration  $mMRRegistration
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MMRRegistration $mMRRegistration)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MMRRegistration  $mMRRegistration
     * @return \Illuminate\Http\Response
     */
    public function destroy(MMRRegistration $mMRRegistration)
    {
        //
    }
}
