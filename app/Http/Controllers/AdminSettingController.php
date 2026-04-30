<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\AdminSetting;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['data'] = AdminSetting::first();
        $data['login_user'] = Auth::guard('web')->user();
        return view('backend.settings.index', $data);
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
    public function update_admin_setting(Request $request)
    {
        $data = AdminSetting::first();
        if (!$data) {
            $data = new AdminSetting();
        }

        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $name = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/logo'), $name);
            $imagePath = 'uploads/logo/'.$name;
            $data->logo                 = $imagePath;
        }

        $data->heading                  = $request->heading;
        $data->sub_heading              = $request->sub_heading;
        $data->phone                    = $request->phone;
        $data->email                    = $request->email;
        $data->address                  = $request->address;
        $data->project_name             = $request->project_name;
        $data->min_days                 = $request->min_days;
        $data->max_days                 = $request->max_days;
        $data->banquest_booking_form    = $request->banquest_booking_form=='1' ? 'Active' : 'Inactive';
        $data->room_booking_module      = $request->room_booking_module=='1' ? 'Active' : 'Inactive';
        $data->activity_booking_form    = $request->activity_booking_form=='1' ? 'Active' : 'Inactive';
        $data->table_booking_form       = $request->table_booking_form=='1' ? 'Active' : 'Inactive';
        $data->student_header_message   = $request->student_header_message;
        $data->save();

        return redirect()->back()->with('success', 'Settings updated successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AdminSetting  $adminSetting
     * @return \Illuminate\Http\Response
     */
    public function show(AdminSetting $adminSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AdminSetting  $adminSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(AdminSetting $adminSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AdminSetting  $adminSetting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AdminSetting $adminSetting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AdminSetting  $adminSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(AdminSetting $adminSetting)
    {
        //
    }
}
