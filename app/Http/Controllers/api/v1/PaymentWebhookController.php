<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentTransactionService;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __construct(protected PaymentTransactionService $paymentTransactionService)
    {
    }

    public function handle(string $gateway, Request $request)
    {
        $result = $this->paymentTransactionService->handleWebhook($gateway, $request);

        return response()->json($result, $result['ok'] ? 200 : 422);
    }
}
