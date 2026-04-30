<?php
// app/Http/Controllers/TeeBookingController.php

namespace App\Http\Controllers\Admin\Tee;
use App\CPU\Helpers;
use App\Models\TeeBooking;
use App\Models\TeeSession;
use App\Models\TeeHole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Controllers\Controller;

class TeeBookingController extends Controller
{
    
    public function index()
    {
        
        $futureDate = Carbon::now()->addDays(20)->toDateString();
    
            $teeBookings = TeeBooking::where('booking_date', '<=', $futureDate)
            ->orderBy('booking_date', 'DESC')
            ->get();
        return view('admin.tee.tee_bookings.index', compact('teeBookings'));
    }

  
    public function create()
    {
        $sessions = TeeSession::all();
        $teeHoles = TeeHole::all();

        return view('admin.tee.tee_bookings.create', compact('sessions', 'teeHoles'));
        // return view('admin.tee.tee_bookings.create');
    }

   
    public function store(Request $request)
    {
        $request->validate([
            'golf_start_time' => 'required|date_format:H:i',
            'golf_end_time' => 'required|date_format:H:i|after:golf_start_time',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'sessions' => 'required|array|min:1',
            'tee_sessions.*.session_id' => 'required|exists:sessions,id',
            'tee_sessions.*.slot_interval' => 'required|integer',
            'tee_sessions.*.tee_off_hole_id' => 'required|exists:tee_holes,id',
        ]);
        
        // Additional custom validation
        foreach ($request->sessions as $session) {
            $sessionStartTime = Carbon::createFromFormat('H:i', $session['start_time']);
            $sessionEndTime = Carbon::createFromFormat('H:i', $session['end_time']);
            $golfstartTime = Carbon::createFromFormat('H:i', $request->input('golf_start_time'));
            $golfendTime = Carbon::createFromFormat('H:i', $request->input('golf_end_time'));
    
            if ($sessionStartTime < $golfstartTime || $sessionEndTime > $golfendTime) {
                return redirect()->back()->with('error', '1Session time does not match golf start and end time.');
            }
        }
        
        // Get input data from the request
        $bookingData = $request->only(['golf_start_time', 'golf_end_time', 'from_date', 'to_date']);
        
        // Convert string dates to Carbon instances for easy manipulation
        $startDate = Carbon::parse($bookingData['from_date']);
        $endDate = Carbon::parse($bookingData['to_date']);
        
        
        // Iterate over the date range
        while ($startDate->lte($endDate)) {
            $existingBooking = TeeBooking::where('booking_date', $startDate->toDateString())->first();
            if (!$existingBooking) {
            // Create a separate entry for each date
            $teeBooking = TeeBooking::create([
                'golf_start_time' => $bookingData['golf_start_time'],
                'golf_end_time' => $bookingData['golf_end_time'],
                'booking_date' => $startDate->toDateString(), 
                'is_active' => false
            ]);
            
            // Generate and store TeeSheet entries
            $this->generateTeeSheetEntries($teeBooking, $request->sessions);
            }
        
            // Move to the next date
            $startDate->addDay();
        }
        return redirect()->back()->with('success', 'Tee booking created successfully.');
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
            'golf_start_time' => 'required',
            'golf_end_time' => 'nullable',
        ]);

        $teeBooking->update($request->all());

        return redirect()->route('tee_bookings.index')->with('success', 'Tee Booking updated successfully!');
    }

    public function destroy(TeeBooking $teeBooking)
    {
        $teeBooking->teeSheets()->delete();
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