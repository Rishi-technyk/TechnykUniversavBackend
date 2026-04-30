<?php

namespace App\Services\Payments\Gateways;

use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Support\Payments\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CashfreeService extends AbstractPaymentGatewayService
{
    public function slug(): string
    {
        return 'cashfree';
    }

    protected function baseUrl(PaymentGateway $gateway): string
    {
        return $this->isTest($gateway)
            ? 'https://sandbox.cashfree.com/pg'
            : 'https://api.cashfree.com/pg';
    }

    public function createOrder(Transaction $transaction, PaymentGateway $gateway, array $context = []): array
    {
        $response = Http::withHeaders([
            'x-client-id' => $gateway->app_id ?: $gateway->key_id,
            'x-client-secret' => $gateway->key_secret,
            'x-api-version' => '2023-08-01',
            'x-request-id' => (string) Str::uuid(),
            'x-idempotency-key' => $transaction->idempotency_key ?: (string) Str::uuid(),
        ])->post($this->baseUrl($gateway) . '/orders', [
            'order_id' => $transaction->order_id,
            'order_amount' => (float) $this->normalizeAmount($transaction->amount),
            'order_currency' => config('payments.currency', 'INR'),
            'order_note' => $transaction->type,
            'customer_details' => [
                'customer_id' => $transaction->member_id ?: 'guest',
                'customer_name' => $context['member_name'] ?? 'Member',
                'customer_email' => $context['email'] ?? 'member@example.com',
                'customer_phone' => $context['phone'] ?? '9999999999',
            ],
            'order_meta' => [
                'return_url' => $this->callbackUrl($this->slug(), $transaction),
                'notify_url' => route('payments.webhook', ['gateway' => $this->slug()]),
            ],
        ]);

        $response->throw();
        $data = $response->json();

        return [
            'gateway_order_id' => $data['order_id'] ?? $transaction->order_id,
            'raw_response' => $data,
            'checkout' => [
                'type' => 'hosted',
                'provider' => $this->slug(),
                'payment_url' => $this->checkoutUrl($transaction),
                'session_id' => $data['payment_session_id'] ?? null,
                'environment' => $gateway->environment,
            ],
        ];
    }

    public function verifyPayment(Transaction $transaction, array $payload, PaymentGateway $gateway): array
    {
        $orderId = $payload['gateway_order_id']
            ?? $payload['cashfree_order_id']
            ?? $payload['order_id']
            ?? $transaction->gateway_order_id
            ?? $transaction->order_id;

        $response = Http::withHeaders([
            'x-client-id' => $gateway->app_id ?: $gateway->key_id,
            'x-client-secret' => $gateway->key_secret,
            'x-api-version' => '2023-08-01',
        ])->get($this->baseUrl($gateway) . '/orders/' . $orderId);

        $response->throw();
        $data = $response->json();
        $status = strtoupper((string) ($data['order_status'] ?? 'ACTIVE'));

        return $this->verificationResult(
            $status === 'PAID',
            $status === 'PAID' ? PaymentStatus::SUCCESS : PaymentStatus::PENDING,
            $payload['gateway_transaction_id'] ?? $payload['cf_payment_id'] ?? null,
            $data,
            $status === 'PAID' ? 'Cashfree order paid' : 'Cashfree order still pending',
            $orderId
        );
    }

    public function verifyWebhookSignature(Request $request, PaymentGateway $gateway): bool
    {
        $signature = $request->header('x-webhook-signature');
        $timestamp = $request->header('x-webhook-timestamp');

        if (!$signature || !$timestamp || !$gateway->webhook_secret) {
            return false;
        }

        $expected = base64_encode(hash_hmac('sha256', $timestamp . $request->getContent(), $gateway->webhook_secret, true));
        return hash_equals($expected, $signature);
    }

    public function parseWebhook(Request $request, PaymentGateway $gateway): array
    {
        $payload = $request->json()->all();
        $paymentStatus = strtoupper((string) (data_get($payload, 'data.payment.payment_status') ?? data_get($payload, 'data.order.order_status') ?? 'ACTIVE'));

        return [
            'status' => $paymentStatus === 'SUCCESS' || $paymentStatus === 'PAID'
                ? PaymentStatus::SUCCESS
                : PaymentStatus::PENDING,
            'gateway_event_type' => $payload['type'] ?? 'unknown',
            'gateway_event_id' => data_get($payload, 'data.payment.cf_payment_id'),
            'gateway_order_id' => data_get($payload, 'data.order.order_id') ?? data_get($payload, 'data.order.cf_order_id'),
            'gateway_transaction_id' => data_get($payload, 'data.payment.cf_payment_id'),
            'payload' => $payload,
        ];
    }

    public function renderCheckoutPage(Transaction $transaction, PaymentGateway $gateway): string
    {
        $raw = $transaction->raw_response ?? [];
        $sessionId = data_get($raw, 'payment_session_id', '');
        $mode = $this->isTest($gateway) ? 'sandbox' : 'production';
        $callback = htmlspecialchars($this->callbackUrl($this->slug(), $transaction), ENT_QUOTES, 'UTF-8');
        $session = htmlspecialchars((string) $sessionId, ENT_QUOTES, 'UTF-8');

        $body = <<<HTML
<div class="badge">Cashfree</div>
<div class="loader"></div>
<h1>Opening secure payment</h1>
<p>Please wait while we connect you to Cashfree. After you finish, this page will update automatically and you can return to the app.</p>
<p class="small">If nothing happens within a few seconds, use the fallback button below.</p>
<button id="fallbackBtn" type="button">Continue to payment</button>
<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
<script>
  const paymentSessionId = "{$session}";
  const redirectUrl = "{$callback}";
  const fallback = document.getElementById('fallbackBtn');
  function launch() {
    const cashfree = Cashfree({ mode: "{$mode}" });
    cashfree.checkout({
      paymentSessionId,
      redirectTarget: "_self",
      returnUrl: redirectUrl
    });
  }
  fallback.addEventListener('click', launch);
  setTimeout(launch, 300);
</script>
HTML;

        return $this->htmlShell('Cashfree Checkout', $body, '#5b5fc7');
    }
}
