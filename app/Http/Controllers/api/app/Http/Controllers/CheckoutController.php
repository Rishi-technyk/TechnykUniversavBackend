<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{RoomBookingOccupant, RoomBooking, RoomBookingTemp};
use App\Models\RoomPrice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        // with data
        /* if (session('temp_booking_id')) {
            $booking_number = session('temp_booking_id');
            $roomBookingData = RoomBookingTemp::select('room_bookings_temp.*', 'rooms.type AS room_type', 'occupant_types.name AS occupant_types_name')
                ->join('rooms', 'rooms.id', '=', 'room_bookings_temp.room_id')
                ->join('occupant_types', 'occupant_types.id', '=', 'room_bookings_temp.occupant_type_id')
                ->where('room_bookings_temp.booking_number', $booking_number)->first();
            //dd($roomBookingData);
            return view('member.checkout', compact('roomBookingData'));
        } */
        $room_cart = session('room_cart');

        return view('member.checkout', compact('room_cart'));
    }





    /* public function store(Request $request)
    {
        if (session('temp_booking_id')) {
            return redirect()->route('checkout');
        }
        //dd($request->all());
        $request->validate(
            [
                'room_id' => 'required',
                'total_rooms' => 'required',
                'occupant_type_id' => 'required',
                'occupant_type_1' => 'required',
                'occupant_name_1' => 'required',
                'occupant_type_2' => 'required',
                'occupant_name_2' => 'required',
            ],
        );

        $room_id = $request->post('room_id');
        $total_rooms = $request->post('total_rooms');
        $occupant_type_id = $request->post('occupant_type_id');
        $occupant_type_1 = $request->post('occupant_type_1');
        $occupant_name_1 = $request->post('occupant_name_1');
        $occupant_type_2 = $request->post('occupant_type_2');
        $occupant_name_2 = $request->post('occupant_name_2');

        $checkin = date('Y-m-d', strtotime(session('checkin')));
        $checkout = date('Y-m-d', strtotime(session('checkout')));

        // server side business validation

        // 0. same as validateRoomBooking

        // 1. rooms available or not


        // get night and price
        $earlier = new \DateTime($checkin);
        $later = new \DateTime($checkout);
        $total_nights = $later->diff($earlier)->format("%a");

        $price_details = RoomPrice::select('room_prices.*', 'occupant_types.*')
            ->join('occupant_types', 'room_prices.occupant_type_id', '=', 'occupant_types.id')
            ->where(['room_prices.room_type_id' => $room_id, 'room_prices.occupant_type_id' => $occupant_type_id])
            ->first();
        $price_per_night = $price_details->price;

        $total_price = $price_per_night *  $total_rooms * $total_nights;

        $booking_number = bin2hex(random_bytes(8));
        $member_id = Auth::id();

        // 2. insert into temp table       
        $RoomBookingTemp = RoomBookingTemp::create([
            'booking_number' => $booking_number,
            'member_id' => $member_id,
            'room_id' => $room_id,
            'checkin' => $checkin,
            'checkout' => $checkout,
            'occupant_type_id' => $occupant_type_id,
            'total_nights' => $total_nights,
            'total_rooms' => $total_rooms,
            'occupant_type_1' => $occupant_type_1,
            'occupant_name_1' => $occupant_name_1,
            'occupant_type_2' => $occupant_type_2,
            'occupant_name_2' => $occupant_name_2,
            'price' => $total_price,
            'gst' => 0,
        ]);

        // for test

        $booking = RoomBooking::create([
            'booking_number' => $booking_number,
            'room_id' => $room_id,
            'occupant_type_id' => $occupant_type_id,
            'checkin' => $checkin,
            'checkout' => $checkout,
            'no_of_room' => $total_rooms,
            'price' => $total_nights,
            'gst' => 0,
            'status' => 'pending',
        ]);;

        //INSERT INTO `room_booking_occupants`(`id`, `booking_id`, `occupant_type`, `name`, `created_at`) 
        RoomBookingOccupant::insert([
            [
                'booking_id' => $booking->id,
                'occupant_type' => $occupant_type_1,
                'name' => $occupant_name_1,
            ],
            [
                'booking_id' => $booking->id,
                'occupant_type' => $occupant_type_2,
                'name' => $occupant_name_2,
            ]
        ]);
        //.for test

        if ($RoomBookingTemp) {
            session(['temp_booking_id' => $booking_number]);

            return redirect()->route('checkout');
        }
        return redirect()->route('roomDetails')->with('error', 'Something went wrong.');
    } */

}
