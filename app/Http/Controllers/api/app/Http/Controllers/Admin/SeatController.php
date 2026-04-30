<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Seat;
use App\Models\SeatCategory;
use App\Models\EventSeatingLayout;
use Illuminate\Support\Facades\DB;
use App\Models\TicketBooking;
class SeatController extends Controller
{

public function redirect()
{
    $event = Event::latest()->first();

    if (!$event) {
        return redirect()->back()->with('error','No event found');
    }

    return redirect()->route('admin.events.seating.index', $event->id);
}
public function index($id)
{
    $event = Event::findOrFail($id);

    $events = Event::latest()->get();

    $layouts = EventSeatingLayout::with(['seats.category'])
                ->where('event_id', $id)
                ->get();


    $categories = SeatCategory::where('event_id', $id)->get();

    return view('admin.events.seating.index', compact(
        'event',
        'events',
        'layouts',
        'categories'
    ));
}

    public function create($eventId)
    {
        $event = Event::findOrFail($eventId);

        $categories = SeatCategory::where('event_id',$eventId)->get();
        return view('admin.events.seating.create',compact(
            'event',
            'categories'
        ));
    }




public function store(Request $request)
{
    $request->validate([
        'event_id' => 'required',
        'rows' => 'required|integer',
        'columns' => 'required|integer',
        'category_id' => 'required'
    ]);

    $eventId = $request->event_id;
    $rows = $request->rows;
    $columns = $request->columns;

    DB::beginTransaction();

    try {

        // 🔹 Find last row used in this event
        $lastSeat = Seat::where('event_id', $eventId)
            ->orderByDesc('row_label')
            ->first();

        // Start from next alphabet
        $startAscii = $lastSeat ? ord($lastSeat->row_label) + 1 : 65;

        // Create seating layout
        $layout = EventSeatingLayout::create([
            'event_id' => $eventId,
            'total_rows' => $rows,
            'total_columns' => $columns
        ]);

        // Generate seats
        for ($row = 0; $row < $rows; $row++) {

            $rowLabel = chr($startAscii + $row);

            for ($col = 1; $col <= $columns; $col++) {

                Seat::create([
                    'event_id' => $eventId,
                    'layout_id' => $layout->id,
                    'category_id' => $request->category_id,
                    'row_label' => $rowLabel,
                    'seat_number' => $col,
                    'seat_code' => $rowLabel.$col,
                    'pos_x' => $col,
                    'pos_y' => $row + 1,
                    'status' => 'available'
                ]);

            }
        }

        DB::commit();

    } catch (\Exception $e) {

        DB::rollBack();
        throw $e;

    }

    return redirect()
        ->route('admin.events.seating.index', $eventId)
        ->with('success', 'Seat Layout Created');
}

public function categories($id)
{

$event = Event::findOrFail($id);

$categories = SeatCategory::where('event_id',$id)->get();

return view(
'admin.events.seating.categories',
compact('event','categories')
);

}
public function storeCategory(Request $request)
{

$request->validate([
'name'=>'required',
'price'=>'required'
]);
SeatCategory::create($request->all());

return back()->with('success','Category Created');

}
public function getSeatBooking($seatId)
{
    \Log::info($seatId);
    $bookingSeat = DB::table('event_booking_seats')
        ->where('seat_id',$seatId)
        ->first();

    if(!$bookingSeat){
        return response()->json([
            'status'=>false,
            'message'=>'No booking found'
        ]);
    }

    $booking = TicketBooking::with([
        'member',
        'seats.seat'
    ])->find($bookingSeat->booking_id);
\Log::info($booking);
    return response()->json([
        'status'=>true,
        'booking'=>$booking
    ]);
}
public function toggleSeatBlock(Request $request)
{
    $seat = Seat::findOrFail($request->seat_id);

    if ($seat->status == 'booked') {
        return response()->json([
            'status' => false,
            'message' => 'Booked seat cannot be changed.'
        ]);
    }

    $seat->status = $seat->status == 'blocked'
        ? 'available'
        : 'blocked';

    $seat->save();

    return response()->json([
        'status' => true,
        'new_status' => $seat->status
    ]);
}
}