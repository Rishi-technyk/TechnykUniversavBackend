<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Transaction;
use App\Models\GameBooking;
use App\Models\RoomBooking;
use Illuminate\Http\Request;
use App\Models\MemberProfile;
use App\Models\MemberReceipt;
use App\Models\BanquetBooking;
use App\Models\CardRecharge;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\BanquetBookingCharges;

class PaymentController extends Controller
{
    private $merchantId;
    private $subMerchantId;
    private $paymode = "9";
    private $returnUrl;
    private $key;

    public function __construct()
    {
        $this->merchantId = env('MERCHANT_ID');
        $this->subMerchantId = env('SUB_MERCHANT_CODE');
        $this->returnUrl = url('payment/response'); // better than URL::to
        $this->key = env('YOUR_SECRET_KEY');
    }

    function payment_confirmed($order_id)
    {
        $order_id = decrypt($order_id);
        $transaction = Transaction::where('order_id', $order_id)->first();
        return view('frontend.billdesk.payment_confirmed', compact('transaction'));
    }

    /**
     * Step 1: Initiate Payment
     */
    public function pay($order_id, Request $request)
    {
        $order_id       = decrypt($order_id);
        $transaction    = Transaction::where('order_id', $order_id)->first();
        $amount         = number_format($transaction->amount, 2, '.', '');
        $member         = Auth::guard('student')->user();
        $returnUrl      = URL::to("payment/response");

        $amount = number_format($transaction->amount, 2, '.', '');

        $data = [
            "merchantId" => $this->merchantId,
            "aggregatorID" => $this->subMerchantId,
            "merchantTxnNo" => $order_id,
            "amount" => $amount,
            "currencyCode" => "356",
            "payType" => "0",
            "customerEmailID" => $member->Email ?? 'text@gmail.com',
            "transactionType" => "SALE",
            "returnURL" => $this->returnUrl,
            "txnDate" => now()->format('YmdHis'),
            "customerMobileNo" => $member->Mobile,
            "customerName" => $member->DisplayName,
            "addlParam1" => "NA",
            "addlParam2" => "NA",
        ];
        
        // 🔐 Generate Hash
        $data['secureHash'] = $this->generateHash($data);

        // 🌐 API Call
        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post(
            'https://pgpay.icicibank.com/pg/api/v2/initiateSale',
            $data
        );

        $result = $response->json();

        // 🔁 Redirect to ICICI Page
        if (isset($result['responseCode']) && $result['responseCode'] === 'R1000') {

            $redirectUrl = $result['redirectURI'] . '?tranCtx=' . $result['tranCtx'];

            return redirect($redirectUrl);
        }

        return response()->json($result);
    }

    /**
     * Step 2: Generate Secure Hash
     */
    private function generateHash($data)
    {
        unset($data['secureHash']);

        ksort($data); // sort alphabetically

        $plainText = implode('', $data);

        return hash_hmac('sha256', $plainText, $this->key);
    }

    /**
     * Step 3: Handle Response
     */
    public function response(Request $request)
    {
        $data = $request->all();
        
        $order_id       = $request->merchantTxnNo ?? '';
        
        if($order_id && isset($order_id)){

            $bank_status    = $request->responseCode;
            $paymentID      = $request->paymentID;
            $txnID          = $request->txnID;
            $paymentMode    = $request->paymentMode;
            
            if (isset($bank_status) && $bank_status === '0000') {
                $order_status = 'Paid';
            } else {
                $order_status = 'Failed';
            }

            $transaction = Transaction::where('order_id', $order_id)->first();

            $member      = MemberProfile::where('SC_ID', $transaction->member_id ?? '')->first();

            Auth::guard('student')->login($member);

            if ($transaction) {
                $transaction->payment_status    = $order_status;
                $transaction->bank_response     = $txnID;
                $transaction->bank_refrance_no  = $paymentID;
                $transaction->paymentMode       = $paymentMode;
                $transaction->payment_type      = 'ICICI';
                $transaction->entry_come        = 'Web';
                $transaction->save();
            }

            if ($order_status == 'Paid') {
                $status = 'success';
                $mm_amt = $transaction->amount ?? 0 + $transaction->additionalAmt ?? 0;
            } else {
                $status = 'pending';
                $mm_amt = '0';
            }

            if($transaction->type=='Subscription'){

                MemberReceipt::where('Mem_Id', $member->SC_ID ?? '')->update(['PayStatus' => $status, 'PaymentReceived' => $mm_amt, 'ReceivingDate' => date('Y-m-d H:i:s')]);

            } else {

                DB::table('CardRecharge')->where('TxnRefrenceNo', $order_id)->update(['PayStatus' => $status, 'RechargeAmt' => $mm_amt, 'RechargeDate' => date('Y-m-d H:i:s')]);

            }

            $trans = $transaction;

            if($trans && $trans->type=='Banquet Booking'){

                $status = $order_status;

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

            } elseif ($trans && $trans->type=='Activity Booking' && $status=='Paid'){

                $g_params['status'] = 'Active';
                $g_params['payment_status'] = 'Paid';

                GameBooking::where('id', $trans->game_booking_id)->update($g_params);

            }

            return redirect('payment/confirmed/'.encrypt($order_id));

        } else {
            return redirect()->route('student.login')->with('error', 'Payment Failed.');;
        }
    }

    /**
     * Step 4: Verify Hash
     */
    private function verifyHash($data)
    {
        $receivedHash = $data['secureHash'] ?? '';

        unset($data['secureHash']);

        ksort($data);

        $plainText = implode('', $data);

        $calculatedHash = hash_hmac('sha256', $plainText, $this->key);

        return $receivedHash === $calculatedHash;
    }

}
