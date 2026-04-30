<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\InitiatePaymentRequest;
use App\Services\Payments\PaymentTransactionService;
use App\Support\Payments\PaymentModule;
use Illuminate\Http\Request;

class CentralizedPaymentController extends Controller
{
    public function __construct(protected PaymentTransactionService $paymentTransactionService)
    {
    }

    public function initiatePayment(InitiatePaymentRequest $request)
    {
        $module = PaymentModule::fromType($request->module);
        $payload = $this->paymentTransactionService->initiate(
            $request->user(),
            (float) $request->amount,
            $module,
            $request->module_reference_id,
            [
                'type' => $request->type ?: PaymentModule::displayLabel($module),
            ]
        );

        return response()->json([
            'status' => true,
            'data' => $payload,
        ]);
    }

    public function verifyPayment(Request $request)
    {
        return response()->json(
            $this->paymentTransactionService->verify($request->user(), $request->all())
        );
    }

    public function transactionStatus(Request $request, string $reference)
    {
        return response()->json(
            $this->paymentTransactionService->status($request->user(), $reference)
        );
    }

    public function retryPayment(Request $request, string $reference)
    {
        return response()->json(
            $this->paymentTransactionService->retry($request->user(), $reference)
        );
    }
}
