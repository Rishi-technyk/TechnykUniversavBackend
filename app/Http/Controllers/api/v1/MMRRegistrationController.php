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

    $memberRegistrationCount = $user
        ? MMRRegistration::where('member_id', $user->id)->count()
        : 0;

    $latestRegistration = $user
        ? MMRRegistration::where('member_id', $user->id)
            ->latest('created_at')
            ->first()
        : null;

    $completionRatio = $totalDays > 0 && $currentDayNumber
        ? min(100, (int) round(($currentDayNumber / $totalDays) * 100))
        : 0;

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
            'window_status'      => $alreadyRegistered
                ? 'registered'
                : ($isOpen ? 'open' : 'closed'),
            'next_action_label'  => $alreadyRegistered
                ? 'Registered'
                : ($isOpen ? 'Register now' : 'Registration closed'),
            'progress_percent'   => $completionRatio,
            'summary'            => [
                'total_registrations' => $memberRegistrationCount,
                'latest_registration_at' => optional($latestRegistration?->created_at)->toDateTimeString(),
                'latest_registration_window' => $latestRegistration
                    ? Carbon::parse($latestRegistration->start_date)->format('d M') . ' - ' . Carbon::parse($latestRegistration->end_date)->format('d M Y')
                    : null,
            ],
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

    $registrations->getCollection()->transform(function ($registration) {
        $registration->window_label = Carbon::parse($registration->start_date)->format('d M')
            . ' - '
            . Carbon::parse($registration->end_date)->format('d M Y');
        $registration->registered_at = optional($registration->created_at)->format('d M Y, h:i A');
        $registration->status_label = Carbon::parse($registration->end_date)->endOfDay()->isPast()
            ? 'Completed window'
            : 'Active window';
        $registration->duration_days = Carbon::parse($registration->start_date)
            ->diffInDays(Carbon::parse($registration->end_date)) + 1;

        return $registration;
    });

    return response()->json([
        'status' => true,
        'data' => $registrations,
        'meta' => [
            'total_registrations' => $registrations->total(),
            'current_page' => $registrations->currentPage(),
            'last_page' => $registrations->lastPage(),
        ],
    ]);
}

}
