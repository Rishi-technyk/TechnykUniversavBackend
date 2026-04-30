<?php

namespace App\Http\Controllers\api\v1;


use App\Http\Controllers\Controller;

use App\Models\AppModule;

use App\Models\Event;
use DB;

use AESEncDec;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\MemberProfile;

class PaymentController extends Controller

{

private static function generateHash($data)
{
    ksort($data); // sort keys

    $hashString = '';
    foreach ($data as $key => $value) {
        if ($key !== 'secureHash' && $value !== null && $value !== '') {
            $hashString .= $value;
        }
    }

    // 🔐 Append secret key
    $hashString .= env('HASH_KEY'); // or SECRET_KEY (check ICICI docs)

    return hash('sha512', $hashString);
}

public function handleResponse(Request $request)
{
// \App\Helpers\PaymentHelper::verifyPayment($request->all());
    // ✅ Extract values
    $orderId = $request->merchantTxnNo;
    $tranCtx = $request->tranCtx;
    $responseCode = $request->responseCode;

    // ✅ Determine status
    $status = ($responseCode === '0000') ? 'Paid' : 'Failed';


        DB::table('transactions')
            ->where('order_id', $orderId)
            ->whereDate('transaction_date' ,now())
            ->update([
                'payment_status' => $status,
                'bank_response' => $request->all(),
                'bank_refrance_no' => $request->paymentID,
                'paymentMode' => $request->paymentMode,
                'transaction_date' => now()
            ]);

 
    // ✅ REDIRECT TO APP (DEEP LINK)
    $deepLink = "myapp://payment?status={$status}";

    return redirect()->away($deepLink);
}


// public function handleResponse(Request $request)
// {
//     $orderId = $request->merchantTxnNo;
//     $status  = ($request->responseCode === 'R1000') ? 'SUCCESS' : 'FAILED';

//     $deepLink = "lgc://payment?status={$status}&order_id={$orderId}";

//     return response("
//         <html>
//             <head>
//                 <title>Redirecting...</title>
//             </head>
//             <body>
//                 <script>
//                     // Try opening app
//                     window.location.href = '{$deepLink}';

//                     // Fallback after 2 sec
//                     setTimeout(function() {
//                         document.body.innerHTML = 'If app did not open, please return to app manually.';
//                     }, 2000);
//                 </script>
//             </body>
//         </html>
//     ");
// }
}