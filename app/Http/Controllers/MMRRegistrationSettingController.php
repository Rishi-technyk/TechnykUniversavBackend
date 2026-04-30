<?php

namespace App\Http\Controllers;

use App\Models\MMRRegistration;
use App\Models\MMRRegistrationSetting;
use Illuminate\Http\Request;

class MMRRegistrationSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['data'] = MMRRegistrationSetting::first();
        return view('backend.mmr_registration.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function enquery(Request $request)
    {
        $data['datas'] = MMRRegistration::orderBy('id', 'desc')->get();
        return view('backend.mmr_registration.enquery', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MMRRegistrationSetting  $mMRRegistrationSetting
     * @return \Illuminate\Http\Response
     */
    public function show(MMRRegistrationSetting $mMRRegistrationSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MMRRegistrationSetting  $mMRRegistrationSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(MMRRegistrationSetting $mMRRegistrationSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MMRRegistrationSetting  $mMRRegistrationSetting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        $data = MMRRegistrationSetting::first();
        if (!$data) {
            $data = new MMRRegistrationSetting();
        }

        $data->start_date = $request->start_date;
        $data->end_date = $request->end_date;
        $data->save();

        return redirect()->back()->with('success', 'MMR Registration Setting Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MMRRegistrationSetting  $mMRRegistrationSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(MMRRegistrationSetting $mMRRegistrationSetting)
    {
        //
    }
}
