<?php
namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\GameType;
use Log;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\FacilityBanner;

class GameTypeController extends Controller
{
     public function getGameTypes($id)
    {
          $user = auth()->user();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'Member not authenticated'
        ], 401);
    }
        $gameTypes = DB::table("facility_game_types")
        ->where('facility_game_types.facility_id','=',$id)
        ->join('game_types', 'game_types.id', 'facility_game_types.game_type_id')
        ->select('game_types.*','game_types.id as game_type')
        ->get();
        $cancellations = DB::table('activity_cancellation_policies')->where('facility_id','=',$id)->get();

       

        return response()->json([
            'status' => true,
            'data' => [
                'gameTypes' => $gameTypes,
                'cancellation_policy' => $cancellations
            ]
        ]);
    }
       public function createActivityGuest(Request $request)
    {
        $user = auth()->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Member not authenticated'
        ], 401);
    }
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'unique:guest_infos,email'],
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'digits:10', 'unique:guest_infos,mobile'], // 10 digit mobile
            'occupant_id' => ['required', ], // 10 digit mobile
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }
        $id = DB::table('guest_infos')->insertGetId([
    'member_id' => $user->id,
    'email' => $request->email,
    'name' => $request->name,
    'mobile' => $request->mobile,
    'occupant_id' => $request->occupant_id,
    'created_at' => now(),
    'updated_at' => now()
]);

$guest = DB::table('guest_infos')->where('id', $id)->first();

return response()->json([
    'success' => true,
    'data'=> $guest,
    'message' => 'Guest info added successfully.',
]);
    }
    public function updateActivityGuest(Request $request, $id)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Member not authenticated'
        ], 401);
    }

    // Validate input
    $validator = Validator::make($request->all(), [
        'email' => ['required', 'email', Rule::unique('guest_infos')->ignore($id)],
        'name' => ['required', 'string', 'max:255'],
        'mobile' => ['required', 'digits:10', Rule::unique('guest_infos')->ignore($id)],
        'occupant_id' => ['required'],
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()
        ], 422);
    }

    // Check if guest exists and belongs to the user
    $guest = DB::table('guest_infos')
        ->where('id', $id)
        ->where('member_id', $user->id)
        ->first();

    if (!$guest) {
        return response()->json([
            'success' => false,
            'message' => 'Guest not found or unauthorized.'
        ], 404);
    }

    // Update guest info
    DB::table('guest_infos')
        ->where('id', $id)
        ->update([
            'email' => $request->email,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'occupant_id' => $request->occupant_id,
            'updated_at' => now(),
        ]);

    return response()->json([
        'success' => true,
        'message' => 'Guest updated successfully.'
    ]);
}

    
    public function deleteAccount($id = null)
{
    if (empty($id)) {
        return response()->json([
            'status' => false,
            'message' => 'ID is missing',
        ], 400);
    }

    // Here you would typically delete the account based on ID
    // Example: User::destroy($id);

    return response()->json([
        'status' => true,
        'message' => 'Account deleted successfully',
    ], 200);
}
 public function getBanners()
{
     $user = auth()->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Member not authenticated'
        ], 401);
    }
    $banners = FacilityBanner::with('facility:id,name')->where('status', 1)->get();

    return response()->json([
        'status' => true,
        'data' => $banners
    ]);
}

}