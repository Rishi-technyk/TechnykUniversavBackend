<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\OccupantMaster;
use Illuminate\Http\Request;
use App\Models\FunctionMaster;
use App\Models\BanquetBooking;
use App\Models\VenueMaster;
use App\Models\VenueCharge;
use App\Models\Member;
use App\Models\AdminSetting;
use App\Models\SOP;
use App\Models\BanquetBookingCharges;
use App\Models\VenuePax;
use App\Models\CancellationPolicy;
use Illuminate\Support\Facades\Validator;
use AESEncDec;
use DB;
use Razorpay\Api\Api;

use Illuminate\Support\Facades\Log;

class BanquetBookingController extends Controller
{
   public function get_menuVenue(Request $request)
{
    $user = auth()->user();
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
        ], 401);
    }

    $funDate = $request->input('funDate');
    $noofPerson = $request->input('noofPerson');

    \Log::info("get_menuVenue called", ['funDate' => $funDate, 'noofPerson' => $noofPerson]);

    // ✅ Fetch SOP, functions, occupants, settings
    $sop = SOP::where('type', 'Banquet Booking')->first(['id', 'content', 'type']);
    $occupant = OccupantMaster::where('status', 'Active')->get();
    $functions = FunctionMaster::where('status', 'Active')->orderBy('id', 'DESC')->get();
    $setting = AdminSetting::first();
    \Schema::getColumnListing('banquet_booking_charges');

    // ✅ Step 1: Get all booked session IDs per venue for the selected date
   $bookings = DB::table('banquet_bookings as b')
    ->join('banquet_booking_charges as c', 'b.id', '=', 'c.banquet_booking_id')
    ->where('b.funDate', $funDate)
    ->where('b.status', 'Active')
    ->select('c.vanue_id as venue_id', 'c.session_id')  // ✅ Corrected here
    ->get();

 $bookedMap = [];
// ✅ Step 2: Get all blocked sessions for the date (from `venue_blocks`)
$blockedSessions = DB::table('venue_blocks')
    ->whereDate('from_date', '<=', $funDate)
    ->whereDate('to_date', '>=', $funDate)
    ->select('venue_id', 'session_id')
    ->get();

// ✅ Add blocked sessions to the same $bookedMap
foreach ($blockedSessions as $block) {
    $bookedMap[$block->venue_id][] = $block->session_id;
}

// Optional: Deduplicate session IDs for each venue
foreach ($bookedMap as $venueID => &$sessionIDs) {
    $sessionIDs = array_unique($sessionIDs);
}
   

    foreach ($bookings as $booking) {
        $bookedMap[$booking->venue_id][] = $booking->session_id;
    }


    // ✅ Get sessions
    $sessions = DB::table('sessions')->where('status', 'Active')->orderBy('id', 'DESC')->get();

    // ✅ Get all venues
    $allVenues = VenueMaster::where('status', 'Active')->orderBy('id', 'DESC')->get();

    // ✅ Match VenuePax rule
    $matchedPaxRule = VenuePax::where('min_pax', '<=', $noofPerson)
        ->where('max_pax', '>=', $noofPerson)
        ->first();

    if ($matchedPaxRule) {
        $groupId = $matchedPaxRule->group_id;
        \Log::info("Group ID found", ['group_id' => $groupId]);

        if ($groupId !== null) {
            // ✅ Filter by group_id
            $filteredVenues = $allVenues->where('group_id', $groupId)->values();
        } else {
            // ✅ No group, return all
            $filteredVenues = $allVenues;
        }
    } else {
        // ✅ No rule matched, return empty venue list
        \Log::info("No matching rule for pax", ['pax' => $noofPerson]);
        $filteredVenues = collect(); // empty collection
    }

 

$availableVenues = $filteredVenues->map(function ($venue) use ($bookedMap, $sessions) {
    // Filter sessions which are not booked
    $venue->sessions = $sessions->filter(function ($session) use ($venue, $bookedMap) {
        return !in_array($session->id, $bookedMap[$venue->id] ?? []);
    })->values();

    // Attach venue charges (decoded arrays because of casts in model)
    $venue->venue_charges = VenueCharge::where('venue_id', $venue->id)->get();

    return $venue;
});
  

    $maxPax = DB::table('venue_paxs')->get();

    return response()->json([
        'success' => true,
        'max' => $maxPax,
        'sop' => $sop,
        'venues' => $availableVenues,
        'sessions' => $sessions,
        'functions' => $functions,
        'occupants' => $occupant,
        'settings' => $setting,
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
//     public function banquet_traction(Request $request)
// {
//     $member = Member::where("memberprofile.id", auth()->user()->id)
//         ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')
//         ->first();

//     $search = $request->query('search');
//     $type = $request->query('type');
//     $limit = $request->query('limit', 10);
//     $filterStatus = $request->query('status');

//     // Mark bookings as Cancelled if all related charges are Cancelled
//     DB::table('banquet_bookings')
//         ->whereNotIn('status', ['Cancelled'])
//         ->whereExists(function ($query) {
//             $query->select(DB::raw(1))
//                 ->from('banquet_booking_charges')
//                 ->whereColumn('banquet_booking_charges.banquet_booking_id', 'banquet_bookings.id');
//         })
//         ->whereNotExists(function ($query) {
//             $query->select(DB::raw(1))
//                 ->from('banquet_booking_charges')
//                 ->whereColumn('banquet_booking_charges.banquet_booking_id', 'banquet_bookings.id')
//                 ->where('status', '!=', 'Cancelled');
//         })
//         ->update(['status' => 'Cancelled']);

//     // Mark bookings as Completed if no upcoming or pending charges
//     // DB::table('banquet_bookings')
//     //     ->whereNotIn('status', ['Completed'])
//     //     ->whereNotExists(function ($query) {
//     //         $query->select(DB::raw(1))
//     //             ->from('banquet_booking_charges')
//     //             ->whereColumn('banquet_booking_charges.banquet_booking_id', 'banquet_bookings.id')
//     //             ->where(function ($subQuery) {
//     //                 $subQuery->whereDate('funDate', '>=', now())
//     //                          ->orWhere('status', 'Active');
//     //             });
//     //     })
//     //     ->update(['status' => 'Completed']);

//     // Query builder
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

//     return response()->json([
//         'status' => true,
//         'data' => $datas
//     ]);
// }

public function banquet_traction(Request $request)
{
    $member = Member::where("memberprofile.id", auth()->user()->id)
        ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')
        ->first();

    $limit = $request->query('limit', 10);
    $filterStatus = $request->query('status');

    /* -------------------------------
       AUTO STATUS UPDATES
    --------------------------------*/

    // ðŸ”´ Cancelled â†’ when ALL charges are cancelled
    DB::table('banquet_bookings')
        ->whereNotIn('status', ['Cancelled'])
        ->whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('banquet_booking_charges')
                ->whereColumn(
                    'banquet_booking_charges.banquet_booking_id',
                    'banquet_bookings.id'
                );
        })
        ->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('banquet_booking_charges')
                ->whereColumn(
                    'banquet_booking_charges.banquet_booking_id',
                    'banquet_bookings.id'
                )
                ->where('status', '!=', 'Cancelled');
        })
        ->update(['status' => 'Cancelled']);

    // âœ… Completed â†’ ONLY when payment is PAID & no pending charges
    DB::table('banquet_bookings')
        ->where('payment_status', 'Paid')
        ->whereNotIn('status', ['Completed', 'Cancelled'])
        ->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('banquet_booking_charges')
                ->whereColumn(
                    'banquet_booking_charges.banquet_booking_id',
                    'banquet_bookings.id'
                )
                ->where('status', 'Pending');
        });

    /* -------------------------------
       MAIN DATA
    --------------------------------*/

    $q = BanquetBooking::where('memberID', $member->MemberID)
        ->with('occupant', 'function')
        ->orderBy('id', 'DESC');

    if ($request->function_date) {
        $q->whereDate('funDate', $request->function_date);
    }

    if ($request->booking_no) {
        $q->where('booking_ID', $request->booking_no);
    }

    if (!empty($filterStatus)) {
        $q->where('status', $filterStatus);
    }

    $datas = $q->paginate($limit);

    // ðŸŸ¡ Normalize empty / null status â†’ Pending
    $datas->getCollection()->transform(function ($item) {
        if (empty($item->status)) {
            $item->status = 'Pending';
        }
        return $item;
    });

    /* -------------------------------
       UPCOMING BOOKINGS
    --------------------------------*/

  $upcomingBookings = BanquetBooking::where('memberID', $member->MemberID)
    ->whereDate('funDate', '>=', now())
    ->whereNotIn('status', ['Cancelled', 'Completed'])
    ->orderBy('funDate', 'ASC')
    ->get()
    ->map(function ($item) {
        return [
            'id' => $item->id,
            'type' => 'hall',
            'title' => $item->booking_ID,
            'date' => $item->funDate,
            'status' => $item->status ?: 'Pending',
            'payment_status' => $item->payment_status,
        ];
    });


    return response()->json([
        'status' => true,
        'data' => $datas,
        'upcoming_bookings' => $upcomingBookings
    ]);
}

    public function details($id='')
    {
        $datas = BanquetBooking::find($id);
    $policys = CancellationPolicy::first();
        if($datas){

            $datas->payment_info = DB::table('transactions')->where('banquet_booking_id', $id)->first();
       
            $datas->bookings = BanquetBookingCharges::where('banquet_booking_id', $id)->with('venue')->with('session')->get();

            $datas->occupant; 

            $datas->function;

        }

        $return_data['data'] = $datas;

        $return_data['status'] = true;
         $return_data['policy'] = $policys;

        return response()->json($return_data);
    }

    public function banquet_store(Request $request)
    {
          $validator = Validator::make($request->all(), [
        'occupant_type'   => 'required', // Assuming 1 and 2 are valid types
        'funDate'         => 'required|date|after_or_equal:today',
        'functionType'    => 'required',
        'noofPerson'      => 'required|integer|min:1',
        'venue_details.*.sessionID'     => 'required|integer',
        'venue_details.*.venueID'       => 'required|integer',
      
        // Optional fields
        'memberName'      => 'nullable|string|max:255',
        'memberMobile'    => 'nullable|string|max:15',
        'memberEmail'     => 'nullable|email',
        'address'         => 'nullable|string|max:255',
        'remark'          => 'nullable|string|max:255',
        'charge'        =>'required|numeric|min:0'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ], 422);
    }
        $funDate = \Carbon\Carbon::parse($request->funDate);

    // 1. Check for Tuesday
    if ($funDate->isTuesday()) {
        return response()->json([
            'status' => false,
            'message' => 'Booking not allowed: Tuesday is weekly off.'
        ], 422);
    }

    // 2. Check for national holidays
    $restrictedDates = [
        '01-26', // 26th Jan
        '08-15', // 15th Aug
        '10-02'  // 2nd Oct
    ];

    if (in_array($funDate->format('m-d'), $restrictedDates)) {
        return response()->json([
            'status' => false,
            'message' =>'Booking not allowed: Selected date is a national holiday.'
        ], 422);
    }

     $bookingID = now()->format('dmY') . '-' . rand(10000, 99999);

    // Step 3: Prepare main booking params
    $params = [
        'occupant_type'   => $request->occupant_type,
        'memberID'        => auth()->user()->MemberID,
        'cardID'          => auth()->user()->SC_ID,
        'memberName'      => $request->memberName ?? auth()->user()->DisplayName,
        'memberMobile'    => $request->memberMobile ?? auth()->user()->Phone,
        'memberEmail'     => $request->memberEmail ?? auth()->user()->Email,
        'address'         => $request->address ?? '',
        'funDate'         => $request->funDate,
        'functionType'    => $request->functionType,
        'noofPerson'      => $request->noofPerson,
        'remark'          => $request->remark ?? '',
        'booking_ID'      => $bookingID,
    ]; 
    // Step 4: Create main booking
    $booking = BanquetBooking::create($params);

    if (!$booking) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to create banquet booking.'
        ], 500);
    }

        if($booking){

          $venueCount = count($request->venue_details);

    // Calculate per-venue charge (divide equally)
    $perCharge = $venueCount > 0 ? ($request->charge / $venueCount) : 0;
             foreach ($request->venue_details as $detail) {
        BanquetBookingCharges::create([
            'banquet_booking_id' => $booking->id,
            'session_id'         => $detail['sessionID'],
            'vanue_id'           => $detail['venueID'],
            'status'            =>'Pending',
            'payment_status'    =>'Not Paid',
            'charges'             => $perCharge,
            'total'             => $perCharge,
            'funDate'           =>$request->funDate
        ]);
    }


            $banquet_details_amt = $request->charge;
             $member = Member::where("memberprofile.id",auth()->user()->id)->first();
            $SC_ID = $member->SC_ID;
            $RechargeAmount = $banquet_details_amt;
            $txnid = 'BQB'.substr(hash('sha256', mt_rand() . microtime()), 0, 20);
            $PayStatus ="Pending";
            // Extract user data
             $MemID = $member->MemberID;
          
            $MemberName = $member->DisplayName;
            $MobileNo = $member->Mobile;
            $Email = $member->Email;
            $Category = $member->CategoryTypeCode;
            $Status = $member->Status;
            
            // $key =    config('services.razorpay.key');
            // $secret = config('services.razorpay.secret');
            

            $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
            
            $notes = [
    'member_id'      => $member->MemberID,
    'sc_id'          => $SC_ID,
    'booking_type'   => 'Banquet Booking',
    'member_name'    => $member->DisplayName,
    'member_email'   => $member->Email,
    'member_mobile'  => $member->Phone,
    'booking_id'     => $bookingID,
    'total_amount'   => $banquet_details_amt,
    'function_date'  => $request->funDate,
];
                        $order = $api->order->create([
                'receipt' => $txnid,
                'amount' => $banquet_details_amt * 100, // Amount in paise (100 paise = 1 INR)
                'currency' => 'INR',
                'notes' =>$notes
        //         'notes' => [
        // 'notes_1' => $member->MemberID,
        // 'notes_2' => $SC_ID,
        // 'notes_3' => 'Banquet Booking',
        // 'notes_4' => '',
        // 'notes_5' => $member->DisplayName,
        //         ]
            ]);
            
            DB::table('transactions')->insert([
            'member_id' => $SC_ID,
            'amount'=>$banquet_details_amt,
               'order_id'=>$txnid,
               'payment_status'=> 'Not Paid',
               'type'=>'Banquet Booking',
               	'transaction_date'=>now(),
               	'banquet_booking_id'=>$booking->id,
               	'razorpay_order_id'=>$order['id'],
               	'transID'=>$bookingID

        ]);
            
             $data = [
                        'orderId' => $order['id'], 
                        'amount' => $banquet_details_amt, 
                        'razorpayKey' => config('services.razorpay.key')
                    ];
        	$return_data['data'] = $data;
            $return_data['message'] = 'Submitted Successfully';
            $return_data['status'] = true;
           
        } else {

            $return_data['status'] = false;
            $return_data['msg'] = 'Try Again';
        }

        return response()->json($return_data);
    }

   private function getAccessToken() {
        $serviceAccountData = json_decode(file_get_contents('https://teebooking.aepta.in/AEPTAServiceAccountKey.json'), true);
    
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
        $url = 'https://fcm.googleapis.com/v1/projects/aepta-edc61/messages:send';
        $serverKey = $this->getAccessToken();
        $data = [
            "message" => [
                "token" => $fcmTokens,
                "notification" => [
                    "title" => $notification['title'],
                    "body" => $notification['short_descriptions'],
                    "image" => 'https://aepta.in/wp-content/uploads/2023/08/aptalogo.png'
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
    
    
    public function update_payment(Request $request)
    {
        if($request->status){

            $status = 'Paid';

        } else {

            $status = 'Not Paid';

        }

$member=auth()->user();
        $trans = DB::table('transactions')->where('razorpay_order_id', $request->razorpay_order_id)->latest()->first();

        if($trans){

            if($status){
                $generatedSignature = hash_hmac('sha256', $request->razorpay_order_id . "|" . $request->razorpay_payment_id, config('services.razorpay.secret'));
                $paramss['razorpay_signature']       = $request->status ==true ?$generatedSignature :null;
                $r_params['status'] = $request->status ==true ?'Active':"Failed";
                $r_params['payment_status'] = $request->status ==true ?'Paid':"Not Paid";
                BanquetBooking::where('id', $trans->banquet_booking_id)->update($r_params);
                BanquetBookingCharges::where('banquet_booking_id', $trans->banquet_booking_id)
    ->update($r_params);
                
            }
           
           
            

            $paramss['payment_status']      = $status;
            $paramss['razorpay_payment_id']    = $request->razorpay_payment_id ??null;
           
            DB::table('transactions')->where('razorpay_order_id', $request->razorpay_order_id)->update($paramss);

            $return_data['message'] = 'Payment Updated.';
 $notification = [
                    'title' => 'Banquet Booking',
                    'short_descriptions' => $request->status == true ? 'Your Banquet booked successfully.' : 'Your payment is failed.',
                ];    
                $this->sendFCMMessage($notification, auth()->user()->device_id); 
            $return_data['status'] = $request->status == true ; 
            $data['Status']=$request->status == true ? 'Success' : 'Failed'; 
             $data['paid_amount'] = $trans->amount;
             $data['reference_number']=$request->razorpay_payment_id;
              $data['orderId']=$request->razorpay_order_id;
              $data['MemberSCID']=$member->SC_ID; 
              $data['MemberName']=$member->DisplayName;
               $data['MemberID']=$member->MemberID;
                  
        } else {

            $return_data['message'] = 'Data not found.';

            $return_data['status'] = false; 
            $data['Status']='Failed'; 
             $data['paid_amount'] = $trans->amount ??0;
             $data['reference_number']=$request->razorpay_payment_id??"N/A";
              $data['orderId']=$request->razorpay_order_id ??"N/A";

        }
        
       return response()->json([
    'message' => $return_data['message'],
    'status' => $return_data['status'],
    'data' => $data
]);
        
    }

public function cancelVenue(Request $request)
{
    $booking =  BanquetBooking::find($request->ban_charge_id);

    if (!$booking) {
        return response()->json([
            'msg' => 'Data not found.',
            'status' => false
        ]);
    }

    $policys = CancellationPolicy::all();

    Log::info('Loaded cancellation policies', ['policies' => $policys]);

    if (!$policys) {
        return response()->json([
            'msg' => 'Cancellation policy is not available for this venue.',
            'status' => false
        ]);
    }

    $cdate = date('Y-m-d');
    $startTimeStamp = strtotime($booking->funDate);
    $endTimeStamp = strtotime($cdate);
    $timeDiff = abs($endTimeStamp - $startTimeStamp);
    $numberDays = intval($timeDiff / 86400);


    $policy = null;

    foreach ($policys as $ploy) {
       $from_days = (int) $ploy->from_days;
        $to_days = (int) $ploy->to_days;

       

        if ($numberDays >= $from_days && $numberDays <= $to_days) {
            $policy = $ploy;
            break;
        }
    }
    if (!$policy) {
        return response()->json([
            'msg' => 'No applicable cancellation policy found for this date.',
            'status' => false
        ]);
    }
    $charge= DB::table('transactions')->where('banquet_booking_id', $request->ban_charge_id)->first();
  
    $percentage = (float) $policy->GST;
    $totalCharge = (float) $charge->amount;

    $cancellationAmt = ($policy->deduction / 100) * $totalCharge;
    $gstAmt = ($percentage / 100) * $cancellationAmt;
    $totalDeduction = $cancellationAmt + $gstAmt;

    $params = [
        'cancellation_per' => $policy->deduction,
        'cancellation_amt' => $cancellationAmt,
        'cancellation_GST' => $percentage,
        'cancellation_GST_amt' => $gstAmt,
        'cancellation_deducation' => $totalDeduction,
        'cancellation_date' => now(),
        'status' => 'Cancelled'
    ];

    BanquetBooking::whereId($booking->id)->update($params);
    BanquetBookingCharges::where('banquet_booking_id', $booking->id)
    ->update($params);
    return response()->json([
        'msg' => 'Banquet cancelled successfully as per policy.',
        'status' => true,
        'data' => [
            'deduction_percentage' => $policy->deduction,
            'deduction_amount' => $cancellationAmt,
            'GST_percentage' => $percentage,
            'GST_amount' => $gstAmt,
            'total_deduction' => $totalDeduction,
            'policy_applied' => $policy
        ]
    ]);
}

}
