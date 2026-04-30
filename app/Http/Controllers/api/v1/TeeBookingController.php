<?php

namespace App\Http\Controllers\api\v1;



use App\CPU\Helpers;

use Illuminate\Support\Facades\Cache ;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;

use App\Models\AffilatedClubs;

use App\Models\AffilatedClubsPhones;

use App\Models\CardClosingBalance;

use App\Models\CustomerStatement;

use App\Models\TeeSessionCategory;

use App\Models\MemberReceipt;

use App\Models\OtpModel;

use App\Models\TeeSheet;

use App\Models\Member;

use App\Models\TeeMyBuddies;

use App\Models\TeeBookingDetails;

use App\Models\Teehole;

use App\Models\TeeSession;

use App\Models\TeeGroup;

use App\Services\FCMService;

use App\Rules\CurrentPasswordValidation;

use Rap2hpoutre\FastExcel\FastExcel;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Auth;

use DB;

use DateTime;

use AESEncDec;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


// use App\Models\RentalClub;
use Illuminate\Validation\Rule;


use Razorpay\Api\Api;


class TeeBookingController extends Controller

{
  public function index(Request $request)
{
    $user = auth()->user();
    $date = $request->date ?? now()->addDay()->format('Y-m-d');

    $selectedHole = $request->teeHole;
    $selectedSession = $request->session_name;

    // ✅ CACHE SETTINGS (60 sec)
    $settings = Cache::remember('tee_settings', 60, function () {
        return DB::table('tee_business_settings')
            ->pluck('key_value', 'key_name')
            ->toArray();
    });

    // ✅ CACHE HOLES (5 min)
$teeHoles = Cache::remember('tee_holes', 300, function () {
    return TeeHole::where("is_active", 1)
        ->get(['id','hole_number'])
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'hole_number' => $item->hole_number,
            ];
        })
        ->toArray();
});
\Log::info(json_encode($teeHoles));
    // ✅ CACHE SESSIONS PER CATEGORY
    $teeSessions = Cache::remember("sessions_{$user->CategoryCode}", 300, function () use ($user) {
        $categoryCodes = TeeSessionCategory::where('category_type_Code', $user->CategoryCode)
            ->pluck('tee_session_id');

        return TeeSession::where('is_active', 1)
            ->whereIn('id', $categoryCodes)
            ->select('id', 'session_name')
            ->get();
    });

    $selectedSessions = $teeSessions->pluck('id')->toArray();

    if ($selectedSession && $selectedSession !== 'All Day') {
        $selectedSessions = [$selectedSession];
    }

    // ✅ BOOKING WINDOW CALCULATION
    $teeTime = Cache::remember("first_tee_time_$date", 60, function () use ($date) {
        return TeeSheet::leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')
            ->where('tee_booking.booking_date', $date)
            ->orderBy('tee_time', 'asc')
            ->value('tee_time') ?? '06:00:00';
    });

    $specifiedDateTime = Carbon::parse("$date $teeTime");

    $windowStart = $specifiedDateTime->copy()->subHours($settings['hour_before_booking'] ?? 0);
    $windowEnd   = $windowStart->copy()->addHours($settings['hour_booking_range'] ?? 0);

    $now = now();

    $windowStatus = "closed";
    $notesMessage = "The window is closed.";
    $windowMessage = [];

    $teeSheets = [];

    if ($specifiedDateTime->dayOfWeek !== Carbon::TUESDAY) {

        if ($now->between($windowStart, $windowEnd)) {

            // ✅ CACHE MAIN DATA (VERY IMPORTANT)
            $cacheKey = "tee_sheets_{$date}_" . implode('_', $selectedSessions) . "_{$selectedHole}";

            $teeSheets = Cache::remember($cacheKey, 5, function () use ($date, $selectedSessions, $selectedHole) {

                $query = TeeSheet::where("is_locked_by_admin", 0)
                    ->select(
                        'tee_sheet.*',
                        'tee_holes.hole_number',
                        DB::raw('EXISTS(
                            SELECT 1 FROM tee_booking_details 
                            WHERE tee_booking_details.tee_sheet_id = tee_sheet.id 
                            AND tee_booking_details.is_cancelled = 0
                        ) as is_booked')
                    )
                    ->leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')
                    ->leftJoin('tee_holes', 'tee_holes.id', '=', 'tee_sheet.tee_off_hole_id')
                    ->where('tee_booking.booking_date', $date)
                    ->whereIn('tee_sheet.session_id', $selectedSessions);

                if ($selectedHole) {
                    $query->where('tee_sheet.tee_off_hole_id', $selectedHole);
                }

                return $query->orderBy('tee_sheet.id')->get();
            });

            $windowStatus = "open";
            $notesMessage = "The window is open.";

            $windowMessage = [
                "start" => $windowStart->format('d-m-Y H:i'),
                "end"   => $windowEnd->format('d-m-Y H:i'),
            ];
        }
    }

    // ✅ OPTIMIZED BOOKING CHECK
    $is_booking_exist = TeeBookingDetails::where('is_cancelled', 0)
        ->whereHas('booking', function ($q) use ($date) {
            $q->where('booking_date', $date);
        })
        ->where(function ($q) use ($user) {
            $q->where('player1_id', $user->id)
              ->orWhere('player2_id', $user->id)
              ->orWhere('player3_id', $user->id)
              ->orWhere('player4_id', $user->id);
        })
        ->exists();

    return response()->json([
        "status"           => true,
        "selectedDate"     => $date,
        "window_status"    => $windowStatus,
        "notes"            => $notesMessage,
        "window_message"   => $windowMessage,
        "tee_sheets"       => $teeSheets,
        "tee_sessions"     => $teeSessions,
        "tee_holes"        => $teeHoles,
        "is_booking_exist" => $is_booking_exist,
        "settings"         => $settings
    ]);
}
public function index_AEPTA(Request $request)
{
    $date = $request->date;
    if (!$date) {
        $currentDate = now();
        $timelineDate = $currentDate->copy();
        $date = $timelineDate->modify("+1 day")->format('Y-m-d');
    }

    // Default teeTime (fallback)
    $teeSheetStartTime = TeeSheet::leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')
        ->where('tee_booking.booking_date', $date)
        ->orderBy('tee_time', 'asc')
->first();


    $teeTime = $teeSheetStartTime ? $teeSheetStartTime->tee_time : '06:00:00';

    $currentDateTime = now();
    // $specifiedDateTime = new DateTime($date . $teeTime);
    $specifiedDateTime = Carbon::parse("$date $teeTime");
   $Category_id = auth()->user()->CategoryCode;
   $categoryCodes = TeeSessionCategory::where('category_type_Code', $Category_id)
    ->pluck('tee_session_id')
    ->toArray();
    // Fetch active sessions
    $teeSessions = TeeSession::where('is_active', 1)
    ->whereIn('id', $categoryCodes)
    ->select('id', 'session_name')
    ->get();
    $teeSheets = [];
    $selectedHole = $request->teeHole;
    $selectedSession = $request->session_name;

    // Session filtering
    $selectedSessions = $teeSessions->pluck('id')->toArray();
    if ($selectedSession && $selectedSession !== 'All Day') {
        $selectedSessions = [$selectedSession];
    }

    $notesMessage = "";
    $windowMessage = "";
    $windowStatus = "closed"; // default
    $loggedInMemberId = Auth::id();
    // Skip Tuesday
    if ($specifiedDateTime->format('N') === '3') {
        $notesMessage = "The window is closed on Monday.";
    } else {
        // Booking window calculation
        $windowStart = clone $specifiedDateTime;
        $HBB = Helpers::get_setting('hour_before_booking');
        // $windowStart->modify('-' . $HBB . ' hours');

        $windowEnd = clone $windowStart;
        $HBR = Helpers::get_setting('hour_booking_range');
        // $windowEnd->modify('+' . $HBR . ' hours');
        
        $windowStart = $specifiedDateTime->copy()->subHours($HBB);
$windowEnd   = $windowStart->copy()->addHours($HBR);

 
if ($currentDateTime >= $windowStart && $currentDateTime <= $windowEnd) {
    $tableObj = TeeSheet::where("is_locked_by_admin",0)->select(
        'tee_sheet.*',
        'tee_holes.hole_number',
        DB::raw('CASE WHEN tee_booking_details.id IS NOT NULL THEN true ELSE false END as is_booked')
    )
        ->leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')
        ->leftJoin('tee_holes', 'tee_holes.id', '=', 'tee_sheet.tee_off_hole_id')
        ->leftJoin('tee_sessions', 'tee_sessions.id', '=', 'tee_sheet.session_id')
        ->leftJoin('tee_booking_details', function ($join) {
            $join->on('tee_booking_details.tee_sheet_id', '=', 'tee_sheet.id')
                 ->where('tee_booking_details.is_cancelled', '=', 0);
        })
        ->where('tee_booking.booking_date', $date)
        ->whereIn('tee_sheet.session_id', $selectedSessions);

    if ($selectedHole) {
        $tableObj->where("is_locked_by_admin",0)->where("tee_sheet.tee_off_hole_id", $selectedHole);
    }



 
            $teeSheets = $tableObj->orderBy('tee_sheet.id')->get();

            $windowStatus = "open";
            $notesMessage = "The window is open.";
            $windowMessage = [
                "start" => $windowStart->format('d-m-Y H:i'),
                "end"   => $windowEnd->format('d-m-Y H:i'),
            ];
        } else {
            $notesMessage = "The window is closed.";
        }
    }
    // Rentals & Holes
    // $rentalClubs = RentalClub::active()->get();
    // $teeHoles = TeeHole::where("is_active", 1)->get(['id','hole_number']);
$teeHoles = Cache::remember('tee_holes', 300, function () {
    return TeeHole::where("is_active", 1)->get(['id','hole_number']);
});
    // Check if user already booked
    $is_booking_exist = TeeBookingDetails::join('tee_booking', 'tee_booking.id', '=', 'tee_booking_details.tee_booking_id')
        ->where(function ($query) use ($loggedInMemberId) {
            $query->where('player1_id', $loggedInMemberId)
                ->orWhere('player2_id', $loggedInMemberId)
                ->orWhere('player3_id', $loggedInMemberId)
                ->orWhere('player4_id', $loggedInMemberId);
        })
        ->where('tee_booking_details.is_cancelled', 0)
        ->where('tee_booking.booking_date', $date)
        ->exists();
// $settings = DB::table('tee_business_settings')
//     ->pluck('key_value', 'key_name')
//     ->toArray();
$settings = Cache::remember('tee_settings', 60, function () {
    return DB::table('tee_business_settings')
        ->pluck('key_value', 'key_name')
        ->toArray();
});

\Log::info($settings);
    return response()->json([
        "status"          => true,
        "selectedDate"    => $date,
        "window_status"   => $windowStatus,
        "notes"           => $notesMessage,
        "window_message"  => $windowMessage,
        "tee_sheets"      => $teeSheets,
        "tee_sessions"    => $teeSessions,
        "tee_holes"       => $teeHoles,
        // "rental_clubs"    => $rentalClubs,
        "is_booking_exist"=> $is_booking_exist,
        'settings'=>$settings
    ]);
}
public function index_AEPTAold(Request $request)
{
    $date = $request->date;
    if (!$date) {
        $currentDate = now();
        $timelineDate = $currentDate->copy();
        $date = $timelineDate->modify("+1 day")->format('Y-m-d');
    }

    // Default teeTime (fallback)
    $teeSheetStartTime = TeeSheet::leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')
        ->where('tee_booking.booking_date', $date)
        ->orderBy('tee_time', 'asc')
->first();


    $teeTime = $teeSheetStartTime ? $teeSheetStartTime->tee_time : '06:00:00';

    $currentDateTime = now();
    // $specifiedDateTime = new DateTime($date . $teeTime);
    $specifiedDateTime = Carbon::parse("$date $teeTime");
   $Category_id = auth()->user()->CategoryCode;
   $categoryCodes = TeeSessionCategory::where('category_type_Code', $Category_id)
    ->pluck('tee_session_id')
    ->toArray();
    // Fetch active sessions
    $teeSessions = TeeSession::where('is_active', 1)
    ->whereIn('id', $categoryCodes)
    ->select('id', 'session_name')
    ->get();
    $teeSheets = [];
    $selectedHole = $request->teeHole;
    $selectedSession = $request->session_name;

    // Session filtering
    $selectedSessions = $teeSessions->pluck('id')->toArray();
    if ($selectedSession && $selectedSession !== 'All Day') {
        $selectedSessions = [$selectedSession];
    }

    $notesMessage = "";
    $windowMessage = "";
    $windowStatus = "closed"; // default
    $loggedInMemberId = Auth::id();
    // Skip Tuesday
    if ($specifiedDateTime->format('N') === '1') {
        $notesMessage = "The window is closed on Monday.";
    } else {
        // Booking window calculation
        $windowStart = clone $specifiedDateTime;
        $HBB = Helpers::get_setting('hour_before_booking');
        // $windowStart->modify('-' . $HBB . ' hours');

        $windowEnd = clone $windowStart;
        $HBR = Helpers::get_setting('hour_booking_range');
        // $windowEnd->modify('+' . $HBR . ' hours');
        
        $windowStart = $specifiedDateTime->copy()->subHours($HBB);
$windowEnd   = $windowStart->copy()->addHours($HBR);

 
if ($currentDateTime >= $windowStart && $currentDateTime <= $windowEnd) {
    $tableObj = TeeSheet::where("is_locked_by_admin",0)->select(
        'tee_sheet.*',
        'tee_holes.hole_number',
        DB::raw('CASE WHEN tee_booking_details.id IS NOT NULL THEN true ELSE false END as is_booked')
    )
        ->leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')
        ->leftJoin('tee_holes', 'tee_holes.id', '=', 'tee_sheet.tee_off_hole_id')
        ->leftJoin('tee_sessions', 'tee_sessions.id', '=', 'tee_sheet.session_id')
        ->leftJoin('tee_booking_details', function ($join) {
            $join->on('tee_booking_details.tee_sheet_id', '=', 'tee_sheet.id')
                 ->where('tee_booking_details.is_cancelled', '=', 0);
        })
        ->where('tee_booking.booking_date', $date)
        ->whereIn('tee_sheet.session_id', $selectedSessions);

    if ($selectedHole) {
        $tableObj->where("is_locked_by_admin",0)->where("tee_sheet.tee_off_hole_id", $selectedHole);
    }

    // Optional: this is now redundant, already filtered above
    // if (!empty($selectedSessions)) {
    //     $tableObj->whereIn('tee_sessions.id', $selectedSessions);
    // }


 
            $teeSheets = $tableObj->orderBy('tee_sheet.id')->get();

            $windowStatus = "open";
            $notesMessage = "The window is open.";
            $windowMessage = [
                "start" => $windowStart->format('d-m-Y H:i'),
                "end"   => $windowEnd->format('d-m-Y H:i'),
            ];
        } else {
            $notesMessage = "The window is closed.";
        }
    }
    // Rentals & Holes
    // $rentalClubs = RentalClub::active()->get();
    $teeHoles = TeeHole::where("is_active", 1)->get(['id','hole_number']);

    // Check if user already booked
    $is_booking_exist = TeeBookingDetails::join('tee_booking', 'tee_booking.id', '=', 'tee_booking_details.tee_booking_id')
        ->where(function ($query) use ($loggedInMemberId) {
            $query->where('player1_id', $loggedInMemberId)
                ->orWhere('player2_id', $loggedInMemberId)
                ->orWhere('player3_id', $loggedInMemberId)
                ->orWhere('player4_id', $loggedInMemberId);
        })
        ->where('tee_booking_details.is_cancelled', 0)
        ->where('tee_booking.booking_date', $date)
        ->exists();
$settings = DB::table('tee_business_settings')
    ->pluck('key_value', 'key_name')
    ->toArray();
    return response()->json([
        "status"          => true,
        "selectedDate"    => $date,
        "window_status"   => $windowStatus,
        "notes"           => $notesMessage,
        "window_message"  => $windowMessage,
        "tee_sheets"      => $teeSheets,
        "tee_sessions"    => $teeSessions,
        "tee_holes"       => $teeHoles,
        // "rental_clubs"    => $rentalClubs,
        "is_booking_exist"=> $is_booking_exist,
        'settings'=>$settings
    ]);
}

public function getSessions(Request $request) {
    // Check if user is authenticated
    $user = auth()->user()->id;
    
    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized. Token is missing or invalid.'
        ], 401);
    }
   $Category_id = auth()->user()->CategoryCode;
       Log::info($Category_id);
   $categoryCodes = TeeSessionCategory::where('category_type_Code', $Category_id)
    ->pluck('tee_session_id')
    ->toArray();
            Log::info($categoryCodes);
    // Fetch active sessions
    $sessions = TeeSession::where('is_active', 1)
    ->whereIn('id', $categoryCodes)
    ->select('id', 'session_name')
    ->get();

    return response()->json([
        'status' => true,
        'message' => 'Active sessions fetched successfully',
        'data' => $sessions
    ], 200);
}

public function search_tee_buddies($id, Request $request)
{
    try {
        $search = $request->query('search'); // optional search param

        // 1. Get all CategoryTypeCodes for the given tee_session_id
        $categoryCodes = TeeSessionCategory::where('tee_session_id', $id)
            ->pluck('category_type_code')
            ->toArray();

        if (empty($categoryCodes)) {
            return response()->json([
                'status'  => false,
                'message' => 'No categories found for this session',
                'data'    => []
            ], 404);
        }

        // 2. Query Members belonging to those category codes
        $query = Member::select('MemberID', 'DisplayName', 'id','CategoryCode')
            ->whereIn('CategoryCode', $categoryCodes);

        // 3. Apply search if provided
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('MemberID', 'LIKE', "%{$search}%")
                  ->orWhere('DisplayName', 'LIKE', "%{$search}%");
            });
        }

        $members = $query->limit(15)->get(); // limit for performance

        return response()->json([
            'status'  => true,
            'message' => 'Buddies fetched successfully',
            'data'    => $members
        ], 200);

    } catch (\Exception $e) {
        Log::error("search_tee_buddies error: " . $e->getMessage());
        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong',
            'error'   => $e->getMessage()
        ], 500);
    }
}


 public function store_buddy(Request $request)
{
    $validator = Validator::make($request->all(), [
        'buddy_member_id' => 'required|integer|exists:memberprofile,id', // adjust table name if needed
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 422);
    }

    if (auth()->user()->id == $request->buddy_member_id) {
        return response()->json([
            'status' => false,
            'message' => "You cannot add yourself as a buddy."
        ], 400);
    }

    $data = [
        "member_id"  => $request->buddy_member_id,
        "created_by" => auth()->user()->id,
        'tee_session_id'=>$request->tee_session_id
    ];

   $buddyExist = TeeMyBuddies::where($data)
    ->where('is_active', 1) // check only active buddies
    ->first();

if ($buddyExist) {
    return response()->json([
        'status' => false,
        'message' => "This member is already in your buddy list."
    ], 400);
}

    $buddy = TeeMyBuddies::create($data);

    return response()->json([
        'status' => true,
        'message' => 'Buddy added successfully',
        'data' => $buddy
    ], 201);
}


public function delete_buddy($id)
{
    $userId = auth()->user()->id;
Log::info($userId);
    $group = TeeMyBuddies::where('id', $id)
        ->where('created_by', $userId)
        ->first();

    if (!$group) {
        return response()->json([
            'status' => false,
            'message' => 'Buddy not found.'
        ], 404);
    }

    $group->is_active = 0; // Soft delete
    $group->save();

    return response()->json([
        'status' => true,
        'message' => 'Buddy deleted successfully.'
    ], 200);
}


public function update_buddy(Request $request, $id)
{
    $buddy = TeeMyBuddies::find($id);

    if (!$buddy) {
        return response()->json([
            'status' => false,
            'message' => 'Buddy not found.'
        ], 404);
    }
    if ((int)$buddy->created_by !== auth()->user()->id) {
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized action.'
        ], 403);
    }

    $validator = Validator::make($request->all(), [
        'buddy_member_id' => 'required|integer|exists:memberprofile,id'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 422);
    }

    if ($request->buddy_member_id == auth()->user()->id) {
        return response()->json([
            'status' => false,
            'message' => "You cannot add yourself as a buddy."
        ], 400);
    }

    // Check if the new buddy already exists
    $exists = TeeMyBuddies::where([
        'member_id' => $request->buddy_member_id,
        
        'created_by' => auth()->user()->id
    ])->first();

    if ($exists) {
        return response()->json([
            'status' => false,
            'message' => "This member is already in your buddy list."
        ], 400);
    }

    $buddy->update([
        'member_id' => $request->buddy_member_id,
        'tee_session_id'=>$request->tee_session_id
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Buddy updated successfully.',
        'data' => $buddy
    ], 200);
}



///group

public function store_group(Request $request)
{
$allowedCategoryType = DB::table('tee_session_categories')
    ->where('tee_session_id', $request->tee_session_id)
    ->pluck('category_type_code')
    ->toArray();

$validator = Validator::make($request->all(), [
    'group_name' => 'required|string|max:255',

    'player1_id' => [
        'required',
        'different:player2_id,player3_id,player4_id',
        Rule::exists('memberprofile', 'id')
            ->where(function ($query) use ($allowedCategoryType) {
                $query->whereIn('CategoryCode', $allowedCategoryType);
            }),
    ],
    'player2_id' => [
        'required',
        'different:player1_id,player3_id,player4_id',
        Rule::exists('memberprofile', 'id')
            ->where(function ($query) use ($allowedCategoryType) {
                $query->whereIn('CategoryCode', $allowedCategoryType);
            }),
    ],
    'player3_id' => [
        'required',
        'different:player1_id,player2_id,player4_id',
        Rule::exists('memberprofile', 'id')
            ->where(function ($query) use ($allowedCategoryType) {
                $query->whereIn('CategoryCode', $allowedCategoryType);
            }),
    ],
    'player4_id' => [
        'nullable',
        'different:player1_id,player2_id,player3_id',
        Rule::exists('memberprofile', 'id')
            ->where(function ($query) use ($allowedCategoryType) {
                $query->whereIn('CategoryCode', $allowedCategoryType);
            }),
    ],

    'tee_session_id' => 'required|exists:tee_session_categories,tee_session_id',
  
], [
    'player1_id.required' => 'Player 1 is required.',
    'player2_id.required' => 'Player 2 is required.',
    'player3_id.required' => 'Player 3 is required.',
]);
    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 422);
    }

    if (TeeGroup::where('group_name', $request->group_name)->exists()) {
        return response()->json([
            'status' => false,
            'message' => 'Group name already exists.'
        ], 400);
    }

    $playerIds = array_filter($request->only('player1_id', 'player2_id', 'player3_id', 'player4_id'));

    if (count($playerIds) < 3) {
        return response()->json([
            'status' => false,
            'message' => 'At least 3 players are required.'
        ], 400);
    }

    $teeGroup = TeeGroup::create([
        'group_name' => $request->group_name,
        'player1_id' => $request->player1_id,
        'player2_id' => $request->player2_id,
        'player3_id' => $request->player3_id,
        'player4_id' => $request->player4_id,
        'created_by' => auth()->user()->id,
        'tee_session_id'=>$request->tee_session_id
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Group created successfully',
        'data' => $teeGroup
    ], 201);
}

public function get_group(Request $request)
{
    $userId = auth()->id();

    $groups = TeeGroup::with([
            'player1',
            'player2',
            'player3',
            'player4',
            'session'
        ])
        ->where('created_by', $userId)
        ->where('is_active', 1)
        ->get();

    $buddies = TeeMyBuddies::with('member')
        ->where('created_by', $userId)
        ->where('is_active', 1)
        ->get();

    return response()->json([
        'status' => true,
        'message' => 'Buddy list fetched successfully',
        'data' => [
            'groups' => $groups,
            'buddies' => $buddies
        ]
    ]);
}

public function get_fevroit_group(Request $request, $id)
{
    $userId = auth()->user()->id;

    // ✅ Get allowed categories for this tee_session_id
    $allowedCategoryType = DB::table('tee_session_categories')
        ->where('tee_session_id', $id)
        ->pluck('category_type_code')
        ->toArray();
Log::info($allowedCategoryType);
    // ✅ Fetch groups with players
   $groups = TeeGroup::with([
        'player1' => function ($q) use ($allowedCategoryType) {
            $q->whereIn('CategoryCode', $allowedCategoryType);
        },
        'player2' => function ($q) use ($allowedCategoryType) {
            $q->whereIn('CategoryCode', $allowedCategoryType);
        },
        'player3' => function ($q) use ($allowedCategoryType) {
            $q->whereIn('CategoryCode', $allowedCategoryType);
        },
        'player4' => function ($q) use ($allowedCategoryType) {
            $q->whereIn('CategoryCode', $allowedCategoryType);
        },
    ])
    ->where('created_by', $userId)
    ->where('is_active', 1)
    ->get()
    ->map(function ($group) {
        return [
            'id'=>$group->id,
            'group_name' => $group->group_name,
            'players' => collect([
                $group->player1,
                $group->player2,
                $group->player3,
                $group->player4,
            ])->filter()->values()
        ];
    })
    ->values();


    // ✅ Fetch buddies with allowed categories
   $buddies = TeeMyBuddies::with('member')
    ->where('created_by', $userId)
    ->where('is_active', 1)
    ->whereHas('member', function ($q) use ($allowedCategoryType) {
        $q->whereIn('CategoryCode', $allowedCategoryType);
    })
    ->get()
    ->pluck('member') // extract only member objects
    ->values();   
\Log::info($buddies);
    return response()->json([
        'status' => true,
        'message' => 'Buddy list fetched successfully',
        'data' => [
            'groups' => $groups,
            'buddies' => $buddies
        ]
    ], 200);
}


public function delete_group($id)
{
    $userId = auth()->user()->id;
Log::info($id);
    $group = TeeGroup::where('id', $id)
        ->where('created_by', $userId)
        ->first();

    if (!$group) {
        return response()->json([
            'status' => false,
            'message' => 'Group not found.'
        ], 404);
    }

    // Option 1: Soft delete (recommended if you have is_active flag)
    $group->is_active = 0;
    $group->save();

    // Option 2: Hard delete
    // $group->delete();

    return response()->json([
        'status' => true,
        'message' => 'Group deleted successfully.'
    ], 200);
}

public function update_group(Request $request, $id)
{
    $userId = auth()->user()->id;

    $group = TeeGroup::where('id', $id)
        ->where('created_by', $userId)
        ->first();

    if (!$group) {
        return response()->json([
            'status' => false,
            'message' => 'Group not found.'
        ], 404);
    }

   $validator = Validator::make($request->all(), [
        'group_name' => 'sometimes|required|string|max:255',
        'player1_id' => 'sometimes|required|different:player2_id,player3_id,player4_id|exists:memberprofile,id',
        'player2_id' => 'sometimes|required|different:player1_id,player3_id,player4_id|exists:memberprofile,id',
        'player3_id' => 'sometimes|required|different:player1_id,player2_id,player4_id|exists:memberprofile,id',
        'player4_id' => 'nullable|different:player1_id,player2_id,player3_id|exists:memberprofile,id',
        'tee_session_id' => 'required|exists:tee_session_categories,tee_session_id',
    ], [
        'player1_id.required' => 'Player 1 is required.',
        'player2_id.required' => 'Player 2 is required.',
        'player3_id.required' => 'Player 3 is required.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 422);
    }

    $group->update([
        'group_name' => $request->group_name ?? $group->group_name,
        'player1_id' => $request->player1_id ?? $group->player1_id,
        'player2_id' => $request->player2_id ?? $group->player2_id,
        'player3_id' => $request->player3_id ?? $group->player3_id,
        'player4_id' => $request->player4_id ?? $group->player4_id,
        'tee_session_id'=>$request->tee_session_id
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Group updated successfully',
        'data' => $group
    ], 200);
}



public function LockTee(Request $request)
{
    // 1️⃣ Validate request
    $validator = Validator::make($request->all(), [
        'session_id'      => 'required|exists:tee_sessions,id',
        'tee_off_hole_id' => 'required|exists:tee_holes,id',
        'tee_sheet_id'    => 'required|exists:tee_sheet,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first()
        ], 422);
    }

    // 2️⃣ Check authenticated user
    $user = auth()->user();
    if (!$user) {
        return response()->json([
            'status'  => false,
            'message' => 'Member not found'
        ], 401);
    }

    $sheetId = $request->tee_sheet_id;

    // 3️⃣ Check if already fully booked
    $alreadyBooked = TeeBookingDetails::where('tee_sheet_id', $sheetId)
        ->whereNotNull('player1_id')
        ->where('is_cancelled', 0)
        ->exists();

    if ($alreadyBooked) {
        return response()->json([
            'status'  => false,
            'message' => 'This slot is already booked.'
        ], 400);
    }

    // 4️⃣ Get block time (only once)
    $blockTime = DB::table('tee_business_settings')
        ->where('key_name', 'tee_block_time')
        ->value('key_value');

    $blockTime = (int) $blockTime;

    $lockedTill = Carbon::now()->addSeconds($blockTime);

    // 5️⃣ Atomic lock (race-condition safe)
   $updated = TeeSheet::where('id', $sheetId)
    ->where(function ($q) use ($user) {
        $q->whereNull('locked_till')
          ->orWhere('locked_till', '<', now())
          ->orWhere('locked_by_user', $user->id);
    })
    ->update([
        'locked_till'    => $lockedTill,
        'locked_by_user' => $user->id
    ]);

if (!$updated) {
    return response()->json([
        'status'  => false,
        'message' => 'This slot is temporarily reserved by another user.'
    ], 400);
}

return response()->json([
    'status'      => true,
    'message'     => 'Slot locked successfully.',
    'locked_till' => $blockTime
]);

}

public function sendFCM(Request $request){
    
    
    $playerstoBeSend = array_filter(
    $request->only(['player2_id', 'player3_id', 'player4_id'])
);

$deviceTokens = Member::whereIn('id', $playerstoBeSend)
    ->where('has_notification_permission', 1)
    ->whereNotNull('device_id')
    ->pluck('device_id')
    ->toArray();
    echo(json_encode($deviceTokens));
    if (!empty($deviceTokens)) {
    foreach ($deviceTokens as $token) {
    FCMService::sendFCMMessagev1(
        $token,
        'Tee Slot Reserved',
        'You have been added to a tee booking.',
        [
            'data' =>  '1',
            'tee_booking_detail_id' =>(string)11,
            'screen' => 'TeeBookingDetail'
        ]
    );
}
}
}
public function storeTeeBooking(Request $request)
{
    $user = auth()->user();



$allowedCategoryType = DB::table('tee_session_categories')
    ->where('tee_session_id', $request->tee_session_id)
    ->pluck('category_type_code')
    ->toArray();
$validator = Validator::make($request->all(), [
     'tee_booking_id' => 'required|exists:tee_booking,id',
        'tee_sheet_id' => 'required|exists:tee_sheet,id',

    'player1_id' => [
        'required',
        'different:player2_id,player3_id,player4_id',
        Rule::exists('memberprofile', 'id')
            ->where(function ($query) use ($allowedCategoryType) {
                $query->whereIn('CategoryCode', $allowedCategoryType);
            }),
    ],
    'player2_id' => [
        'required',
        'different:player1_id,player3_id,player4_id',
        Rule::exists('memberprofile', 'id')
            ->where(function ($query) use ($allowedCategoryType) {
                $query->whereIn('CategoryCode', $allowedCategoryType);
            }),
    ],
    'player3_id' => [
        'required',
        'different:player1_id,player2_id,player4_id',
        Rule::exists('memberprofile', 'id')
            ->where(function ($query) use ($allowedCategoryType) {
                $query->whereIn('CategoryCode', $allowedCategoryType);
            }),
    ],
    'player4_id' => [
        'nullable',
        'different:player1_id,player2_id,player3_id',
        Rule::exists('memberprofile', 'id')
            ->where(function ($query) use ($allowedCategoryType) {
                $query->whereIn('CategoryCode', $allowedCategoryType);
            }),
    ],

   
  
], [
    'player1_id.required' => 'Player 1 is required.',
    'player2_id.required' => 'Player 2 is required.',
    'player3_id.required' => 'Player 3 is required.',
]);
    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 422);
    }
    // Step 2: Check if tee sheet is already booked by someone else
    $alreadyBooked = TeeBookingDetails::where('tee_sheet_id', $request->tee_sheet_id)
        ->whereNotNull('player1_id')
        ->where('is_cancelled', 0)
        ->where('created_by', '!=', $user->id)
        ->exists();

    if ($alreadyBooked) {
        return response()->json(['message' => 'This slot is already booked.','status' => false,], 400);
    }

    // Step 3: Check for at least 3 players
    $players = array_filter($request->only(['player1_id', 'player2_id', 'player3_id', 'player4_id']));
    if (count($players) < 3) {
        return response()->json(['message' => 'At least 3 players are required.','status' => false,], 400);
    }

    // Step 4: Check if any player has already booked in same booking
    $teeBookingDetailId = $request->input('tee_booking_detail_id');

    foreach (array_values($players) as $index => $playerId) {
    $alreadyExists = TeeBookingDetails::where('tee_booking_id', $request->tee_booking_id)
        ->where('is_cancelled', 0)
        ->where('id', '!=', $teeBookingDetailId)
        ->where(function ($q) use ($playerId) {
            $q->where('player1_id', $playerId)
              ->orWhere('player2_id', $playerId)
              ->orWhere('player3_id', $playerId)
              ->orWhere('player4_id', $playerId);
        })
        ->exists();

    if ($alreadyExists) {
        return response()->json([
            'message' => "Player " . ($index + 1) . " has already booked a slot.",'status' => false,
        ], 400);
    }
}

    // Step 5: Delete any empty/locked records by this user
    TeeBookingDetails::whereNull('player1_id')
        ->where('locked_by', $user->id)
        ->delete();

    // Step 6: Save or update booking
    $booking_type = $teeBookingDetailId ? 'modified' : 'confirmed';
    $subject = $booking_type === 'confirmed' ? 'Tee Booking Confirmation' : 'Tee Booking Modification';
$dataToSave = [
    'player1_id' => $request->player1_id,
    'player2_id' => $request->player2_id,
    'player3_id' => $request->player3_id,
    'player4_id' => $request->player4_id,
    'tee_booking_id' => $request->tee_booking_id,
    'tee_sheet_id' => $request->tee_sheet_id,
    'locked_by' => null,
];

if (!$teeBookingDetailId) {
    $dataToSave['created_by'] = $user->id;
}

$teeBookingDetail = TeeBookingDetails::updateOrCreate(
    ['id' => $teeBookingDetailId],
    $dataToSave
);

    // Step 7: Generate bookingId and save
    $bookingId = 'TB' . str_pad($teeBookingDetail->id, 8, '0', STR_PAD_LEFT);
    $teeBookingDetail->bookingId = $bookingId;
    $teeBookingDetail->save();

    // Step 8: Get full booking details
    $data = TeeBookingDetails::get_booking_details($teeBookingDetail->id);
    $data->booking_type = $booking_type;

    // Optional: send confirmation email
$playerstoBeSend = array_filter(
    $request->only(['player2_id', 'player3_id', 'player4_id'])
);

$deviceTokens = Member::whereIn('id', $playerstoBeSend)
    ->where('has_notification_permission', 1)
    ->whereNotNull('device_id')
    ->pluck('device_id')
    ->toArray();
    echo(json_encode($deviceTokens));
    if (!empty($deviceTokens)) {
    foreach ($deviceTokens as $token) {
    FCMService::sendFCMMessagev1(
        $token,
        'Tee Slot Reserved',
        'You have been added to a tee booking.',
        [
            'data' =>  '1',
            'tee_booking_detail_id' =>(string) $teeBookingDetail->id,
            'screen' => 'TeeBookingDetail'
        ]
    );
}
}

    return response()->json([
        'message' => 'Tee booking saved successfully.',
        'data' => $data,
        'status' => true
    ], 200);
}

public function getMyBookings(Request $request)
{
    $user = auth()->user();

    $limit = $request->query('limit', 10);

    $bookings = TeeBookingDetails::get_player_booking($user->id)
        ->paginate($limit);

    return response()->json([
        'status' => true,
        'message' => 'Bookings retrieved successfully.',
        'data' => $bookings
    ]);
}

public function getBookingsDetail(Request $request,$id)
{
    $user = auth()->user();

    $bookings = TeeBookingDetails::get_booking_details($id);



if ($bookings) {
    $bookings->is_cancelled = (int) $bookings->is_cancelled;
    $bookings->created_by=(int) $bookings->created_by ;
    $players = collect([
        ['slot' => 1, 'id' => $bookings->player1_id ?? null, 'member_id' => $bookings->player1_member_id ?? null, 'name' => $bookings->player1_name ?? null],
        ['slot' => 2, 'id' => $bookings->player2_id ?? null, 'member_id' => $bookings->player2_member_id ?? null, 'name' => $bookings->player2_name ?? null],
        ['slot' => 3, 'id' => $bookings->player3_id ?? null, 'member_id' => $bookings->player3_member_id ?? null, 'name' => $bookings->player3_name ?? null],
        ['slot' => 4, 'id' => $bookings->player4_id ?? null, 'member_id' => $bookings->player4_member_id ?? null, 'name' => $bookings->player4_name ?? null],
    ])->filter(fn ($player) => !empty($player['name']))->values();
    $creator = Member::find($bookings->created_by);
    $bookings->summary = [
        'booking_number' => $bookings->tee_booking_detail_id,
        'status' => (int) $bookings->is_cancelled === 1 ? 'Cancelled' : 'Active',
        'booking_date' => Carbon::parse($bookings->booking_date)->format('d M Y'),
        'tee_time' => $bookings->tee_time,
        'hole_number' => $bookings->hole_number,
        'player_count' => $players->count(),
        'available_spots' => (int) $bookings->available_players,
        'booked_on' => optional($bookings->created_at)->format('d M Y, h:i A'),
    ];
    $bookings->players = $players;
    $bookings->payment = [
        'status' => 'Reserved',
        'amount' => null,
        'gateway_name' => null,
        'reference_number' => null,
    ];
    $bookings->timeline = [
        [
            'title' => 'Tee slot reserved',
            'description' => 'Players were assigned to the tee sheet.',
            'time' => optional($bookings->created_at)->format('d M Y, h:i A'),
            'tone' => 'neutral',
        ],
        [
            'title' => (int) $bookings->is_cancelled === 1 ? 'Booking cancelled' : 'Ready for play',
            'description' => (int) $bookings->is_cancelled === 1
                ? 'The tee booking was cancelled and released.'
                : 'Booking remains active for the scheduled tee time.',
            'time' => (int) $bookings->is_cancelled === 1
                ? optional($bookings->cancelled_at)->format('d M Y, h:i A')
                : Carbon::parse($bookings->booking_date . ' ' . $bookings->tee_time)->format('d M Y, h:i A'),
            'tone' => (int) $bookings->is_cancelled === 1 ? 'danger' : 'success',
        ],
    ];
    $bookings->support = [
        'name' => 'Golf Operations',
        'phone' => null,
        'email' => null,
        'note' => 'Contact golf operations for buddy changes, cancellations, or waitlist help.',
    ];
    $bookings->rules = [
        'Arrive before the scheduled tee time to avoid release of the slot.',
        'Player changes are subject to slot availability and club rules.',
        'Cancelled tee reservations reopen the available player count automatically.',
    ];
    $bookings->member = [
        'member_id' => $creator->MemberID ?? null,
        'sc_id' => $creator->SC_ID ?? null,
        'name' => $creator->DisplayName ?? ($players->first()['name'] ?? null),
    ];
}

    return response()->json([
        'status' => true,
        'message' => 'Bookings retrieved successfully.',
        'data' => $bookings,
    ]);
}

public function cancelBooking(Request $request,$id){
 $user = auth()->user();
     if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'User Not found.',
        ], 404);
    }

    $teeBooking = TeeBookingDetails::find($id);

    if (!$teeBooking) {
        return response()->json([
            'status' => false,
            'message' => 'Booking not found.',
        ], 404);
    }

    if ($teeBooking->is_cancelled) {
        return response()->json([
            'status' => false,
            'message' => 'Booking is already cancelled.',
        ], 400);
    }

    $teeBooking->cancelled_by = $user->id; // Or use token-based user
    $teeBooking->is_cancelled = 1;
    $teeBooking->cancelled_at = Carbon::now();
    $teeBooking->save();

    $data = TeeBookingDetails::get_booking_details($id); // Assuming this returns an object or array

   

    return response()->json([
        'status' => true,
        'message' => 'Booking cancelled successfully.',
        'booking_id' => $id,
    ], 200);

}

}

?>
