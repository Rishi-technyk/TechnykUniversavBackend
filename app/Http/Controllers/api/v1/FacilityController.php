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

    $query = DB::table('guest_infos')
        ->leftJoin('occupant_masters', 'guest_infos.occupant_id', '=', 'occupant_masters.id')
        ->where('guest_infos.member_id', $user->id)
        ->select(
            'guest_infos.*',
            'guest_infos.name as DisplayName',
            'guest_infos.player_memberId as MemberID',
            'occupant_masters.charge'
        );

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

    $players = $query
        ->orderBy('guest_infos.is_favorite', 'desc')
        ->orderBy('guest_infos.updated_at', 'desc')
        ->get();

    $favorites = $players
        ->where('is_favorite', 1)
        ->take(6)
        ->values()
        ->map(fn ($player) => $this->formatSuggestedPlayer($player, 'favorite'));

    $recentPlayers = DB::table('game_booking_guests as guests')
        ->join('game_bookings as bookings', 'guests.game_booking_id', '=', 'bookings.id')
        ->leftJoin('guest_infos as saved_guest', function ($join) use ($user) {
            $join->where('saved_guest.member_id', '=', $user->id)
                ->where(function ($query) {
                    $query->on('saved_guest.mobile', '=', 'guests.player_mobile')
                        ->orOn('saved_guest.email', '=', 'guests.player_email')
                        ->orOn('saved_guest.name', '=', 'guests.player_name');
                });
        })
        ->where('bookings.memberID', $user->MemberID)
        ->selectRaw('
            MAX(guests.id) as id,
            guests.player_name as DisplayName,
            MAX(saved_guest.player_memberId) as MemberID,
            guests.player_mobile as mobile,
            guests.player_email as email,
            guests.occupant_id as occupant_id,
            guests.occupant_charge as charge,
            COALESCE(MAX(saved_guest.is_favorite), 0) as is_favorite,
            MAX(guests.created_at) as last_played_at,
            COUNT(*) as played_count
        ')
        ->groupBy(
            'guests.player_name',
            'guests.player_mobile',
            'guests.player_email',
            'guests.occupant_id',
            'guests.occupant_charge'
        )
        ->orderByRaw('MAX(guests.created_at) desc')
        ->limit(8)
        ->get()
        ->map(fn ($player) => $this->formatSuggestedPlayer($player, 'recent'));

    $guestPlayers = $players
        ->where('occupant_id', 2)
        ->take(8)
        ->values()
        ->map(fn ($player) => $this->formatSuggestedPlayer($player, 'guest'));

    return response()->json([
        'status' => true,
        'data' => $players,
        'favorites' => $favorites,
        'recent_players' => $recentPlayers,
        'guest_players' => $guestPlayers,
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

private function formatSuggestedPlayer($player, string $source): array
{
    return [
        'id' => $player->id ?? null,
        'DisplayName' => $player->DisplayName ?? $player->name ?? 'Player',
        'MemberID' => $player->MemberID ?? $player->player_memberId ?? null,
        'mobile' => $player->mobile ?? null,
        'email' => $player->email ?? null,
        'occupant_id' => (int) ($player->occupant_id ?? 2),
        'charge' => (float) ($player->charge ?? 0),
        'is_favorite' => (int) ($player->is_favorite ?? 0),
        'played_count' => (int) ($player->played_count ?? 0),
        'last_played_at' => $player->last_played_at ?? null,
        'source' => $source,
    ];
}
 

}
