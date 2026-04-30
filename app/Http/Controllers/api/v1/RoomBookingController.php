<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoomCancellationPolicy;
use App\Models\RoomChargesMaster;
use App\Models\RoomBookingItem;
use App\Models\OccupantMaster;
use App\Models\AdminSetting;
use App\Models\RoomBooking;
use App\Models\CardItem;
use App\Models\Member;
use App\Models\Card;
use App\Models\SOP;
use App\Models\BlockRoom;
use App\Services\FCMService;
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Api;
use Carbon\Carbon;
use App\Models\RoomCategoryMaster; 
use AESEncDec;
use DB;

class RoomBookingController extends Controller
{
    public function blockRooms(Request $request)
{
    $request->validate([
        'rooms' => 'required|array',
        'rooms.*.room_category_id' => 'required|integer',
        'rooms.*.from_date' => 'required|date',
        'rooms.*.to_date' => 'required|date|after_or_equal:rooms.*.from_date',
        'rooms.*.blocked_room' => 'required',
        'rooms.*.remark' => 'nullable|string'
    ]);

    try {
        DB::transaction(function () use ($request) {

            // ✅ Delete only API blocked rooms (NOT truncate)
            BlockRoom::where('block_type', 'API')->delete();

            foreach ($request->rooms as $room) {

                if (Carbon::parse($room['to_date'])->lt(Carbon::parse($room['from_date']))) {
                    throw new \Exception("To date must be after or equal to From date");
                }

                BlockRoom::create([
                    'room_category_id' => $room['room_category_id'],
                    'from_date'        => $room['from_date'],
                    'to_date'          => $room['to_date'],
                    'blocked_room'     => $room['blocked_room'],
                    'remark'           => $room['remark'] ?? null,
                    'block_type'       => 'API'
                ]);
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Room blocks updated successfully',
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}


public function room_traction(Request $request)
{
    $member = Member::where("memberprofile.id", auth()->user()->id)
        ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')
        ->first();

    $q = RoomBooking::where('memberID', $member->MemberID);

    // Filters (unchanged)
    if ($request->memberID) {
        $q->where('memberID', $request->memberID);
    }

    if ($request->memberName) {
        $q->where('memberName', $request->memberName);
    }

    if ($request->booking_no) {
        $q->where('booking_number', $request->booking_no);
    }

    if ($request->checkIn) {
        $q->where('checkin', '>=', $request->checkIn . ' ' . env('CheckIn'));
    }

    if ($request->checkOut) {
        $q->where('checkout', '<=', $request->checkOut . ' ' . env('CheckOut'));
    }

    // MAIN DATA (unchanged)
    $datas = $q->orderBy('id', 'DESC')->paginate(10);

    // Attach payment info (unchanged)
    foreach ($datas as $item) {
        $item->payment = DB::table('transactions')
            ->where('room_booking_id', $item->id)
            ->select('payment_status')
            ->first();
    }

    // 🔥 UPCOMING BOOKINGS
   $upcomingBookings = RoomBooking::where('memberID', $member->MemberID)
    ->whereDate('checkin', '>', now())
    ->where('status', '!=', 'Cancelled')
    ->orderBy('checkin', 'ASC')
    ->get()
    ->map(function ($item) {
        return [
            'id' => $item->id,
            'type' => 'room',
            'title' => $item->booking_number,
            'date' => $item->checkin,
            'status' => $item->status ?: 'Pending',
            'payment_status' => optional(
                DB::table('transactions')
                    ->where('room_booking_id', $item->id)
                    ->first()
            )->payment_status ?? 'Not Paid',
        ];
    });


    return response()->json([
        'status' => true,
        'data' => $datas,
        'upcoming_bookings' => $upcomingBookings
    ]);
}


    // public function room_traction(Request $request)
    // {
    //     $member = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();

    //     if($request && $request->memberID || $request->memberName || $request->booking_no || $request->checkIn || $request->checkOut){

    //         $q = RoomBooking::query();            

    //         if($request->memberID){
    //             $q->where('memberID', $request->memberID);
    //         }

    //         if($request->memberName){
    //             $q->where('memberName', $request->memberName);
    //         }

    //         if($request->booking_no){
    //             $q->where('booking_number', $request->booking_no);
    //         }

    //         if($request->checkIn){
    //             $q->where('checkin', '>=', $request->checkIn.' '.env('CheckIn',null));
    //         }

    //         if($request->checkOut){
    //             $q->where('checkout', '<=', $request->checkOut.' '.env('CheckOut',null));
    //         }

    //         $datas = $q->where('memberID', $member->MemberID)->orderBy('id', 'DESC')->paginate(15);

    //     } else {

    //         $datas = RoomBooking::where('memberID', $member->MemberID)->orderBy('id', 'DESC')->paginate(15);

    //     }

    //     foreach ($datas as $key => $value) {
    //         $value->payment = DB::table('transactions')->where('room_booking_id', $value->id)->select('payment_status')->first();
    //     }

    //     $return_data['data'] = $datas;

    //     $return_data['message'] = '';

    //     $return_data['status'] = true;

    //     return response()->json($return_data);
    // }

    public function room_traction_details($id='')
    {
        $datas = RoomBooking::where('id', $id)->first();

        if (!$datas) {
            return response()->json([
                'status' => false,
                'message' => 'Booking not found.',
            ], 404);
        }

        $datas->paid_payment = getBookingTotal($id);

        $data_items = RoomBookingItem::where('booking_id', $id)->get();

        foreach ($data_items as $key => $item) {

            $item->room_name = $item->room->name;
            $item->occupant_name = $item->occupant->name;
            
        }

        $member = Member::where('MemberID', $datas->memberID)
            ->orWhere('SC_ID', $datas->chartID)
            ->first();
        $payment = DB::table('transactions')
            ->where('room_booking_id', $datas->id)
            ->latest('id')
            ->first();
        $setting = AdminSetting::first();
        $subtotal = $data_items->sum(fn ($item) => (float) $item->room_charges * (int) $item->no_of_rooms);
        $gstTotal = $data_items->sum(fn ($item) => (float) $item->gst_amount);
        $grandTotal = $data_items->sum(fn ($item) => (float) $item->room_charge_total);
        $nightCount = max(1, Carbon::parse($datas->checkout)->diffInDays(Carbon::parse($datas->checkin)));
        $roomCount = $data_items->sum('no_of_rooms');
        $guestCount = $data_items->sum(fn ($item) => (int) $item->adult + (int) $item->child);

        $datas->payment_info = $payment ? (object) [
            'transID' => $payment->transID,
            'payment_status' => $payment->payment_status,
            'bank_refrance_no' => $payment->bank_refrance_no,
        ] : null;

        $datas->room_details = $data_items;
        $datas->member = [
            'member_id' => $datas->memberID,
            'sc_id' => $datas->chartID,
            'name' => $datas->memberName,
            'email' => $member->Email ?? null,
            'phone' => $member->Mobile ?? null,
        ];
        $datas->summary = [
            'booking_number' => $datas->booking_number,
            'status' => $datas->status,
            'booking_from' => $datas->booking_from,
            'booked_on' => optional($datas->created_at)->format('d M Y, h:i A'),
            'night_count' => $nightCount,
            'room_count' => (int) $roomCount,
            'guest_count' => (int) $guestCount,
            'checkin' => Carbon::parse($datas->checkin)->format('d M Y, h:i A'),
            'checkout' => Carbon::parse($datas->checkout)->format('d M Y, h:i A'),
            'total_amount' => round($grandTotal, 2),
        ];
        $datas->payment = [
            'status' => $payment->payment_status ?? 'Not Paid',
            'status_code' => $payment->payment_status_code ?? null,
            'gateway_name' => $payment->gateway_name ?? null,
            'gateway_order_id' => $payment->gateway_order_id ?? $payment->transID ?? null,
            'reference_number' => $payment->gateway_transaction_id ?? $payment->bank_refrance_no ?? null,
            'transaction_date' => optional($payment?->transaction_date)->format('d M Y, h:i A'),
            'processed_at' => optional($payment?->processed_at)->format('d M Y, h:i A'),
            'amount' => round((float) ($payment->amount ?? $grandTotal), 2),
        ];
        $datas->invoice = [
            'currency' => 'INR',
            'subtotal' => round($subtotal, 2),
            'gst_total' => round($gstTotal, 2),
            'total' => round($grandTotal, 2),
            'items' => $data_items,
        ];
        $datas->timeline = [
            [
                'title' => 'Booking created',
                'description' => 'Reservation was created from the member app.',
                'time' => optional($datas->created_at)->format('d M Y, h:i A'),
                'tone' => 'neutral',
            ],
            [
                'title' => 'Payment status',
                'description' => $payment?->payment_status ?: 'Awaiting payment confirmation',
                'time' => optional($payment?->transaction_date)->format('d M Y, h:i A'),
                'tone' => strtolower((string) ($payment?->payment_status)) === 'paid' ? 'success' : 'warning',
            ],
            [
                'title' => 'Stay window',
                'description' => 'Check-in to check-out timeline for the reservation.',
                'time' => Carbon::parse($datas->checkin)->format('d M Y') . ' - ' . Carbon::parse($datas->checkout)->format('d M Y'),
                'tone' => strtolower((string) $datas->status) === 'cancelled' ? 'danger' : 'neutral',
            ],
        ];
        $datas->payment_timeline = [
            [
                'title' => 'Order generated',
                'description' => $payment?->gateway_name ? 'Gateway: ' . $payment->gateway_name : 'Payment order created',
                'time' => optional($payment?->created_at)->format('d M Y, h:i A'),
                'tone' => 'neutral',
            ],
            [
                'title' => 'Transaction update',
                'description' => $payment?->payment_status ?: 'Awaiting update',
                'time' => optional($payment?->processed_at ?? $payment?->transaction_date)->format('d M Y, h:i A'),
                'tone' => strtolower((string) ($payment?->payment_status)) === 'paid' ? 'success' : 'warning',
            ],
        ];
        $datas->rules = [
            'Check-in follows the club room policy and assigned room availability.',
            'Cancellation and refund amounts depend on the room cancellation policy in effect at booking time.',
            'Carry the booking number and member ID at arrival for faster check-in.',
        ];
        $datas->support = [
            'name' => $setting->club_name ?? 'Club Front Desk',
            'phone' => $setting->mobile ?? $setting->phone ?? null,
            'email' => $setting->email ?? null,
            'note' => 'Contact the room booking desk for amendments, guest changes, or refund questions.',
        ];

        $return_data['data'] = $datas;

        $return_data['message'] = '';

        $return_data['status'] = true;

        return response()->json($return_data);
    }

    
    public function get_rooms(Request $request)
{
    $request->validate([
        'check_in'  => 'required|date',
        'check_out' => 'required|date|after:check_in',
    ]);

    $member = Member::where("memberprofile.id", auth()->user()->id)
        ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')
        ->first();

    $startDate = Carbon::parse($request->check_in);
    $endDate   = Carbon::parse($request->check_out);
    $daysDifference = $startDate->diffInDays($endDate);

    $rooms = [];
    $max_nitie = null;
\Log::info($request);
    $q = RoomChargesMaster::query();
    $q->where('status', 'Active')
      ->whereHas('room_category', fn($q) => $q->where('status', 'Active'));

    if ($member?->CategoryTypeCode) {
        $q->where('category_type_id', $member->CategoryTypeCode);
    }

    if ($member?->CategoryCode) {
        $q->where('category_id', $member->CategoryCode);
    }

    $roomss = $q->groupBy(['category_id','room_category_id'])
                ->with('room_category')
                ->get();

    $check_in_date_last = Carbon::parse($request->check_in)->addDay()->format('Y-m-d');

    $max_nitie = RoomChargesMaster::where('category_type_id', $member->CategoryTypeCode)
                    ->max('max_no_of_nites');

    foreach ($roomss as $room) {

        $available = getBookedRooms(
            $room->category_id,
            $room->category_type_id,
            $room->room_category_id,
            $check_in_date_last,
            $request->check_out,
            $request->check_in
        );

        if ($available > 0) {

            // Enforce max nights rule if exists
            if ($max_nitie && $daysDifference > $max_nitie) {
                continue;
            }

            $room->available_room = $available;
            $room->image='https://gvicc.in/wp-content/uploads/2025/10/G6.jpeg';
            $rooms[] = $room;
        }
    }

    return response()->json([
        'status' => true,
        'data' => [
            'rooms'          => $rooms,
            'member'         => $member,
            'max_nitie'      => $max_nitie,
            'daysDifference' => $daysDifference,
        ]
    ]);
}


    public function get_occupant(Request $request)
    {
        $occupant = OccupantMaster::where('status', 'Active')->get();

        $return_data['data'] = $occupant;

        $return_data['status'] = true;

        return response()->json($return_data);
    }

    public function get_setting(Request $request)
    {
        $setting = AdminSetting::first();

        $return_data['data'] = $setting;

        $return_data['status'] = true;

        return response()->json($return_data);
    }

    public function get_SOP(Request $request)
    {
        $SOP = SOP::where('type', 'Room Booking')->first();

        $return_data['data'] = $SOP;

        $return_data['status'] = true;

        return response()->json($return_data);
    }

    public function get_room_charge(Request $request)
    {
        $data['occupan'] = OccupantMaster::where('status', 'Active')->where('id', $request->occupant_id)->first();

        $data['room_charge'] = RoomChargesMaster::where('category_id', $request->category_id)->where('category_type_id', $request->category_type_id)->where('occupant_type_id', $request->occupant_type_id)->where('room_category_id', $request->room_category_id)->first();

        $return_data['data'] = $data;

        $return_data['status'] = true;
\Log::info( $request->all());
        return response()->json($return_data);
    }

    public function store_in_summary(Request $request)
    {
        $member = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();

        $checkCard = Card::where('memberID', $member->MemberID)->exists();

        if($checkCard){

            $card = Card::where('memberID', $member->MemberID)->first();

        } else {

            $bookingID = date('dmY').'-'.rand(9999,100000);

            $params['booking_number']   = $bookingID;
            $params['memberID']         = $member->MemberID;
            $params['chartID']          = $member->SC_ID;
            $params['checkin']          = $request->check_in.' '.env('CheckIn',null);
            $params['checkout']         = $request->check_out.' '.env('CheckOut',null);

            $card = Card::create($params);
        }

        if($card){

            $date1=date_create($request->check_in);
            $date2=date_create($request->check_out);
            $diff=date_diff($date1,$date2);
            $in_days = str_replace('+','',$diff->format("%R%a"));

            $paramss['card_id']             = $card->id;
            $paramss['category_id']         = $request->category_id;
            $paramss['category_type_id']    = $request->category_type_id;
            $paramss['room_category_id']    = $request->room_category_id;
            $paramss['occupant_id']         = $request->occupant_id;
            $paramss['no_of_rooms']         = $request->booked_room_no;
            $paramss['no_of_days']          = $in_days;
            $paramss['room_charges']        = $request->room_charges;
            $paramss['adult']               = $request->adult;
            $paramss['child']               = $request->child;
            $paramss['guest_name']          = $request->guest_name ?$request->guest_name:$member->DisplayName;
            $paramss['guest_email']         = $request->guest_email?$request->guest_email:$member->Email;
            $paramss['guest_mobile']        = $request->guest_mobile?$request->guest_mobile:$member->Mobile;

            $room_charges_total = ($request->room_charges*$request->booked_room_no)*$in_days;

            $GST_a = ($request->gst / 100) * $room_charges_total; 

            $paramss['gst_per']             = $request->gst;
            $paramss['gst_amount']          = $GST_a;
            $paramss['room_charge_total']   = $room_charges_total+$GST_a;

            $res = CardItem::create($paramss);

            $return_data['message'] = 'Booking insert in session';

            $return_data['status'] = true;

        } else {

            $return_data['message'] = 'Try Again';

            $return_data['status'] = false;

        }

        return response()->json($return_data);
    }

    public function get_summary(Request $request)
    {
        $member = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();

        $datas = Card::where('memberID', $member->MemberID)->first();

        if($datas){

            $data_items = CardItem::where('card_id', $datas?$datas->id:'')->get();

            foreach ($data_items as $key => $item) {

                $item->room_name = $item->room->name;
                $item->occupant_name = $item->occupant->name;
                
            }

            $datas->room_details = $data_items;
        }
        

        $return_data['data'] = $datas;

        $return_data['status'] = true;
       
        return response()->json($return_data);
    }

    public function booking_checkout($card_id)
    {
        $card = Card::where('id', $card_id)->first();

        $member = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();

        if($card){

            $params['booking_number']   = $card->booking_number;
            $params['memberID']         = $card->memberID;
            $params['memberName']       = $member->DisplayName;
            $params['chartID']          = $card->chartID;
            $params['checkin']          = $card->checkin;
            $params['checkout']         = $card->checkout;
            $params['booking_from']     = 'App';

            $booking = RoomBooking::create($params);

            if($booking){

                $amount = '0';

                foreach (CardItem::where('card_id', $card->id)->get() as $key => $value) {
                    
                    $paramss['booking_id']          = $booking->id;
                    $paramss['category_id']         = $value->category_id;
                    $paramss['category_type_id']    = $value->category_type_id;
                    $paramss['room_category_id']    = $value->room_category_id;
                    $paramss['occupant_id']         = $value->occupant_id;
                    $paramss['no_of_rooms']         = $value->no_of_rooms;
                    $paramss['no_of_days']          = $value->no_of_days;
                    $paramss['room_charges']        = $value->room_charges;
                    $paramss['room_charge_total']   = $value->room_charge_total;
                    $paramss['adult']               = $value->adult;
                    $paramss['child']               = $value->child;
                    $paramss['guest_name']          = $value->guest_name;
                    $paramss['guest_email']         = $value->guest_email;
                    $paramss['guest_mobile']        = $value->guest_mobile;
                    $paramss['gst_per']             = $value->gst_per;
                    $paramss['gst_amount']          = $value->gst_amount;

                    $res = RoomBookingItem::create($paramss);

                    $amount += $value->room_charge_total;

                }
                $member = Member::where("memberprofile.id",auth()->user()->id)->first();
                CardItem::where('card_id', $card->id)->delete();
                Card::where('booking_number', $card_id)->delete();

$order = \App\Helpers\PaymentHelper::createOrder(
    $member,
    $amount,
    'Room Booking',
    $booking->id,
    'RMB'
);
               
            $return_data['status'] = true;
$return_data['message'] = 'Booking inserted successfully.';
 return response()->json([
    'status' => true,
    'data' => [
        'order_id' => $order['order_id'],
        'amount' => $amount,
       'txnid'=>  $order['txnid'],
        'access_key' => $order['access_key'],
    'end_point' => 'member/update/room/payment']
]);

return response()->json($return_data);
            }

        } else {

            $return_data['message'] = 'Data not found.';

            $return_data['status'] = false; 

        }

        return response()->json($return_data);
    }
public function update_payment(Request $request, FCMService $fcm)
{
    DB::beginTransaction();

    try {

        // 1️⃣ Verify payment using helper
        $result = \App\Helpers\PaymentHelper::verifyPayment($request);

        $transaction = DB::table('transactions')
            ->where('transID', $request->razorpay_order_id)
            ->first();

        if (!$transaction) {
            throw new \Exception('Transaction missing');
        }

        $member = auth()->user();

        // 2️⃣ Activate booking if payment success
        if ($result['success'] && $transaction->room_booking_id) {

            RoomBooking::where('id', $transaction->room_booking_id)
                ->update(['status' => 'Active']);
        }

             if ($member->device_id) {
            $fcm->sendNotification(
                $member->device_id,
               'Room Booking',
                 $result['success']
                ? 'Your room booked successfully.'
                : 'Your room booking payment failed.',
                 [
                    'id' => $transaction->room_booking_id,
                    'screen' => 'InvoiceDetails'
                ]
            );
        }
        DB::commit();

        return response()->json($result);

    } catch (\Exception $e) {

        DB::rollBack();

        \Log::error('Room payment verify error', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Payment processing failed'
        ]);
    }
}


      public static function getAccessToken()
{
    // $serviceAccountPath = env('FIREBASE_CREDENTIALS_PATH'); // load from .env
$serviceAccountPath = storage_path('app/firebase/holidayclub-service-account.json');

    if (!file_exists($serviceAccountPath)) {
        throw new \Exception("Firebase credentials file not found at: {$serviceAccountPath}");
    }

    $serviceAccountData = json_decode(file_get_contents($serviceAccountPath), true);

    if (!$serviceAccountData || !isset($serviceAccountData['client_email'], $serviceAccountData['private_key'])) {
        throw new \Exception("Invalid Firebase credentials JSON.");
    }

    // JWT Header
    $jwtHeader = rtrim(strtr(base64_encode(json_encode([
        'alg' => 'RS256',
        'typ' => 'JWT'
    ])), '+/', '-_'), '=');

    $now = time();

    // JWT Payload
    $jwtPayload = rtrim(strtr(base64_encode(json_encode([
        'iss'   => $serviceAccountData['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud'   => 'https://oauth2.googleapis.com/token',
        'exp'   => $now + 3600,
        'iat'   => $now
    ])), '+/', '-_'), '=');

    $dataToSign = $jwtHeader . '.' . $jwtPayload;

    // Sign with private key
    $privateKey = openssl_pkey_get_private($serviceAccountData['private_key']);
    openssl_sign($dataToSign, $jwtSignature, $privateKey, 'SHA256');
    $jwtSignature = rtrim(strtr(base64_encode($jwtSignature), '+/', '-_'), '=');

    $jwt = $dataToSign . '.' . $jwtSignature;

    // Exchange JWT for access token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion'  => $jwt
    ]));

    $response = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($response, true);

    if (isset($response['access_token'])) {
        return $response['access_token'];
    }

    throw new \Exception("Failed to fetch Firebase access token: " . json_encode($response));
}

    private function sendFCMMessage($notification, $fcmTokens) {
        $url = 'https://fcm.googleapis.com/v1/projects/gvi-club/messages:send';
        $serverKey = $this->getAccessToken();
        $data = [
            "message" => [
                "token" => $fcmTokens,
                "notification" => [
                    "title" => $notification['title'],
                    "body" => $notification['short_descriptions'],
                    "image" => 'https://gvicc.in/wp-content/uploads/2025/11/gviclogo.png'
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

    public function room_cancel($id)
    {
        $item = RoomBookingItem::find($id);

        if($item){

            $booking = RoomBooking::where('id', $item->booking_id)->first();

            $in_days = $item->no_of_days;

            $startDate = new \DateTime($booking->checkin);

            $first_date = [];

            // Loop through the next 15 days
            for ($i = 0; $i < $in_days; $i++) {
                // Print the current date in 'Y-m-d' format
                $fdt = $startDate->format('Y-m-d'); 
                array_push($first_date, $fdt);
                // Move to the next day
                $startDate->modify('+1 day');
            }

            $cdate = date('Y-m-d');

            $deduction = [];

            $deduction_Amt = '0';

            $new_width = '0';

            foreach ($first_date as $key => $t_date) {
                
                $startTimeStamp = strtotime($t_date);

                $endTimeStamp = strtotime($cdate);

                $timeDiff = abs($endTimeStamp - $startTimeStamp);

                $numberDays = $timeDiff/86400; 
                  
                $numberDays = intval($numberDays);

                $policys = RoomCancellationPolicy::get();

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

                    $totalWidth = $item->room_charges;

                    $balaance_Amt = ($policy->deduction / 100) * $totalWidth;

                    $new_width += ($percentage / 100) * $balaance_Amt; 

                    $p_deduction = $policy->deduction;

                    $deduction_Amt += $balaance_Amt;

                } else {

                    $p_deduction = '0';

                    $percentage = '0';

                }

                array_push($deduction, $p_deduction);

            }

            $params['cancellation_per']         = $p_deduction;
            $params['cancellation_amt']         = $deduction_Amt;
            $params['cancellation_GST']         = $percentage;
            $params['cancellation_GST_amt']     = $new_width;
            $params['cancellation_deducation']  = $deduction_Amt+$new_width;
            $params['cancellation_date']        = date('Y-m-d H:i:s');
            $params['status']                   = 'Cancelled';

            $res = RoomBookingItem::whereId($item->id)->update($params);

            $checkIi = RoomBookingItem::where('booking_id', $item->booking_id)->where('status', 'Active')->get();

            if(count($checkIi)=='0'){

                $rparams['status'] = 'Cancelled';

                RoomBooking::where('id', $item->booking_id)->update($rparams);

            }

            $return_data['message'] = 'Room Cancelled';

            $return_data['status'] = true; 

        } else {

            $return_data['message'] = 'Data not found.';

            $return_data['status'] = false; 

        }

        return response()->json($return_data);
    }

    public function empty_card($card_id='')
    {
        $card = Card::where('id', $card_id)->first();

        CardItem::where('card_id', $card->id)->delete();

        Card::where('booking_number', $card_id)->delete();

        $return_data['message'] = 'Card Empty';

        $return_data['status'] = true;

        return response()->json($return_data);
    }

    public function cancel_room_item($ciid='')
    {
        $card = CardItem::where('id', $ciid)->first();

        CardItem::where('id', $ciid)->delete();

        $cards = CardItem::where('id', $card->card_id)->get();

        if(empty($cards)){

            Card::where('id', $card->card_id)->delete();

        }

        $return_data['message'] = 'Item Remove';

        $return_data['status'] = true;

        return response()->json($return_data);
        
    }
    
  public function availability(Request $request)
{
     $request->validate([
        'startdate' => 'required|date',
        'enddate'   => 'required|date|after_or_equal:startdate'
    ]);
    
  

    $startDate = Carbon::parse($request->startdate);
    $endDate   = Carbon::parse($request->enddate);

    $categories = RoomCategoryMaster::where('status', 'Active')->get();

    $results = [];
    $totalAvailableRooms = 0;

    foreach ($categories as $cat) {

        // BOOKED rooms overlapping the date range
        $bookedRooms = RoomBookingItem::join('room_bookings', 'room_bookings.id', '=', 'room_booking_items.booking_id')
            ->where('room_booking_items.room_category_id', $cat->id)
            ->where('room_bookings.status', 'Active')
            ->whereDate('room_bookings.checkin', '<=', $endDate)
            ->whereDate('room_bookings.checkout', '>=', $startDate)
            ->sum(DB::raw('room_booking_items.no_of_rooms'));

        // BLOCKED rooms overlapping
        $blockedRooms = DB::table('block_rooms')
            ->where('room_category_id', $cat->id)
            ->whereDate('from_date', '<=', $endDate)
            ->whereDate('to_date', '>=', $startDate)
            ->sum(DB::raw('blocked_room'));

        // AVAILABLE rooms
        $available = $cat->no_of_rooms - ($bookedRooms + $blockedRooms);
        $available = max($available, 0);

        $results[] = [
            'id'             => $cat->id,
            'name'           => $cat->name,
            'total_rooms'    => $cat->no_of_rooms,
            'booked_rooms'   => $bookedRooms,
            'blocked_rooms'  => $blockedRooms,
            'available_rooms'=> $available,
        ];

        $totalAvailableRooms += $available;
    }
\Log::info($results);
    return response()->json([
        "status" => true,
        "startdate" => $startDate->format('Y-m-d'),
        "enddate"   => $endDate->format('Y-m-d'),
        "total_available_rooms" => $totalAvailableRooms,
        "rooms" => $results
    ]);
}

public function getRooms(Request $request)
{
    $request->validate([
        'check_in'  => 'required|date',
        'check_out' => 'required|date|after:check_in',
        // OPTIONAL BUT IMPORTANT
        // 'category_id' => 'required',
        // 'category_type_id' => 'required',
    ]);

    $checkIn  = Carbon::parse($request->check_in)->startOfDay();
    $checkOut = Carbon::parse($request->check_out)->startOfDay();
    $nights   = $checkOut->diffInDays($checkIn);

    /* -------------------------
       SETTINGS VALIDATION
    -------------------------- */
    $settings = AdminSetting::first();
    $minDays  = $settings->min_days ?? 1;

    if ($checkIn->lt(now()->addDays($minDays)->startOfDay())) {
        return response()->json([
            'status' => false,
            'message' => "Booking allowed only after {$minDays} days"
        ], 422);
    }

    /* -------------------------
       BLOCKED ROOMS
    -------------------------- */
    $blockedRooms = BlockRoom::where(function ($q) use ($checkIn, $checkOut) {
        $q->whereBetween('from_date', [$checkIn, $checkOut])
          ->orWhereBetween('to_date', [$checkIn, $checkOut])
          ->orWhere(function ($q2) use ($checkIn, $checkOut) {
              $q2->where('from_date', '<=', $checkIn)
                 ->where('to_date', '>=', $checkOut);
          });
    })
    ->select('room_category_id', DB::raw('SUM(blocked_room) as blocked'))
    ->groupBy('room_category_id')
    ->pluck('blocked', 'room_category_id');

    /* -------------------------
       BOOKED ROOMS (FIXED CAST)
    -------------------------- */
    $bookedRooms = RoomBookingItem::join('room_bookings', 'room_bookings.id', '=', 'room_booking_items.booking_id')
        ->where('room_bookings.status', 'Active')
        ->where('room_booking_items.status', 'Active')
        ->where(function ($q) use ($checkIn, $checkOut) {
            $q->whereBetween('room_bookings.checkin', [$checkIn, $checkOut])
              ->orWhereBetween('room_bookings.checkout', [$checkIn, $checkOut])
              ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                  $q2->where('room_bookings.checkin', '<=', $checkIn)
                     ->where('room_bookings.checkout', '>=', $checkOut);
              });
        })
        ->select(
            'room_booking_items.room_category_id',
            DB::raw('SUM(CAST(room_booking_items.no_of_rooms AS UNSIGNED)) as booked')
        )
        ->groupBy('room_booking_items.room_category_id')
        ->pluck('booked', 'room_category_id');

    /* -------------------------
       MAIN LOGIC (IMPORTANT CHANGE)
    -------------------------- */
    $charges = RoomChargesMaster::with('room_category','occupant')
        ->where('status', 'Active')
        ->where('category_type_id',auth()->user()->CategoryTypeCode)
        ->where('category_id',auth()->user()->CategoryCode)
        ->get();

    $grouped = $charges->groupBy('room_category_id');

    $rooms = [];

    foreach ($grouped as $roomCategoryId => $chargeRows) {

        $category = RoomCategoryMaster::find($roomCategoryId);
        
        $firstCharge = $chargeRows->first();

$categoryId = $firstCharge->category_id;
$categoryTypeId = $firstCharge->category_type_id;
        if (!$category || $category->status !== 'Active') continue;

        $totalRooms   = $category->no_of_rooms;
        $blockedCount = $blockedRooms[$roomCategoryId] ?? 0;
        $bookedCount  = $bookedRooms[$roomCategoryId] ?? 0;

        // 🔥 NEW: respect charge limit
        $chargeLimit = $chargeRows->sum('no_of_booked_room');

        $available = max(
            0,
            min($totalRooms, $chargeLimit) - $blockedCount - $bookedCount
        );

        if ($available <= 0) continue;

        /* -------------------------
           OCCUPANTS (FILTER BY NIGHTS)
        -------------------------- */
        $occupants = [];
\Log::info(json_encode($category));
        foreach ($chargeRows as $charge) {

            // 🔥 NEW: max nights validation
            if ($charge->max_no_of_nites && $nights > $charge->max_no_of_nites) {
                continue;
            }

            $gstPer    = $category->GST ?? 0;
            $gstAmount = ($charge->charges_nite * $gstPer) / 100;

           $occupants[] = [
    'occupant_id'     => $charge->occupant_type_id,
    'name'            => $charge->occupant->name ?? '',
    'additional_info' => $charge->occupant->additional_info ?? '',
    
    'price_per_night' => (float) $charge->charges_nite,
    'gst_per'         => (float) $gstPer,
    'gst_amount'      => round($gstAmount, 2),
    'total_per_night' => round($charge->charges_nite + $gstAmount, 2),
];
        }

        if (empty($occupants)) continue;

        $rooms[] = [
            'room_category_id' => $category->id,
                'category_id'      => $categoryId,
    'category_type_id' => $categoryTypeId,
            'name'             => $category->name,
            'image'            => $category->room_image,
            'available_rooms'  => $available,
            'occupants'        => $occupants,
        ];
    }
\Log::info(json_encode($rooms));
    return response()->json([
        'status' => true,
        'data' => [
            'check_in'  => $checkIn->toDateString(),
            'check_out' => $checkOut->toDateString(),
            'nights'    => $nights,
            'rooms'     => array_values($rooms),
        ]
    ]);
}


// OLD
// public function getRooms(Request $request)
// {
//     $request->validate([
//         'check_in'  => 'required|date',
//         'check_out' => 'required|date|after:check_in',
//     ]);

//     $checkIn  = Carbon::parse($request->check_in)->startOfDay();
//     $checkOut = Carbon::parse($request->check_out)->startOfDay();
//     $nights   = $checkOut->diffInDays($checkIn);

//     // ðŸ”¹ Settings
//     $settings = AdminSetting::first();
//     $minDays  = $settings->min_days ?? 1;

//     if ($checkIn->lt(now()->addDays($minDays)->startOfDay())) {
//         return response()->json([
//             'status' => false,
//             'message' => "Booking allowed only after {$minDays} days"
//         ], 422);
//     }

//     /* -----------------------------------------
//       BLOCKED ROOMS
//     ----------------------------------------- */
// $blockedRooms = BlockRoom::where(function ($q) use ($checkIn, $checkOut) {
//         $q->whereBetween('from_date', [$checkIn, $checkOut])
//           ->orWhereBetween('to_date', [$checkIn, $checkOut])
//           ->orWhere(function ($q2) use ($checkIn, $checkOut) {
//               $q2->where('from_date', '<=', $checkIn)
//                  ->where('to_date', '>=', $checkOut);
//           });
//     })
//     ->select(
//         'room_category_id',
//         DB::raw('SUM(blocked_room) as blocked')
//     )
//     ->groupBy('room_category_id')
//     ->pluck('blocked', 'room_category_id');

// $bookedRooms = RoomBookingItem::join('room_bookings', 'room_bookings.id', '=', 'room_booking_items.booking_id')
//     ->where('room_bookings.status', 'Active')
//     ->where('room_booking_items.status', 'Active')
//     ->where(function ($q) use ($checkIn, $checkOut) {
//         $q->whereBetween('room_bookings.checkin', [$checkIn, $checkOut])
//           ->orWhereBetween('room_bookings.checkout', [$checkIn, $checkOut])
//           ->orWhere(function ($q2) use ($checkIn, $checkOut) {
//               $q2->where('room_bookings.checkin', '<=', $checkIn)
//                  ->where('room_bookings.checkout', '>=', $checkOut);
//           });
//     })
//     ->select(
//         'room_booking_items.room_category_id',
//         DB::raw('SUM(room_booking_items.no_of_rooms) as booked')
//     )
//     ->groupBy('room_booking_items.room_category_id')
//     ->pluck('booked', 'room_category_id');

//     /* -----------------------------------------
//       ROOM CATEGORIES
//     ----------------------------------------- */
//     $categories = RoomCategoryMaster::where('status', 'Active')->get();

//     $rooms = [];

//     foreach ($categories as $category) {

//         $totalRooms   = $category->no_of_rooms;
//         $blockedCount = $blockedRooms[$category->id] ?? 0;
    
//         $bookedCount = $bookedRooms[$category->id] ?? 0;
      

// $available = max(
//     0,
//     $totalRooms - $blockedCount - $bookedCount
// );


//         if ($available <= 0) continue;

//         /* -----------------------------------------
//           OCCUPANT PRICING
//         ----------------------------------------- */
      
//         $charges = RoomChargesMaster::query()
//     ->join('occupant_masters as om', 'om.id', '=', 'room_charges_masters.occupant_type_id')
//     ->where('room_charges_masters.room_category_id', $category->id)
//     ->where('room_charges_masters.status', 'Active')
//     ->select(
//         'om.id as occupant_id',
//         'om.name',
//         'om.additional_info',
//         'room_charges_masters.charges_nite'
//     )
//     ->distinct()
//     ->get();
//                             // \Log::info(json_encode($roomss));
//         $occupants = [];

// foreach ($charges as $charge) {
//     $gstPer    = $category->GST ?? 0;
//     $gstAmount = ($charge->charges_nite * $gstPer) / 100;

//     $occupants[] = [
//         'occupant_id'     => $charge->occupant_id,
//         'name'            => $charge->name,
//         'additional_info' => $charge->additional_info,
//         'price_per_night' => (float) $charge->charges_nite,
//         'gst_per'         => (float) $gstPer,
//         'gst_amount'      => round($gstAmount, 2),
//         'total_per_night' => round($charge->charges_nite + $gstAmount, 2),
//     ];
// }



//         $rooms[] = [
//             'room_category_id' => $category->id,
//             'name'             => $category->name,
//             'image'            => $category->room_image,
//             'description'      => $category->description,
//             'available_rooms'  => $available,
//             'max_adults'       => 2,   
//             'max_children'     => 1,  
//             'occupants'        => $occupants,
//         ];
//     }
// \Log::info(json_encode($rooms));
//     return response()->json([
//         'status' => true,
//         'data' => [
//             'check_in'  => $checkIn->toDateString(),
//             'check_out' => $checkOut->toDateString(),
//             'nights'    => $nights,
//             'rooms'     => $rooms,
//         ]
//     ]);
// }
public function initiatePayment(Request $request)
{
    $request->validate([
        'check_in'  => 'required|date',
        'check_out' => 'required|date|after:check_in',
        'rooms'     => 'required|array|min:1',
    ]);

    DB::beginTransaction();

    try {
        $member = auth()->user();

        $checkIn  = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        $nights   = $checkOut->diffInDays($checkIn);

        /* ---------------- CREATE BOOKING ---------------- */
        $bookingNumber = now()->format('dmY') . '-' . rand(10000, 99999);

        $booking = RoomBooking::create([
            'booking_number' => $bookingNumber,
            'memberID'       => $member->MemberID,
            'memberName'     => $member->DisplayName,
            'chartID'        => $member->SC_ID,
            'checkin'        => $request->check_in . ' ' . env('CheckIn'),
            'checkout'       => $request->check_out . ' ' . env('CheckOut'),
            'booking_from'   => 'App',
            'status'         => 'Pending',
        ]);

        $amount = 0;
      

        /* ---------------- ROOM ITEMS ---------------- */
        foreach ($request->rooms as $room) {

            $roomChargeTotal =
                ($room['price_per_night'] * $room['quantity']) * $nights;

            $gstAmount =
                ($room['gst_per'] / 100) * $roomChargeTotal;

            RoomBookingItem::create([
                'booking_id'        => $booking->id,
                'category_id'       => $member->CategoryCode,
                'category_type_id'  => $member->CategoryTypeCode,
                'room_category_id'  => $room['room_category_id'],
                'occupant_id'       => $room['occupant_id'],
                'no_of_rooms'       => $room['quantity'],
                'no_of_days'        => $nights,
                'room_charges'      => $room['price_per_night'],
                'room_charge_total' => $roomChargeTotal + $gstAmount,
                'gst_per'           => $room['gst_per'],
                'gst_amount'        => $gstAmount,
                'adult'             => $room['adults'],
                'child'             => $room['children'],
                'guest_name'        => $room['guest_name'] ?? $member->DisplayName,
                'guest_email'       => $room['guest_email'] ?? $member->Email,
                'guest_mobile'      => $room['guest_mobile'] ?? $member->Mobile,
                'status'            =>'Pending'
            ]);

            $amount += ($roomChargeTotal + $gstAmount);
        }

        /* ---------------- TRANSACTION ENTRY ---------------- */
       $payment = app(\App\Services\Payments\PaymentTransactionService::class)->initiate(
            auth()->user(),
            (float) $amount,
            \App\Support\Payments\PaymentModule::ROOM_BOOKING,
            $booking->id,
            [
                'type' => 'Room Booking',
                'prefix' => 'RMB',
            ]
        );

        DB::commit();

  return response()->json([
    'status' => true,
    'data' => [
        'order_id' => $payment['order_id'],
        'amount' => $amount,
        'merchant_order_id' => $payment['merchant_order_id'],
        'txnid'=>  $payment['merchant_order_id'],
        'access_key' => $payment['access_key'] ?? null,
        'payment_url' => $payment['payment_url'] ?? null,
        'gateway' => $payment['gateway'] ?? null,
        'checkout' => $payment['checkout'] ?? null,
        'status_reference' => $payment['status_reference'] ?? $payment['merchant_order_id'],
        'status_endpoint' => $payment['status_endpoint'] ?? null,
        'razorpayKey' => $payment['razorpayKey'] ?? data_get($payment, 'checkout.key'),
        'end_point'   => 'member/room/payment/update'
    ]
]);


    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


public function updatePayment(Request $request, FCMService $fcm)
{
    $member = auth()->user();

    DB::beginTransaction();

    try {
        $reference = data_get($request->all(), 'payment_response.txnid')
            ?? $request->merchant_order_id
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

        // ✅ 3️⃣ If payment success → activate booking
        if ($result['success'] && !$isCentralized) {

            RoomBooking::where('id', $transaction->room_booking_id)
                ->update(['status' => 'Active']);

            RoomBookingItem::where('booking_id', $transaction->room_booking_id)
                ->update(['status' => 'Active']);
        }

       if ($member->device_id) {
            $fcm->sendNotification(
                $member->device_id,
               'Room Booking',
                 $result['success']
                ? 'Your room booked successfully.'
                : 'Your room booking payment failed.',
                 [
                    'id' => $transaction->room_booking_id,
                    'screen' => 'InvoiceDetails'
                ]
            );
        }
        DB::commit();

        // ✅ 5️⃣ Return unified response
        return response()->json($result);

    } catch (\Exception $e) {

        DB::rollBack();

        \Log::error('Room payment update error', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Payment processing failed'
        ]);
    }
}


public function updateBookingNo(Request $request)
{
    $validator = Validator::make($request->all(), [
        'bookings' => 'required|array|min:1',
        'bookings.*.booking_ID' => 'required|string|exists:room_bookings,booking_number',
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
         $room = RoomBooking::where('booking_number', $booking['booking_ID'])->first();
        if ($room) {
            $room->update([
                'BookingNo' => $booking['BookingNo'],
            ]);

            $updated[] = [
                'booking_number' => $room->booking_number,
                'BookingNo' => $room->BookingNo,
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
        $bookings = RoomBooking::select([
                'room_bookings.id',
                'room_bookings.memberID',
                'room_bookings.chartID',
                'room_bookings.booking_number',
                'room_bookings.checkin',
                'room_bookings.checkout',
                'room_bookings.BookingNo',
                'room_bookings.status',

                // ✅ Subquery columns from transactions table
                DB::raw('(SELECT amount FROM transactions WHERE transactions.room_booking_id = room_bookings.id LIMIT 1) as advance_amount'),
                DB::raw('(SELECT order_id FROM transactions WHERE transactions.room_booking_id = room_bookings.id LIMIT 1) as order_id'),
                DB::raw('(SELECT bank_refrance_no FROM transactions WHERE transactions.room_booking_id = room_bookings.id LIMIT 1) as bank_refrance_no'),
            ])
            ->where('room_bookings.BookingNo', 0)
            ->where('room_bookings.status', 'Active')
            ->where('room_bookings.status', '!=', 'Cancelled')
            ->with(['roomItem' => function ($query) {
                $query->select([
                        'id',
                        'booking_id',
                        'category_id',
                        'category_type_id',
                        'room_category_id',
                        'occupant_id',
                        'no_of_rooms',
                        'no_of_days',
                        'room_charges',
                        'room_charge_total',
                        'gst_per',
                        'gst_amount',
                        'adult',
                        'child',
                        'guest_name',
                        'status'
                    ])
                    ->where('status', 'Active');
                    // ->where('status', '!=', 'Cancelled');
            }])
            ->orderByDesc('room_bookings.id')
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
// public function updatePayment(Request $request)
// {
//     // $request->validate([
//     //     'razorpay_order_id'   => 'required',
//     //     'razorpay_payment_id' => 'required',
//     //     'razorpay_signature'  => 'required',
//     //     'status'              => 'required|boolean',
//     // ]);

//     $trans = DB::table('transactions')
//         ->where('transID', $request->razorpay_order_id)
//         ->latest()
//         ->first();

//     if (!$trans) {
//         return response()->json([
//             'status' => false,
//             'message' => 'Transaction not found',
//              'data' => [
//                     'MemberID' => auth()->user()->MemberID,
//                 'MemberName' => auth()->user()->DisplayName,
//                 'MemberSCID' => auth()->user()->SC_ID,
//                 'TransactionID' => $request->razorpay_payment_id ??'N/A',
//                 'Status' => $request->status == true ? 'Success' : 'Failed',
//                 'paid_amount'=>0,
//                 'reference_number'=>'N/A'
//             ]
//         ]);
//     }
   

//     try {
    
//         $paymentStatus = $request->status ? 'Paid' : 'Failed';
//  \Log::info(['payment_status'   => $paymentStatus,
//                 'razorpay_payment_id' => $request->razorpay_payment_id,
//                 'bank_refrance_no' => $request->razorpay_payment_id?$request->razorpay_payment_id :null,
//                 'bank_response'    => json_encode($request->razorpay_response),]);
//   DB::table('transactions')
//             ->where('id', $trans->id)
//             ->update([
//                 'payment_status'   => $paymentStatus,
//                 'razorpay_payment_id' => $request->razorpay_payment_id,
//                 'bank_refrance_no' => $request->razorpay_payment_id?$request->razorpay_payment_id :null,
//               'bank_response' => $request->razorpay_response
                
//             ]);
//  \Log::info(json_encode($trans));
//         if ($paymentStatus === 'Paid') {
//             RoomBooking::where('id', $trans->room_booking_id)
//                 ->update(['status' => 'Active']);
//         }

//         return response()->json([
//             'status' => true,
//             'message' => 'Payment updated successfully',
//             'data' => [
//                     'MemberID' => auth()->user()->MemberID,
//                 'MemberName' => auth()->user()->DisplayName,
//                 'MemberSCID' => auth()->user()->SC_ID,
//                 'TransactionID' => $request->razorpay_payment_id,
//                 'Status' => $request->status == true ? 'Success' : 'Failed',
//                 'paid_amount'=>$trans->amount,
//                 'reference_number'=>$trans->order_id
//             ]
//         ]);

//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => false,
//             'message' => 'Payment verification failed',
//             'data' => [
//                     'MemberID' => auth()->user()->MemberID,
//                 'MemberName' => auth()->user()->DisplayName,
//                 'MemberSCID' => auth()->user()->SC_ID,
//                 'TransactionID' => $request->razorpay_payment_id ,
//                 'Status' => $request->status == true ? 'Success' : 'Failed',
//                 'paid_amount'=>$trans->amount,
//                 'reference_number'=>$request->razorpay_order_id
//             ]
//         ], 422);
//     }
// }

}
