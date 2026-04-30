<?php
// app/Http/Controllers/TeeBookingController.php

namespace App\Http\Controllers\Admin\Tee;
use App\CPU\Helpers;
use App\Models\TeeBooking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TeeBookingController extends Controller
{
    
    public function index()
    {
        $teeBookings = TeeBooking::orderBy('booking_date','desc')->all();
        return view('admin.tee.tee_bookings.index', compact('teeBookings'));
    }

  
    public function create()
    {
        return view('admin.tee.tee_bookings.create');
    }

   
    public function store(Request $request)
    {
        $request->validate([
            'golf_timing_id' => 'required|numeric',
            'tee_date' => 'nullable|date',
            'tee_day' => 'nullable|string',
            'remarks' => 'nullable|string',
            'booking_status' => 'nullable|string',
        ]);
        $request['tee_date'] =  date('Y-m-d', strtotime($request['tee_date'])); 
        // print_r($request->all());
        // die();
        $request = Helpers::set_common_request($request);
        TeeBooking::create($request->all());

        return redirect()->route('tee_bookings.index')->with('success', 'Tee Booking created successfully!');
    }

    public function edit(TeeBooking $teeBooking)
    {
        return view('admin.tee.tee_bookings.edit', compact('teeBooking'));
    }

 
    public function update(Request $request, TeeBooking $teeBooking)
    {
        $request->validate([
            'golf_timing_id' => 'required|numeric',
            'tee_date' => 'nullable|date',
            'tee_day' => 'nullable|string',
            'remarks' => 'nullable|string',
            'booking_status' => 'nullable|string',
        ]);

        $teeBooking->update($request->all());

        return redirect()->route('tee_bookings.index')->with('success', 'Tee Booking updated successfully!');
    }

    public function destroy(TeeBooking $teeBooking)
    {
        $teeBooking->delete();

        return redirect()->route('tee_bookings.index')->with('success', 'Tee Booking deleted successfully!');
    }
}


?>