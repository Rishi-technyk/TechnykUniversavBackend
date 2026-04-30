<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\TableMeal;
use App\Models\TableVenue;
use App\Models\TableTime;
use App\Models\TableBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TableBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['meals']  = TableMeal::where('status', 'active')->get();
        $data['venues'] = TableVenue::where('status', 'active')->get();
        $data['times']  = TableTime::where('status', 'active')->get();
        $data['request'] = $request;
        $data['table'] = [];
        
        if($request->meal_id && $request->venue_id && $request->time_id) {

            $table_ids = TableBooking::where('meal_id', $request->meal_id)->where('time_id', $request->time_id)->where('venue_id', $request->venue_id)->where('booking_date', $request->booking_date)->pluck('table_id');

            $data['table'] = Table::where('status', 'Active')->whereNotIn('id',$table_ids)->where('meal_id', $request->meal_id)->get();

        } else {
            $data['table'] = [];
        }

        return view('frontend.table_booking.booking', $data);
    }

    function get_times(Request $request) 
    {
        $meal_id = $request->mealId;

        $times = TableTime::where('meal_id', $meal_id)->where('status', 'Active')->get();
        return view('frontend.table_booking.time_options', compact('times'))->render();
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'meal_id' => 'required',
            'venue_id' => 'required',
            'time_id' => 'required',
            'table_id' => 'required',
            'booking_date' => 'required'
        ]);

        if(TableBooking::where('booking_date', $request->booking_date)->where('venue_id', $request->venue_id)->where('meal_id', $request->meal_id)->where('time_id', $request->time_id)->where('table_id', $request->table_id)->exists()){
            return redirect()->back()->with('warning', 'These tables are already booked.');
        }

        if(TableBooking::where('member_id', Auth::guard('student')->user()->id)->where('meal_id', $request->meal_id)->where('time_id', $request->time_id)->where('table_id', $request->table_id)->exists()){

            $booking = TableBooking::where('member_id', Auth::guard('student')->user()->id)->where('meal_id', $request->meal_id)->where('time_id', $request->time_id)->where('table_id', $request->table_id)->first();

        } else {

            $booking                = new TableBooking();

        }
        
        $booking->member_id     = Auth::guard('student')->user()->id;
        $booking->meal_id       = $request->meal_id;
        $booking->venue_id      = $request->venue_id;
        $booking->time_id       = $request->time_id;
        $booking->table_id      = $request->table_id;
        $booking->booking_date  = $request->booking_date;
        $booking->save();

        return redirect()->route('table.booking')->with('success', 'Table booked successfully!');
    }

    // Backend Routes
    public function booking_list(Request $request)
    {
        
        if($request && $request->time || $request->venue || $request->booking_date){

            $q = TableBooking::query();

            if($request->time){
                $q->where('time_id', $request->time);
            }

            if($request->venue){
                $q->where('venue_id', $request->venue);
            }

            if($request->booking_date){
                $q->whereDate('created_at', $request->booking_date);
            }

            $datas = $q->orderBy('id', 'DESC')->get();

        } else {

            $datas = TableBooking::orderBy('id', 'DESC')->get();

        }

        $data['datas'] = $datas;

        $data['times']  = TableTime::where('status', 'Active')->get();
        $data['venues'] = TableVenue::where('status', 'Active')->get();
        $data['request'] = $request;

        return view('backend.table_booking.index', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TableBooking  $tableBooking
     * @return \Illuminate\Http\Response
     */
    public function edit(TableBooking $tableBooking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TableBooking  $tableBooking
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TableBooking $tableBooking)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TableBooking  $tableBooking
     * @return \Illuminate\Http\Response
     */
    public function destroy(TableBooking $tableBooking)
    {
        //
    }

    function index_transaction()
    {
        $data['datas'] = TableBooking::where('member_id', Auth::guard('student')->user()->id)->orderBy('id', 'DESC')->paginate(20);
        return view('frontend.table_booking.transaction', $data);
    }
}
