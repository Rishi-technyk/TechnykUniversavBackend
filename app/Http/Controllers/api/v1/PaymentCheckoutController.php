<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\Payments\PaymentTransactionService;
use Illuminate\Http\Request;

class PaymentCheckoutController extends Controller
{
    public function __construct(protected PaymentTransactionService $paymentTransactionService)
    {
    }

    public function showCheckout(Request $request, Transaction $transaction)
    {
        abort_unless($request->hasValidSignature(), 403);

        return response(
            $this->paymentTransactionService->buildCheckoutPage($transaction),
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    public function handleCallback(string $gateway, Transaction $transaction, Request $request)
    {
        abort_unless($request->hasValidSignature(), 403);

        $result = $this->paymentTransactionService->handleBrowserCallback($gateway, $transaction, $request);
        $statusText = $result['success'] ? 'Payment complete' : 'Payment pending review';
        $description = $result['success']
            ? 'Your payment has been recorded successfully. You can now return to the app.'
            : 'We have received your response and are finishing verification. Please return to the app and check the payment status.';
        $accent = $result['success'] ? '#16a34a' : '#f97316';

        $html = <<<HTML
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$statusText}</title>
    <style>
      body { margin: 0; background: #f4f7fb; color: #0f172a; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
      .wrap { min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 24px; }
      .card { width: min(520px, 100%); background: #fff; padding: 32px; border-radius: 24px; box-shadow: 0 28px 80px rgba(15,23,42,0.12); }
      .dot { width: 56px; height: 56px; border-radius: 50%; background: {$accent}; margin-bottom: 18px; }
      h1 { margin: 0 0 12px; font-size: 28px; }
      p { color: #475569; line-height: 1.6; margin: 0 0 12px; }
    </style>
  </head>
  <body>
    <div class="wrap">
      <div class="card">
        <div class="dot"></div>
        <h1>{$statusText}</h1>
        <p>{$description}</p>
        <p>Reference: {$transaction->order_id}</p>
      </div>
    </div>
  </body>
</html>
HTML;

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
