<?php

namespace App\Services\Payments\Gateways;

use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Support\Payments\PaymentStatus;
use Illuminate\Http\Request;

class PaynimoService extends AbstractPaymentGatewayService
{
    public function slug(): string
    {
        return 'paynimo';
    }

    protected function paymentUrl(PaymentGateway $gateway): string
    {
        return $this->isTest($gateway)
            ? 'https://test.paynimo.com/Paynimocheckout/server/lib/checkout'
            : 'https://www.paynimo.com/Paynimocheckout/server/lib/checkout';
    }

    public function createOrder(Transaction $transaction, PaymentGateway $gateway, array $context = []): array
    {
        $callbackUrl = $this->callbackUrl($this->slug(), $transaction);
        $fields = [
            'merchantId' => $gateway->merchant_id,
            'merchantTxnId' => $transaction->order_id,
            'amount' => $this->normalizeAmount($transaction->amount),
            'currency' => config('payments.currency', 'INR'),
            'buyerEmail' => $context['email'] ?? 'member@example.com',
            'buyerPhone' => $context['phone'] ?? '9999999999',
            'returnUrl' => $callbackUrl,
            'requestDateTime' => now()->format('d-m-Y H:i:s'),
        ];

        $fields['requestHash'] = hash(
            'sha512',
            implode('|', [
                $fields['merchantId'],
                $fields['merchantTxnId'],
                $fields['amount'],
                $fields['currency'],
                $fields['buyerEmail'],
                $fields['buyerPhone'],
                $fields['returnUrl'],
                $gateway->key_secret,
                $gateway->salt,
            ])
        );

        return [
            'gateway_order_id' => $transaction->order_id,
            'raw_response' => [
                'payment_url' => $this->paymentUrl($gateway),
                'post_data' => $fields,
            ],
            'checkout' => [
                'type' => 'hosted',
                'provider' => $this->slug(),
                'payment_url' => $this->checkoutUrl($transaction),
            ],
        ];
    }

    public function verifyPayment(Transaction $transaction, array $payload, PaymentGateway $gateway): array
    {
        $data = $payload['payment_response'] ?? $payload;
        if (is_string($data)) {
            parse_str($data, $parsed);
            $data = !empty($parsed) ? $parsed : $payload;
        }

        $status = strtoupper((string) ($data['txn_status'] ?? $data['payment_status'] ?? $data['status'] ?? 'FAILED'));
        $gatewayTransactionId = $data['transaction_id'] ?? $data['bank_txn_id'] ?? null;
        $responseHash = $data['responseHash'] ?? $data['hash'] ?? null;
        $expectedHash = hash(
            'sha512',
            implode('|', [
                $gateway->merchant_id,
                $data['merchantTxnId'] ?? $transaction->order_id,
                $data['amount'] ?? $this->normalizeAmount($transaction->amount),
                $status,
                $gateway->encryption_key,
                $gateway->salt,
            ])
        );

        $success = in_array($status, ['SUCCESS', 'CAPTURED'], true)
            && (!$responseHash || hash_equals($expectedHash, $responseHash));

        return $this->verificationResult(
            $success,
            $success ? PaymentStatus::SUCCESS : PaymentStatus::FAILED,
            $gatewayTransactionId,
            is_array($data) ? $data : ['payload' => $payload],
            $success ? 'Paynimo payment successful' : 'Paynimo payment failed',
            $data['merchantTxnId'] ?? $transaction->order_id
        );
    }

    public function parseWebhook(Request $request, PaymentGateway $gateway): array
    {
        $payload = $request->all();
        $verification = $this->verifyPayment(new Transaction(['amount' => $payload['amount'] ?? 0, 'order_id' => $payload['merchantTxnId'] ?? '']), $payload, $gateway);

        return [
            'status' => $verification['status'],
            'gateway_event_type' => 'payment.callback',
            'gateway_event_id' => $payload['transaction_id'] ?? null,
            'gateway_order_id' => $payload['merchantTxnId'] ?? null,
            'gateway_transaction_id' => $payload['transaction_id'] ?? null,
            'payload' => $payload,
        ];
    }

    public function renderCheckoutPage(Transaction $transaction, PaymentGateway $gateway): string
    {
        $raw = $transaction->raw_response ?? [];
        $postData = $raw['post_data'] ?? [];
        $action = htmlspecialchars((string) ($raw['payment_url'] ?? $this->paymentUrl($gateway)), ENT_QUOTES, 'UTF-8');

        $inputs = '';
        foreach ($postData as $key => $value) {
            $name = htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8');
            $val = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            $inputs .= "<input type=\"hidden\" name=\"{$name}\" value=\"{$val}\">";
        }

        $body = <<<HTML
<div class="badge">Worldline / Paynimo</div>
<div class="loader"></div>
<h1>Taking you to Worldline</h1>
<p>Your payment page is being prepared securely.</p>
<form id="paynimoForm" method="post" action="{$action}">{$inputs}</form>
<button type="button" onclick="document.getElementById('paynimoForm').submit()">Continue to payment</button>
<script>
  setTimeout(function () {
    document.getElementById('paynimoForm').submit();
  }, 250);
</script>
HTML;

        return $this->htmlShell('Worldline Checkout', $body, '#f97316');
    }
}
