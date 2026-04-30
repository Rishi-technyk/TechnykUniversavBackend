<?php
namespace App\Http\Controllers\api\v1;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\MMRRegistration;
use App\Models\MMRRegistrationSetting;
use Carbon\Carbon;

class MMRRegistrationController extends Controller
{
public function getMMRSettings()
{
    $user = auth()->user();

   $setting = MMRRegistrationSetting::whereDate('start_date', '<=', now())
    ->whereDate('end_date', '>=', now())
    ->first();

    if (!$setting) {
        return response()->json([
            'status' => false,
            'message' => 'Registration settings not configured'
        ]);
    }

    $now = now();

    /* ------------------------------
       1️⃣ CHECK WINDOW STATUS
    ------------------------------*/
    $isOpen = $now->between(
        Carbon::parse($setting->start_date)->startOfDay(),
        Carbon::parse($setting->end_date)->endOfDay()
    );

    /* ------------------------------
       2️⃣ CHECK WINDOW REGISTRATION
    ------------------------------*/
    $alreadyRegistered = false;

    if ($user) {
        $alreadyRegistered = MMRRegistration::where('member_id', $user->id)
            ->whereDate('start_date', Carbon::parse($setting->start_date)->toDateString())
            ->whereDate('end_date', Carbon::parse($setting->end_date)->toDateString())
            ->exists();
    }

    /* ------------------------------
       3️⃣ CALCULATE DAYS
    ------------------------------*/
    $start = Carbon::parse($setting->start_date);
    $end   = Carbon::parse($setting->end_date);

    $totalDays = $start->diffInDays($end) + 1;

    $daysRemaining = $now->lte($end)
        ? $now->diffInDays($end)
        : 0;

    $currentDayNumber = $isOpen
        ? $start->diffInDays($now) + 1
        : null;

    /* ------------------------------
       4️⃣ RESPONSE
    ------------------------------*/
    return response()->json([
        'status' => true,
        'data' => [
            'registration_start' => $setting->start_date,
            'registration_end'   => $setting->end_date,
            'server_time'        => $now,
            'mmr_id'             => $setting->id,
            'is_open'            => $isOpen,
            'already_registered' => $alreadyRegistered,
            'total_days'         => $totalDays,
            'days_remaining'     => $daysRemaining,
            'current_day'        => $currentDayNumber,
            'today'              => Carbon::today()->toDateString(),
            'reg_message'        => $alreadyRegistered
                ? 'You have already registered for this registration window.'
                : null,
        ]
    ]);
}

public function store(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'User not authenticated'
        ], 401);
    }

    /* ----------------------------------
       1️⃣ CHECK REGISTRATION WINDOW
    ----------------------------------*/
   $setting = MMRRegistrationSetting::whereDate('start_date', '<=', now())
    ->whereDate('end_date', '>=', now())
    ->first();

    if (!$setting) {
        return response()->json([
            'status' => false,
            'message' => 'Registration window not configured'
        ]);
    }

    $now = now();

    if (
        $now->lt(Carbon::parse($setting->start_date)->startOfDay()) ||
        $now->gt(Carbon::parse($setting->end_date)->endOfDay())
    ) {
        return response()->json([
            'status' => false,
            'message' => 'Registration is closed'
        ]);
    }

    /* ----------------------------------
       2️⃣ CHECK WINDOW DUPLICATE
    ----------------------------------*/
    $alreadyRegistered = MMRRegistration::where('member_id', $user->id)
        ->whereDate('start_date', Carbon::parse($setting->start_date)->toDateString())
        ->whereDate('end_date', Carbon::parse($setting->end_date)->toDateString())
        ->exists();

    if ($alreadyRegistered) {
        return response()->json([
            'status' => false,
            'message' => 'You have already registered for this registration window'
        ]);
    }

    /* ----------------------------------
       3️⃣ STORE REGISTRATION
    ----------------------------------*/
    MMRRegistration::create([
        'member_id'    => $user->id,
        'member_SC_ID' => $user->SC_ID,
        'start_date'   => $setting->start_date,
        'end_date'     => $setting->end_date,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Registration successful'
    ]);
}

    
    public function history(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'User not authenticated'
        ], 401);
    }

    $perPage = $request->get('per_page', 10);

    $registrations = MMRRegistration::where('member_id', $user->id)
        ->orderBy('start_date', 'desc')
        ->paginate($perPage);

    return response()->json([
        'status' => true,
        'data' => $registrations
    ]);
}

}
