<?php
namespace App\Http\Controllers\api\v1;
use App\Http\Controllers\Controller;

use App\Models\TableBooking;
use App\Models\Table;
use App\Models\TableMeal;
use App\Models\TableTime;
use App\Models\TableVenue;
use Illuminate\Http\Request;
use DB;

class TableBookingController extends Controller
{

public function availability(Request $request)
{
    $date = $request->query('date');
    $mealId = $request->query('meal_id');
    $timeId = $request->query('time_id');
    $venueId = $request->query('venue_id');

    // ✅ Always load dropdown data FIRST
    $venues = TableVenue::where('status','Active')
        ->select(['id','name'])
        ->get();

    $meals = TableMeal::where('status', 'Active')
        ->select(['id','name'])
        ->with(['times' => function ($query) {
            $query->select('id', 'meal_id', 'time');
        }])
        ->get();

    // ✅ Only run availability logic if all filters exist
    if ($mealId && $timeId && $venueId && $date) {

        $memberId = auth()->id();

        // ✅ Restriction: one booking per meal
        $alreadyBooked = TableBooking::where([
            'member_id' => $memberId,
            'booking_date' => $date,
            'meal_id' => $mealId,
            'status' => 'Booked'
        ])->exists();

        if ($alreadyBooked) {
            return response()->json([
                'status' => false,
                'message' => 'You already booked for this meal',
                'data' => [
                    'venues' => $venues,
                    'meals' => $meals,
                    'tables' => []
                ],
            ]);
        }

        // ✅ Get booked tables
        $bookedTableIds = TableBooking::where([
            'booking_date' => $date,
            'meal_id' => $mealId,
            'time_id' => $timeId,
            'venue_id' => $venueId,
            'status' => 'Booked'
        ])->pluck('table_id');

        // ✅ Available tables
        $tables = Table::where('meal_id', $mealId)
            ->where('venue_id', $venueId)
            ->whereNotIn('id', $bookedTableIds)
            ->get(['id','name']);

        return response()->json([
            'status' => true,
            'data' => [
                'venues' => $venues,
                'meals' => $meals,
                'tables' => $tables
            ],
        ]);
    }

    // ✅ Default (initial load)
    return response()->json([
        'status' => true,
        'data' => [
            'venues' => $venues,
            'meals' => $meals
        ],
    ]);
}

  public function getMeals()
{
    $meals = TableMeal::where('status','Active')->get();

    return response()->json([
        'status' => true,
        'data' => $meals
    ]);
}
public function getTimes($meal_id)
{
    $times = TableTime::where('meal_id',$meal_id)
                ->where('status','Active')
                ->get();

    return response()->json([
        'status' => true,
        'data' => $times
    ]);
}
public function getVenues()
{
    $venues = TableVenue::where('status','Active')->get();

    return response()->json([
        'status' => true,
        'data' => $venues
    ]);
}
public function getTables($meal_id)
{
    $tables = Table::where('meal_id',$meal_id)
                ->where('status','Active')
                ->get();

    return response()->json([
        'status' => true,
        'data' => $tables
    ]);
}
public function checkAvailability(Request $request)
{
    $request->validate([
        'meal_id' => 'required',
        'venue_id' => 'required',
        'time_id' => 'required',
        'booking_date' => 'required|date'
    ]);

    $bookedTables = TableBooking::where([
            'meal_id' => $request->meal_id,
            'venue_id' => $request->venue_id,
            'time_id' => $request->time_id,
            'booking_date' => $request->booking_date,
            'status' => 'Booked'
        ])->pluck('table_id');

    $availableTables = Table::where('meal_id',$request->meal_id)
        ->whereNotIn('id',$bookedTables)
        ->where('status','Active')
        ->get();

    return response()->json([
        'status' => true,
        'available_tables' => $availableTables
    ]);
}
public function createBooking(Request $request)
{
    $request->validate([
        'meal_id' => 'required',
        'venue_id' => 'required',
        'time_id' => 'required',
        'table_id' => 'required',
        'booking_date' => 'required|date'
    ]);

    $member_id = auth()->user()->id;

    // ✅ 1. Prevent multiple bookings per meal
    $alreadyBooked = TableBooking::where([
        'member_id' => $member_id,
        'booking_date' => $request->booking_date,
        'meal_id' => $request->meal_id,
        'status' => 'Booked'
    ])->exists();

    if ($alreadyBooked) {
        return response()->json([
            'status' => false,
            'message' => 'You already booked for this meal'
        ]);
    }

    // ✅ 2. Prevent table double booking
    $exists = TableBooking::where([
        'table_id' => $request->table_id,
        'booking_date' => $request->booking_date,
        'meal_id' => $request->meal_id,
        'time_id' => $request->time_id,
        'venue_id' => $request->venue_id,
        'status' => 'Booked'
    ])->exists();

    if ($exists) {
        return response()->json([
            'status' => false,
            'message' => 'Table already booked'
        ]);
    }

    // ✅ 3. Create booking
    $booking = TableBooking::create([
        'member_id' => $member_id,
        'meal_id' => $request->meal_id,
        'venue_id' => $request->venue_id,
        'time_id' => $request->time_id,
        'table_id' => $request->table_id,
        'booking_date' => $request->booking_date,
        'status' => 'Booked'
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Booking Successful',
        'data' => $booking
    ]);
}
public function updateBooking(Request $request, $id)
{
    $booking = TableBooking::findOrFail($id);

    if ($booking->status == 'Cancelled') {
        return response()->json([
            'status' => false,
            'message' => 'Cannot edit cancelled booking'
        ]);
    }

    $booking->update($request->all());

    return response()->json([
        'status' => true,
        'message' => 'Booking Updated',
        'data' => $booking
    ]);
}
public function cancelBooking($id)
{
    $booking = TableBooking::findOrFail($id);

    $booking->update([
        'status' => 'Cancelled'
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Booking Cancelled'
    ]);
}
public function myBookings(Request $request)
{
    $member_id = auth()->user()->id;

    $status = $request->status; // Booked / Cancelled
    $type   = $request->type;   // upcoming / past

    $query = TableBooking::with([
            'table:id,name',
            'meal:id,name',
            'time:id,time',
            'venue:id,name'
        ])
        ->where('member_id', $member_id);

    // Filter by status
    if ($status) {
        $query->where('status', $status);
    }

    // Filter upcoming or past
    if ($type === 'upcoming') {
        $query->whereDate('booking_date', '>=', now()->toDateString());
    }

    if ($type === 'past') {
        $query->whereDate('booking_date', '<', now()->toDateString());
    }

    $bookings = $query->latest()
        ->paginate(10);

    return response()->json([
        'status' => true,
        'message' => 'Bookings fetched successfully',
        'data' => $bookings
    ]);
}
public function getBookingByID($id)
{
    $member_id = auth()->id();

    $booking = TableBooking::with([
            'meal:id,name',
            'time:id,time',
            'venue:id,name',
            'table:id,name'
        ])
        ->where('id', $id)
        ->where('member_id', $member_id)
        ->first();

    if (!$booking) {
        return response()->json([
            'status' => false,
            'message' => 'Booking not found'
        ], 404);
    }

    return response()->json([
        'status' => true,
        'message' => 'Booking fetched successfully',
        'data' => $booking
    ]);
}


public function myUpComingBookings()
{
    $memberId = auth()->id();
    $today = now()->toDateString();

    $bookings = TableBooking::with([
        'meal:id,name',
        'table:id,name',
        'time:id,time',
        'venue:id,name'
    ])
    ->where('member_id', $memberId)
    ->whereDate('booking_date', '>=', $today)
    ->orderBy('booking_date', 'asc')
    ->get();

    return response()->json([
        'status' => true,
        'data' => $bookings
    ]);
}

public function ClubMenues(Request $request){
    
  $menues = DB::table('club_menus')
      ->where('status', 'Active')
      ->orderBy('id', 'asc')
      ->get()
      ->map(function ($menu) {
          $menuData = (array) $menu;
          $subtitle = $menuData['subTitle']
              ?? $menuData['subtitle']
              ?? $menuData['description']
              ?? $menuData['details']
              ?? null;

          $menuData['subtitle'] = $subtitle;
          $menuData['icon_url'] = !empty($menuData['icon'])
              ? asset('mobileAPI/icons/' . ltrim((string) $menuData['icon'], '/'))
              : null;
          $menuData['cta_label'] = $menuData['cta_label'] ?? 'Open menu';
          $menuData['module_type'] = $menuData['module_type']
              ?? strtolower(str_replace(' ', '_', (string) ($menuData['name'] ?? 'club_menu')));

          return $menuData;
      })
      ->values();
  
   return response()->json([
        'status' => true,
        'message' => 'Menues fetched successfully.',
        'data' => $menues,
        'meta' => [
            'total_menus' => $menues->count(),
            'menus_with_icons' => $menues->filter(fn ($menu) => !empty($menu['icon_url']))->count(),
        ],
    ], 200);
  
}

}
