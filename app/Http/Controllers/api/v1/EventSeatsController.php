<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\Participant;
use Illuminate\Http\Request;
use App\Models\TicketBooking;
use App\Models\Member;
use Carbon\Carbon;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\EventSeatingLayout;
use App\Models\Seat;


class EventSeatsController extends Controller
{

public function getEventSeats($id)
{
    $user = auth()->user();

    $event = Event::findOrFail($id);

    /** Check if seating layout exists */
    $isSeatBookingAvailable = Seat::where('event_id',$id)->exists();

    if(!$isSeatBookingAvailable){
        return response()->json([
            'status' => false,
            'message' => 'Seat layout not available'
        ]);
    }

    /** Calculate age */
    $age = Carbon::parse($user->DOB)->age;

    /** Complimentary eligibility */
    $isComplimentaryAllowed = false;

    if($event->complimentary_age && $age >= $event->complimentary_age){
        $isComplimentaryAllowed = true;
    }

    /** Seat counts by type */
    $seatCounts = Seat::join('seat_categories','seats.category_id','=','seat_categories.id')
        ->where('seats.event_id',$id)
        ->select(
            DB::raw('LOWER(seat_categories.seat_type) as type'),
            DB::raw('COUNT(*) as total')
        )
        ->groupBy('type')
        ->pluck('total','type');

  

    /** 🔹 Layouts (ADDED — as you asked) */
 $layouts = EventSeatingLayout::where('event_id', $id)
    ->with(['seats' => function ($q) {
        $q->select(
            'id',
            'layout_id',
            'category_id',
            'row_label',
            'seat_number',
            'seat_code',
            'pos_x',
            'pos_y',
            'status'
        )->with(['category' => function ($c) {
            $c->select(
                'id',
                'name',
                'price',
                'color',
                'seat_type',
                'status'
            )->where('status', 'active');
        }]);
    }])
    ->get();

    return response()->json([
        'status' => true,
        'seat_booking_available' => true,
        'complimentary_allowed' => $isComplimentaryAllowed,
        'vip_allowed'=>false,
        'seat_counts' => $seatCounts,
        'layouts' => $layouts   
    ]);
}
}