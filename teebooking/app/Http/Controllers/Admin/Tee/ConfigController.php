<?php
// app/Http/Controllers/TeeBookingController.php

namespace App\Http\Controllers\Admin\Tee;
use App\CPU\Helpers;
use App\Models\TeeBooking;
use App\Models\TeeSession;
use App\Models\TeeHole;
use App\Models\BusinessSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Controllers\Controller;

class ConfigController extends Controller
{

    public function index()
    {

     
    $startTimeString = Helpers::get_setting('booking_start_time');

    // Convert the string to a Carbon instance
    $startCountTime = Carbon::createFromFormat('H:i', $startTimeString);

    // Check if conversion is successful before proceeding
    if ($startCountTime instanceof Carbon) {
        // Perform operations on $startTime
        $new1Time = $startCountTime->subHours(Helpers::get_setting('hour_before_booking'));
        $startTime = $new1Time->format('H:i');

        $newTime = $startCountTime->addHours(Helpers::get_setting('hour_booking_range'));
        $endTime = $newTime->format('H:i');
        
    } else {
        // Handle the case where the conversion failed
        $endTime = 'Invalid time format';
    }


        $sessions = TeeSession::all();
        $teeHoles = Teehole::all();
        return view('admin.tee.config.create', compact('sessions','startTime','teeHoles','startTime','endTime'));
    }

  
    public function create()
    {
        $sessions = TeeSession::all();
        $teeHoles = Teehole::all();

        return view('admin.tee.tee_bookings.create', compact('sessions', 'teeHoles'));
        // return view('admin.tee.tee_bookings.create');
    }

   
    public function store(Request $request)
    {
        // $bookingStartTime = Carbon::createFromFormat('H:i', $request['booking_start_time']);
        
        // $timeOnly = $bookingStartTime->format('H:i');
        //dd($request->all(), $bookingStartTime,$timeOnly);
        $settings = [
            // "booking_start_time"=>$timeOnly ,
           "hour_before_booking"=>$request['hour_before_booking'],
           "hour_booking_range"=>$request['hour_booking_range'],
           "day_open_booking"=>$request['day_open_booking']
        ];
        
        
        foreach ($settings as $key=> $setting) {
            BusinessSetting::updateOrInsert(['key_name' => $key], [
                'key_value' => $setting
            ]);
        }

      
        return redirect()->back()->with('success', 'Configuration updated successfully.');
    }
    
    public function generateTeeSheetEntries(TeeBooking $teeBooking, array $sessions)
    {
        foreach ($sessions as $session) {
            // Calculate time slots within the specified golf start and end time using the given slot interval
            $startTime = Carbon::createFromFormat('H:i', $teeBooking->golf_start_time);
            $endTime = Carbon::createFromFormat('H:i', $teeBooking->golf_end_time);
            $sessionStartTime = Carbon::createFromFormat('H:i', $session['start_time']);
            $sessionEndTime = Carbon::createFromFormat('H:i', $session['end_time']);
            // $currentTime = $startTime->copy();
            $currentTime = $sessionStartTime->copy();
    
            while ($currentTime <= $endTime && $currentTime <= $sessionEndTime) {
                // Insert into TeeSheet table using the correct TeeBooking instance
                $teeBooking->teeSheets()->create([
                    'tee_time' => $currentTime->format('H:i'),
                    'session_id' => $session['session_id'],
                    'slot_interval' => $session['slot_interval'],
                    'tee_off_hole_id' => $session['tee_off_hole_id'], 
                    'is_active' => false
                ]);
    
                // Increment time by slot interval
                $currentTime->addMinutes($session['slot_interval']);
            }
        }
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

    public function status_update(Request $request)
    {
        $teeBooking = TeeBooking::find($request['id']);
        $newStatus = $request['status'];
    
        // Update the is_active status of the TeeBooking
        $teeBooking->is_active = $newStatus;
        $teeBooking->save();
    
        // Update the is_active status of related TeeSheet entries
        $teeBooking->teeSheets()->update(['is_active' => $newStatus]);
        
        return response()->json(['success' => 1], 200);
    }
}


?>