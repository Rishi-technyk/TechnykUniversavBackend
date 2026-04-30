<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\Participant;
use Illuminate\Http\Request;
use App\Models\TicketBooking;
use Carbon\Carbon;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Validator;
use App\Models\Member;
use App\Models\Seat;


class EventAdminController extends Controller

{
    public function settings()
{
    $role = auth()->user()->role; // Admin, EventEntry, EventFood, EventDrinks
    \Log::info("User Role: " . $role);

    // 🔥 Common menu template
    $menuTemplate = [
        [
            'title' => 'Scan QR',
            'icon' => 'qr.png',
            'navigation' => 'Camera',
            'visible' => true,
            'type' => 'QR'
        ],
        [
            'title' => 'Verify Member',
            'icon' => 'qr.png',
            'navigation' => 'Varify',
            'visible' => true,
            'type' => 'Member'
        ]
    ];

    // 🔥 Base full menu (for Admin)
    $allMenus = [
        [
            'id' => 1,
            'name' => 'Entry',
            'data' => $menuTemplate
        ],
        [
            'id' => 2,
            'name' => 'Food',
            'data' => $menuTemplate
        ],
        [
            'id' => 3,
            'name' => 'Alcohol',
            'data' => $menuTemplate
        ],
        [
            'id' => 4,
            'name' => 'Non_Alcohol',
            'data' => $menuTemplate
        ],
    ];

    // -------------------------------------------------------
    // 🎯 ROLE-BASED FILTER
    // -------------------------------------------------------
    $available = [];

    switch ($role) {

        case 'Admin':
            // Admin sees everything
            $available = $allMenus;
            break;
         case 'Event Admin':
            $available = $allMenus;
            break;
            
        case 'EventEntry':
            $available = array_filter($allMenus, fn($x) => $x['name'] == 'Entry');
            break;

        case 'EventFood':
            $available = array_filter($allMenus, fn($x) => $x['name'] == 'Food');
            break;

        case 'EventDrinks':
            // Drinks role can see both Alcohol + Non-Alcohol
            $available = array_filter($allMenus, fn($x) =>
                in_array($x['name'], ['Alcohol', 'Non_Alcohol'])
            );
            break;

        default:
            // No permissions → empty result
            $available = [];
    }

    // Re-index array to avoid key mismatch for frontend
    $available = array_values($available);

    return response()->json([
        'status' => true,
        'locations' => $available,
    ]);
}

public function paymentTypes()
{
    return response()->json([
        'status' => true,
        'payment_types' => [

            [
                'id' => 1,
                'name' => 'UPI',
                'type_code' => 'upi',
                'icon' => 'qrcode-scan', // MaterialCommunityIcons
                'icon_type' => 'MaterialCommunityIcons',
                'description' => 'Pay instantly using UPI (GPay, PhonePe, Paytm).',
                'ref_no'=>true
            ],

            [
                'id' => 2,
                'name' => 'Cash',
                'type_code' => 'cash',
                'icon' => 'cash-multiple', // MaterialCommunityIcons
                'icon_type' => 'MaterialCommunityIcons',
                'description' => 'Pay using physical cash.',
                 'ref_no'=>false
            ],

            [
                'id' => 3,
                'name' => 'Credit Card',
                'type_code' => 'credit_card',
                'icon' => 'credit-card', // MaterialIcons
                'icon_type' => 'MaterialIcons',
                'description' => 'Pay using your credit card.',
                 'ref_no'=>true
            ],

            [
                'id' => 4,
                'name' => 'Debit Card',
                'type_code' => 'debit_card',
                'icon' => 'credit-card-outline', // MaterialCommunityIcons
                'icon_type' => 'MaterialCommunityIcons',
                'description' => 'Pay using debit card.',
                 'ref_no'=>true
            ],

            [
                'id' => 5,
                'name' => 'Net Banking',
                'type_code' => 'netbanking',
                'icon' => 'bank-transfer', // MaterialCommunityIcons
                'icon_type' => 'MaterialCommunityIcons',
                'description' => 'Pay using your bank net banking.',
                 'ref_no'=>true
            ],

        ],
    ]);
}
private function isComplimentary(string $ticketType,int $age, $DOB): bool
{
    
$member_complimentary_age =$age;
    // Member age ≥ 60
    if ($ticketType === 'member' && $DOB && $age) {
        return Carbon::parse($DOB)->age >= $member_complimentary_age;
        // return true;
    }
  if ($ticketType === 'spouse' && $DOB  && $age) {
        return Carbon::parse($DOB)->age >= $member_complimentary_age;
        //   return true;
    }
    return false; // guests, dependents → never free
}


public function adminBookTickets(Request $request)
{
     $validator = Validator::make($request->all(), [
        'event_id'   => 'required|integer',
        'member'     => 'required',  
        'tickets'    => 'nullable|array',
        'waiters'    => 'nullable|integer',
        'payment_type' => 'required|string',
        'reference_no'=>'nullable|string',
        'seatGroups' => 'nullable|array',
        'seatGroups.*.seats' => 'nullable|array'
    ], [
        'event_id.required' => 'Event ID is missing.',
        'member.required' => 'Please select a member.',
        'payment_type.required' => 'Please select a payment type.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),  // 👈 only first error
            'errors' => $validator->errors(),            // 👈 all errors (optional)
        ], 422);
    }

    $event = Event::with(['ticketTypes', 'waiter'])->findOrFail($request->event_id);

    $memberId = $request->member['id'];

    $alreadyBooked = Participant::whereHas('booking', function ($q) use ($event, $memberId) {
            $q->where('event_id', $event->id)
              ->where('member_id', $memberId);
        })
        ->count();

    /** Requested ticket count */
    $requestedCount = collect($request->tickets)->sum('quantity');

    if ($alreadyBooked + $requestedCount > $event->max_per_member_tickets) {
        return response()->json([
            'status' => false,
            'message' => "This member already booked {$alreadyBooked} tickets. Maximum allowed is {$event->max_per_member_tickets}.",
            'allowed' => max(0, $event->max_per_member_tickets - $alreadyBooked),
        ], 422);
    }

$seatIds = collect($request->seatGroups ?? [])
    ->pluck('seats')
    ->flatten(1)
    ->pluck('id')
    ->toArray();
    
   
    /** ------------------------------------------------------
     * 🔹 2. Waiter Limit Validation
     * ------------------------------------------------------ */
    $requestedWaiters = (int) $request->waiters;

    if ($requestedWaiters > 0 && !$event->waiter) {
        return response()->json([
            'status' => false,
            'message' => 'Waiter service not available.',
        ], 422);
    }

    $alreadyBookedWaiters = DB::table('event_waiter_bookings')
        ->where('event_id', $event->id)
        ->where('member_id', $memberId)
        ->sum('quantity');

    if (
        $requestedWaiters > 0 &&
        ($alreadyBookedWaiters + $requestedWaiters) > $event->waiter->max_waiters_per_member
    ) {
        return response()->json([
            'status' => false,
            'message' => "Max allowed waiters: {$event->waiter->max_waiters_per_member}",
        ], 422);
    }
/** ------------------------------------------------------
 * 🔹 LIMIT: Member & Spouse can only have 1 ticket each
 * ------------------------------------------------------ */
$memberTicketCount = 0;
$spouseTicketCount = 0;

foreach ($request->tickets as $t) {
    $ticketType = $event->ticketTypes->firstWhere('id', $t['id']);
    if (!$ticketType) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid ticket type selected.'
        ], 400);
    }

    if (strtolower($ticketType->type) === 'member') {
        $memberTicketCount += $t['quantity'];
    }

    if (strtolower($ticketType->type) === 'spouse') {
        $spouseTicketCount += $t['quantity'];
    }
}

// Member ticket limit
if ($memberTicketCount > 1) {
    return response()->json([
        'status' => false,
        'message' => "Member can only have 1 MEMBER ticket."
    ], 422);
}

// Spouse ticket limit
if ($spouseTicketCount > 1) {
    return response()->json([
        'status' => false,
        'message' => "Member can only have 1 SPOUSE ticket."
    ], 422);
}

/** ------------------------------------------------------
 * 🔹 ALSO CHECK ALREADY BOOKED MEMBER + SPOUSE TICKETS
 * ------------------------------------------------------ */

$previousMemberTicket = Participant::whereHas('booking', function ($q) use ($event, $memberId) {
        $q->where('event_id', $event->id)
          ->where('member_id', $memberId);
    })
    ->where('ticket_type', 'member')
    ->count();

if ($previousMemberTicket >= 1 && $memberTicketCount > 0) {
    return response()->json([
        'status' => false,
        'message' => "This member already has 1 MEMBER ticket."
    ], 422);
}

$previousSpouseTicket = Participant::whereHas('booking', function ($q) use ($event, $memberId) {
        $q->where('event_id', $event->id)
          ->where('member_id', $memberId);
    })
    ->where('ticket_type', 'spouse')
    ->count();

if ($previousSpouseTicket >= 1 && $spouseTicketCount > 0) {
    return response()->json([
        'status' => false,
        'message' => "This member already has 1 SPOUSE ticket."
    ], 422);
}

    /** ------------------------------------------------------
     * 🔹 3. Recalculate pricing (same logic as member)
     * ------------------------------------------------------ */
    $ticketSubtotal = 0;

    if (!empty($request->tickets)) {
        foreach ($request->tickets as $t) {
            $ticketType = $event->ticketTypes->firstWhere('id', $t['id']);
            if (!$ticketType) {
                return response()->json(['status' => false, 'message' => 'Invalid ticket type'], 400);
            }

            $price = $this->isComplimentary($ticketType->type,$event->complimentary_age, Member::find($memberId)->DOB) ? 0 : $ticketType->amount;
            $ticketSubtotal += $price * $t['quantity'];
        }
    }

    /** Waiter subtotal */
    $waiterSubtotal = 0;
    if ($event->waiter && $requestedWaiters > 0) {
        $waiterSubtotal = $event->waiter->waiter_cost * $requestedWaiters;
    }

    $taxable = $ticketSubtotal + $waiterSubtotal;

    $serviceCharge = ($taxable * ($event->service_charge ?? 0)) / 100;
    $gst = ($taxable * ($event->gst ?? 0)) / 100;
    $total = round($taxable + $serviceCharge + $gst, 2);
    
    $adminId=auth()->user()->id;
    
    DB::beginTransaction();
    try {
        $booking = TicketBooking::create([
            'event_id' => $event->id,
            'member_id' => $memberId,
            'booking_no' => 'EVT-' . strtoupper(uniqid()),
            'total_amount' => $total,
            'payment_type'   => $request->payment_type,
            "payment_status"=>'admin',
            'razorpay_payment_id'=>$request->reference_no,
            'admin_id'=>$adminId,
            'Mobile'=> $request->member['Mobile'] ?? 0,
            'Name'  => $request->member['DisplayName'] ?? 'N/A',
        ]);
 if (!empty($seatIds)) {

    $seats = Seat::with('category')
        ->whereIn('id', $seatIds)
        ->get();

    foreach ($seats as $seat) {

        $price = $seat->category->price ?? 0;


        DB::table('event_booking_seats')->insert([
            'booking_id' => $booking->id,
            'seat_id' => $seat->id,
            'amount' => $price, // ✅ store seat price
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Seat::where('id', $seat->id)
            ->update(['status' => 'booked']);
    }
}
        /** ------------------------------------------------------
         * 🔹 5. Add Participants
         * ------------------------------------------------------ */
        foreach ($request->tickets as $ticket) {
          
            $ticketType = $event->ticketTypes->firstWhere('id', $ticket['id']);
      $price = $this->isComplimentary($ticketType->type,$event->complimentary_age,Member::find($memberId)->DOB) ? 0 : $ticketType->amount;
            for ($i = 0; $i < $ticket['quantity']; $i++) {
$member = Member::find($memberId);


                $participant = Participant::create([
                    'booking_id' => $booking->id,
                    'ticket_id'=> $ticket['id'],
                    'ticket_type'=> $ticketType->type,
                    'name'  => $request->member['DisplayName'] ?? 'N/A',
'mobile'=> $request->member['Mobile'] ?? 0,
'email' => $member->Email ?? 'N/A',
                    'is_complimentary' => $this->isComplimentary($ticketType->type,$event->complimentary_age,Member::find($memberId)->DOB) ? 1 : 0,
                    'amount'=>$price,
                ]);

                $participant->update([
                    'qr_code' => Crypt::encryptString(json_encode([
                        'booking_id' => $booking->id,
                        'participant_id' => $participant->id,
                        'event_id' => $event->id,
                        'ticket_type' => $ticketType->type,
                        'issued_at' => now()->timestamp
                    ])),
                ]);
            }
        }

        /** ------------------------------------------------------
         * 🔹 6. Add waiter booking (if any)
         * ------------------------------------------------------ */
        if ($requestedWaiters > 0) {
            DB::table('event_waiter_bookings')->insert([
                'event_id' => $event->id,
                'booking_id' => $booking->id,
                'member_id' => $memberId,
                'quantity' => $requestedWaiters,
                'price' => $event->waiter->waiter_cost,
                'created_at' => now(),
            ]);
        }

        /** 🔹 Send Notification */
        $this->sendFCMMessage([
            'title' => $event->name,
            'short_descriptions' => "Your event passes were booked by Admin via {$request->payment_type}.",
        ], Member::find($memberId)->device_id);

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Booking created successfully',
            'booking_id' => $booking->id,
            'booking_no' => $booking->booking_no,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => false,
            'message' => 'Admin booking failed',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    private function getAccessToken() {
       $remoteUrl = 'https://holidayclub.in/holidayclub/HolidayClubServiceAccountKey.json';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $remoteUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$serviceAccountData = json_decode($response, true);
    
        $jwtHeader = base64_encode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT'
        ]));
    
        $now = time();
        $jwtPayload = base64_encode(json_encode([
            'iss' => $serviceAccountData['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]));
    
        $dataToSign = $jwtHeader . '.' . $jwtPayload;
    
        $privateKey = openssl_pkey_get_private($serviceAccountData['private_key']);
        openssl_sign($dataToSign, $jwtSignature, $privateKey, 'SHA256');
        $jwtSignature = base64_encode($jwtSignature);
    
        $jwt = $dataToSign . '.' . $jwtSignature;
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        $response = json_decode($response, true);
        return $response['access_token'];
    }

   private function sendFCMMessage($notification, $fcmTokens) {
        $url = 'https://fcm.googleapis.com/v1/projects/holiday-club-d7294/messages:send';
        $serverKey = $this->getAccessToken();
        $data = [
            "message" => [
                "token" => $fcmTokens,
                "notification" => [
                    "title" => $notification['title'],
                    "body" => $notification['short_descriptions'],
                    "image" => '	https://holidayclub.in/wp-content/uploads/2025/07/Holiday-Club-logos.png'
                ],
                "data" => [
                    "type" => "Notification"
                ]
            ]
        ];

        $encodedData = json_encode($data);
    
        $headers = [
            'Authorization: Bearer ' . $serverKey,
            'Content-Type: application/json; UTF-8',
        ];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        return $response;
    }

public function eventBookings(Request $request)
{
    $type = $request->query('type', 'upcoming');
    $eventId = $request->query('id');

    if (!$eventId) {
        return response()->json([
            'status' => false,
            'message' => 'Please select an event'
        ], 422);
    }

    // preload waiter data (OPTIMIZED)
    $waiters = DB::table('event_waiter_bookings')
        ->selectRaw('booking_id,
            SUM(quantity) as waiter_count,
            SUM(quantity * price) as waiter_amount')
        ->groupBy('booking_id')
        ->get()
        ->keyBy('booking_id');

    $bookings = TicketBooking::with([
            'event',
            'participants.ticketType'
        ])
        ->where('payment_status', 'admin')
        ->where('event_id', $eventId)
        ->when($type === 'upcoming', function ($q) {
            $q->whereHas('event', fn($e) =>
                $e->whereDate('event_date', '>=', now())
            );
        })
        ->when($type === 'past', function ($q) {
            $q->whereHas('event', fn($e) =>
                $e->whereDate('event_date', '<', now())
            );
        })
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($booking) use ($waiters) {

            $waiterData = $waiters[$booking->id] ?? null;

            return [
                'booking_id'   => $booking->id,
                'booking_no'   => $booking->booking_no,

                'event_name'   => $booking->event->name,
                'event_date'   => Carbon::parse($booking->event->event_date)->format('M d, Y'),
                'event_image'  => $booking->event->image ?? 'default.jpg',

                // 🎫 tickets
                'ticket_count' => $booking->participants->count(),

                'ticket_types' => $booking->participants
                    ->pluck('ticketType.name')
                    ->filter()
                    ->unique()
                    ->values(),

                // 🍽 waiters
                'waiter_count' => (int) ($waiterData->waiter_count ?? 0),
                'waiter_amount'=> (float) ($waiterData->waiter_amount ?? 0),

                // 💰 amount
                'total_amount' => (float) $booking->total_amount,

                'status' => 'confirmed',
            ];
        });
        
    return response()->json([
        'status' => true,
        'data'   => $bookings,
    ]);
}
    
    public function validateTicket(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'role' => 'required|string|in:Admin,EventEntry,EventBuffe',
        ]);

        $role = $request->role;
        $ticketData = json_decode(Crypt::decryptString($request->qr_code), true);

        $participant = Participant::find($ticketData['participant_id']);

        if (!$participant) {
            return response()->json(['status' => false, 'message' => 'Invalid QR'], 404);
        }

        // Role-based validation
        if ($role === 'EventEntry') {
            $participant->entry_status = 1;
        } elseif ($role === 'EventBuffe') {
            $participant->food_status = 1;
        } else {
            // Full control
            $participant->entry_status = 1;
            $participant->food_status = 1;
        }

        $participant->save();

        return response()->json([
            'status' => true,
            'message' => 'Updated successfully',
            'participant' => $participant
        ]);
    }

public function summary(Request $request)
{
    $eventId = $request->query('id');
$admin_id=auth()->user()->id;
    if (!$eventId) {
        return response()->json([
            'status' => false,
            'message' => 'Please select an event'
        ], 422);
    }

    // Step 1: get booking ids
    $bookingIds = DB::table('ticket_bookings')
        ->where('event_id', $eventId)
         ->where('admin_id',$admin_id)
        ->pluck('id');

    if ($bookingIds->isEmpty()) {
        return response()->json([
            'status' => true,
            'summary' => [
                'total_tickets' => 0,
                'total_entry'   => 0,
                'total_buffet'  => 0,
                'total_seats'  => 0,
                'breakup'       => [],
            ]
        ]);
    }

    // Step 2: filtered participants
    $participants = DB::table('participants')
        ->whereIn('booking_id', $bookingIds);
        // ->where('is_complimentary', 0)
        // ->where('amount', '>', 0);
$seats=DB::table('event_booking_seats')
        ->whereIn('booking_id', $bookingIds);
        
        $totalSeats=(clone $seats)->count();
    $totalTickets = (clone $participants)->count();

    $totalEntry = (clone $participants)
        ->where('entry_status', 1)
        ->count();

    $totalBuffet = (clone $participants)
        ->where('food_status', 1)
        ->count();

    // ticket type breakup
    $breakup = (clone $participants)
        ->select('ticket_id','name', DB::raw('COUNT(*) as total'))
        ->groupBy('ticket_id')
        ->get();

    return response()->json([
        'status' => true,
        'summary' => [
            'total_tickets' => $totalTickets,
            'total_entry'   => $totalEntry,
            'total_buffet'  => $totalBuffet,
            'total_seats'  => $totalSeats,
            'breakup'       => $breakup,
        ],
    ]);
}

public function memberBookings(Request $request, $memberId)
{
    $limit = $request->get('limit', 10);

    $bookings = TicketBooking::with([
        'participants' => function ($q) {
            $q->select(
                'id',
                'booking_id',
                'ticket_id',
                'ticket_type',
                'is_complimentary',
                'entry_status',
                'food_status',
                'consumed_alcohol',
        'consumed_non_alcohol',
        'is_complimentary'
            );
        },
        'participants.ticketType:id,name'
    ])
    ->where('member_id', $memberId)
    ->select('id', 'booking_no', 'total_amount', 'payment_type', 'created_at')
    ->orderBy('id', 'DESC')
    ->paginate($limit);

    return response()->json([
        'status' => true,
        'data' => [
            'pagination' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
            'bookings' => $bookings->items()
        ]
    ]);
}

public function changeParticipantStatus($participantId, $type)
{
    if (!in_array($type, ['entry', 'food', 'alcohol', 'non_alcohol'])) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid status type'
        ], 400);
    }

    $user = auth()->user();
    $role = $user->role; // Admin, EventEntry, EventFood, EventDrinks

   $participant = Participant::with([
    'ticketType:id,name',
    'booking.event'
])->find($participantId);
if (!$participant) {
    return response()->json([
        'status' => false,
        'message' => 'Participant not found'
    ], 404);
}

$event = $participant->booking->event ?? null;

if (!$event) {
    return response()->json([
        'status' => false,
        'message' => 'Event not found'
    ], 404);
}

// 🔒 Event must be active
if ($event->status !== 'active') {
    return response()->json([
        'status' => false,
        'message' => 'Event is not active'
    ], 403);
}

// Optional: block if event date already passed
if (now()->gt($event->event_date)) {
    return response()->json([
        'status' => false,
        'message' => 'Event already finished'
    ], 403);
}

    if (!$participant) {
        return response()->json([
            'status' => false,
            'message' => 'Participant not found'
        ], 404);
    }

    // ----------------------------------------------------------
    // ROLE PERMISSIONS
    // ----------------------------------------------------------
    $canEntry  = in_array($role, ['Admin', 'EventEntry','Event Admin']);
    $canFood   = in_array($role, ['Admin', 'EventFood','Event Admin']);
    $canDrink  = in_array($role, ['Admin', 'EventDrinks','Event Admin']);

    if ($type === 'entry' && !$canEntry) {
        return response()->json(['status' => false, 'message' => "Unauthorized for entry"], 403);
    }

    if ($type === 'food' && !$canFood) {
        return response()->json(['status' => false, 'message' => "Unauthorized for food"], 403);
    }

    if (in_array($type, ['alcohol', 'non_alcohol']) && !$canDrink) {
        return response()->json(['status' => false, 'message' => "Unauthorized for drinks"], 403);
    }

    // ----------------------------------------------------------
    // ENTRY
    // ----------------------------------------------------------
    if ($type === 'entry') {

        if ($participant->entry_status == 1) {
            return response()->json(['status' => false, 'message' => 'Entry already marked'], 409);
        }

        $participant->entry_status = 1;
        $participant->entry_at = now();
        $participant->save();

        return response()->json([
            'status' => true,
            'message' => 'Entry marked successfully',
            'participant' => $participant
        ]);
    }

    // ----------------------------------------------------------
    // FOOD
    // ----------------------------------------------------------
    if ($type === 'food') {

        if (!$participant->entry_status) {
            return response()->json(['status' => false, 'message' => 'Entry not done yet'], 422);
        }

        if ($participant->food_status == 1) {
            return response()->json(['status' => false, 'message' => 'Food already marked'], 409);
        }

        $participant->food_status = 1;
        $participant->food_at = now();
        $participant->save();

        return response()->json([
            'status' => true,
            'message' => 'Food served successfully',
            'participant' => $participant
        ]);
    }

    // ----------------------------------------------------------
    // DRINK VALIDATION
    // ----------------------------------------------------------

    // Only complimentary Member or Spouse gets drinks
    $isSeniorComplimentary =
        $participant->is_complimentary == 1 &&
        in_array(strtolower($participant->ticket_type), ['member', 'spouse']);

    if (!$isSeniorComplimentary) {
        return response()->json([
            'status' => false,
            'message' => 'Not eligible for complimentary drinks'
        ], 403);
    }

    if (!$participant->entry_status) {
        return response()->json(['status' => false, 'message' => 'Entry not done yet'], 422);
    }

    $alcoholLimit = 2;
    $nonAlcoholLimit = 1;

    // ----------------------------------------------------------
    // ALCOHOL
    // ----------------------------------------------------------
    if ($type === 'alcohol') {

        if ($participant->consumed_alcohol >= $alcoholLimit) {
            return response()->json([
                'status' => false,
                'message' => 'Alcohol limit reached'
            ], 403);
        }

        $participant->consumed_alcohol++;
        $participant->save();

        return response()->json([
            'status' => true,
            'message' => 'Alcohol served successfully',
            'participant' => $participant
        ]);
    }

    // ----------------------------------------------------------
    // NON ALCOHOL
    // ----------------------------------------------------------
    if ($type === 'non_alcohol') {

        if ($participant->consumed_non_alcohol >= $nonAlcoholLimit) {
            return response()->json([
                'status' => false,
                'message' => 'Non-alcoholic drink limit reached'
            ], 403);
        }

        $participant->consumed_non_alcohol++;
        $participant->save();

        return response()->json([
            'status' => true,
            'message' => 'Non-alcoholic drink served successfully',
            'participant' => $participant
        ]);
    }

    return response()->json(['status' => false, 'message' => 'Invalid action'], 400);
}

// public function validateQRCode(Request $request)

public function validateQRCode(Request $request)
{
    $request->validate([
        'qr_code' => 'required|string',
        'role'    => 'required|string',        // Admin / EventEntry / EventFood / EventDrinks
        'type'    => 'nullable|string',        // entry | food | alcohol | non_alcohol
    ]);

    $qr = trim($request->qr_code);
    $role = $request->role;
    
  
    
    $manualType = strtolower($request->type);
    // ----------------------------------------------------------
    // 1️⃣ FIND PARTICIPANT BY QR (FAST & RELIABLE)
    // ----------------------------------------------------------
    $participant = Participant::where('qr_code', 'LIKE', $qr . '%')
          ->with(['ticketType', 'booking.event'])
        ->first();

    if (!$participant) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid QR Code'
        ], 404);
    }

$event = $participant->booking->event ?? null;

if (!$event) {
    return response()->json([
        'status' => false,
        'message' => 'Event not found'
    ], 404);
}


if ($event->status === 'closed') {
    return response()->json([
        'status' => false,
        'message' => 'Event is closed'
    ], 403);
}
    // ----------------------------------------------------------
    // 2️⃣ DETERMINE ACTION
    // ----------------------------------------------------------
    $action = $manualType ?: match($role) {
        'EventFood'   => 'food',
        'EventDrinks' => 'alcohol',
        default       => 'entry',
    };

    // ----------------------------------------------------------
    // 3️⃣ PERMISSIONS
    // ----------------------------------------------------------
    $canEntry  = in_array($role, ['Admin', 'EventEntry','Event Admin']);
    $canFood   = in_array($role, ['Admin', 'EventFood','Event Admin']);
    $canDrink  = in_array($role, ['Admin', 'EventDrinks','Event Admin']);

    if ($action === 'entry' && !$canEntry) {
        return response()->json(['status' => false, 'message' => 'Unauthorized for Entry'], 403);
    }

    if ($action === 'food' && !$canFood) {
        return response()->json(['status' => false, 'message' => 'Unauthorized for Food'], 403);
    }

    if (in_array($action, ['alcohol', 'non_alcohol']) && !$canDrink) {
        return response()->json(['status' => false, 'message' => 'Unauthorized for Drinks'], 403);
    }

    // ----------------------------------------------------------
    // 4️⃣ ENTRY
    // ----------------------------------------------------------
    if ($action === 'entry') {

        if ($participant->entry_status == 1) {
            return response()->json(['status' => false, 'message' => 'Entry already marked!'], 409);
        }

        $participant->entry_status = 1;
        $participant->entry_at     = now();
        $participant->save();

        return response()->json([
            'status' => true,
            'message' => 'Entry marked successfully!',
            'participant' => $participant
        ]);
    }

    // ----------------------------------------------------------
    // 5️⃣ FOOD
    // ----------------------------------------------------------
    if ($action === 'food') {

        if (!$participant->entry_status) {
            return response()->json(['status' => false, 'message' => 'Entry not done yet!'], 422);
        }

        if ($participant->food_status == 1) {
            return response()->json(['status' => false, 'message' => 'Food already served!'], 409);
        }

        $participant->food_status = 1;
        $participant->food_at     = now();
        $participant->save();

        return response()->json([
            'status' => true,
            'message' => 'Food served successfully!',
            'participant' => $participant
        ]);
    }

    // ----------------------------------------------------------
    // 6️⃣ DRINK VALIDATION — STRICT RULES
    // ----------------------------------------------------------
    $ticket = $participant->ticketType;

    if (!$ticket) {
        return response()->json(['status' => false, 'message' => 'Ticket Type missing'], 400);
    }

    // Only Senior Citizen Complimentary can drink:
    // (Member + Spouse) + is_complimentary = 1
    $isSeniorComplimentary =
        $participant->is_complimentary == 1 &&
        in_array($participant->ticket_type, ['member', 'spouse']);

    if (!$isSeniorComplimentary) {
        return response()->json([
            'status' => false,
            'message' => 'Not eligible for drinks'
        ], 403);
    }

    // Must have Entry before drinks
    if (!$participant->entry_status) {
        return response()->json([
            'status' => false,
            'message' => 'Entry not done yet!',
        ], 422);
    }

    // DRINK LIMITS
    $alcoholLimit     = 2;
    $nonAlcoholLimit  = 1;

    // ----------------------------------------------------------
    // ALCOHOL
    // ----------------------------------------------------------
    if ($action === 'alcohol') {

        if ($participant->consumed_alcohol >= $alcoholLimit) {
            return response()->json([
                'status' => false,
                'message' => 'Alcohol limit reached'
            ], 403);
        }

        $participant->consumed_alcohol++;
        $participant->save();

        return response()->json([
            'status' => true,
            'message' => 'Alcohol served successfully!',
            'participant' => $participant
        ]);
    }

    // ----------------------------------------------------------
    // NON ALCOHOL
    // ----------------------------------------------------------
    if ($action === 'non_alcohol') {

        if ($participant->consumed_non_alcohol >= $nonAlcoholLimit) {
            return response()->json([
                'status' => false,
                'message' => 'Non-alcohol drink limit reached'
            ], 403);
        }

        $participant->consumed_non_alcohol++;
        $participant->save();

        return response()->json([
            'status' => true,
            'message' => 'Non-alcoholic drink served successfully!',
            'participant' => $participant
        ]);
    }

    return response()->json([
        'status' => false,
        'message' => 'Invalid type or role'
    ], 400);
}


public function getEventSummery(Request $request)
{
 $request->validate([
   'date' => 'nullable|date'
]);
  $eventId = $request->query('id');
  
      if (!$eventId) {
        return response()->json([
            'status' => false,
            'message' => 'Please select an event'
        ], 422);
    }
    $reportDate = $request->date;
    $ticketType  = $request->query('ticket_type');
$entryStatus = $request->query('entry_status');
$foodStatus  = $request->query('food_status');
    // ============================
    // LOAD BOOKINGS (NO DATE FILTER)
    // ============================
  $bookings = TicketBooking::with([
        'participants.ticketType',
        'member',
        'waiterBooking'
    ])
    ->where('payment_status', '!=', 'failed')
    ->where('event_id', $eventId)
    ->where('total_amount', '>', 0)
    ->when($reportDate, function ($q) use ($reportDate) {
        $q->whereDate('created_at', $reportDate);
    })
    ->orderBy('created_at', 'asc')
    ->get();

    if ($bookings->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'No bookings found.',
        ], 404);
    }
    // ============================
    // FILTER PAYABLE PARTICIPANTS
    // ============================
$participants = $bookings->pluck('participants')->flatten()
    ->where('is_complimentary', 0)
    ->where('amount', '>', 0)
    ->when($ticketType, fn($c) => $c->where('ticket_id', $ticketType))
    ->when($entryStatus !== null, fn($c) => $c->where('entry_status', $entryStatus))
    ->when($foodStatus !== null, fn($c) => $c->where('food_status', $foodStatus));

    // Remove bookings having ZERO payable participants
$bookings = $bookings->filter(function ($b) use ($participants) {
    return $participants->where('booking_id', $b->id)->count() > 0;
})->values();
    //     return $b->participants
    //         ->where('is_complimentary', 0)
    //         ->where('amount', '>', 0)
    //         ->count() > 0;
    // })->values();

    if ($bookings->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'No valid payable bookings found.',
        ], 404);
    }

    // ============================
    // GLOBAL SUMMARY
    // ============================
    $totalBookings   = $bookings->count();
    $totalTickets    = $participants->count();
    $totalEntry      = $participants->where('entry_status', 1)->count();
    $totalFood       = $participants->where('food_status', 1)->count();
    $totalCollection = $participants->sum('amount');

    // Ticket type breakup
    $breakup = $participants
        ->groupBy(fn($p) => $p->ticketType->name ?? 'Unknown')
        ->map(fn($g) => $g->count());

    // ============================
    // WAITER SUMMARY
    // ============================
    $waiters = $bookings->pluck('waiterBooking')
        ->filter(fn($w) => $w && $w->quantity > 0);

    $totalWaiters = $waiters->sum('quantity');
    $totalWaiterAmount = $waiters->sum(fn($w) =>
        ($w->quantity ?? 0) * ($w->price ?? 0)
    );
    // ============================
    // HTML START
    // ============================
    $html = '<html><body>
    <style>
        body { font-family: sans-serif; font-size: 13px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 15px; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background: #e2e2e2; }
        .center { text-align: center; }
        .header td { border: none; }
        hr { height: 2px; background: black; border: none; }
    </style>';

    // ============================
    // HEADER
    // ============================
    $html .= '
        <table class="header">
            <tr>
                <td width="110">
                    <img src="https://holidayclub.in/wp-content/uploads/2025/07/Holiday-Club-logos.png" width="100"/>
                </td>
                <td>
                    <b>Holiday Club</b><br>
                    Panchsheel Enclave<br>
                    New Delhi - 110017<br>
                    Phone: 011-46525627
                </td>
            </tr>
        </table>

        <h2 class="center">EVENT SUMMARY</h2>
        <p class="center"><b>Report Generated On:</b> ' . now()->format('d-m-Y') . '</p>
        <hr>
    ';
if ($reportDate) {
   $html .= '<p class="center"><b>Report Date:</b> '.$reportDate.'</p>';
}
    // ============================
    // SUMMARY TABLE
    // ============================
    $html .= "
        <table>
            <tr><th>Metric</th><th>Value</th></tr>
            <tr><td>Total Bookings</td><td>{$totalBookings}</td></tr>
            <tr><td>Total Tickets</td><td>{$totalTickets}</td></tr>
            <tr><td>Total Entry Marked</td><td>{$totalEntry}</td></tr>
            <tr><td>Total Food Served</td><td>{$totalFood}</td></tr>
            <tr><td>Total Waiters</td><td>{$totalWaiters}</td></tr>
            <tr><td>Total Waiter Amount</td><td>₹{$totalWaiterAmount}</td></tr>
            <tr><td>Total Collection</td><td>₹{$totalCollection}</td></tr>
        </table>
    ";

    // ============================
    // TICKET BREAKUP
    // ============================
    $html .= '<h3>Ticket Type Breakdown</h3>
        <table>
            <tr><th>Ticket Type</th><th>Count</th></tr>';

    foreach ($breakup as $type => $count) {
        $html .= "<tr><td>{$type}</td><td>{$count}</td></tr>";
    }

    $html .= '</table>';

    // ============================
    // BOOKING SUMMARY (WITH TIME)
    // ============================
    $html .= '<h3>Booking Summary</h3>
        <table>
            <tr>
                <th>Booking No</th>
                <th>Booking Date</th>
                <th>Booking Time</th>
                <th>Member</th>
                <th>Total Amount</th>
                <th>Tickets</th>
                <th>Payment Status</th>
                <th>Payment Type</th>
                <th>Payment Ref No.</th>
                <th>Razorpay order_id</th>
            </tr>';

    foreach ($bookings as $b) {
       $ticketCount = $participants
    ->where('booking_id', $b->id)
    ->count();

        $html .= '
            <tr>
                <td>' . $b->booking_no . '</td>
                <td>' . $b->created_at->format('d-m-Y') . '</td>
                <td>' . $b->created_at->format('h:i A') . '</td>
               <td>' . ($b->member->DisplayName ?? '-') . '</td>
                <td>₹' . $b->total_amount . '</td>
                <td>' . $ticketCount . '</td>
                <td>' . ucfirst($b->payment_status) . '</td>
                <td>' . $b->payment_type . '</td>
                <td>' . $b->razorpay_payment_id . '</td>
                <td>' . $b->razorpay_order_id . '</td>
            </tr>';
    }

    $html .= '</table>';

    // ============================
    // PARTICIPANT DETAILS
    // ============================
    $html .= '<h3>Participant Details</h3>';
    

    foreach ($bookings as $b) {

        $html .= '
        <table>
            <tr style="background:#f5f5f5">
                <td colspan="7">
                    <b>Booking:</b> ' . $b->booking_no . ' |
                    <b>Date:</b> ' . $b->created_at->format('d-m-Y h:i A') . '
                    <b>order_id:</b> ' . $b->razorpay_order_id . ' 
                </td>
            </tr>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Ticket</th>
                <th>Entry</th>
                <th>Food</th>
                <th>QR</th>
                <th>Amount</th>
            </tr>';

        $i = 1;
      foreach ($participants->where('booking_id', $b->id) as $p) {

    if ($p->is_complimentary || $p->amount <= 0) continue;

   if ($ticketType && $p->ticket_id != $ticketType) continue;
    if ($entryStatus !== null && $p->entry_status != $entryStatus) continue;
    if ($foodStatus !== null && $p->food_status != $foodStatus) continue;

            $html .= '
            <tr>
                <td>' . $i++ . '</td>
                <td>' . $p->name . '</td>
                <td>' . ($p->ticketType->name ?? 'N/A') . '</td>
                <td>' . ($p->entry_status ? 'Yes' : 'No') . '</td>
                <td>' . ($p->food_status ? 'Yes' : 'No') . '</td>
                <td>' . ($p->qr_code ? 'Yes' : 'No') . '</td>
                <td>₹' . number_format($p->amount, 2) . '</td>
            </tr>';
        }

        $html .= '</table>';
    }

    $html .= '</body></html>';

    return response()->json([
        'status'  => true,
        'message' => 'Full summary generated successfully',
        'html'    => $html,
        'date'    => $reportDate
    ]);
}
}