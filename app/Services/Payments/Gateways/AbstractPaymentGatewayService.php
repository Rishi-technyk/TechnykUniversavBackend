<?php

namespace App\Services\Payments\Gateways;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Support\Payments\PaymentStatus;
use Illuminate\Http\Request;

abstract class AbstractPaymentGatewayService implements PaymentGatewayInterface
{
    protected function callbackUrl(string $gatewaySlug, Transaction $transaction): string
    {
        return \URL::temporarySignedRoute(
            'payments.callback',
            now()->addMinutes((int) config('payments.callback_expiry_minutes', 90)),
            ['gateway' => $gatewaySlug, 'transaction' => $transaction->id]
        );
    }

    protected function checkoutUrl(Transaction $transaction): string
    {
        return \URL::temporarySignedRoute(
            'payments.checkout',
            now()->addMinutes((int) config('payments.callback_expiry_minutes', 90)),
            ['transaction' => $transaction->id]
        );
    }

    protected function isTest(PaymentGateway $gateway): bool
    {
        return strtolower((string) $gateway->environment) !== 'live';
    }

    protected function normalizeAmount($amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    protected function verificationResult(
        bool $success,
        string $status,
        ?string $gatewayTransactionId = null,
        array $rawResponse = [],
        ?string $message = null,
        ?string $gatewayOrderId = null
    ): array {
        return [
            'success' => $success,
            'status' => $status,
            'gateway_transaction_id' => $gatewayTransactionId,
            'gateway_order_id' => $gatewayOrderId,
            'raw_response' => $rawResponse,
            'message' => $message,
        ];
    }

    protected function htmlShell(string $title, string $body, string $accent = '#111827'): string
    {
        return <<<HTML
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$title}</title>
    <style>
      body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #f4f7fb; color: #0f172a; }
      .wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
      .card { width: min(520px, 100%); background: #fff; border-radius: 24px; padding: 32px; box-shadow: 0 28px 80px rgba(15, 23, 42, 0.12); }
      .badge { display: inline-block; padding: 8px 14px; border-radius: 999px; background: rgba(17, 24, 39, 0.06); color: {$accent}; font-weight: 700; letter-spacing: 0.02em; margin-bottom: 16px; }
      h1 { margin: 0 0 12px; font-size: 26px; line-height: 1.15; }
      p { margin: 0 0 14px; color: #475569; line-height: 1.6; }
      .loader { width: 44px; height: 44px; border: 4px solid rgba(15, 23, 42, 0.08); border-top-color: {$accent}; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 18px; }
      @keyframes spin { to { transform: rotate(360deg); } }
      .small { font-size: 13px; color: #64748b; }
      form { display: none; }
      button { margin-top: 16px; padding: 12px 18px; background: {$accent}; border: 0; color: #fff; border-radius: 12px; font-weight: 700; }
    </style>
  </head>
  <body>
    <div class="wrap">
      <div class="card">{$body}</div>
    </div>
  </body>
</html>
HTML;
    }

    public function verifyWebhookSignature(Request $request, PaymentGateway $gateway): bool
    {
        return true;
    }

    public function parseWebhook(Request $request, PaymentGateway $gateway): array
    {
        return [
            'status' => PaymentStatus::PENDING,
            'gateway_event_type' => 'unknown',
            'payload' => $request->all(),
        ];
    }
}
