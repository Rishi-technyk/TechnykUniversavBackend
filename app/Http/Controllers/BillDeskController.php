<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class BillDeskController extends Controller
{
    public function pay($order_id, Request $request)
    {
        $order_id       = decrypt($order_id);
        $transaction    = Transaction::where('order_id', $order_id)->first();
        $amount         = number_format($transaction->amount, 2, '.', '');

        $merchantId     = env('BILLDESK_MERCHANT_ID');
        $returnUrl      = URL::to("billdesk/response");

        $msg = $merchantId . '|' . $order_id . '|' . $amount . '|' . $returnUrl;
        
        // Checksum generate (BillDesk algorithm docs se match hona chahiye)
        $checksum = hash_hmac('sha256', $msg, env('BILLDESK_CHECKSUM_KEY'));

        $finalMsg = $msg . '|' . $checksum;

        return view('frontend.billdesk.redirect', [
            'txnUrl' => env('BILLDESK_TXN_URL'),
            'msg'    => $finalMsg,
        ]);
    }

    public function response(Request $request)
    {
        $responseMsg = $request->input('msg'); // BillDesk mostly msg field bhejta hai

        // 🔥 Parse response (pipe separated)
        $data = explode('|', $responseMsg);

        $order_id = $data[1] ?? null; // docs ke according index change ho sakta hai
        $status   = $data[14] ?? null; // SUCCESS/FAIL etc.

        if ($order_id) {
            $transaction = Transaction::where('order_id', $order_id)->first();

            if ($transaction) {
                if ($status == 'SUCCESS') {
                    $transaction->payment_status = 'Paid';
                } else {
                    $transaction->payment_status = 'Failed';
                }
                $transaction->save();
            }
        }

        return redirect()->route('payment.confirmed', ['order_id' => encrypt($order_id)])->with('success', 'Payment Response Received!');
    }

    function payment_confirmed($order_id)
    {
        $order_id = decrypt($order_id);
        $transaction = Transaction::where('order_id', $order_id)->first();
        return view('frontend.billdesk.payment_confirmed', compact('transaction'));
    }

}
