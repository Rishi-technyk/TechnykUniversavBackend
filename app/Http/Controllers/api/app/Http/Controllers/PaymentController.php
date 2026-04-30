<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BanquetBookingCharges;
use App\Models\BanquetBooking;
use Illuminate\Support\Str;
use App\Models\RoomBooking;
use App\Models\GameBooking;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\HdfcGatewayService;
use Session;
use Auth;
use DB;
use App\Services\FCMService;
use App\Models\MemberReceipt;

class PaymentController extends Controller
{
    public function HDFC_checkout(Request $request)
    {
        // return $request;
        return view('payments.HDFC.proceed',compact('request'));
    }

    public function HDFC_payment_response(Request $request)
    {
        $tt = DB::table('transactions')->where('order_id', $request->order_id)->first();

        $member = Member::where("memberprofile.SC_ID",$tt->member_id)->first();

        $bank_response = $this->checkPaymentStatus($request->order_id);

        $randomNumber = $request->session()->get('randomNumber');
        $pwd=$member->Password ?? '';
        $sha256Hash=$pwd.$randomNumber;
        $sha256Hash = hash('sha256', $sha256Hash);        
           
        $sessionToken = Str::random(60);    
        session(['session_token' => $sessionToken]);    
        $member->session_token = $sessionToken;
        $member->save();

        Auth::login($member);
        
        if($bank_response){

            $decryptValues = '';
            
            if($bank_response->amount==$tt->amount) {

                if($bank_response->status=='CHARGED'){

                    $status = 'Paid';

                    $decryptValues = 'customer_phone : '. $bank_response->customer_phone. ' | customer_id : '. $bank_response->customer_id . ' | status : '. $bank_response->status . ' | id : '. $bank_response->id . ' | amount : '. $bank_response->amount . ' | order_id : '. $bank_response->order_id. ' | payment_method_type : '. $bank_response->payment_method_type . ' | payment_method : '. $bank_response->payment_method. ' | date_created : '. $bank_response->date_created;

                } elseif ($bank_response->status=='PENDING_VBV' || $bank_response->status=='AUTHORIZING' || $bank_response->status=='STARTED') {
                    
                    $status = 'Pending';

                } elseif ($bank_response->status=='AUTHENTICATION_FAILED' || $bank_response->status=='AUTHORIZATION_FAILED' || $bank_response->status=='AUTO_REFUNDED' || $bank_response->status=='PARTIAL_CHARGED') {
                    
                    $status = 'Failed';
                    
                } else {

                    $status = 'Not Paid';

                }

            } else {

                $status = 'Not Paid';

            } 
        }

        

        $order_id = $request->order_id;

        $paramss['payment_status']  = $status;
        $paramssm['PayStatus']      = $status;
        $recharge['PayStatus']      = $status;

        $paramss['bank_refrance_no']    = $bank_response->id;
        $paramss['bank_response']       = $decryptValues;
       
        $paramssm['BankRefrenceNo']     = $bank_response->id;
        $paramssm['PaymentResponse']    = $decryptValues;

        $recharge['BankRefrenceNo']     = $bank_response->id;
        $recharge['OrderResponse']      = $decryptValues;

        DB::table('transactions')->where('order_id', $order_id)->update($paramss);

        DB::table('MemberReceipts')->where('TransactionID', $order_id)->update($paramssm);

        DB::table('CardRecharge')->where('TransactionID', $order_id)->update($recharge);

        $data_re = DB::table('CardRecharge')->where('TransactionID', $order_id)->where('PayStatus','Paid')->first();
      
        if($data_re){

            $data_close = DB::table('CardClosingBalance')->where('MemberID', $data_re->Card_ID)->first();

            if($data_close && $data_close->CardBalance){
                $CardBalance = $data_close->CardBalance; 
            } else {
                $CardBalance = '0';
            }

            $close['CardBalance'] = $data_re->RechargeAmt + $CardBalance;

            DB::table('CardClosingBalance')->where('MemberID', $data_re->Card_ID)->update($close);

        }

        $trans = DB::table('transactions')->where('order_id', $order_id)->latest()->first();

        if($trans && $trans->type=='Banquet Booking'){

            $banq_params['payment_status'] = $status;

            BanquetBookingCharges::where('banquet_booking_id', $trans->banquet_booking_id)->update($banq_params);

            BanquetBooking::where('id', $trans->banquet_booking_id)->update($banq_params);

            if($status=='Paid'){

                $trans = BanquetBookingCharges::where('banquet_booking_id', $trans->banquet_booking_id)->get();

                foreach ($trans as $key => $value) {

                    $check = BanquetBookingCharges::where('id', '!=' , $value->id)->where('status', 'Active')->where('payment_status', 'Paid')->whereDate('funDate', $value->funDate)->where('session_id', $value->session_id)->where('vanue_id', $value->vanue_id)->first();

                    if($check){

                        $check_params['status'] = 'Pending';

                        BanquetBookingCharges::where('banquet_booking_id', $value->banquet_booking_id)->update($check_params);

                        BanquetBooking::where('id', $value->banquet_booking_id)->update($check_params);

                        break;
                        
                    } else {

                        $check_params['status'] = 'Active';

                        BanquetBookingCharges::where('banquet_booking_id', $value->banquet_booking_id)->update($check_params);

                        BanquetBooking::where('id', $value->banquet_booking_id)->update($check_params);

                    }                    
                    
                }

            }
            

        } elseif ($trans && $trans->type=='Room Booking' && $status=='Paid'){

            $r_params['status'] = 'Active';

            RoomBooking::where('id', $trans->room_booking_id)->update($r_params);

        } elseif ($trans && $trans->type=='Facility Booking' && $status=='Paid'){

            $g_params['status'] = 'Active';
            $g_params['payment_status'] = 'Paid';

            GameBooking::where('id', $trans->game_booking_id)->update($g_params);

        }
        
        return redirect('payment-confirm/'.$order_id);

    }

    public function checkPaymentStatus($order_id='')
    {        
        // Test QUM0QzdFMzc5MkY0OUE0QkY5NDdGMDhGRDJFOTI2Og==
        // Live 

        // Test URL https://smartgatewayuat.hdfcbank.com/orders/{order_id}
        // Live URL https://smartgateway.hdfcbank.com/orders/{order_id}

        $curl = curl_init();


        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://smartgatewayuat.hdfcbank.com/orders/'.$order_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'x-merchantid: SG2559',
                'Authorization: Basic QUM0QzdFMzc5MkY0OUE0QkY5NDdGMDhGRDJFOTI2Og=='
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        
        return json_decode($response);

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
    
        private function initCardRechargeProcess($cardId, $rechargeAmount, $payStatus, $txnid, $orderResponse)
    {
          DB::table('transactions')->insert([
            'member_id' => $cardId,
            'amount'=>$rechargeAmount,
               'order_id'=>$txnid,
               'payment_status'=> 'Not Paid',
               'type'=>'Card Recharge',
               	'bank_refrance_no'=>'',
               	'bank_response'=>'',
               
               	'transaction_date'=>now(),

        ]);
        return DB::table('CardRecharge')->insertGetId([
            'Card_ID' => $cardId,
            'RechargeAmt' => $rechargeAmount,
            'RechargeDate' => now()->format('Y-m-d'),
            'PayStatus' => $payStatus,
            'TxnRefrenceNo' => '',
            'BankRefrenceNo' => '',
            'TransactionID' => $txnid,
            'ImportStatus' => 1,
            'PaymentResponse' => '',
            'TransactionType'=>"Card Recharge",
            'OrderResponse'=>json_encode($orderResponse),
            'PayMode'=>'Mobile',
             'WebhookResponse' => '',      
        ]);
    }
   public function createPayOrder(Request $request)
    {
        Log::info(auth()->user()->id);
        $user = Member::where("id",auth()->user()->id)->first();
        if ($user) {            
            $SC_ID = $user->SC_ID;
            $RechargeAmount = $request->amount;
            $txnid = 'MCR'.substr(hash('sha256', mt_rand() . microtime()), 0, 20);
            $PayStatus ="Pending";
            // Extract user data
            $MemID = $user->MemberID;
          
            $MemberName = $user->DisplayName;
            $MobileNo = $user->Mobile;
            $Email = $user->Email;
            $Category = $user->CategoryTypeCode;
            $Status = $user->Status;

          Log::info('Checking SC_ID', ['SC_ID' => $SC_ID]);
              $user = Member::where("id",auth()->user()->id)->first();
                $key = config('services.razorpay.key');
$secret = config('services.razorpay.secret');

Log::info('Initializing Razorpay API', [
    'razorpay_key' => $key,
    // 'razorpay_secret' => $secret, // ⚠️ Avoid logging secrets unless absolutely necessary!
]);

$api = new Api($key, $secret);

        // $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
            $order = $api->order->create([
                'receipt' => $txnid,
                'amount' => $request->amount * 100, // Amount in paise (100 paise = 1 INR)
                'currency' => 'INR',
                'notes' => [
        'notes_1' => $user->MemberID,
        'notes_2' => $SC_ID,
        'notes_3' => 'Card Recharge',
        'notes_4' => '',
        'notes_5' => $user->DisplayName,
                ]
            ]);

            // Delete pending recharge records for the user
            DB::table('CardRecharge')->where('Card_ID', $SC_ID)->where('PayStatus', 'pending')->delete();
    
            // Insert new recharge record
            $rechargeID=DB::table('CardRecharge')->insertGetId([
                'Card_ID' => $SC_ID,
                'RechargeAmt' => $RechargeAmount,
                'RechargeDate' => now(),
                'PayStatus' => $PayStatus,
                'TxnRefrenceNo' => $order['id'],
                'BankRefrenceNo' => '',
                'TransactionID' => $txnid,
                'ImportStatus' => 1,
                'PaymentResponse' => '',
                'OrderResponse' => json_encode($order->toArray()),
                'TransactionType'=>'MCR',
                'PayMode'=>'mobile',
                'WebhookResponse'=>''
            ]);
             $trans_number = now()->format('dmY') . '-' . random_int(10000, 99999);
             DB::table('transactions')->insert([
            'member_id' => $SC_ID,
            'amount'=>$RechargeAmount,
               'order_id'=>$txnid,
               'payment_status'=> 'Not Paid',
               'type'=>'Card Recharge',
               	'transaction_date'=>now(),
               	'order_id'=>$order['id'],
               	'transID'=>$trans_number

        ]);
            $data = [
                        'orderId' => $order['id'], 
                        'amount' => $request->amount, 
                        'razorpayKey' => config('services.razorpay.key'),
                         'end_point'=> 'card_recharge_response',
                    ];
        	$return_data['data'] = $data;
            $return_data['message'] = '';
            $return_data['status'] = true;
        } else {
            $return_data['data'] = '';
            $return_data['message'] = 'User not found';
            $return_data['status'] = false;
        }
        return response()->json($return_data , 200);
    }

  private function updateCardClosingBalance($memberId, $rechargeAmount, $receiptDate)
{
    $existing = DB::table('CardClosingBalance')->where('MemberID', $memberId)->first();

    if (!$existing) {
        DB::table('CardClosingBalance')->insert([
            'RecNo' => 0,
            'MemberID' => $memberId,
            'CardBalance' => $rechargeAmount,
            'ClosingDate' => $receiptDate,
        ]);
        Log::info("Created new CardClosingBalance for MemberID {$memberId}");
    } else {
        DB::table('CardClosingBalance')
            ->where('MemberID', $memberId)
            ->update([
                'CardBalance' => DB::raw("CardBalance + $rechargeAmount"),
                'ClosingDate' => $receiptDate,
            ]);
        Log::info("Updated CardClosingBalance for MemberID {$memberId}");
    }
}
   public function cardRechargeResponse(Request $request )
 {
        // Validate request parameters
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_response' => 'required|string',
            'status' => 'required|bool'
        ]);
                $user = Member::where("memberprofile.id",auth()->user()->id);
                Log::info($user);
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        $attributes = [
            'razorpay_response' => $request->razorpay_response,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_order_id' => $request->razorpay_order_id,
            'status' => $request->status,
        ];

        $payment_response = json_encode($attributes);
        $cardRecharge = DB::table('CardRecharge')->where('TransactionID', $request->razorpay_order_id)->first();

        try {
            // // Verify the payment signature
            // $api->utility->verifyPaymentSignature($attributes);            

            if (isset($cardRecharge)) {
                $cardRecharge->PayStatus = $request->status == true ? 'Success' : 'Failed';
                $cardRecharge->BankRefrenceNo = $request->razorpay_payment_id;
                $cardRecharge->PaymentResponse = $payment_response;
                $cardRecharge->save(); // Now save() will work
            }
            
              DB::table('transactions')
         ->where('order_id', $request->razorpay_order_id)
         ->update([
               'payment_status'=>$request->status == true ? 'Paid' : 'Failed',
               	'bank_refrance_no'=>$request->razorpay_payment_id?$request->razorpay_payment_id :null,
               	'transaction_date'=>now(),
        ]);
      
          $transaction = DB::table('transactions')
    ->where('order_id', $request->razorpay_order_id)
    ->first();
            $notification = [
                'title' => 'Card Recharge',
                'short_descriptions' => $request->status == true ? 'Your card is recharged by Rs. ' . number_format($transaction->amount, 2): 'Your card recharge payment has failed.',
            ];    
            $this->sendFCMMessage($notification, auth()->user()->device_id);
   if ($request->status == true) {
          
               $this->updateCardClosingBalance($user->SC_ID, $transaction->amount, $transaction->transaction_date);
        }
            $data = [
                'MemberID' => auth()->user()->MemberID,
                'MemberName' => auth()->user()->DisplayName,
                'MemberSCID' => auth()->user()->SC_ID,
                'TransactionID' => $request->razorpay_payment_id,
                'Status' => $request->status == true ? 'Success' : 'Failed',
                'paid_amount'=>$transaction->amount,
                'reference_number'=>$transaction->order_id
            ];

            // Payment successful, return success response
            return response()->json(['success' => true, 'message' => 'Payment Successful!', 'data' => $data], 200);
        } catch (\Exception $e) {

            if (isset($cardRecharge)) {
                $cardRecharge->PayStatus = 'Failed';
                $cardRecharge->BankRefrenceNo = $request->razorpay_payment_id;
                $cardRecharge->PaymentResponse = $payment_response;
                $cardRecharge->save(); // Now save() will work
            }
            $notification = [
                'title' => 'Card Recharge',
                'short_descriptions' => 'Your card recharge payment has failed due to unknown error.',
            ];    
            $this->sendFCMMessage($notification, auth()->user()->device_id);

            $data = [
                'MemberID' => auth()->user()->MemberID,
                'MemberName' => auth()->user()->DisplayName,
                'MemberSCID' => auth()->user()->SC_ID,
                'TransactionID' => $request->razorpay_payment_id,
                'Status' => 'Failed',
            ];

            return response()->json(['success' => false, 'message' => $e->getMessage(), 'data' => $data], 400);
        }
    }
  public function createBillPayOrder(Request $request, HdfcGatewayService $HdfcService)
    {

        $member_id = $request->get('member_id');
        $amount = $request->get('amount');

        $user = DB::table('memberprofile')->where('MemberID', $member_id)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }



        if (!$user) {
            return response()->json([
                'data' => '',
                'message' => 'User not found',
                'status' => false
            ], 200);
        }

        $SC_ID = $user->SC_ID;
        $txnid = 'MMR' . substr(hash('sha256', mt_rand() . microtime()), 0, 20);

        // Extract user data
        $MemID = $user->MemberID;
        $MemberName = $user->DisplayName;
        $MobileNo = $user->Mobile;
        $Email = $user->Email;

        // Get latest pending receipt
        $obj_memberReceipt = DB::table('MemberReceipts')->where('Mem_Id', $SC_ID)->first();
        Log::info('obj_memberReceipt', ['obj_memberReceipt' => $obj_memberReceipt]);
        if (!$obj_memberReceipt) {
            return response()->json([
                'data' => '',
                'message' => 'No pending receipt found.',
                'status' => false
            ], 200);
        }
        if (strtolower($obj_memberReceipt->PayStatus) === 'paid') {
              return response()->json([
            'data' => '',
            'message' => 'This month\'s payment was already made.',
            'status' => false
             ], 400); 
        }
        $BillAmt = number_format($obj_memberReceipt->BillAmt, 2, '.', '');
        $BalanceAmt = $obj_memberReceipt->BalanceAmt;
        $PaymentReceived = $obj_memberReceipt->PaymentReceived;
        $AdditionalAmount = number_format($request->amount ?? 0, 2, '.', '');
        $paymentType = $request->payment_type;

        // Calculate AmountPayable
        if ($paymentType === "Bill to Bill") {
            $AmountPayable = $BillAmt > 0 ? $BillAmt : 0;
        } elseif ($paymentType === "Less than Bill") {
            $AmountPayable = isset($request->less_than_amount)
                ? number_format($request->less_than_amount, 2, '.', '')
                : 0;
        } else {
            $AmountPayable = ($BillAmt > 0)
                ? $AdditionalAmount + $BillAmt - $PaymentReceived
                : $AdditionalAmount - $PaymentReceived;
        }

        $BalAmt = ($BillAmt > 0)
            ? $AdditionalAmount + $BillAmt - $PaymentReceived
            : $BalanceAmt - $AdditionalAmount - $PaymentReceived;

        // Prepare HdfcService payload
        $data = $this->getUserById($MemID, $AmountPayable, 'Bill Payment','_MBP');

        // Initiate HdfcService Payment
        $response = $HdfcService->initiatePayment($data);
        Log::info('HDFC Payment Initiation Response:', $response);
        if (!isset($response['status']) || (int) $response['status'] !== 1) {
            return response()->json([
                'data' => $response,
                'message' => 'Failed to initiate payment with Easebuzz.',
                'status' => false
            ], 200);
        }
        
$affected = DB::table('MemberReceipts')
    ->where('Mem_Id', $SC_ID)
    // ->whereIn('PayStatus', ['pending', 'FAILED', 'FAILURE', 'Failed'])
    ->update([
        'TxnRefrenceNo' => $data['txnid'],
        'TransactionID' => $data['txnid'],
        'BankRefrenceNo' => '',
        'OrderResponse'=>$response,
        'AdditionalAmt' => $AdditionalAmount,
        'BalanceAmt' => $BalAmt,
        'PaymentResponse' => '',
        'PayStatus' => 'Pending'
    ]);
     DB::table('transactions')->insert([
            'member_id' => $SC_ID,
            'amount'=>$AmountPayable,
               'order_id'=>$data['txnid'],
               'payment_status'=> 'Not Paid',
               'type'=>'Bill Payment',
               	'bank_refrance_no'=>'',
               	'bank_response'=>'',
               	'transaction_date'=>now(),

        ]);
        return response()->json([

            'status' => true,
            'message' => 'Payment initiated successfully.',
            // 'data' => [
            //     'txnid' => $data['txnid'],
            //     'pay_mode' => $this->env = env('EASEBUZZ_ENV', 'test'),
            //     'amount' => $AmountPayable,
            //     'url' => $response['data'],
            //     'end_point' => 'process_invoice_payment',
            // ]
            'data'    => [
                'txnid'    => $data['txnid'],
                'amount'   => $AmountPayable,
                'url'      => $response['details']['payment_links']['web'] ?? null, // e.g. payment redirect url
              'response'=> $response,
                'end_point'=> 'process_invoice_payment',
            ],

        ], 200);
    }
    public function processInvoicePayment(Request $request, FCMService $fcm)
{
  Log::info('Incoming Payment Request:', request()->all());

    $user = DB::table('memberprofile')->where('MemberID', $request->member_id)->first();

    if (!$user) {
        return response()->json(['status' => false, 'message' => 'Member not found'], 404);
    }

    $memberReceipt = MemberReceipt::where('TransactionID', $request->transaction_id)->first();

    if (!$memberReceipt) {
        return response()->json(['status' => false, 'message' => 'Member receipt not found'], 404);
    }

    $payment_response = $request->payment_response;
    $paymentData = json_decode($payment_response, true);
    $bankRefNum = $paymentData['requestId'] ?? '';

    $payStatus = $request->transaction_status ? 'Paid' : 'Not Paid';
    
    $amount = DB::table('transactions')
    ->where('order_id',  $request->transaction_id)
    ->value('amount');
    Log::info('Fetched transaction amount', [
    'order_id' => $request->transaction_id,
    'amount'   => $amount
]);
    $orderData = [
        'response_code'     =>  $request->transaction_status ?'Success':'Failed',
        'reference_number'  => $bankRefNum,
        'transaction_id'    => $request->transaction_id,
        'm_id'              => $user->MemberID,
        'c_id'              => $user->SC_ID,
        'member_name'       => $user->DisplayName,
        'paid_amount' => $amount,
    ];

    try {
        // Update the receipt in a single query
        MemberReceipt::where('TransactionID', $request->transaction_id)->update([
            'PayStatus'       => $payStatus,
            'BankRefrenceNo'  => $bankRefNum,
            'PaymentResponse' => $payment_response,
        ]);

        DB::table('transactions')
         ->where('order_id', $request->transaction_id)
         ->update([
               'payment_status'=>$request->transaction_status ? 'Paid' : 'Failed',
               	'bank_refrance_no'=>$bankRefNum,
               	'bank_response'=>$payment_response,
               	'transaction_date'=>now(),
        ]);

        $message = $payStatus === 'Paid' ? 'Your Bill was Payment Successful!' : 'Your Bill was Payment Failed!';

 $deviceToken = $user->device_id;

    // ✅ Compose FCM message
    
  Log::error('FCM Send Error: ' . $deviceToken);
    if ($deviceToken) {
        try {
            $fcm->sendNotification(
                $deviceToken,
                'Bill Payment',
                $message
            );
        } catch (\Exception $e) {
            Log::error('FCM Send Error: ' . $e->getMessage());
        }
    }
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $orderData,
        ], 200);
    } catch (\Exception $e) {
        Log::error('Payment processing failed: ' . $e->getMessage(), [
            'TransactionID' => $request->transaction_id,
            'error'         => $e,
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong while processing payment.',
            'error'   => $e->getMessage(),
        ], 400);
    }
}
    public function processActivityPayment(Request $request, FCMService $fcm)
{

    $user = DB::table('memberprofile')->where('MemberID', $request->member_id)->first();

    if (!$user) {
        return response()->json(['status' => false, 'message' => 'Member not found'], 404);
    }

  

    $payment_response = $request->payment_response;
    $paymentData = json_decode($payment_response, true);
    $bankRefNum = $paymentData['requestId'] ?? '';

    $payStatus = $request->transaction_status ? 'Paid' : 'Not Paid';
    
  $transaction = DB::table('transactions')
    ->where('order_id', $request->transaction_id)
    ->select('amount', 'game_booking_id')
    ->first();

if (!$transaction) {
    Log::warning('Transaction not found for order_id: ' . $request->transaction_id);
    return response()->json([
        'message' => 'Transaction not found.',
        'status' => false
    ], 404);
}

Log::info('Fetched transaction details', [
    'order_id' => $request->transaction_id,
    'amount'   => $transaction->amount,
    'game_booking_id' => $transaction->game_booking_id
]);

// Step 2: Prepare order data
$orderData = [
    'response_code'     => $request->transaction_status ? 'Success' : 'Failed',
    'reference_number'  => $bankRefNum,
    'transaction_id'    => $request->transaction_id,
    'm_id'              => $user->MemberID,
    'c_id'              => $user->SC_ID,
    'member_name'       => $user->DisplayName,
    'paid_amount'       => $transaction->amount,
];


    try {
      // Step 3: Update game_booking using game_booking_id
$updated = DB::table('game_bookings')
    ->where('id', $transaction->game_booking_id)
    ->update([
        'payment_status' => $payStatus,
        'status' => $request->transaction_status ? 'Active' : 'Failed',
    ]);
    DB::table('game_booking_slots')
    ->where('game_booking_id', $transaction->game_booking_id)
    ->update([
      
        'status' => $request->transaction_status ? 'Active' : 'Pending',
    ]);
    

// Step 4: Log update result
if ($updated) {
    Log::info("Booking updated successfully", [
        'game_booking_id' => $transaction->game_booking_id,
        'status' => $request->transaction_status ? 'Active' : 'Cancelled'
    ]);
} else {
    Log::warning("Booking update failed or no change detected", [
        'game_booking_id' => $transaction->game_booking_id
    ]);
}

        DB::table('transactions')
         ->where('order_id', $request->transaction_id)
         ->update([
               'payment_status'=>$request->transaction_status ? 'Paid' : 'Failed',
               	'bank_refrance_no'=>$bankRefNum,
               	'bank_response'=>$payment_response,
               	'transaction_date'=>now(),
        ]);

        $message = $payStatus === 'Paid' ? 'Your facility has been successfully booked!' : 'Your facility could not be booked.';

 $deviceToken = $user->device_id;
$facilityId = DB::table('game_bookings')
    ->where('id', $transaction->game_booking_id)
    ->value('facility_id');
    // ✅ Compose FCM message
    
  Log::error('FCM Send Error: ' . $facilityId);
    if ($deviceToken) {
        try {
            $fcm->sendNotification(
                $deviceToken,
                'Facility Booking',
                $message,
                 [
        'id' => $transaction->game_booking_id,
        'screen' => 'Bookings',   
        'click_action' => 'FLUTTER_NOTIFICATION_CLICK', 
        'type' => $facilityId
    ]
            );
        } catch (\Exception $e) {
            Log::error('FCM Send Errorssss: ' . $e->getMessage());
        }
    }
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $orderData,
        ], 200);
    } catch (\Exception $e) {
        Log::error('Payment processing failed: ' . $e->getMessage(), [
            'TransactionID' => $request->transaction_id,
            'error'         => $e,
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong while processing payment.',
            'error'   => $e->getMessage(),
        ], 400);
    }
}
}
