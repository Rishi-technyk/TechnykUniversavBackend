<?php

namespace App\Services\Payments\Gateways;

use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Support\Payments\PaymentStatus;
use Illuminate\Http\Request;

class EasebuzzService extends AbstractPaymentGatewayService
{
    public function slug(): string
    {
        return 'easebuzz';
    }

    protected function paymentUrl(PaymentGateway $gateway): string
    {
        return $this->isTest($gateway)
            ? 'https://testpay.easebuzz.in/payment/initiateLink'
            : 'https://pay.easebuzz.in/payment/initiateLink';
    }

    public function createOrder(Transaction $transaction, PaymentGateway $gateway, array $context = []): array
    {
        $postData = [
            'key' => $gateway->key_id,
            'txnid' => $transaction->order_id,
            'amount' => $this->normalizeAmount($transaction->amount),
            'productinfo' => $transaction->type,
            'firstname' => $context['member_name'] ?? 'Member',
            'email' => $context['email'] ?? 'member@example.com',
            'phone' => $context['phone'] ?? '9999999999',
            'surl' => $this->callbackUrl($this->slug(), $transaction),
            'furl' => $this->callbackUrl($this->slug(), $transaction),
        ];

        $postData['hash'] = hash(
            'sha512',
            implode('|', [
                $postData['key'],
                $postData['txnid'],
                $postData['amount'],
                $postData['productinfo'],
                $postData['firstname'],
                $postData['email'],
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                $gateway->salt,
            ])
        );

        return [
            'gateway_order_id' => $transaction->order_id,
            'raw_response' => [
                'payment_url' => $this->paymentUrl($gateway),
                'post_data' => $postData,
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
        $result = $payload['result'] ?? $payload['payment_response'] ?? $payload;
        if (is_string($result)) {
            $decoded = json_decode($result, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $result = $decoded;
            }
        }

        $status = strtolower((string) ($result['status'] ?? $payload['status'] ?? 'failed'));
        $gatewayTransactionId = $result['easepayid'] ?? $result['bank_ref_num'] ?? $payload['payment_id'] ?? null;
        $hash = $result['hash'] ?? null;
        $txnid = $result['txnid'] ?? $transaction->order_id;

        $expectedHash = hash(
            'sha512',
            implode('|', [
                $gateway->salt,
                $status,
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                $result['email'] ?? '',
                $result['firstname'] ?? '',
                $result['productinfo'] ?? $transaction->type,
                $result['amount'] ?? $this->normalizeAmount($transaction->amount),
                $txnid,
                $gateway->key_id,
            ])
        );

        $success = $status === 'success' && (!$hash || hash_equals($expectedHash, $hash));

        return $this->verificationResult(
            $success,
            $success ? PaymentStatus::SUCCESS : PaymentStatus::FAILED,
            $gatewayTransactionId,
            is_array($result) ? $result : ['payload' => $payload],
            $success ? 'Easebuzz payment successful' : 'Easebuzz payment failed',
            $txnid
        );
    }

    public function parseWebhook(Request $request, PaymentGateway $gateway): array
    {
        $payload = $request->all();
        $verification = $this->verifyPayment(new Transaction(['amount' => $payload['amount'] ?? 0, 'type' => $payload['productinfo'] ?? 'Payment', 'order_id' => $payload['txnid'] ?? '']), $payload, $gateway);

        return [
            'status' => $verification['status'],
            'gateway_event_type' => 'payment.callback',
            'gateway_event_id' => $payload['easepayid'] ?? null,
            'gateway_order_id' => $payload['txnid'] ?? null,
            'gateway_transaction_id' => $payload['easepayid'] ?? null,
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
<div class="badge">Easebuzz</div>
<div class="loader"></div>
<h1>Redirecting to Easebuzz</h1>
<p>We are preparing your secure payment page now.</p>
<form id="easebuzzForm" method="post" action="{$action}">{$inputs}</form>
<button type="button" onclick="document.getElementById('easebuzzForm').submit()">Continue to payment</button>
<script>
  setTimeout(function () {
    document.getElementById('easebuzzForm').submit();
  }, 250);
</script>
HTML;

        return $this->htmlShell('Easebuzz Checkout', $body, '#1f9d77');
    }
}
