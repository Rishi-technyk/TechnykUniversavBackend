<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\Participant;
use Illuminate\Http\Request;
use App\Models\TicketBooking;
use App\Models\Member;
use Carbon\Carbon;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\EventSeatingLayout;
use App\Models\Seat;
use Illuminate\Support\Str;


class EventController extends Controller
{
    
    public function getEvents (Request $request){
        
        $user=auth()->user()->id;
       
        if(!$user){
              return response()->json([
               'status' => false,
            'message' => 'user not found.',
        ], 404);
        }
          $events = Event::with('ticketTypes')->where('status', 'active')
          ->orderBy('event_date', 'asc')
          ->select(['id','name','image','location','event_date','booking_end_at','booking_start_at'])->get();
          
        $events = $events->map(function ($event) {
        $event->image = url('teebooking/public/event/' . $event->image);
        return $event;
    });
        
          
           return response()->json([
            'status' => true,
            'data' => $events,
            'message'=>'Events fetched successfully.'
        ], 200);
    }
    
        public function getBanner (Request $request){
        
        $user=auth()->user()->id;
       
        if(!$user){
              return response()->json([
               'status' => false,
            'message' => 'user not found.',
        ], 404);
        }
         $event = Event::orderBy('event_date','desc')->where('status','active')->select(['id','name','banner'])->first();
         if($event->id){
               return response()->json([
            'status' => true,
            'data' => [
                'image'=>url('teebooking/public/banners/'.$event->banner),
                'navigation'=>'Event',
                'data'=>$event->id
                ],
            'message'=>'Events fetched successfully.'
        ], 200);
         }else{
               return response()->json([
            'status' => false,
            
            'message'=>'No event found.'
        ], 400);
         }
         
    }
    
    
public function show(Request $request,$id)
{
    $event = Event::with(['ticketTypes', 'waiter'])
        ->active()
        ->findOrFail($id);
        $memberId=0;
        $Role= auth()->user()->role;
     $ExsistingId=$request->query('memberid');
if($request->query('memberid')){
    $memberId = $request->query('memberid');
}else{
    $memberId= auth()->id();
}

    /** 🔹 Already booked tickets by this member for this event */
    $memberBookedCount = Participant::whereHas('booking', function ($q) use ($id,$memberId) {
        $q->where('event_id', $id)
          ->where('member_id', $memberId);
    })->count();

    /** 🔹 Ticket-type specific checks */
    $hasMemberTicket = Participant::where('ticket_type', 'member')
        ->whereHas('booking', function ($q) use ($id,$memberId) {
            $q->where('event_id', $id)
              ->where('member_id', $memberId);
        })
        ->exists();
    
$hasDeopendetTicket=Participant::where('ticket_type', 'dependent')
        ->whereHas('booking', function ($q) use ($id,$memberId) {
            $q->where('event_id', $id)
              ->where('member_id', $memberId);
        })
        ->exists();
        
    $hasSpouseTicket = Participant::where('ticket_type', 'spouse')
        ->whereHas('booking', function ($q) use ($id,$memberId) {
            $q->where('event_id', $id)
              ->where('member_id', $memberId);
        })
        ->count();
         $hasGuestTicket = Participant::where('ticket_type', 'guest')
        ->whereHas('booking', function ($q) use ($id,$memberId) {
            $q->where('event_id', $id)
              ->where('member_id',$memberId);
        })
        ->count();
    /** 🔹 Event capacity */
    $eventBookedCount = Participant::whereHas('booking', function ($q) use ($id) {
        $q->where('event_id', $id);
    })->count();

    $eventSoldOut = $eventBookedCount >= $event->max_tickets;

    /** 🔹 Remaining tickets allowed for this member (GLOBAL LIMIT) */
    $remainingForMember = max(
        0,
        $event->max_per_member_tickets - $memberBookedCount
    );


    $tickets = $event->ticketTypes->map(function ($ticket) use (
        $eventSoldOut,
        $hasMemberTicket,
        $hasSpouseTicket,
        $remainingForMember,
        $hasDeopendetTicket,
        $hasGuestTicket,
        $memberId,
        $Role,
        $ExsistingId,
        $event
    ) {
   if ($Role == 'Event Admin' &&$ExsistingId == auth()->id() && $ticket->type !== 'vip'&& $ticket->type !== 'guest') {
        return [
            'id'        => $ticket->id,
            
            'type'      => $ticket->type,
            'title'     => ucfirst($ticket->name) . ' Ticket',
            'price'     => (float) $ticket->amount,
            'max'       => 0,            
            'sold_out'  => true, 
        ];
    }
    
    
        $max = $ticket->max_per_member;

        /** 🔐 Per-ticket rules */
        if ($ticket->type === 'member' && $hasMemberTicket) {
            $max = 0;
            
        }

        if ($ticket->type === 'spouse' && $hasSpouseTicket ) {
            $max = 0;
        }

        if ($ticket->type === 'vip'  && $Role == 'Event Admin') {
            $max = 250;
        }
         if ($ticket->type === 'guest'  && $Role == 'Event Admin') {
            $max = 20;
        }
        
        $price= (float) $ticket->amount;
     if (in_array($ticket->type, ['member', 'spouse'])) {
    $price = $this->isComplimentary($ticket->type,$event->complimentary_age) ? 0 :(float) $ticket->amount;
}
        /** 🔐 Global per-member limit */
        $max = min($max, $remainingForMember);
        return [
            'id'              => $ticket->id,
            'type'            => $ticket->type,
            'title'           => ucfirst($ticket->name) . ' Ticket',
            'description'     => $this->getDescription($ticket->type),
            'price'           => $price,
            'max'             => $eventSoldOut ? 0 : $max,
            'sold_out'        => $eventSoldOut || $max === 0,
            'required_fields' => $ticket->required_fields,
        ];
    });
    
    
$waitersBooked = DB::table('event_waiter_bookings')
    ->where('event_id', $id)
    ->where('member_id',$memberId)
->sum('quantity');
$maxWaiters = $event->waiter?->max_waiters_per_member ?? 0;
$remainingWaiters = max(0, $maxWaiters - $waitersBooked);

$seatCounts = [];

$layoutsExist = EventSeatingLayout::where('event_id',$id)->exists();

if ($layoutsExist) {

  $seatCounts = Seat::join('seat_categories','seats.category_id','=','seat_categories.id')
    ->where('seats.event_id',$id)
    ->select(
        DB::raw('LOWER(seat_categories.seat_type) as type'),
        DB::raw('COUNT(*) as total')
    )
    ->groupBy('type')
    ->pluck('total','type');
}

return response()->json([
    'status' => true,
    'event' => [
        'id'=>$event->id,
        'name' => $event->name,
        'date' => $event->event_date->format('Y-m-d H:i'),
        'location' => $event->location,
        'gst' => $event->gst,
        'service_charge' => $event->service_charge,
            'max_per_member_tickets' => $event->max_per_member_tickets,
            'remaining_for_member'   => $remainingForMember,
            'image'                  => url('teebooking/public/event/'.$event->image),
        // 🔥 NEW
        'waiter' => $event->waiter ? [
            'price' => (float) $event->waiter->waiter_cost,
            'max_per_member' => $event->waiter->max_waiters_per_member,
            'remaining' => $remainingWaiters,
        ] : null,
    ],
    'tickets' => $tickets,
'is_seat_booking_available' => $layoutsExist,
'seat_counts' => $seatCounts,
]);

}

private function isComplimentary(string $ticketType,int $age): bool
{
    $user = auth()->user();
    
$member_complimentary_age =$age;
    // Member age ≥ 60
    if ($ticketType === 'member' && $user->DOB && $age) {
        return Carbon::parse($user->DOB)->age >= $member_complimentary_age;
        // return true;
    }
  if ($ticketType === 'spouse' && $user->DOB  && $age) {
        return Carbon::parse($user->DOB)->age >= $member_complimentary_age;
        //   return true;
    }
    return false; // guests, dependents → never free
}

public function preview(Request $request)
{
    $request->validate([
        'event_id' => 'required|integer',
        'tickets' => 'nullable|array',
        'waiters' => 'required_without:tickets|integer',
    'seatGroups' => 'nullable|array',
    'seatGroups.*.seats' => 'nullable|array'
    ]);
$seatIds = collect($request->seatGroups ?? [])
    ->pluck('seats')
    ->flatten(1)
    ->pluck('id')
    ->toArray();
    $event = Event::with(['ticketTypes', 'waiter'])
        ->findOrFail($request->event_id);

    /** 🔹 1. Already booked tickets */
    $alreadyBooked = Participant::whereHas('booking', function ($q) use ($event) {
        $q->where('event_id', $event->id)
          ->where('member_id', auth()->id());
    })->count();

    /** 🔹 2. Already booked waiters */
    $alreadyBookedWaiters = DB::table('event_waiter_bookings')
        ->where('event_id', $event->id)
        ->where('member_id', auth()->id())
        ->sum('quantity');
        
if (!empty($seatIds)) {

    $alreadyBookedSeats = DB::table('event_booking_seats')
        ->whereIn('seat_id', $seatIds)
        ->exists();

    if ($alreadyBookedSeats) {
        return response()->json([
            'status' => false,
            'message' => 'One or more seats already booked'
        ], 422);
    }
}
    /** 🔹 3. Requested ticket count */
    $requestedCount = collect($request->tickets)->sum('quantity');

    if ($alreadyBooked + $requestedCount > $event->max_per_member_tickets) {
        return response()->json([
            'status' => false,
            'message' => "You can book maximum {$event->max_per_member_tickets} tickets",
            'allowed' => max(0, $event->max_per_member_tickets - $alreadyBooked),
        ], 422);
    }

    /** 🔹 4. Validate waiters */
    $requestedWaiters = (int) ($request->waiters ?? 0);

    if ($requestedWaiters > 0 && !$event->waiter) {
        return response()->json([
            'status' => false,
            'message' => 'Waiter service not available',
        ], 422);
    }

    if (
        $requestedWaiters > 0 &&
        ($alreadyBookedWaiters + $requestedWaiters) > $event->waiter->max_waiters_per_member
    ) {
        return response()->json([
            'status' => false,
            'message' => "Maximum {$event->waiter->max_waiters_per_member} waiters allowed",
        ], 422);
    }

$ticketSubtotal = 0;

if (!empty($request->tickets)) {
    foreach ($request->tickets as $t) {
        $ticketType = $event->ticketTypes->firstWhere('id', $t['id']);
        if (!$ticketType) {
            return response()->json(['status' => false, 'message' => 'Invalid ticket'], 400);
        }

        $isFree = $this->isComplimentary($ticketType->type,$event->complimentary_age);
        $price  = $isFree ? 0 : $ticketType->amount;

        $ticketSubtotal += $price * $t['quantity'];
    }
}


$seatSubtotal = 0;

if (!empty($seatIds)) {

    $seats = Seat::with('category')
        ->whereIn('id', $seatIds)
        ->get();

    foreach ($seats as $seat) {

        $price = $seat->category->price ?? 0;

        // complimentary check
        if (
            $seat->category->seat_type == 'complimentary' &&
            !$this->isComplimentary('member', $event->complimentary_age)
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Complimentary seats not allowed'
            ], 422);
        }

        $seatSubtotal += $price;
    }
}
/** 🔹 6. Waiter subtotal */
$waiterSubtotal = 0;
if ($event->waiter && $requestedWaiters > 0) {
    $waiterSubtotal = $event->waiter->waiter_cost * $requestedWaiters;
}

/** 🔹 7. Taxable amount */
$taxableAmount = $ticketSubtotal + $waiterSubtotal+$seatSubtotal;

/** 🔹 8. Taxes */
$serviceCharge = ($taxableAmount * ($event->service_charge ?? 0)) / 100;
$gst = ($taxableAmount * ($event->gst ?? 0)) / 100;
$total = round($taxableAmount + $serviceCharge + $gst, 2);


    /** 🔹 8. Razorpay order only if amount > 0 */
//     $orderId = null;
//     $key = null;
    $member=auth()->user();
//     $txnid='EVT_' . time();
//     if ($total > 0) {
//         $key = config('services.razorpay.key');
//         $razorpay = new Api($key, config('services.razorpay.secret'));
//           $notes = [
//     'member_id'      => $user->MemberID,
//     'sc_id'          => $user->SC_ID,
//     'type'   => 'Event Booking',
//     'member_name'    => $user->DisplayName,
//     'member_email'   => $user->Email,
//     'member_mobile'  =>$user->Mobile,
//     'transaction_id'     => $txnid,
//     'total_amount'   =>$total,
// ];

//         $order = $razorpay->order->create([
//             'receipt' => $txnid,
//             'amount'  => (int) ($total * 100),
//             'currency'=> 'INR',
//             'notes' =>$notes
//         ]);

//         $orderId = $order['id'];
//     }
if($total == 0){
    return response()->json([
    'status' => true,
    'ticket_amount' => $ticketSubtotal,
    'seat_amount' => $seatSubtotal,
    'waiter_amount' => $waiterSubtotal,
    'amount' => $total,
    'is_free' => $total == 0,
    // 'order_id' => $orderId,
    //  'payment_session_id' => $data['payment_session_id'],
    'Environment'=>env('CASHFREE_ENV'),
    'remaining' => max(0, $event->max_per_member_tickets - $alreadyBooked),
]);
}

$payment = app(\App\Services\Payments\PaymentTransactionService::class)->initiate(
    $member,
    (float) $total,
    \App\Support\Payments\PaymentModule::EVENT_BOOKING,
    null,
    [
        'type' => 'Event Booking',
        'prefix' => 'EVT',
    ]
);

$payment['end_point'] = 'member/tickets/process-payment';
return response()->json([
    'status' => true,
    'ticket_amount' => $ticketSubtotal,
    'seat_amount' => $seatSubtotal,
    'waiter_amount' => $waiterSubtotal,
    'amount' => $total,
    'is_free' => $total == 0,
    'order_id' => $payment['order_id'],
    'merchant_order_id' => $payment['merchant_order_id'],
    'payment_session_id' => $payment['access_key'] ?? null,
    'razorpay_key' => $payment['razorpayKey'] ?? data_get($payment, 'checkout.key'),
    'gateway' => $payment['gateway'] ?? null,
    'checkout' => $payment['checkout'] ?? null,
    'payment' => $payment,
    'Environment'=>data_get($payment, 'gateway.environment', env('CASHFREE_ENV')),
    'remaining' => max(0, $event->max_per_member_tickets - $alreadyBooked),
]);

}

public function processPayment(Request $request)
{
    $request->validate([
        'event_id' => 'required|integer',
        'tickets' => 'nullable|array',
        'waiters' => 'required_without:tickets|integer',
        'is_free'  => 'required|boolean',
        'order_id' => 'required_if:is_free,false|nullable|string',
        'merchant_order_id' => 'nullable|string',
        'status_reference' => 'nullable|string',
        'seatGroups' => 'nullable|array',
        'seatGroups.*.seats' => 'nullable|array',
    ]);

    DB::beginTransaction();

    try {
        $event = Event::with(['ticketTypes', 'waiter'])->findOrFail($request->event_id);

        $data = [];
        $orderPaid = false;
        $orderReference = $request->merchant_order_id
            ?? $request->status_reference
            ?? $request->order_id;

        if (!$request->is_free) {
            $transaction = DB::table('transactions')
                ->where(function ($query) use ($orderReference, $request) {
                    $query->where('order_id', $orderReference)
                        ->orWhere('gateway_order_id', $request->order_id)
                        ->orWhere('transID', $request->order_id);
                })
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                throw new \Exception('Transaction not found');
            }

            $isCentralized = !empty($transaction->gateway_slug) || !empty($transaction->payment_status_code);

            if ($isCentralized) {
                if (
                    !$transaction->payment_status_code
                    || !\App\Support\Payments\PaymentStatus::isSuccessful($transaction->payment_status_code)
                ) {
                    $verification = app(\App\Services\Payments\PaymentTransactionService::class)
                        ->verify(auth()->user(), [
                            'merchant_order_id' => $transaction->order_id,
                            'gateway_order_id' => $transaction->gateway_order_id,
                            'order_id' => $request->order_id,
                        ]);
                    $orderPaid = (bool) ($verification['success'] ?? false);
                } else {
                    $orderPaid = true;
                }

                $transaction = DB::table('transactions')->where('id', $transaction->id)->first();
                $data = [
                    'customer_name' => auth()->user()->DisplayName,
                    'customer_phone' => auth()->user()->Mobile,
                ];
            } else {
                $orderId = $request->order_id;
                $url = env('CASHFREE_BASE_URL') . '/orders/' . $orderId;

                $headers = [
                    'x-client-id' => env('CASHFREE_APP_ID'),
                    'x-client-secret' => env('CASHFREE_SECRET_KEY'),
                    'x-api-version' => '2025-01-01',
                    'Content-Type' => 'application/json',
                ];

                $response = \Http::withHeaders($headers)->get($url);
                \Log::info("VERIFY RESPONSE: " . $response->body());

                $data = $response->json();
                $orderStatus = $data['order_status'] ?? null;

                if ($orderStatus === 'PAID') {
                    $orderPaid = true;

                    DB::table('transactions')
                        ->where('id', $transaction->id)
                        ->update([
                            'payment_status' => 'Paid',
                            'transaction_date' => now(),
                            'bank_response' => json_encode($data, JSON_PRETTY_PRINT)
                        ]);
                }
            }
        }

        // ✅ Only proceed if free or paid successfully
        if ($request->is_free || $orderPaid) {
            if (!$request->is_free) {
                $existingBooking = TicketBooking::where('razorpay_order_id', $request->order_id)->first();

                if ($existingBooking) {
                    DB::commit();

                    return response()->json([
                        'status' => true,
                        'booking_id' => $existingBooking->id,
                        'booking_no' => $existingBooking->booking_no,
                    ]);
                }
            }

            

            // Prepare seat IDs
            $seatIds = collect($request->seatGroups ?? [])
                ->pluck('seats')
                ->flatten(1)
                ->pluck('id')
                ->toArray();

            // Calculate totals (tickets, seats, waiters, taxes)
            $ticketSubtotal = 0;
            if (!empty($request->tickets)) {
                foreach ($request->tickets as $t) {
                    $ticketType = $event->ticketTypes->firstWhere('id', $t['id']);
                    if (!$ticketType) throw new \Exception('Invalid ticket type');
                    $price = $this->isComplimentary($ticketType->type, $event->complimentary_age) ? 0 : $ticketType->amount;
                    $ticketSubtotal += $price * $t['quantity'];
                }
            }

            $seatSubtotal = 0;
            if (!empty($seatIds)) {
                $seats = Seat::with('category')->whereIn('id', $seatIds)->get();
                foreach ($seats as $seat) {
                    $seatSubtotal += $seat->category->price ?? 0;
                }
            }

            $waiterSubtotal = 0;
            if ($event->waiter && $request->waiters > 0) {
                $waiterSubtotal = $event->waiter->waiter_cost * $request->waiters;
            }

            $taxableAmount = $ticketSubtotal + $seatSubtotal + $waiterSubtotal;
            $serviceCharge = ($taxableAmount * ($event->service_charge ?? 0)) / 100;
            $gst = ($taxableAmount * ($event->gst ?? 0)) / 100;
            $total = round($taxableAmount + $serviceCharge + $gst, 2);

            // 🔹 Create booking
            $booking = TicketBooking::create([
                'event_id' => $event->id,
                'member_id' => auth()->id(),
                'booking_no' => 'EVT-' . strtoupper(uniqid()),
                'total_amount' => $total,
                'payment_status' => $request->is_free ? 'free' : 'paid',
                'razorpay_order_id' => $request->is_free ? null : $request->order_id,
                'razorpay_payment_id'=>$request->is_free ? null : ($transaction->gateway_transaction_id ?? $request->order_id),
                'Name' => $data['customer_name'] ?? ' ',
                'Mobile' => $data['customer_phone'] ?? '9999999999'
            ]);

            // 🔹 Create participants
            foreach ($request->tickets ?? [] as $ticket) {
                $ticketType = $event->ticketTypes->firstWhere('id', $ticket['id']);
                $price = $this->isComplimentary($ticketType->type, $event->complimentary_age) ? 0 : $ticketType->amount;

                for ($i = 0; $i < $ticket['quantity']; $i++) {
                    $participant = Participant::create([
                        'booking_id' => $booking->id,
                        'ticket_type' => $ticket['type'],
                        'ticket_id' => $ticket['id'],
                        'name' => auth()->user()->DisplayName,
                        'is_complimentary' => $this->isComplimentary($ticket['type'], $event->complimentary_age) ? 1 : 0,
                        'mobile' => auth()->user()->Mobile,
                        'email' => auth()->user()->Email,
                        'amount' => $price,
                    ]);

                    $participant->update([
                        'qr_code' => Crypt::encryptString(json_encode([
                            'booking_id' => $booking->id,
                            'participant_id' => $participant->id,
                            'event_id' => $event->id,
                            'ticket_type' => $ticket['type'],
                            'issued_at' => now()->timestamp
                        ]))
                    ]);
                }
            }

            // 🔹 Store seats
            foreach ($seatIds as $seatId) {
                $seat = Seat::with('category')->find($seatId);
                $price = $seat->category->price ?? 0;

                DB::table('event_booking_seats')->insert([
                    'booking_id' => $booking->id,
                    'seat_id' => $seat->id,
                    'amount' => $price,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                Seat::where('id', $seat->id)->update(['status' => 'booked']);
            }

            // 🔹 Waiters
            if ($event->waiter && $request->waiters > 0) {
                DB::table('event_waiter_bookings')->insert([
                    'event_id' => $event->id,
                    'booking_id' => $booking->id,
                    'member_id' => auth()->id(),
                    'quantity' => $request->waiters,
                    'price' => $event->waiter->waiter_cost,
                    'created_at' => now(),
                ]);
            }

            // 🔹 Notification
            $notification = [
                'title' => $event->name,
                'short_descriptions' => 'Your event passes are booked successfully!',
                'image'=>$event->image,
            ];
            $this->sendFCMMessage($notification, auth()->user()->device_id);

            DB::commit();

            return response()->json([
                'status' => true,
                'booking_id' => $booking->id,
                'booking_no' => $booking->booking_no,
            ]);
        }

        // ❌ Payment failed
        DB::rollBack();
        return response()->json([
            'status' => false,
            'message' => 'Payment failed',
        ], 402);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => false,
            'message' => 'Booking failed',
            'error' => $e->getMessage()
        ], 500);
    }
}
// public function processPayment(Request $request)
// {
   
//     $request->validate([
//         'event_id' => 'required|integer',
//         'tickets' => 'nullable|array',
// 'waiters' => 'required_without:tickets|integer',

//         'is_free'  => 'required|boolean',

//         'order_id' => 'required_if:is_free,false|nullable|string',
//         'seatGroups' => 'nullable|array',
// 'seatGroups.*.seats' => 'nullable|array',
//     ]);



//     /** 🔐 Razorpay verification ONLY for paid */
//     if (!$request->is_free) {
//       $orderId=$request->order_id;

//     //  $url = env('CASHFREE_BASE_URL') . '/orders';
//     $url = env('CASHFREE_BASE_URL') . '/orders/' . $orderId;

//         $headers = [
//             'x-client-id' => env('CASHFREE_APP_ID'),
//             'x-client-secret' => env('CASHFREE_SECRET_KEY'),
//         "x-api-version" => "2025-01-01",
//         "Content-Type" => "application/json"
//     ];

//     $response = \Http::withHeaders($headers)->get($url);

//     \Log::info("VERIFY RESPONSE: " . $response->body());

  

//     $data = $response->json();
// DB::beginTransaction();
//     $orderStatus = $data['order_status'] ?? null;
//   $transaction = DB::table('transactions')
//             ->where('order_id', $orderId)
//             ->lockForUpdate()
//             ->first();

//         if (!$transaction) {
//             throw new \Exception('Transaction not found');
//         }

   

//       if ($orderStatus === "PAID") {

//     \Log::info("inside $orderStatus");

//     $updated = DB::table('transactions')
//         ->where('id', $transaction->id)
//         ->update([
//             'payment_status' => 'Paid',
//             'transaction_date' => now(),
//             'bank_response' => json_encode($data, JSON_PRETTY_PRINT)
//         ]);
// }
//         if (TicketBooking::where('razorpay_order_id', $request->order_id)->exists()) {
//             return response()->json(['status' => false, 'message' => 'Order already used'], 409);
//         }
        
//     }
    
// $seatIds = collect($request->seatGroups ?? [])
//     ->pluck('seats')
//     ->flatten(1)
//     ->pluck('id')
//     ->toArray();
   

//     try {
//         $event = Event::with(['ticketTypes', 'waiter'])
//             ->findOrFail($request->event_id);


// $ticketSubtotal = 0;

// if (!empty($request->tickets)) {
//     foreach ($request->tickets as $t) {
//           $ticketType = $event->ticketTypes->firstWhere('id', $t['id']);
//         if (!$ticketType) throw new \Exception('Invalid ticket');

//         $price = $this->isComplimentary($ticketType->type,$event->complimentary_age) ? 0 : $ticketType->amount;
//         $ticketSubtotal += $price * $t['quantity'];
//     }
// }

// $seatSubtotal = 0;

// if (!empty($seatIds)) {

//     $seats = Seat::with('category')
//         ->whereIn('id', $seatIds)
//         ->get();

//     foreach ($seats as $seat) {
//         $seatSubtotal += $seat->category->price ?? 0;
//     }
// }
// /** 🔹 Waiter subtotal */
// $waiterSubtotal = 0;
// if ($event->waiter && $request->waiters > 0) {
//     $waiterSubtotal = $event->waiter->waiter_cost * $request->waiters;
// }

// /** 🔹 Taxable */
// $taxableAmount = $ticketSubtotal + $waiterSubtotal +$seatSubtotal;

// /** 🔹 Taxes */
// $serviceCharge = ($taxableAmount * ($event->service_charge ?? 0)) / 100;
// $gst = ($taxableAmount * ($event->gst ?? 0)) / 100;
// $total = round($taxableAmount + $serviceCharge + $gst, 2);


//         /** 🔹 Create booking */
//         $booking = TicketBooking::create([
//             'event_id' => $event->id,
//             'member_id' => auth()->id(),
//             'booking_no' => 'EVT-' . strtoupper(uniqid()),
//             'total_amount' => $total,
//             'payment_status' => $request->is_free ? 'free' : 'paid',
//             'razorpay_order_id' => $request->is_free ? null : $request->order_id,
//             'Name'=>$data['customer_name'] ??' ',
//             'Mobile'=>$data['customer_phone']??'9999999999'
//             // 'razorpay_payment_id' => $request->is_free ? null : $request->razorpay_payment_id,
            
//         ]);

//         /** 🔹 Participants */
//         foreach ($request->tickets as $ticket) {
            
//     $ticketType = $event->ticketTypes->firstWhere('id', $ticket['id']);
//     if (!$ticketType) throw new \Exception('Invalid ticket type');

//     $price = $this->isComplimentary($ticketType->type,$event->complimentary_age) ? 0 : $ticketType->amount;
//             for ($i = 0; $i < $ticket['quantity']; $i++) {
//                 $participant = Participant::create([
//                     'booking_id' => $booking->id,
//                     'ticket_type'=> $ticket['type'],
//                     'ticket_id'=> $ticket['id'],
//                     'name' => auth()->user()->DisplayName,
//                     'is_complimentary'=>$this->isComplimentary($ticket['type'],$event->complimentary_age) ? 1:0,
//                     'mobile' => auth()->user()->Mobile,
//                     'email' => auth()->user()->Email,
//                     'amount'       => $price, 
//                 ]);

//                 $participant->update([
//                     'qr_code' => Crypt::encryptString(json_encode([
//                         'booking_id' => $booking->id,
//                         'participant_id' => $participant->id,
//                         'event_id' => $event->id,
//                         'ticket_type' => $ticket['type'],
//                         'issued_at' => now()->timestamp
//                     ]))
//                 ]);
//             }
//         }



//         /** 🔹 Waiters */
//         if ($event->waiter && $request->waiters > 0) {
//             DB::table('event_waiter_bookings')->insert([
//                 'event_id' => $event->id,
//                 'booking_id' => $booking->id,
//                 'member_id' => auth()->id(),
//                 'quantity' => $request->waiters,
//                 'price' => $event->waiter->waiter_cost,
//                 'created_at' => now(),
//             ]);
//         }
// /** 🔹 Store seats */
// if (!empty($seatIds)) {

//     $seats = Seat::with('category')
//         ->whereIn('id', $seatIds)
//         ->get();

//     foreach ($seats as $seat) {

//         $price = $seat->category->price ?? 0;


//         DB::table('event_booking_seats')->insert([
//             'booking_id' => $booking->id,
//             'seat_id' => $seat->id,
//             'amount' => $price, // ✅ store seat price
//             'created_at' => now(),
//             'updated_at' => now()
//         ]);

//         Seat::where('id', $seat->id)
//             ->update(['status' => 'booked']);
//     }
// }
//     $notification = [
//             'title'              => $event->name,
//             'short_descriptions' => 'Your event passes are booked successfully!',
//         ]; 
//             $this->sendFCMMessage($notification, auth()->user()->device_id);
//         DB::commit();

//         return response()->json([
//             'status' => true,
//             'booking_id' => $booking->id,
//             'booking_no' => $booking->booking_no,
//         ]);

//     } catch (\Exception $e) {
//         DB::rollBack();
//         return response()->json([
//             'status' => false,
//             'message' => 'Booking failed',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }


    
    public function eventBookings(Request $request)
{
    $limit = $request->query('limit', 10);
    $bookings = TicketBooking::with(['event', 'participants'])
        ->where('member_id', auth()->id())
        ->orderBy('created_at', 'desc')
        ->paginate($limit);

    // transform paginated collection
    $bookings->getCollection()->transform(function ($booking) {

        // 🔹 Waiter calculations
        $waiterData = DB::table('event_waiter_bookings')
            ->where('booking_id', $booking->id)
            ->selectRaw('
                COALESCE(SUM(quantity), 0) as waiter_count,
                COALESCE(SUM(quantity * price), 0) as waiter_amount
            ')
            ->first();

        return [
            'booking_id'   => $booking->id,
            'booking_no'   => $booking->booking_no,
            'event_name'   => $booking->event->name,
            'event_date'   => \Carbon\Carbon::parse($booking->event->event_date)->format('M d, Y'),
            'event_image'  => url('/teebooking/public/event/'.$booking->event->image) ?? 'default.jpg',

            // 🎫 Tickets
            'ticket_count' => $booking->participants->count(),
            'ticket_types' => $booking->participants
                ->pluck('ticket_type')
                ->unique()
                ->values(),

            // 🍽️ Waiters
            'waiter_count'  => (int) $waiterData->waiter_count,
            'waiter_amount' => (float) $waiterData->waiter_amount,

            // 💰 Amount
            'total_amount' => (float) $booking->total_amount,

            // ✅ Status
            'status' => $booking->payment_status === 'paid'
                ? 'confirmed'
                : $booking->payment_status,

            'is_staff' => $booking->payment_status === 'free',
        ];
    });

    return response()->json([
        'status' => true,
        'data'   => $bookings
    ]);
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
        $url = 'https://fcm.googleapis.com/v1/projects/cgoma-217f3/messages:send';
        $serverKey = $this->getAccessToken();
        $data = [
            "message" => [
                "token" => $fcmTokens,
                "notification" => [
                    "title" => $notification['title'],
                    "body" => $notification['short_descriptions'],
                    "image" => url('teebooking/public/event/'.$notification['image']),
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

public function showBookings(TicketBooking $booking)
{
    $booking->load([
        'participants.ticketType',
        'seats.seat',
        'event',
    ]);
    // 1️⃣ Ticket subtotal using OLD PRICES stored in participants
    $ticketSubtotal = $booking->participants
        ->where('is_complimentary', 0)
        ->sum('amount');

    // 2️⃣ Waiters
    $waiterData = DB::table('event_waiter_bookings')
        ->where('booking_id', $booking->id);

    $waiterAmount = $waiterData->sum(DB::raw('quantity * price'));
    $waiterCount  = $waiterData->sum('quantity');

    // 3️⃣ Subtotal + Tax
    $subtotal       = $ticketSubtotal + $waiterAmount;
    $serviceCharge  = round(($subtotal * ($booking->event->service_charge ?? 0)) / 100, 2);
    $gst            = round(($subtotal * ($booking->event->gst ?? 0)) / 100, 2);
    $totalPaid      = round($subtotal + $serviceCharge + $gst, 2);

    // 4️⃣ Get booked ticket types only (unique ticket_ids)
    $bookedTypeIds = $booking->participants
        ->pluck('ticket_id')
        ->unique()
        ->values()
        ->toArray();

    // 5️⃣ Fetch ONLY those types from DB
    $types = DB::table('ticket_types')
        ->whereIn('id', $bookedTypeIds)
        ->select('id', 'name', 'type', 'amount','image_background')
        ->get();
    // 6️⃣ Get old prices from participants table
    $oldPrices = $booking->participants
        ->groupBy('ticket_id')
        ->map(fn($items) => $items->first()->amount);
        
$seats = $booking->seats->map(function ($s) {
       return [
        'amount' => $s->amount,
        'seat_code'=> $s->seat->seat_code,
        ];
});
    $participants = $booking->participants->map(function ($p) {
        return [
            'id'               => $p->id,
            'name'             => $p->name,
            'ticket_id'        => $p->ticket_id,
            'title'            => $p->ticketType->name,
            'amount'           => $p->amount,  // old amount
            'ticket_type'      => ucfirst($p->ticket_type),
            'qr_code'          =>$p->qr_code,
            // base64_encode(
            //     QrCode::format('svg')->size(220)->generate($p->qr_code)
            // ),
             'image_background' => url('teebooking/public/passes/'.$p->ticketType->image_background) ,
             'amount' => $p->ticketType->amount ,
            'entry_status'     => (bool) $p->entry_status,
            'food_status'      => (bool) $p->food_status,
            'is_complimentary' => $p->is_complimentary,
        ];
    });

    // 8️⃣ Add old + new price per booked type
    $finalTypes = $types->map(function ($t) use ($oldPrices) {
        return [
            'id'          => $t->id,
            'name'        => $t->name,
            'type'        => $t->type,                // new updated table price
            'amount'  => $oldPrices[$t->id] ?? 0,    // old booking price
            'img'=>url('teebooking/public/passes/'.$t->image_background)
        ];
    });

    $member = Member::find($booking->member_id);
$TicketFormate = DB::table('Event_ticket_formate')
    ->pluck('value', 'name');
    
    
    $transaction = DB::table('transactions')
        ->where('order_id', $booking->razorpay_order_id)
        ->orWhere('transID', $booking->razorpay_order_id)
        ->latest('id')
        ->first();
    $timeline = [
        [
            'title' => 'Booking created',
            'description' => 'Event passes were generated for the member.',
            'time' => optional($booking->created_at)->format('d M Y, h:i A'),
            'tone' => 'neutral',
        ],
        [
            'title' => 'Payment status',
            'description' => $booking->payment_status,
            'time' => optional($transaction?->processed_at ?? $transaction?->transaction_date ?? $booking->created_at)->format('d M Y, h:i A'),
            'tone' => strtolower((string) $booking->payment_status) === 'paid' ? 'success' : 'warning',
        ],
        [
            'title' => 'Event day',
            'description' => $booking->event->location ?: 'Venue details available in pass information',
            'time' => Carbon::parse($booking->event->event_date)->format('d M Y, h:i A'),
            'tone' => 'neutral',
        ],
    ];

    return response()->json([
        'status' => true,
        'booking' => [
            'memberId'        => $member->role === 'Event Admin' ? 'Event Admin' : $member->MemberID,
            'booking_no'      => $booking->booking_no,
            'event_name'      => $booking->event->name,
            'event_date'      => $booking->event->event_date,
            'event_location'  => $booking->event->location,
            'formate'=>$TicketFormate,
            'ticket_subtotal' => $ticketSubtotal + $waiterAmount,
            'waiter_amount'   => $waiterAmount,
            'waiter_count'    => $waiterCount,
            'subtotal'        => $subtotal,
            'service_charge'  => $serviceCharge,
            'gst'             => $gst,
            'total_paid'      => $totalPaid += $booking->seats->sum('amount'),
            'seats'=>$seats
        ],
        'participants' => $participants,
        'summary' => [
            'booking_number' => $booking->booking_no,
            'status' => $booking->payment_status,
            'ticket_count' => $booking->participants->count(),
            'seat_count' => $booking->seats->count(),
            'waiter_count' => $waiterCount,
            'booked_on' => optional($booking->created_at)->format('d M Y, h:i A'),
            'total_amount' => round($totalPaid, 2),
        ],
        'member' => [
            'member_id' => $member->MemberID,
            'sc_id' => $member->SC_ID,
            'name' => $member->DisplayName,
            'phone' => $member->Mobile,
            'email' => $member->Email,
        ],
        'payment' => [
            'status' => $transaction->payment_status ?? $booking->payment_status,
            'status_code' => $transaction->payment_status_code ?? null,
            'gateway_name' => $transaction->gateway_name ?? null,
            'gateway_order_id' => $transaction->gateway_order_id ?? $transaction->order_id ?? $booking->razorpay_order_id,
            'reference_number' => $transaction->gateway_transaction_id ?? $transaction->bank_refrance_no ?? null,
            'transaction_date' => optional($transaction?->transaction_date)->format('d M Y, h:i A'),
            'processed_at' => optional($transaction?->processed_at)->format('d M Y, h:i A'),
            'amount' => round((float) ($transaction->amount ?? $totalPaid), 2),
        ],
        'invoice' => [
            'currency' => 'INR',
            'ticket_subtotal' => round($ticketSubtotal, 2),
            'seat_subtotal' => round($booking->seats->sum('amount'), 2),
            'waiter_amount' => round($waiterAmount, 2),
            'service_charge' => round($serviceCharge, 2),
            'gst' => round($gst, 2),
            'total' => round($totalPaid, 2),
        ],
        'timeline' => $timeline,
        'payment_timeline' => [
            [
                'title' => 'Order created',
                'description' => $transaction?->gateway_name ? 'Gateway: ' . $transaction->gateway_name : 'Payment order created',
                'time' => optional($transaction?->created_at)->format('d M Y, h:i A'),
                'tone' => 'neutral',
            ],
            [
                'title' => 'Transaction update',
                'description' => $transaction?->payment_status ?? $booking->payment_status,
                'time' => optional($transaction?->processed_at ?? $transaction?->transaction_date)->format('d M Y, h:i A'),
                'tone' => strtolower((string) ($transaction->payment_status ?? $booking->payment_status)) === 'paid' ? 'success' : 'warning',
            ],
        ],
        'support' => [
            'name' => 'Event Desk',
            'phone' => null,
            'email' => null,
            'note' => 'For pass validation, seating changes, or event help, contact the event desk.',
        ],
        'rules' => [
            'Carry a valid QR pass for each participant at the venue.',
            'Seat assignments, if booked, should be retained until scanning at entry.',
            'Complimentary and VIP passes remain subject to event admission rules.',
        ],
    ]);
}



    private function getDescription($type)
    {
        return match ($type) {
            'member'    => 'Exclusive access for registered members.',
            'spouse'    => 'Bring your significant other.',
            'dependent' => 'Dependent Childrens.',
            'guest'     => 'For non-member attendees.',
            default     => ''
        };
    }
    public function paymentFailed(Request $request)
{
    $request->validate([
        'event_id' => 'required|integer',
        'razorpay_order_id' => 'required|string',
        'reason' => 'nullable|string',
        'total_amount'=>'nullable'
    ]);

    // Prevent duplicate logs
    if (TicketBooking::where('razorpay_order_id', $request->razorpay_order_id)->exists()) {
        return response()->json([
            'status' => false,
            'message' => 'Order already completed'
        ], 409);
    }

    TicketBooking::create([
        'event_id' => $request->event_id,
        'member_id' => auth()->id(),
        'booking_no' => 'FAILED-' . strtoupper(uniqid()),
        'total_amount' => $request->total_amount,
        'payment_status' => 'failed',
        'razorpay_order_id' => $request->razorpay_order_id,
        'failure_reason' => $request->reason ?? 'User cancelled payment',
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Payment failure recorded'
    ]);
}

public function adminBook(Request $request)
{
    abort_unless(auth()->user()->is_admin, 403);

    DB::transaction(function () use ($request) {

        $booking = TicketBooking::create([
            'event_id' => $request->event_id,
            'member_id' => $request->member_id,
            'booking_no' => 'ADMIN-' . strtoupper(uniqid()),
            'total_amount' => 0,
            'payment_status' => 'free',
            'booked_by' => 'admin',
            'is_complimentary' => 1,
        ]);

        foreach ($request->tickets as $ticket) {
            for ($i = 0; $i < $ticket['quantity']; $i++) {

                $participant = Participant::create([
                    'booking_id' => $booking->id,
                    'ticket_id'=> $ticket['id'],
                    'ticket_type' => $ticket['type'],
                    'name' => $request->name,
                    'mobile' => $request->mobile,
                    'email' => $request->email,
                ]);

                $participant->update([
                    'qr_code' => Crypt::encryptString(json_encode([
                        'booking_id' => $booking->id,
                        'participant_id' => $participant->id,
                        'event_id' => $request->event_id,
                        'ticket_type' => $ticket['type'],
                       
                    ])),
                ]);
            }
        }
    });

    return response()->json([
        'status' => true,
        'message' => 'VIP tickets issued successfully'
    ]);
}

}
