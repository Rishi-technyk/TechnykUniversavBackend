<?php

namespace App\Http\Controllers\api\v1;
use App\Models\OccupantMaster;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FunctionMaster;
use App\Models\BanquetBooking;
use App\Models\VenueMaster;
use App\Models\VenueCharge;
use App\Models\AdminSetting;
use App\Models\VenueBlock;
use App\Services\FCMService;
use App\Models\Member;
use App\Models\SOP;
use App\Models\BanquetBookingCharges;
use App\Models\CancellationPolicy;
use Illuminate\Support\Facades\Validator;
use App\Services\BookingNotificationService;
use AESEncDec;
use DB;
use Razorpay\Api\Api;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Helpers\MailHelper;

class BanquetBookingController extends Controller
{
    
    
    public function blockVenues(Request $request)
{
    $request->validate([
        'venues' => 'required|array',
        'venues.*.venue_id' => 'required|integer|exists:venue_masters,id',
        'venues.*.session_id' => 'required|integer|exists:sessions,id',
        'venues.*.from_date' => 'required|date',
        'venues.*.to_date' => 'required|date|after_or_equal:venues.*.from_date',
        'venues.*.remark' => 'nullable|string'
    ]);

    try {
        DB::transaction(function () use ($request) {

            // âœ… Delete only API blocked venues (NOT truncate)
            VenueBlock::where('block_type', 'API')->delete();

            foreach ($request->venues as $venue) {

                if (Carbon::parse($venue['to_date'])->lt(Carbon::parse($venue['from_date']))) {
                    throw new \Exception("To date must be after or equal to From date");
                }

                VenueBlock::create([
                    'venue_id'   => $venue['venue_id'],
                    'session_id' => $venue['session_id'],
                    'from_date'  => $venue['from_date'],
                    'to_date'    => $venue['to_date'],
                    'remark'     => $venue['remark'] ?? null,
                    'block_type' => 'API'
                ]);
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Venue blocks updated successfully'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
        ], 422);
    }
}



public function get_menuVenue(Request $request)
{
    /* ----------------------------------
       AUTH CHECK
    ---------------------------------- */
    $user = auth()->user();
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
        ], 401);
    }

    /* ----------------------------------
       VALIDATION
    ---------------------------------- */
    $request->validate([
        'funDate'       => 'required|date',
        'occupantType'  => 'required|integer',
    ]);

    $funDate      = $request->funDate;
    $occupantType = $request->occupantType;

    /* ----------------------------------
       MASTER DATA
    ---------------------------------- */
    $sop       = SOP::where('type', 'Banquet Booking')->first(['id', 'content', 'type']);
    $occupants = OccupantMaster::where('status', 'Active')->get();
    $functions = FunctionMaster::where('status', 'Active')->orderByDesc('id')->get();
    $settings  = AdminSetting::first();

    /* ----------------------------------
       LOAD ACTIVE SESSIONS (IMPORTANT FIRST)
    ---------------------------------- */
    $sessions = DB::table('sessions')
        ->where('status', 'Active')
        ->orderBy('id')
        ->get();

    $sessionIds = $sessions->pluck('id')->toArray();

    /* ----------------------------------
       BOOKED VENUE + SESSION (ACTIVE ONLY)
    ---------------------------------- */
    $bookedMap = [];

    $bookings = DB::table('banquet_bookings as b')
        ->join('banquet_booking_charges as c', 'b.id', '=', 'c.banquet_booking_id')
        ->whereDate('b.funDate', $funDate)
        ->where('b.status', 'Active')
        ->where('c.status', 'Active')
        ->select('c.vanue_id as venue_id', 'c.session_id')
        ->get();

    foreach ($bookings as $booking) {
        $bookedMap[$booking->venue_id][] = $booking->session_id;
    }

    /* ----------------------------------
       BLOCKED VENUES (OFFLINE / ADMIN)
    ---------------------------------- */
    $blocked = DB::table('venue_blocks')
        ->whereDate('from_date', '<=', $funDate)
        ->whereDate('to_date', '>=', $funDate)
        ->select('venue_id', 'session_id')
        ->get();

    foreach ($blocked as $block) {
        // If session_id is NULL or 0 â†’ block ALL sessions
        if (empty($block->session_id)) {
            $bookedMap[$block->venue_id] = $sessionIds;
        } else {
            $bookedMap[$block->venue_id][] = $block->session_id;
        }
    }

    /* ----------------------------------
       LOAD VENUES
    ---------------------------------- */
    $venues = VenueMaster::where('status', 'Active')
        ->orderByDesc('id')
        ->get();

    /* ----------------------------------
       FILTER VENUES & SESSIONS
    ---------------------------------- */
    $availableVenues = $venues->map(function ($venue) use (
        $sessions,
        $bookedMap,
        $occupantType
    ) {

        // Charges for this venue + occupant
        $charges = \App\Models\VenueCharge::where('venue_id', $venue->id)
            ->where('occupant_id', $occupantType)
            ->get();

        if ($charges->isEmpty()) {
            return null;
        }

        // Available sessions
        $venue->sessions = $sessions->filter(function ($session) use (
            $venue,
            $bookedMap,
            $charges
        ) {
            $isBlocked = in_array(
                $session->id,
                $bookedMap[$venue->id] ?? []
            );

            $hasRate = $charges->contains('session_id', $session->id);

            return !$isBlocked && $hasRate;
        })->values();

        if ($venue->sessions->isEmpty()) {
            return null;
        }

        // Attach valid charges only
        $venue->venue_charges = $charges->values();

        return $venue;
    })->filter()->values();

    /* ----------------------------------
       RESPONSE
    ---------------------------------- */
    return response()->json([
        'success'   => true,
        'sop'       => $sop,
        'venues'    => $availableVenues,
        'sessions'  => $sessions,
        'functions' => $functions,
        'occupants' => $occupants,
        'settings'  => $settings,
    ]);
}

    public function get_SOP(Request $request)
    {
        $SOP = SOP::where('type', 'Banquet Booking')->first();

        $return_data['data'] = $SOP;

        $return_data['status'] = true;

        return response()->json($return_data);
    }

    public function get_venue(Request $request)
    {
        $vanue = VenueMaster::where('status', 'Active')->orderBy('id', 'DESC')->get();

        $return_data['data'] = $vanue;

        $return_data['status'] = true;

        return response()->json($return_data);
    }

    public function get_session(Request $request)
    {
        $session = DB::table('sessions')->where('status', 'Active')->orderBy('id', 'DESC')->get();

        $return_data['data'] = $session;

        $return_data['status'] = true;

        return response()->json($return_data);
    }

    public function get_function_master(Request $request)
    {
        $function = FunctionMaster::where('status', 'Active')->orderBy('id', 'DESC')->get();

        $return_data['data'] = $function;

        $return_data['status'] = true;

        return response()->json($return_data);
    }

    public function get_venue_by_session(Request $request)
    {
        $charges = VenueCharge::where('venue_id', $request->venue_id)->where('session_id', $request->session_id)->where('occupant_id', $request->occupant_id)->first();

        $venue = VenueMaster::find($request->venue_id);

        $checkBooking = BanquetBooking::whereDate('funDate', '=', $request->function_date)->first();

        if($checkBooking){

            $checkVenue = BanquetBookingCharges::where('status', 'Active')->where('banquet_booking_id', $checkBooking->id)->where('vanue_id', $request->venue_id)->where('session_id', $request->session_id)->count();

            if($checkVenue){

                return response()->json(['charges'=>$charges, 'venue'=>$venue, 'booking'=>'Booked', 'status' => true]);

            } else {

                return response()->json(['charges'=>$charges, 'venue'=>$venue, 'booking'=>'No Booking', 'status' => true]);

            }

        } else {

            return response()->json(['charges'=>$charges, 'venue'=>$venue, 'booking'=>'No Booking', 'status' => true]);

        }
    }

// public function banquet_traction(Request $request)
// {
//     $member = Member::where("memberprofile.id", auth()->user()->id)
//         ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')
//         ->first();

//     $limit = $request->query('limit', 10);
//     $filterStatus = $request->query('status');

//     /* -------------------------------
//       AUTO STATUS UPDATES
//     --------------------------------*/

//     // ðŸ”´ Cancelled â†’ when ALL charges are cancelled
//     DB::table('banquet_bookings')
//         ->whereNotIn('status', ['Cancelled'])
//         ->whereExists(function ($query) {
//             $query->select(DB::raw(1))
//                 ->from('banquet_booking_charges')
//                 ->whereColumn(
//                     'banquet_booking_charges.banquet_booking_id',
//                     'banquet_bookings.id'
//                 );
//         })
//         ->whereNotExists(function ($query) {
//             $query->select(DB::raw(1))
//                 ->from('banquet_booking_charges')
//                 ->whereColumn(
//                     'banquet_booking_charges.banquet_booking_id',
//                     'banquet_bookings.id'
//                 )
//                 ->where('status', '!=', 'Cancelled');
//         })
//         ->update(['status' => 'Cancelled']);

//     // âœ… Completed â†’ ONLY when payment is PAID & no pending charges
//     DB::table('banquet_bookings')
//         ->where('payment_status', 'Paid')
//         ->whereNotIn('status', ['Completed', 'Cancelled'])
//         ->whereNotExists(function ($query) {
//             $query->select(DB::raw(1))
//                 ->from('banquet_booking_charges')
//                 ->whereColumn(
//                     'banquet_booking_charges.banquet_booking_id',
//                     'banquet_bookings.id'
//                 )
//                 ->where('status', 'Pending');
//         });

//     /* -------------------------------
//       MAIN DATA
//     --------------------------------*/

//     $q = BanquetBooking::where('memberID', $member->MemberID)
//         ->with('occupant', 'function')
//         ->orderBy('id', 'DESC');

//     if ($request->function_date) {
//         $q->whereDate('funDate', $request->function_date);
//     }

//     if ($request->booking_no) {
//         $q->where('booking_ID', $request->booking_no);
//     }

//     if (!empty($filterStatus)) {
//         $q->where('status', $filterStatus);
//     }

//     $datas = $q->paginate($limit);

//     // ðŸŸ¡ Normalize empty / null status â†’ Pending
//     $datas->getCollection()->transform(function ($item) {
//         if (empty($item->status)) {
//             $item->status = 'Pending';
//         }
//         return $item;
//     });

//     /* -------------------------------
//       UPCOMING BOOKINGS
//     --------------------------------*/

//   $upcomingBookings = BanquetBooking::where('memberID', $member->MemberID)
//     ->whereDate('funDate', '>=', now())
//     ->whereNotIn('status', ['Cancelled', 'Completed'])
//     ->orderBy('funDate', 'ASC')
//     ->get()
//     ->map(function ($item) {
//         return [
//             'id' => $item->id,
//             'type' => 'hall',
//             'title' => $item->booking_ID,
//             'date' => $item->funDate,
//             'status' => $item->status ?: 'Pending',
//             'payment_status' => $item->payment_status,
//         ];
//     });


//     return response()->json([
//         'status' => true,
//         'data' => $datas,
//         'upcoming_bookings' => $upcomingBookings
//     ]);
// }
public function banquet_traction(Request $request)
{
    $memberId = auth()->user()->MemberID;
    $limit = $request->query('limit', 10);
    $status = $request->query('status');

    /* ✅ Update Completed bookings safely */
    DB::update("
        UPDATE banquet_bookings bb
        SET status = 'Completed'
        WHERE bb.payment_status = 'Paid'
        AND bb.funDate < ?
        AND bb.status NOT IN ('Completed','Cancelled')
        AND NOT EXISTS (
            SELECT 1 FROM banquet_booking_charges bc
            WHERE bc.banquet_booking_id = bb.id
            AND bc.status = 'Pending'
        )
    ", [now()]);

    /* 📦 MAIN DATA */
    $query = BanquetBooking::where('memberID', $memberId)
        ->orderByDesc('id');

    if ($request->function_date) {
        $query->whereDate('funDate', $request->function_date);
    }

    if ($request->booking_no) {
        $query->where('booking_ID', $request->booking_no);
    }

    if (!empty($status)) {
        $query->where('status', $status);
    }

    $bookings = $query->paginate($limit);

    $bookings->getCollection()->transform(function ($item) {
        $item->status = $item->status ?: 'Pending';
        return $item;
    });

    /* 🔔 UPCOMING */
    $upcoming = BanquetBooking::where('memberID', $memberId)
        ->whereDate('funDate', '>=', now())
        ->whereNotIn('status', ['Cancelled', 'Completed'])
        ->orderBy('funDate')
        ->take(5)
        ->get(['id','booking_ID','funDate','status','payment_status'])
        ->map(fn($b) => [
            'id' => $b->id,
            'title' => $b->booking_ID,
            'date' => $b->funDate,
            'status' => $b->status ?: 'Pending',
            'payment_status' => $b->payment_status,
        ]);

    return response()->json([
        'status' => true,
       'data' =>[
           'data' => $bookings->items(),
        'pagination' => [
            'current_page' => $bookings->currentPage(),
            'last_page' => $bookings->lastPage(),
            'per_page' => $bookings->perPage(),
            'total' => $bookings->total(),
        ],
        'upcoming_bookings' => $upcoming
    ]
    ]);
}





    public function details($id='')
    {
        $datas = BanquetBooking::find($id);

        if($datas){

            $payment = DB::table('transactions')->where('banquet_booking_id', $id)->latest('id')->first();
            $bookings = BanquetBookingCharges::where('banquet_booking_id', $id)->with('venue')->with('session')->get();
            $member = Member::where('MemberID', $datas->memberID)
                ->orWhere('SC_ID', $datas->cardID)
                ->first();
            $setting = AdminSetting::first();
            $subtotal = $bookings->sum(fn ($item) => (float) $item->charges);
            $gstTotal = $bookings->sum(fn ($item) => (float) $item->gst_amount);
            $securityTotal = $bookings->sum(fn ($item) => (float) ($item->security_deposit ?? 0));
            $grandTotal = $bookings->sum(fn ($item) => (float) $item->total);
            $cancelledAmount = $bookings->sum(fn ($item) => (float) ($item->cancellation_deducation ?? 0));

            $datas->payment_info = $payment;
            $datas->bookings = $bookings;

            $datas->occupant;

            $datas->function;
            $datas->member = [
                'member_id' => $datas->memberID,
                'sc_id' => $datas->cardID,
                'name' => $datas->memberName,
                'email' => $datas->memberEmail ?: ($member->Email ?? null),
                'phone' => $datas->memberMobile ?: ($member->Mobile ?? null),
                'address' => $datas->address,
            ];
            $datas->summary = [
                'booking_number' => $datas->booking_ID,
                'status' => $datas->status ?: 'Pending',
                'payment_status' => $datas->payment_status ?: ($payment->payment_status ?? 'Not Paid'),
                'function_date' => Carbon::parse($datas->funDate)->format('d M Y'),
                'attendees' => (int) $datas->noofPerson,
                'venue_count' => $bookings->count(),
                'session_count' => $bookings->pluck('session_id')->unique()->count(),
                'total_amount' => round($grandTotal, 2),
            ];
            $datas->payment = [
                'status' => $payment->payment_status ?? $datas->payment_status ?? 'Not Paid',
                'status_code' => $payment->payment_status_code ?? null,
                'gateway_name' => $payment->gateway_name ?? null,
                'gateway_order_id' => $payment->gateway_order_id ?? $payment->transID ?? null,
                'reference_number' => $payment->gateway_transaction_id ?? $payment->bank_refrance_no ?? null,
                'transaction_date' => optional($payment?->transaction_date)->format('d M Y, h:i A'),
                'processed_at' => optional($payment?->processed_at)->format('d M Y, h:i A'),
                'amount' => round((float) ($payment->amount ?? $grandTotal), 2),
                'refund_amount' => round(max(0, $grandTotal - $cancelledAmount), 2),
            ];
            $datas->invoice = [
                'currency' => 'INR',
                'subtotal' => round($subtotal, 2),
                'gst_total' => round($gstTotal, 2),
                'security_total' => round($securityTotal, 2),
                'total' => round($grandTotal, 2),
                'cancelled_deduction' => round($cancelledAmount, 2),
                'items' => $bookings,
            ];
            $datas->timeline = [
                [
                    'title' => 'Booking created',
                    'description' => 'Banquet reservation was placed by the member.',
                    'time' => optional($datas->created_at)->format('d M Y, h:i A'),
                    'tone' => 'neutral',
                ],
                [
                    'title' => 'Payment status',
                    'description' => $payment->payment_status ?? 'Awaiting payment confirmation',
                    'time' => optional($payment?->processed_at ?? $payment?->transaction_date)->format('d M Y, h:i A'),
                    'tone' => strtolower((string) ($payment->payment_status ?? '')) === 'paid' ? 'success' : 'warning',
                ],
                [
                    'title' => 'Function day',
                    'description' => $datas->function?->name ?: 'Event scheduled',
                    'time' => Carbon::parse($datas->funDate)->format('d M Y'),
                    'tone' => strtolower((string) $datas->status) === 'cancelled' ? 'danger' : 'neutral',
                ],
            ];
            $datas->payment_timeline = [
                [
                    'title' => 'Order created',
                    'description' => $payment?->gateway_name ? 'Gateway: ' . $payment->gateway_name : 'Payment order raised',
                    'time' => optional($payment?->created_at)->format('d M Y, h:i A'),
                    'tone' => 'neutral',
                ],
                [
                    'title' => 'Transaction update',
                    'description' => $payment?->payment_status ?? 'Awaiting update',
                    'time' => optional($payment?->processed_at ?? $payment?->transaction_date)->format('d M Y, h:i A'),
                    'tone' => strtolower((string) ($payment->payment_status ?? '')) === 'paid' ? 'success' : 'warning',
                ],
            ];
            $datas->rules = [
                'Venue and session availability are confirmed only after successful payment.',
                'Cancellations follow the club banquet cancellation matrix and approved deductions.',
                'Carry the booking number for venue access and billing queries.',
            ];
            $datas->support = [
                'name' => $setting->club_name ?? 'Banquet Desk',
                'phone' => $setting->mobile ?? $setting->phone ?? null,
                'email' => $setting->email ?? null,
                'note' => 'Reach out to the banquet desk for menu, venue change, or refund support.',
            ];

        }

        $return_data['data'] = $datas;

        $return_data['status'] = true;

        return response()->json($return_data);
    }
public function banquet_store(Request $request)
{
    DB::beginTransaction();

    try {

        $bookingID = date('dmY') . '-' . rand(9999, 100000);

        $params = [
            'occupant_type' => $request->occupant_type,
            'memberID'      => auth()->user()->MemberID,
            'cardID'        => auth()->user()->SC_ID,
            'memberName'    => $request->memberName,
            'memberMobile'  => $request->memberMobile,
            'memberEmail'   => $request->memberEmail,
            'address'       => $request->address,
            'funDate'       => $request->funDate,
            'functionType'  => $request->functionType,
            'noofPerson'    => $request->noofPerson,
            'remark'        => $request->remark,
            'booking_ID'    => $bookingID,
        ];

        $booking = BanquetBooking::create($params);

        if (!$booking) {
            throw new \Exception('Booking failed');
        }

        /* ---------------- SAVE SESSION CHARGES ---------------- */

        foreach ($request->session_id as $key => $session) {

            BanquetBookingCharges::create([
                'banquet_booking_id' => $booking->id,
                'session_id'         => $session,
                'vanue_id'           => $request->vanue_id[$key],
                'gst_amount'         => $request->gst_amount[$key],
                'gst_per'            => $request->gst_per[$key],
                'charges'            => $request->charges[$key],
                'total'              => $request->total[$key],
                'funDate'            => $request->funDate,
            ]);
        }

        /* ---------------- TOTAL AMOUNT ---------------- */

        $amount = BanquetBookingCharges::where('banquet_booking_id', $booking->id)
            ->sum('total');

        $member = auth()->user();

        /* ---------------- CREATE RAZORPAY ORDER ---------------- */

        $payment = app(\App\Services\Payments\PaymentTransactionService::class)->initiate(
            $member,
            (float) $amount,
            \App\Support\Payments\PaymentModule::BANQUET_BOOKING,
            $booking->id,
            [
                'type' => 'Banquet Booking',
                'prefix' => 'BQB',
            ]
        );

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Submitted Successfully',
            'data' => [
                'order_id'     => $payment['order_id'],
                'amount'       => $amount,
                'merchant_order_id' => $payment['merchant_order_id'],
                'status_reference' => $payment['status_reference'] ?? $payment['merchant_order_id'],
                'status_endpoint' => $payment['status_endpoint'] ?? null,
                'gateway' => $payment['gateway'] ?? null,
                'checkout' => $payment['checkout'] ?? null,
                'payment_url' => $payment['payment_url'] ?? null,
                'access_key' => $payment['access_key'] ?? null,
                'razorpayKey'  => $payment['razorpayKey'] ?? data_get($payment, 'checkout.key'),
                'end_point'    => 'member/update/banquet/payment'
            ]
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        \Log::error('Banquet booking error', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'status' => false,
            'message' => 'Try Again'
        ]);
    }
}
private function verifyPaymentFromRazorpay($paymentId, $orderId)
{
    if (!$paymentId) return false;

    $client = new \Razorpay\Api\Api(
        config('services.razorpay.key'),
        config('services.razorpay.secret')
    );

    try {
        $payment = $client->payment->fetch($paymentId);

        return (
            $payment->status === 'captured' &&
            $payment->order_id === $orderId
        );
    } catch (\Exception $e) {
        Log::error('Razorpay Verify Error', ['error' => $e->getMessage()]);
        return false;
    }
}

public function update_payment(Request $request, FCMService $fcm)
{
    $member = auth()->user();

    DB::beginTransaction();

    try {
        $reference = $request->merchant_order_id
            ?? $request->status_reference
            ?? $request->transaction_id
            ?? $request->gateway_order_id
            ?? $request->razorpay_order_id
            ?? $request->order_id;

        $transaction = DB::table('transactions')
            ->where(function ($query) use ($reference) {
                $query->where('order_id', $reference)
                    ->orWhere('transID', $reference)
                    ->orWhere('gateway_order_id', $reference);
            })
            ->lockForUpdate()
            ->first();

        if (!$transaction) {
            throw new \Exception('Transaction not found');
        }

        $isCentralized = !empty($transaction->gateway_slug) || !empty($transaction->payment_status_code);

        if ($isCentralized) {
            $needsVerification = $request->filled('razorpay_payment_id');
            if (
                !$transaction->payment_status_code
                || !\App\Support\Payments\PaymentStatus::isSuccessful($transaction->payment_status_code)
            ) {
                $result = $needsVerification
                    ? app(\App\Services\Payments\PaymentTransactionService::class)->verify($member, $request->all())
                    : [
                        'success' => strcasecmp((string) $transaction->payment_status, 'Paid') === 0,
                        'data' => [
                            'MemberName' => $member->DisplayName ?? '',
                            'MemberID' => $member->MemberID ?? '',
                            'MemberSCID' => $member->SC_ID ?? '',
                            'paid_amount' => (float) $transaction->amount,
                            'reference_number' => $transaction->gateway_transaction_id
                                ?? $transaction->bank_refrance_no
                                ?? $transaction->transID,
                            'orderId' => $transaction->gateway_order_id
                                ?? $transaction->transID
                                ?? $transaction->order_id,
                            'Status' => strcasecmp((string) $transaction->payment_status, 'Paid') === 0 ? 'Success' : 'Failed',
                        ],
                    ];
            } else {
                $result = [
                    'success' => true,
                    'data' => [
                        'MemberName' => $member->DisplayName ?? '',
                        'MemberID' => $member->MemberID ?? '',
                        'MemberSCID' => $member->SC_ID ?? '',
                        'paid_amount' => (float) $transaction->amount,
                        'reference_number' => $transaction->gateway_transaction_id
                            ?? $transaction->bank_refrance_no
                            ?? $transaction->transID,
                        'orderId' => $transaction->gateway_order_id
                            ?? $transaction->transID
                            ?? $transaction->order_id,
                        'Status' => 'Success',
                    ],
                ];
            }
        } else {
            $result = \App\Helpers\PaymentHelper::verifyPayment($request);
        }

        // ✅ 3️⃣ UPDATE BANQUET STATUS ONLY IF SUCCESS
        if ($result['success'] && !$isCentralized) {

            BanquetBooking::where('id', $transaction->banquet_booking_id)
                ->update([
                    'status' => 'Active',
                    'payment_status' => 'Paid'
                ]);

            BanquetBookingCharges::where('banquet_booking_id', $transaction->banquet_booking_id)
                ->update([
                    'status' => 'Active'
                ]);
        } elseif (!$isCentralized) {

            BanquetBooking::where('id', $transaction->banquet_booking_id)
                ->update([
                    'status' => 'Failed',
                    'payment_status' => 'Not Paid'
                ]);
        }

     

       if ($member->device_id) {
            $fcm->sendNotification(
                $member->device_id,
               'Banquet Booking',
                $result['success']
                ? 'Your banquet booking is confirmed.'
                : 'Banquet Payment failed .',
                 [
                    'id' => $transaction->banquet_booking_id,
                    'screen' => 'InvoiceHallDetails'
                ]
            );
        }
        DB::commit();

        // ✅ 5️⃣ RETURN STANDARD RESPONSE
        return response()->json($result);

    } catch (\Exception $e) {

        DB::rollBack();

        \Log::error('Banquet payment update error', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Payment processing failed'
        ]);
    }
}



//   public function update_payment(Request $request)
//     {
//         if($request->status){

//             $status = 'Paid';

//         } else if (!$request->status){

//             $status = 'Failed';

//         } else {

//             $status = 'Not Paid';

//         }
        

// $member=auth()->user();
//         $trans = DB::table('transactions')->where('transID', $request->razorpay_order_id)->latest()->first();
 
//         if($trans){

//             if($status){
//                   $attributes = [
//             'razorpay_response' => $request->razorpay_response,
//             'razorpay_payment_id' => $request->razorpay_payment_id,
//             'razorpay_order_id' => $request->razorpay_order_id,
            
//             'status' => $request->status,
//         ];

//   Log::info($trans);
//   Log::info($request->status);
//         $payment_response = json_encode($attributes);
//                 $generatedSignature = hash_hmac('sha256', $request->razorpay_order_id . "|" . $request->razorpay_payment_id, config('services.razorpay.secret'));
//                 $paramss['bank_response']= $payment_response ?$payment_response:'';
//                 $paramss['bank_refrance_no']       = $request->status ==true ?$generatedSignature :null;
//                 $r_params['status'] = $request->status ==true ?'Active':"Failed";
//                 $r_params['payment_status'] = $request->status ==true ?'Paid':"Not Paid";
//                 BanquetBooking::where('id', $trans->banquet_booking_id)->update($r_params);
//                  BanquetBookingCharges::where('banquet_booking_id', $trans->banquet_booking_id)->update($r_params);
                
//             }
           
           
            

//             $paramss['payment_status']      = $status;
//             $paramss['transID']    = $request->razorpay_payment_id ??null;
           
//             DB::table('transactions')->where('transID', $request->razorpay_order_id)->update($paramss);

//             $return_data['message'] = 'Payment Updated.';
//              Log::info($status);
//  $notification = [
//                     'title' => 'Banquet Booking',
//                     'short_descriptions' => $request->status == true ? 'Your Banquet booked successfully.' : 'Your payment is failed.',
//                 ];   
//                 Log::info($notification);
//                 $this->sendFCMMessage($notification, auth()->user()->device_id); 
//             $return_data['status'] = $request->status == true ; 
//             $data['Status']=$request->status == true ? 'Success' : 'Failed'; 
//              $data['paid_amount'] = $trans->amount;
//              $data['reference_number']=$request->razorpay_payment_id;
//               $data['orderId']=$request->razorpay_order_id;
//               $data['MemberSCID']=$member->SC_ID; 
//               $data['MemberName']=$member->DisplayName;
//               $data['MemberID']=$member->MemberID;
//         } else {

//             $return_data['message'] = 'Data not found.';

//             $return_data['status'] = false; 
//             $data['Status']='Failed'; 
//              $data['paid_amount'] = $trans->amount ??0;
//              $data['reference_number']=$request->razorpay_payment_id??"N/A";
//               $data['orderId']=$request->razorpay_order_id ??"N/A";

//         }
        
//       return response()->json([
//     'message' => $return_data['message'],
//     'status' => $return_data['status'],
//     'data' => $data
// ]);
        
//     }

    public function cancelVenue(Request $request)
    {

        $booking = BanquetBookingCharges::find($request->ban_charge_id);

        if($booking){

            $policys = CancellationPolicy::where('venue_id', $booking->vanue_id)->get();

            if(count($policys)){
           
                $cdate = date('Y-m-d');
                                                  
                $startTimeStamp = strtotime($booking->funDate);

                $endTimeStamp = strtotime($cdate);

                $timeDiff = abs($endTimeStamp - $startTimeStamp);

                $numberDays = $timeDiff/86400; 
                  
                $numberDays = intval($numberDays);        

                $policy = '';

                if($policys){

                    foreach ($policys as $key => $ploy) {
                        
                        if($numberDays >= $ploy->from_days && $numberDays <= $ploy->to_days){

                            $policy = $ploy;

                        }
                    }
                }

                if(isset($policy)){

                    $percentage = $policy->GST;
                    $totalWidth = $booking->charges;

                    

                    $balaance_Amt = ($policy->deduction / 100) * $totalWidth;

                    $new_width = ($percentage / 100) * $balaance_Amt;

                    $params['cancellation_per']         = $policy->deduction;
                    $params['cancellation_amt']         = $balaance_Amt;
                    $params['cancellation_GST']         = $percentage;
                    $params['cancellation_GST_amt']     = $new_width;
                    $params['cancellation_deducation']  = $balaance_Amt+$new_width;
                    $params['cancellation_date']        = date('Y-m-d H:i:s');
                    $params['status']                   = 'Cancelled';

                    $res = BanquetBookingCharges::whereId($booking->id)->update($params);
                $remaining = BanquetBookingCharges::where('banquet_booking_id', $booking->banquet_booking_id)
    ->where('status', '!=', 'Cancelled')
    ->count();

if ($remaining == 0) {
    DB::table('banquet_bookings')
        ->where('id', $booking->banquet_booking_id)
        ->update([
            'status' => 'Cancelled',
            'updated_at' => now()
        ]);
}
// $member=auth()->user();
// BookingNotificationService::sendCancellationMail(
//     $member,
//     $booking,
//     'Banquet'
// );
MailHelper::sendBanquetCancellation(
    auth()->user(),
    $booking,
    $balaance_Amt,
    $new_width
);
                    $data['msg'] = 'Banquet Cancelled.';
                    $data['status'] = true;

                    return response()->json($data);

                } else {

                    $data['msg'] = 'Cancellation policy is not available for this venue.';
                    $data['status'] = false;

                    return response()->json($data);

                }
            } else {

                $data['msg'] = 'Cancellation policy is not available for this venue.';
                $data['status'] = false;

                return response()->json($data);
            }

        } else {

            $data['msg'] = 'Data not found.';
            $data['status'] = false;

            return response()->json($data);
        }
        
    }
public function availability(Request $request)
{
    $request->validate([
        'startdate' => 'required|date',
        'enddate'   => 'required'
        // 'enddate'   => 'required|date|after_or_equal:startdate',
    ]);

    $startDate = Carbon::parse($request->startdate);
    $endDate   = Carbon::parse($request->startdate);

    // Fetch all venues
    $venues = VenueMaster::where('status', 'Active')->get();

    $finalResults = [];
    $totalAvailableVenues = 0;

    foreach ($venues as $venue) {

        /** 1) Sessions assigned to venue */
        $venueSessions = DB::table('venue_charges')
            ->where('venue_id', $venue->id)
            ->pluck('session_id')
            ->unique()
            ->toArray();

        if (empty($venueSessions)) continue;

        $sessionResults = [];
        $venueHasAtLeastOneAvailableSession = false;

        foreach ($venueSessions as $sessionId) {

            $sessionName = DB::table('sessions')->where('id', $sessionId)->value('name');

            /** ðŸ”¥ 2) Check if session is BOOKED in the range */
            $isBooked = DB::table('banquet_booking_charges as c')
                ->join('banquet_bookings as b', 'b.id', '=', 'c.banquet_booking_id')
                ->where('c.vanue_id', $venue->id)
                ->where('c.session_id', $sessionId)
                ->where('b.status', 'Active')
                ->whereDate('b.funDate', '>=', $startDate)
                ->whereDate('b.funDate', '<=', $endDate)
                ->exists();

            /** ðŸ”¥ 3) Check if session is BLOCKED in the range */
            $isBlocked = DB::table('venue_blocks')
                ->where('venue_id', $venue->id)
                ->where(function ($q) use ($sessionId) {
                    $q->where('session_id', $sessionId)
                      ->orWhere('session_id', 0)
                      ->orWhere('session_id', "");
                })
                ->whereDate('from_date', '<=', $endDate)
                ->whereDate('to_date', '>=', $startDate)
                ->exists();

            /** FINAL STATUS */
            if ($isBooked || $isBlocked) {
                $status = "Booked";
                $bg     = "#fff4ce";
                $color  = "#b78103";
            } else {
                $status = "Available";
                $bg     = "#F1F5F9";
                $color  = "#0f9d58";
                $venueHasAtLeastOneAvailableSession = true;
            }

            $sessionResults[] = [
                "session_id"   => $sessionId,
                "session_name" => $sessionName,
                "status"       => $status,
                "bgc"          => $bg,
                "color"        => $color
            ];
        }

        if ($venueHasAtLeastOneAvailableSession) {
            $totalAvailableVenues++;
        }

        $finalResults[] = [
            "id"          => $venue->id,
            "venue_name"  => $venue->name,
            "capacity"    => $venue->capacity,
            "image"       => $venue->image ? url('uploads/venue/'.$venue->image) : null,
            "description" => $venue->description ?? "",
            "sessions"    => $sessionResults
        ];
    }

    return response()->json([
        "status" => true,
        "startdate" => $startDate->format('Y-m-d'),
        "enddate"   => $endDate->format('Y-m-d'),
        "total_available_venues" => $totalAvailableVenues,
        "banquets" => $finalResults
    ]);
}
public function updateBookingNo(Request $request)
{
    $validator = Validator::make($request->all(), [
        'bookings' => 'required|array|min:1',
        'bookings.*.booking_ID' => 'required|string|exists:banquet_bookings,booking_ID',
        'bookings.*.BookingNo' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422);
    }

    $updated = [];
    foreach ($request->bookings as $booking) {
         $banquet = BanquetBooking::where('booking_ID', $booking['booking_ID'])->first();
        if ($banquet) {
            $banquet->update([
                'BookingNo' => $booking['BookingNo'],
            ]);

            $updated[] = [
                'booking_ID' => $banquet->booking_ID,
                'BookingNo' => $banquet->BookingNo,
            ];
        }
    }

    return response()->json([
        'status' => true,
        'message' => 'Booking numbers updated successfully.',
        'updated_records' => $updated,
    ]);
}
  public function getBookingsWithZeroBookingNo()
{
    try {
        $bookings = BanquetBooking::select([
                'banquet_bookings.id',
                'banquet_bookings.booking_ID',
                'banquet_bookings.occupant_type',
                'banquet_bookings.memberID',
                'banquet_bookings.cardID',
                'banquet_bookings.memberName',
                'banquet_bookings.funDate',
                'banquet_bookings.functionType',
                'banquet_bookings.noofPerson',
                'banquet_bookings.remark',
                'banquet_bookings.status',
                'banquet_bookings.payment_status',

                // âœ… Subquery columns from transactions table
                DB::raw('(SELECT amount FROM transactions WHERE transactions.banquet_booking_id = banquet_bookings.id LIMIT 1) as advance_amount'),
                DB::raw('(SELECT order_id FROM transactions WHERE transactions.banquet_booking_id = banquet_bookings.id LIMIT 1) as order_id'),
                DB::raw('(SELECT bank_refrance_no FROM transactions WHERE transactions.banquet_booking_id = banquet_bookings.id LIMIT 1) as bank_refrance_no'),
            ])
            ->where('banquet_bookings.BookingNo', 0)
            ->where('banquet_bookings.payment_status', 'Paid')
            ->where('banquet_bookings.status', '!=', 'Cancelled')
            ->with(['banquetCharge' => function ($query) {
                $query->select([
                        'id',
                        'banquet_booking_id',
                        'session_id',
                        'vanue_id',
                        'gst_per',
                        'gst_amount',
                        'charges',
                        'security_deposit',
                        'total',
                        'funDate',
                        'status',
                        'payment_status'
                    ])
                    ->where('payment_status', 'Paid')
                    ->where('status', '!=', 'Cancelled');
            }])
            ->orderByDesc('banquet_bookings.id')
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No paid and active banquet bookings found with BookingNo = 0.',
                'data'    => []
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Filtered bookings retrieved successfully.',
            'count'   => $bookings->count(),
            'data'    => $bookings,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Error fetching banquet bookings.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}




}
