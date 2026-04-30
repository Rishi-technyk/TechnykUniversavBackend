<?php
namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use DB;
use Log;
use Illuminate\Http\Request;
use App\Services\FCMService;
use Illuminate\Support\Facades\Validator;
use App\Services\HdfcGatewayService;
use Razorpay\Api\Errors\SignatureVerificationError;
use Carbon\Carbon;
use Razorpay\Api\Api;

class FacilitySlotsController extends Controller
{
    public function getFacilitySlots(Request $request, $id)
    {
       


        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

  
        $sessionId = $request->input('sessionId');

       
        // Fetch slots
        $query = DB::table("facility_slots")
             ->join("slots", "facility_slots.slot_id", "=", "slots.id")
        ->join("activity_sessions", "facility_slots.session_id", "=", "activity_sessions.id")
         ->leftJoin("game_booking_slots", function ($join) use ($fromDate, $toDate, $id) {
        $join->on("facility_slots.slot_id", "=", "game_booking_slots.slot_id")
             ->whereBetween("game_booking_slots.slot_date", [$fromDate, $toDate])
             ->where("game_booking_slots.status", 'Active')
             ->whereExists(function ($subQuery) use ($id) {
                 $subQuery->select(DB::raw(1))
                     ->from('game_bookings')
                     ->whereColumn('game_booking_slots.game_booking_id', 'game_bookings.id')
                     ->where('game_bookings.facility_id', $id);
             });
    })
        
        ->where("facility_slots.facility_id", $id);

        if (!empty($sessionId)) {
            $query->where("facility_slots.session_id", $sessionId);
        }

       // Original raw data (with duplicates due to join)
$slots = $query->select(
    "facility_slots.id as facility_slot_id",
    "facility_slots.session_id",
    "facility_slots.facility_id",
    "game_booking_slots.status as booking_status",
    "game_booking_slots.slot_date as booking_date",
     "game_booking_slots.slot_id as slot_id",
    "slots.*",
    'facility_slots.slot_id'
)->get();

// Grouping by facility_slot_id
$groupedSlots = $slots->groupBy('facility_slot_id')->map(function ($group) {
    $first = $group->first();

    // Collect all bookings for this slot
    $first->booking_data = $group->map(function ($item) {
        return [
            'slot_date' => $item->booking_date,
            'status' => $item->booking_status,
             'slot_id'=> $item->slot_id,
        ];
    })->filter(fn ($item) => $item['slot_date']); // filter nulls

    return $first;
})->sortBy('name')->values();

$dateOptions = [];
$start = \Carbon\Carbon::createFromFormat('Y-m-d', $fromDate);
$end = \Carbon\Carbon::createFromFormat('Y-m-d', $toDate);

        for ($date = $start; $date->lte($end); $date->addDay()) {
            $sessionDate = $date->format('Y-m-d');
            $dateOptions[] = [
                'id' => md5($sessionDate), // unique hash ID
                'session_date' => $sessionDate,
                'day' => $date->format('D'),
                'date' => $date->format('d'),
            ];
        }
        return response()->json([
            'success' => true,
            'data' => $groupedSlots,
            'dateOptions'=>$dateOptions]);

    }


public function getSessions(){
        $sessions = DB::table('activity_sessions')->get();
        return response()->json(['success'=> true,'data'=> $sessions]);
}
   public function getGuestInfo(Request $request, $id)
    {
        $search = $request->query('search');       // search by occupant name
        $type = $request->query('type');         // filter by occupant_id
        $limit = $request->query('limit', 10);    // default pagination size is 10

        Log::info('Fetching guest info', [
            'member_id' => $id,
            'search' => $search,
            'type' => $type,
            'limit' => $limit,
        ]);

        $query = DB::table('guest_infos')
            ->where('guest_infos.member_id', $id)
            ->join('occupant_masters', 'guest_infos.occupant_id', '=', 'occupant_masters.id')
            ->select('guest_infos.player_memberId as MemberID','guest_infos.email','guest_infos.mobile','guest_infos.occupant_id' ,'guest_infos.name as DisplayName', 'occupant_masters.name as occupant_type', 'occupant_masters.charge');

        if (!empty($search)) {
            $searchResult = DB::table('memberprofile')
                ->select('MemberID', 'DisplayName','Email as email','Mobile as mobile') ->where('role', 'Student');

            if (is_numeric($search)) {
                $searchResult->where('MemberID', 'LIKE', "%$search%");
            } else {
                $searchResult->where('DisplayName', 'LIKE', "%$search%");
            }

             $searchResult = $searchResult->limit(15)->get();
             \Log::info($searchResult);
            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $searchResult
                ]
            ]);
        }

        if (!empty($type)) {
            $query->where('guest_infos.occupant_id', $type);
        }

        $data = $query->paginate($limit);
       

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
 
    private function getUserById($member_id, $amount, $infoType = null,$subfix)
    {
        
        $user = DB::table('memberprofile')->where('MemberID', $member_id)->first();
        
        if (!$user) {
            return null;
        }


$txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20) . $subfix;

Log::info('Generated Transaction ID:', ['txnid' => $txnid]);
        return [
            // 'txnid' => substr(hash('sha256', mt_rand() . microtime()), 0, 20),
            'txnid' => substr(hash('sha256', mt_rand() . microtime()), 0, 20) . $subfix,
            'amount' => $amount,
            'firstname' => $user->DisplayName,
            'email' => $user->Email,
            'phone' => $user->Mobile ?: '9999999999',
            'productinfo' => $infoType ?? 'Test Product',
            'surl' => route('payment.success'),
            'furl' => route('payment.failed'),
            'udf1' => '',
            'udf2' => '',
        ];
    }

public function BookSlots(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'Member not authenticated'
        ], 401);
    }

    DB::beginTransaction();

    try {

        $slots = $request->slots;
        $facilityId = $request->facility_id;
        $AmountPayable = (int)$request->payble_amount;

        /* -------------------------------------------------
         🔹 CHECK SLOT AVAILABILITY (LOCK SAFE)
        ------------------------------------------------- */

        foreach ($slots as $slot) {

            $slotDate = $slot['day']['session_date'];
            $slotId   = $slot['time']['slot_id'];
            $label    = $slot['time']['label'];

            $facilityInventory = DB::table('facilities')
                ->where('id', $facilityId)
                ->lockForUpdate()
                ->value('inventory');

            $activeBookingsCount = DB::table('game_booking_slots as gbs')
                ->join('game_bookings as gb', 'gbs.game_booking_id', '=', 'gb.id')
                ->where('gb.facility_id', $facilityId)
                ->where('gbs.slot_id', $slotId)
                ->where('gbs.slot_date', $slotDate)
                ->where('gbs.status', 'Active')
                ->where('gb.status', 'Active')
                ->count();

            if ($activeBookingsCount >= $facilityInventory) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => "Slot on $slotDate at $label is fully booked."
                ], 409);
            }
        }

        /* -------------------------------------------------
         🔹 CREATE BOOKING
        ------------------------------------------------- */

        $booking_number = now()->format('dmY') . '-' . random_int(10000, 99999);

        $gameBookingId = DB::table('game_bookings')->insertGetId([
            'memberID' => $user->MemberID,
            'booking_number' => $booking_number,
            'facility_id' => $facilityId,
            'session_id' => $request->session_id,
            'game_type_id' => $request->game_type_id,
            'facility_amount' => $request->charge,
            'guest_total_amount' => $request->guest_total_amount,
            'facility_gst_per' => $request->facility_gst_per,
            'facility_gst_amt' => $request->facility_gst_amt,
            'facility_total' => $AmountPayable,
            'status' => 'Pending',
            'payment_status' => 'Not Paid',
            'chartID' => $user->SC_ID,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        /* -------------------------------------------------
         🔹 INSERT SLOTS & PLAYERS
        ------------------------------------------------- */

        foreach ($slots as $slot) {

            $slotDate = $slot['day']['session_date'];
            $slotId   = $slot['time']['slot_id'];

            $slotBookingId = DB::table('game_booking_slots')->insertGetId([
                'game_booking_id' => $gameBookingId,
                'slot_id' => $slotId,
                'slot_date' => $slotDate,
                'status' => 'Pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $players = [];

            foreach ($slot['players'] as $player) {
                $players[] = [
                    'game_booking_id' => $gameBookingId,
                    'game_booking_slot_id' => $slotBookingId,
                    'slot_id' => $slotId,
                    'slot_date' => $slotDate,
                    'player_name' => $player['DisplayName'],
                    'player_mobile' => $player['mobile'] ?? 'NA',
                    'player_email' => $player['email'] ?? 'NA',
                    'occupant_id' => $player['occupant_id'],
                    'occupant_charge' => $player['charge'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            DB::table('game_booking_guests')->insert($players);
        }

        $payment = app(\App\Services\Payments\PaymentTransactionService::class)->initiate(
            $user,
            (float) $AmountPayable,
            \App\Support\Payments\PaymentModule::FACILITY_BOOKING,
            $gameBookingId,
            [
                'type' => 'Facility Booking',
                'prefix' => 'ACB',
            ]
        );

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Payment initiated successfully.',
            'data' => [
                'order_id' => $payment['order_id'],
                'amount' => $AmountPayable,
                'merchant_order_id' => $payment['merchant_order_id'],
                'status_reference' => $payment['status_reference'] ?? $payment['merchant_order_id'],
                'status_endpoint' => $payment['status_endpoint'] ?? null,
                'gateway' => $payment['gateway'] ?? null,
                'checkout' => $payment['checkout'] ?? null,
                'payment_url' => $payment['payment_url'] ?? null,
                'access_key' => $payment['access_key'] ?? null,
                'razorpayKey' => $payment['razorpayKey'] ?? data_get($payment, 'checkout.key'),
                'end_point' => 'member/process_activity_payment'
            ]
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        \Log::error('Activity booking error', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'status' => false,
            'message' => 'Unable to process booking'
        ], 500);
    }
}
public function processActivityPayment(Request $request, FCMService $fcm)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Member not authenticated'
        ], 401);
    }

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

        // 🛑 duplicate protection
        if ($transaction->payment_status === 'Paid') {
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Already processed'
            ]);
        }

        $isCentralized = !empty($transaction->gateway_slug) || !empty($transaction->payment_status_code);

        if ($isCentralized) {
            if (
                !$transaction->payment_status_code
                || !\App\Support\Payments\PaymentStatus::isSuccessful($transaction->payment_status_code)
            ) {
                $result = $request->filled('razorpay_payment_id')
                    ? app(\App\Services\Payments\PaymentTransactionService::class)->verify($user, $request->all())
                    : [
                        'success' => strcasecmp((string) $transaction->payment_status, 'Paid') === 0,
                        'data' => [
                            'MemberName' => $user->DisplayName ?? '',
                            'MemberID' => $user->MemberID ?? '',
                            'MemberSCID' => $user->SC_ID ?? '',
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
                        'MemberName' => $user->DisplayName ?? '',
                        'MemberID' => $user->MemberID ?? '',
                        'MemberSCID' => $user->SC_ID ?? '',
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
            $request->validate([
                'razorpay_order_id'   => 'required|string',
                'razorpay_payment_id' => 'required|string',
                'razorpay_signature'  => 'nullable|string',
            ]);

            $result = \App\Helpers\PaymentHelper::verifyPayment($request);
        }

        if (!$result['success']) {
            throw new \Exception('Payment verification failed');
        }

        /* ✅ UPDATE BOOKING */
        if (!$isCentralized) {
            DB::table('game_bookings')
                ->where('id', $transaction->game_booking_id)
                ->update([
                    'payment_status' => 'Paid',
                    'status' => 'Active',
                    'updated_at' => now()
                ]);

            DB::table('game_booking_slots')
                ->where('game_booking_id', $transaction->game_booking_id)
                ->update([
                    'status' => 'Active',
                    'updated_at' => now()
                ]);
        }

        DB::commit();

        /* 🔔 Notification */
        if ($user->device_id) {
            $fcm->sendNotification(
                $user->device_id,
                'Facility Booking Confirmed',
                'Your booking has been confirmed!',
                [
                    'bookingId' => $transaction->game_booking_id,
                    'screen' => 'ActivityBookingDetail'
                ]
            );
        }

        return response()->json($result);

    } catch (\Exception $e) {

        DB::rollBack();

        Log::error('Activity payment failed', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Payment verification failed'
        ], 400);
    }
}


public function GetBookingDetails(Request $request)
{
    $search = $request->query('search');
    $type = $request->query('type');
    $limit = $request->query('limit', 10);
    $filterStatus = $request->query('status');
    $memberID = auth()->user()->MemberID;

    /* -------------------------------------------------
       1️⃣ FETCH BOOKINGS
    -------------------------------------------------*/

    $query = DB::table('game_bookings')
        ->join('facilities', 'game_bookings.facility_id', '=', 'facilities.id')
        ->where('game_bookings.memberID', $memberID)
        ->select(
            'game_bookings.id',
            'game_bookings.booking_number',
            'game_bookings.facility_total',
            'game_bookings.payment_status',
            'game_bookings.status',
            'game_bookings.created_at as booking_date',
            'facilities.name as facility_name',
            'facilities.third_image as facility_image'
        );

    // 🔍 search
    if (!empty($search)) {
        $query->where(function($q) use ($search) {
            $q->where('game_bookings.booking_number','LIKE',"%$search%")
              ->orWhere('game_bookings.memberID','LIKE',"%$search%");
        });
    }

    // 🎯 filter by facility
    if (!empty($type)) {
        $query->where('game_bookings.facility_id', $type);
    }

    // 🎯 filter by status
    if (!empty($filterStatus)) {
        $query->where('game_bookings.status', $filterStatus);
    }

    $bookings = $query
        ->orderBy('game_bookings.created_at','desc')
        ->paginate($limit);

    $bookingIds = $bookings->pluck('id')->toArray();

    /* -------------------------------------------------
       2️⃣ STATUS AUTO UPDATE (ONLY THESE RECORDS)
    -------------------------------------------------*/

    if (!empty($bookingIds)) {

        // Cancelled if all slots cancelled
        DB::table('game_bookings')
            ->whereIn('id', $bookingIds)
            ->whereNotIn('status',['Cancelled'])
            ->whereExists(function ($q){
                $q->select(DB::raw(1))
                  ->from('game_booking_slots')
                  ->whereColumn('game_booking_slots.game_booking_id','game_bookings.id');
            })
            ->whereNotExists(function ($q){
                $q->select(DB::raw(1))
                  ->from('game_booking_slots')
                  ->whereColumn('game_booking_slots.game_booking_id','game_bookings.id')
                  ->where('status','!=','Cancelled');
            })
            ->update(['status'=>'Cancelled']);

        // Completed if no future or pending slots
        DB::table('game_bookings')
            ->whereIn('id', $bookingIds)
            ->whereNotIn('status',['Completed'])
            ->whereNotExists(function ($q){
                $q->select(DB::raw(1))
                  ->from('game_booking_slots')
                  ->whereColumn('game_booking_slots.game_booking_id','game_bookings.id')
                  ->where(function($sub){
                      $sub->whereDate('slot_date','>=',now())
                          ->orWhere('status','Pending');
                  });
            })
            ->update(['status'=>'Completed']);
    }

    /* -------------------------------------------------
       3️⃣ SLOT COUNT OPTIMIZED (NO N+1)
    -------------------------------------------------*/

    $slotCounts = DB::table('game_booking_slots')
        ->whereIn('game_booking_id', $bookingIds)
        ->select('game_booking_id', DB::raw('COUNT(*) as total'))
        ->groupBy('game_booking_id')
        ->pluck('total','game_booking_id');

    $bookings->getCollection()->transform(function($booking) use ($slotCounts){
        $booking->slots = $slotCounts[$booking->id] ?? 0;
        return $booking;
    });

    /* -------------------------------------------------
       4️⃣ UPCOMING BOOKINGS
    -------------------------------------------------*/

    $upcoming = DB::table('game_bookings')
        ->where('memberID',$memberID)
        ->where('status','Active')
        ->whereDate('created_at','>=',now()->subDays(1))
        ->orderBy('created_at','asc')
        ->limit(5)
        ->get()
        ->map(function($item){
            return [
                'id'=>$item->id,
                'title'=>$item->booking_number,
                'date'=>$item->created_at,
                'status'=>$item->status,
            ];
        });

    /* -------------------------------------------------
       5️⃣ RESPONSE
    -------------------------------------------------*/

    return response()->json([
        'status' => true,
        'data' => $bookings,
        'upcoming_bookings' => $upcoming
    ]);
}

// public function GetBookingDetails(Request $request)
// {
//     $search = $request->query('search');
//     $type = $request->query('type');
//     $limit = $request->query('limit', 10);
//     $filterStatus = $request->query('status');
//     $id=auth()->user()->MemberID;

//     // ✅ STEP 1: Fetch bookings first (filtered)
//     $query = DB::table('game_bookings')
//         ->join('facilities', 'game_bookings.facility_id', '=', 'facilities.id')
//         ->where('game_bookings.memberID', $id)
//         ->select(
//             'game_bookings.id',
//             'game_bookings.booking_number',
//             'game_bookings.facility_id',
//             'game_bookings.facility_amount',
//             'game_bookings.facility_gst_per',
//             'game_bookings.facility_gst_amt',
//             'game_bookings.facility_total',
//             'game_bookings.guest_total_amount',
//             'game_bookings.payment_status',
//             'game_bookings.status',
//             'game_bookings.created_at as booking_date',
//             'game_bookings.session_id',
//             'facilities.name as facility_name',
//             'facilities.third_image as facility_image'
//         );

//     if (!empty($search)) {
//         if (is_numeric($search)) {
//             $query->where('game_bookings.booking_number', 'LIKE', "%$search%");
//         } else {
//             $query->where('game_bookings.memberID', 'LIKE', "%$search%");
//         }
//     }

//     if (!empty($type)) {
//         $query->where('game_bookings.facility_id', $type);
//     }

//     if (!empty($filterStatus)) {
//         $query->where('game_bookings.status', $filterStatus);
//     }

//     $bookings = $query->orderBy('game_bookings.created_at', 'desc')->paginate($limit);

//     // ✅ STEP 2: Only update statuses for these bookings
//     $bookingIds = $bookings->pluck('id')->toArray();

//     if (!empty($bookingIds)) {
//         // 2a. Mark as Cancelled if all slots are Cancelled
//         DB::table('game_bookings')
//             ->whereIn('id', $bookingIds)
//             ->whereNotIn('status', ['Cancelled'])
//             ->whereExists(function ($query) {
//                 $query->select(DB::raw(1))
//                     ->from('game_booking_slots')
//                     ->whereColumn('game_booking_slots.game_booking_id', 'game_bookings.id');
//             })
//             ->whereNotExists(function ($query) {
//                 $query->select(DB::raw(1))
//                     ->from('game_booking_slots')
//                     ->whereColumn('game_booking_slots.game_booking_id', 'game_bookings.id')
//                     ->where('status', '!=', 'Cancelled');
//             })
//             ->update(['status' => 'Cancelled']);

//         // 2b. Mark as Completed if no future or pending slots
//         DB::table('game_bookings')
//             ->whereIn('id', $bookingIds)
//             ->whereNotIn('status', ['Completed'])
//             ->whereNotExists(function ($query) {
//                 $query->select(DB::raw(1))
//                     ->from('game_booking_slots')
//                     ->whereColumn('game_booking_slots.game_booking_id', 'game_bookings.id')
//                     ->where(function ($subQuery) {
//                         $subQuery->whereDate('slot_date', '>=', now())
//                                  ->orWhere('status', 'Pending');
//                     });
//             })
//             ->update(['status' => 'Completed']);
//     }

//     // ✅ STEP 3: Attach slots + guests
//     $bookings->getCollection()->transform(function ($booking) {
//         $slots = DB::table('game_booking_slots')
//             ->join('slots', 'game_booking_slots.slot_id', '=', 'slots.id')
//             ->where('game_booking_slots.game_booking_id', $booking->id)
//             ->select(
//                 'game_booking_slots.id as slot_id',
//                 'game_booking_slots.slot_date',
//                 'slots.label',
//                 'slots.name',
//                 'game_booking_slots.status',
//                 'game_booking_slots.cancellation_deducation',
//                 'game_booking_slots.cancellation_date'
//             )
//             ->get();

//         $booking->slots = $slots->count();
//         return $booking;
//     });

//     return response()->json([
//         'success' => true,
//         'data' => $bookings
//     ]);
// }
public function GetBookingDetailById($id)
{
    $memberId = auth()->user()->MemberID;

    $booking = DB::table('game_bookings')
        ->join('facilities', 'game_bookings.facility_id', '=', 'facilities.id')
        ->where('game_bookings.id', $id)
        ->where('game_bookings.memberID', $memberId)
        ->select(
            'game_bookings.*',
            'facilities.name as facility_name',
            'facilities.third_image as facility_image'
        )
        ->first();

    if (!$booking) {
        return response()->json([
            'success' => false,
            'message' => 'Booking not found'
        ], 404);
    }

    // fetch slots
    $slots = DB::table('game_booking_slots')
        ->join('slots', 'game_booking_slots.slot_id', '=', 'slots.id')
        ->where('game_booking_slots.game_booking_id', $booking->id)
        ->select(
            'game_booking_slots.id',
            'game_booking_slots.slot_date',
            'slots.label',
            'game_booking_slots.status',
            'game_booking_slots.cancellation_deducation',
            'game_booking_slots.cancellation_amt',
            'game_booking_slots.cancellation_date'
        )
        ->get()
        ->map(function ($slot) {

            $slot->guests = DB::table('game_booking_guests')
                ->where('game_booking_slot_id', $slot->id)
                ->select(
                    'player_name',
                    'player_mobile',
                    'occupant_charge'
                )
                ->get();

            return $slot;
        });

    $booking->slots = $slots;
    $payment = DB::table('transactions')
        ->where('game_booking_id', $booking->id)
        ->latest('id')
        ->first();
    $guestCount = $slots->sum(fn ($slot) => $slot->guests->count());
    $slotCount = $slots->count();

    $booking->summary = [
        'booking_number' => $booking->booking_number,
        'status' => $booking->status,
        'payment_status' => $booking->payment_status,
        'slot_count' => $slotCount,
        'guest_count' => $guestCount,
        'total_amount' => round((float) $booking->facility_total, 2),
        'booked_on' => Carbon::parse($booking->created_at)->format('d M Y, h:i A'),
    ];
    $booking->payment = [
        'status' => $payment->payment_status ?? $booking->payment_status,
        'status_code' => $payment->payment_status_code ?? null,
        'gateway_name' => $payment->gateway_name ?? null,
        'gateway_order_id' => $payment->gateway_order_id ?? $payment->transID ?? null,
        'reference_number' => $payment->gateway_transaction_id ?? $payment->bank_refrance_no ?? null,
        'transaction_date' => optional($payment?->transaction_date)->format('d M Y, h:i A'),
        'processed_at' => optional($payment?->processed_at)->format('d M Y, h:i A'),
        'amount' => round((float) ($payment->amount ?? $booking->facility_total), 2),
    ];
    $booking->invoice = [
        'currency' => 'INR',
        'facility_amount' => round((float) $booking->facility_amount, 2),
        'guest_total_amount' => round((float) $booking->guest_total_amount, 2),
        'gst_total' => round((float) $booking->facility_gst_amt, 2),
        'total' => round((float) $booking->facility_total, 2),
        'items' => $slots,
    ];
    $booking->timeline = [
        [
            'title' => 'Activity booking created',
            'description' => 'Booking request was recorded for the selected facility.',
            'time' => Carbon::parse($booking->created_at)->format('d M Y, h:i A'),
            'tone' => 'neutral',
        ],
        [
            'title' => 'Payment status',
            'description' => $booking->payment_status,
            'time' => optional($payment?->processed_at ?? $payment?->transaction_date)->format('d M Y, h:i A'),
            'tone' => strtolower((string) $booking->payment_status) === 'paid' ? 'success' : 'warning',
        ],
    ];
    $booking->payment_timeline = [
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
    $booking->rules = [
        'Slots are confirmed only after payment success and inventory validation.',
        'Cancellation deductions are applied according to the facility cancellation policy.',
        'Player updates should be completed before the facility session starts.',
    ];
    $booking->support = [
        'name' => 'Activity Desk',
        'phone' => null,
        'email' => null,
        'note' => 'For slot changes, cancellations, or refund queries, contact the activity desk.',
    ];
    $booking->activity_logs = $slots->map(function ($slot) {
        return [
            'title' => 'Slot ' . $slot->label,
            'description' => 'Players: ' . $slot->guests->pluck('player_name')->implode(', '),
            'time' => Carbon::parse($slot->slot_date)->format('d M Y'),
            'tone' => strtolower((string) $slot->status) === 'cancelled' ? 'danger' : 'success',
        ];
    })->values();

    return response()->json([
        'success' => true,
        'data' => $booking
    ]);
}

public function CancelBookingAmount(Request $request, $id)
{
    $booking = DB::table('game_booking_slots')
        ->join('game_bookings', 'game_booking_slots.game_booking_id', '=', 'game_bookings.id')
        ->where('game_booking_slots.id', $id)
        ->select('game_bookings.*')
        ->first();

    if (!$booking) {
        return response()->json([
            'status' => false,
            'message' => 'Booking not found.'
        ], 404);
    }

    // Parse booking date
    $createdAt = $booking->created_at ? Carbon::parse($booking->created_at) : now();
    $now = now();
    $daysSinceBooking = $createdAt->diffInDays($now);

    // Fetch all policies for the facility
    $policies = DB::table('activity_cancellation_policies')
        ->where('facility_id', $booking->facility_id)
        ->get();

    if ($policies->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'Cancellation policy not configured.'
        ], 404);
    }

    // Match correct policy
    $matchedPolicy = $policies->first(function ($policy) use ($daysSinceBooking) {
        return $daysSinceBooking >= $policy->from_days && $daysSinceBooking <= $policy->to_days;
    });

    if (!$matchedPolicy) {
        return response()->json([
            'status' => false,
            'message' => 'No cancellation allowed at this point as per policy.'
        ], 403);
    }

    // Combine policy with booking info
    $cancelationAmount = (object) array_merge(
        (array) $booking,
        (array) $matchedPolicy
    );

    \Log::info('Cancellation check', [
        'cancelationAmount' => $cancelationAmount
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Booking is eligible for cancellation.',
        'cancelationAmount' => $cancelationAmount
    ]);
}



    public function CancelBooking(Request $request )
{
    $validator = Validator::make($request->all(), [
        'booking_id' => 'required',
        'cancellation_per'=> 'required',
        'cancellation_amt' => 'required',
        'cancellation_GST' => 'required',
        'cancellation_GST_amt' => 'required',
        'cancellation_deducation' => 'required',
        
    ]);
    Log::info($request->all());
    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors()
        ], 422);
    }

    $bookingId = $request->input('booking_id');
    $cancellation_per = $request->input('cancellation_per');
    $cancellation_GST = $request->input('cancellation_GST');
    $cancellation_GST_amt = $request->input('cancellation_GST_amt');
    $cancellation_deducation = $request->input('cancellation_deducation');
    $cancellation_amt = $request->input('cancellation_amt');


    // Check if the booking exists and is active
    $booking = DB::table('game_booking_slots')->where('id', $bookingId)->first();

    if (!$booking) {
        return response()->json([
            'status' => false,
            'message' => 'Booking not found.'
        ], 404);
    }

    // Update the booking status to "Cancelled"
   
    DB::table('game_booking_slots')->where('id', $bookingId)->update([
        'status' => 'Cancelled',
        'cancellation_amt' => $cancellation_amt,
        'cancellation_per'=> $cancellation_per,
        'cancellation_GST'=> $cancellation_GST,
        'cancellation_GST_amt'=> $cancellation_GST_amt,
        'cancellation_deducation'=> $cancellation_deducation,
        'cancellation_date' => now(),
        'updated_at' => now()
    ]);
    return response()->json([
        'status' => true,
        'message' => 'Booking cancelled successfully.'
    ]);

}

}
