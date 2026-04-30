<?php

namespace App\Services\Payments\Gateways;

use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Support\Payments\PaymentStatus;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayService extends AbstractPaymentGatewayService
{
    public function slug(): string
    {
        return 'razorpay';
    }

    public function createOrder(Transaction $transaction, PaymentGateway $gateway, array $context = []): array
    {
        $api = new Api($gateway->key_id, $gateway->key_secret);
        $order = $api->order->create([
            'receipt' => $transaction->order_id,
            'amount' => (int) round(((float) $transaction->amount) * 100),
            'currency' => config('payments.currency', 'INR'),
            'notes' => [
                'member_id' => $transaction->member_id,
                'module' => $transaction->payment_type,
                'reference' => $transaction->module_reference_id,
            ],
        ]);

        return [
            'gateway_order_id' => $order['id'],
            'raw_response' => $order->toArray(),
            'checkout' => [
                'type' => 'native',
                'provider' => $this->slug(),
                'payment_url' => null,
                'key' => $gateway->key_id,
            ],
        ];
    }

    public function verifyPayment(Transaction $transaction, array $payload, PaymentGateway $gateway): array
    {
        $api = new Api($gateway->key_id, $gateway->key_secret);
        $orderId = $payload['gateway_order_id']
            ?? $payload['razorpay_order_id']
            ?? $payload['order_id']
            ?? $transaction->gateway_order_id
            ?? $transaction->transID;
        $paymentId = $payload['gateway_transaction_id']
            ?? $payload['razorpay_payment_id']
            ?? $payload['payment_id']
            ?? null;
        $signature = $payload['signature'] ?? $payload['razorpay_signature'] ?? null;

        if (!$paymentId) {
            return $this->verificationResult(false, PaymentStatus::FAILED, null, $payload, 'Missing Razorpay payment id', $orderId);
        }

        if ($signature) {
            try {
                $api->utility->verifyPaymentSignature([
                    'razorpay_order_id' => $orderId,
                    'razorpay_payment_id' => $paymentId,
                    'razorpay_signature' => $signature,
                ]);
            } catch (SignatureVerificationError $exception) {
                return $this->verificationResult(false, PaymentStatus::FAILED, $paymentId, ['error' => $exception->getMessage()], 'Signature verification failed', $orderId);
            }
        }

        $payment = $api->payment->fetch($paymentId)->toArray();
        $status = ($payment['status'] ?? null) === 'captured' ? PaymentStatus::SUCCESS : PaymentStatus::PENDING;

        return $this->verificationResult(
            $status === PaymentStatus::SUCCESS,
            $status,
            $paymentId,
            $payment,
            $status === PaymentStatus::SUCCESS ? 'Payment captured' : 'Payment is not captured yet',
            $orderId
        );
    }

    public function verifyWebhookSignature(Request $request, PaymentGateway $gateway): bool
    {
        $signature = $request->header('X-Razorpay-Signature');
        if (!$signature || !$gateway->webhook_secret) {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $gateway->webhook_secret);
        return hash_equals($expected, $signature);
    }

    public function parseWebhook(Request $request, PaymentGateway $gateway): array
    {
        $payload = $request->json()->all();
        $payment = data_get($payload, 'payload.payment.entity', []);
        $order = data_get($payload, 'payload.order.entity', []);
        $paymentStatus = ($payment['status'] ?? null) === 'captured' ? PaymentStatus::SUCCESS : PaymentStatus::PENDING;

        return [
            'status' => $paymentStatus,
            'gateway_event_type' => $payload['event'] ?? 'unknown',
            'gateway_event_id' => $payment['id'] ?? null,
            'gateway_order_id' => $payment['order_id'] ?? $order['id'] ?? null,
            'gateway_transaction_id' => $payment['id'] ?? null,
            'payload' => $payload,
        ];
    }

    public function renderCheckoutPage(Transaction $transaction, PaymentGateway $gateway): string
    {
        $body = '<div class="badge">Razorpay</div><h1>Continue in the app</h1><p>This payment is configured for native checkout. Please return to the mobile app to finish the payment securely.</p>';
        return $this->htmlShell('Razorpay Checkout', $body, '#0f5bff');
    }
}
