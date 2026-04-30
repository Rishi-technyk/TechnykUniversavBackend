<?php

namespace App\Http\Controllers\api\v1;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Models\Facility;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\FacilityBanner;

class FacilityController extends Controller
{
    public function getFacilities()
    {
       $settings = DB::table('admin_settings')
    ->select('max_days', 'min_days')
    ->first();

$maxDays = $settings->max_days ?? 0;
$minDays = $settings->min_days ?? 0;

$facilities = DB::table('facilities')
    ->select(
        'facilities.*',
        DB::raw('EXISTS (
            SELECT 1 FROM facility_slots 
            WHERE facility_slots.facility_id = facilities.id
        ) as is_available_slots'),
        DB::raw("$maxDays as max_days"),
        DB::raw("$minDays as min_days")
    )
    ->get();

        // Cast is_available_slots to boolean
        $facilities = $facilities->map(function ($facility) {
            $facility->is_available_slots = (bool) $facility->is_available_slots;
            return $facility;
        });
          $user = auth()->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Member not authenticated'
        ], 401);
    }
    $banners = FacilityBanner::with('facility:id,name')->where('status', 1)->get();
        Log::info("Facility List with Availability", ['facilities' => $facilities]);

        return response()->json([
            'status' => true,
            'data' => $facilities,
            'banners'=>$banners
        ], 200);
    }
//  public function getFavoritePlayers(Request $request)
// {
//     $user = auth()->user();

//     if (!$user) {
//         return response()->json([
//             'status' => false,
//             'message' => 'Member not authenticated'
//         ], 401);
//     }

//     // Optional: Debug log
//     Log::info('Authenticated Member ID: ' . $user->id);

//     $players = DB::table('guest_infos')
//         ->where('member_id', $user->id)
//         ->orderBy('is_favorite', 'desc')
//         ->get();

//     return response()->json([
//         'status' => true,
//         'data' => $players
//     ]);
// }
public function getFavoritePlayers(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'Member not authenticated'
        ], 401);
    }

    // Log the input
    Log::info('Fetching favorites for member ID: ' . $user->id, $request->all());

    $query = DB::table('guest_infos')->where('member_id', $user->id);

    // Apply filters if present
    if ($request->has('is_favorite')&& $request->is_favorite == true) {
        $query->where('is_favorite', '1');
    }

    if ($request->has('member') && $request->member == 1) {
        $query->where('occupant_id', 1);
    }

    if ($request->has('nonmember') && $request->nonmember == 1) {
        $query->where('occupant_id', 2);
    }

    $players = $query->orderBy('is_favorite', 'desc')->get();

    return response()->json([
        'status' => true,
        'data' => $players
    ]);
}

public function editFavoritePlayers(Request $request)
{
    $memberId = $request->input('id');
    $type = $request->input('type');

    // Validate the 'type' input
    if (!in_array($type, ['Delete', 'Like'])) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid action type.'
        ], 400);
    }

    // Retrieve the guest info record
    $guestInfo = DB::table('guest_infos')->where('id', $memberId)->first();

    if (!$guestInfo) {
        return response()->json([
            'status' => false,
            'message' => 'Guest info not found.'
        ], 404);
    }

    // Handle 'Delete' action
    if ($type === 'Delete') {
        $deleted = DB::table('guest_infos')->where('id', $memberId)->delete();
        return response()->json([
            'status' => $deleted > 0
        ], $deleted > 0 ? 200 : 500);
    }

    // Handle 'Like' action
    

    if ($guestInfo->is_favorite) {
        // If already liked, unlike it
        $updated = DB::table('guest_infos')->where('id', $memberId)->update(['is_favorite' => '0']);
        return response()->json([
            'status' => $updated > 0,
            'message' => 'Unliked successfully.'
        ], $updated > 0 ? 200 : 500);
    } else {
        // If not liked, like it
       \Log::info('Trying to update guest info', ['id' => $memberId, 'type' => $type]);

// Check current value
$current = DB::table('guest_infos')->where('id', $memberId)->first();
\Log::info('Current guest info:', (array) $current);

$updated = DB::table('guest_infos')
    ->where('id', $memberId)
    ->update(['is_favorite' => '1']);

\Log::info('Update result', ['updated' => $updated]);
        return response()->json([
            'status' => $updated > 0,
            'message' => 'Liked successfully.'
        ], $updated > 0 ? 200 : 500);
    }
}
 

}