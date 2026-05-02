<?php

namespace App\Services\Payments;

use App\Models\Member;
use App\Models\PaymentWebhookLog;
use App\Models\Transaction;
use App\Support\Payments\PaymentModule;
use App\Support\Payments\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentTransactionService
{
    public function __construct(
        protected PaymentGatewayManager $gatewayManager,
        protected PaymentModuleSyncService $moduleSyncService
    ) {
    }

    public function initiate(Member $member, float $amount, string $module, $referenceId = null, array $context = []): array
    {
        $gateway = $this->gatewayManager->activeGateway();
        $displayType = $context['type'] ?? PaymentModule::displayLabel($module);
        $columns = PaymentModule::transactionColumns($module, $referenceId);
        $internalOrderId = $context['internal_order_id']
            ?? strtoupper(($context['prefix'] ?? 'PAY') . '_' . Str::upper(Str::random(18)));

        $transaction = Transaction::create(array_merge($columns, [
            'member_id' =>  $member->SC_ID,
            'amount' => $amount,
            'order_id' => $internalOrderId,
            'transID' => $internalOrderId,
            'type' => $displayType,
            'payment_type' => $module,
            'module_reference_id' => $referenceId,
            'payment_status' => PaymentStatus::toLegacy(PaymentStatus::INITIATED),
            'payment_status_code' => PaymentStatus::INITIATED,
            'gateway_name' => $gateway->name,
            'gateway_slug' => $gateway->slug,
            'transaction_date' => now(),
            'Importflag' => strtolower((string) $gateway->environment) === 'live' ? 0 : 1,
            'import_flag' => strtolower((string) $gateway->environment) === 'live' ? 0 : 1,
            'entry_come' => $context['entry_come'] ?? 'App',
            'retry_count' => (int) ($context['retry_count'] ?? 0),
            'idempotency_key' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        $driver = $this->gatewayManager->driver($gateway);
        $gatewayOrder = $driver->createOrder($transaction, $gateway, [
            'member_name' => $member->DisplayName,
            'email' => $member->Email,
            'phone' => $member->Mobile,
        ]);

        $transaction->update([
            'transID' => $gatewayOrder['gateway_order_id'] ?? $transaction->transID,
            'gateway_order_id' => $gatewayOrder['gateway_order_id'] ?? $transaction->gateway_order_id,
            'raw_response' => json_encode($gatewayOrder['raw_response'] ?? []),
            'payment_status' => PaymentStatus::toLegacy(PaymentStatus::PENDING),
            'payment_status_code' => PaymentStatus::PENDING,
        ]);

        $branding = config('payments.gateway_branding.' . $gateway->slug, []);

        return [
            'transaction_id' => $transaction->id,
            'order_id' => $transaction->fresh()->gateway_order_id ?: $transaction->fresh()->transID,
            'merchant_order_id' => $transaction->order_id,
            'txnid' => $transaction->order_id,
            'amount' => (float) $amount,
            'gateway' => [
                'name' => $gateway->name,
                'slug' => $gateway->slug,
                'environment' => $gateway->environment,
                'branding' => $branding,
            ],
            'checkout_type' => data_get($gatewayOrder, 'checkout.type'),
            'payment_url' => data_get($gatewayOrder, 'checkout.payment_url'),
            'razorpayKey' => data_get($gatewayOrder, 'checkout.key'),
            'access_key' => data_get($gatewayOrder, 'checkout.session_id'),
            'checkout' => $gatewayOrder['checkout'] ?? [],
            'status_reference' => $transaction->order_id,
            'status_endpoint' => 'member/payments/status/' . $transaction->order_id,
        ];
    }

    public function verify(?Member $member, array $payload): array
    {
        $transaction = $this->resolveTransactionFromPayload($payload, $member);

        if (!$transaction) {
            return $this->buildResponse(false, null, false, 'Transaction not found');
        }

        $syncAfterCommit = false;
        $updatedTransaction = DB::transaction(function () use ($transaction, $payload, &$syncAfterCommit) {
            /** @var Transaction $locked */
            $locked = Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if (PaymentStatus::isSuccessful($locked->payment_status_code)) {
                return $locked;
            }

            $gateway = $this->gatewayManager->gatewayForTransaction($locked);
            $driver = $this->gatewayManager->driver($gateway);
            $verification = $driver->verifyPayment($locked, $payload, $gateway);
            $status = $verification['status'] ?? PaymentStatus::FAILED;

            $locked->update([
                'payment_status' => PaymentStatus::toLegacy($status),
                'payment_status_code' => $status,
                'bank_refrance_no' => $verification['gateway_transaction_id'] ?? $locked->bank_refrance_no,
                'gateway_transaction_id' => $verification['gateway_transaction_id'] ?? $locked->gateway_transaction_id,
                'gateway_order_id' => $verification['gateway_order_id'] ?? $locked->gateway_order_id,
                'bank_response' => json_encode($verification['raw_response'] ?? $payload),
                'raw_response' => json_encode($verification['raw_response'] ?? $payload),
                'transaction_date' => now(),
                'processed_at' => PaymentStatus::isTerminal($status) ? now() : $locked->processed_at,
                'updated_at' => now(),
            ]);

            $syncAfterCommit = PaymentStatus::isSuccessful($status);

            return $locked->fresh();
        });

        if ($syncAfterCommit) {
            $this->moduleSyncService->markPaid($updatedTransaction);
        }

        return $this->buildResponse(
            PaymentStatus::isSuccessful($updatedTransaction->payment_status_code),
            $updatedTransaction,
            false
        );
    }

    public function status(?Member $member, string $reference): array
    {
        $transaction = $this->findByReference($reference, $member);

        if (!$transaction) {
            return [
                'status' => false,
                'message' => 'Transaction not found',
            ];
        }

        return [
            'status' => true,
            'data' => [
                'transaction_id' => $transaction->id,
                'amount' => (float) $transaction->amount,
                'order_id' => $transaction->gateway_order_id ?: $transaction->transID,
                'merchant_order_id' => $transaction->order_id,
                'gateway_name' => $transaction->gateway_name,
                'gateway_slug' => $transaction->gateway_slug,
                'payment_status' => $transaction->payment_status,
                'payment_status_code' => $transaction->payment_status_code ?: PaymentStatus::INITIATED,
                'gateway_transaction_id' => $transaction->gateway_transaction_id ?: $transaction->bank_refrance_no,
                'retry_count' => (int) ($transaction->retry_count ?? 0),
                'processed_at' => optional($transaction->processed_at)->toDateTimeString(),
                'can_retry' => in_array($transaction->payment_status_code, [PaymentStatus::FAILED, PaymentStatus::CANCELLED], true),
            ],
        ];
    }

    public function retry(Member $member, string $reference): array
    {
        $transaction = $this->findByReference($reference, $member);

        if (!$transaction) {
            return [
                'status' => false,
                'message' => 'Transaction not found',
            ];
        }

        $module = PaymentModule::fromType($transaction->payment_type ?: $transaction->type);

        return [
            'status' => true,
            'data' => $this->initiate($member, (float) $transaction->amount, $module, $transaction->module_reference_id, [
                'type' => $transaction->type,
                'prefix' => strtoupper(substr($transaction->order_id, 0, 3)),
                'retry_count' => ((int) $transaction->retry_count) + 1,
                'entry_come' => $transaction->entry_come ?: 'App',
            ]),
        ];
    }

    public function handleWebhook(string $gatewaySlug, Request $request): array
    {
        $gateway = $this->gatewayManager->gatewayBySlug($gatewaySlug);
        $driver = $this->gatewayManager->driver($gateway);
        $parsed = $driver->parseWebhook($request, $gateway);
        $signatureValid = $driver->verifyWebhookSignature($request, $gateway);

        $log = PaymentWebhookLog::create([
            'gateway_name' => $gateway->name,
            'gateway_slug' => $gateway->slug,
            'gateway_order_id' => $parsed['gateway_order_id'] ?? null,
            'gateway_transaction_id' => $parsed['gateway_transaction_id'] ?? null,
            'gateway_event_id' => $parsed['gateway_event_id'] ?? null,
            'gateway_event_type' => $parsed['gateway_event_type'] ?? 'unknown',
            'status' => $signatureValid ? 'received' : 'rejected',
            'signature_valid' => $signatureValid,
            'payload' => $parsed['payload'] ?? $request->all(),
            'headers' => $request->headers->all(),
        ]);

        if (!$signatureValid && $gateway->webhook_secret) {
            $log->update(['response' => ['message' => 'Invalid webhook signature']]);
            return ['ok' => false, 'message' => 'Invalid signature'];
        }

        $transaction = $this->findByGatewayOrderId($parsed['gateway_order_id'] ?? null);
        if (!$transaction) {
            $log->update(['response' => ['message' => 'Transaction not found']]);
            return ['ok' => true, 'message' => 'Ignored'];
        }

        $syncAfterCommit = false;
        $updatedTransaction = DB::transaction(function () use ($transaction, $parsed, $log, &$syncAfterCommit) {
            $locked = Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if (!PaymentStatus::isSuccessful($locked->payment_status_code) && !empty($parsed['status'])) {
                $locked->update([
                    'payment_status' => PaymentStatus::toLegacy($parsed['status']),
                    'payment_status_code' => $parsed['status'],
                    'gateway_transaction_id' => $parsed['gateway_transaction_id'] ?? $locked->gateway_transaction_id,
                    'gateway_order_id' => $parsed['gateway_order_id'] ?? $locked->gateway_order_id,
                    'webhook_response' => json_encode($parsed['payload'] ?? []),
                    'processed_at' => PaymentStatus::isTerminal($parsed['status']) ? now() : $locked->processed_at,
                    'updated_at' => now(),
                ]);
            }

            $log->update([
                'transaction_id' => $locked->id,
                'status' => 'processed',
                'processed_at' => now(),
                'response' => ['status' => $parsed['status'] ?? PaymentStatus::PENDING],
            ]);

            $syncAfterCommit = PaymentStatus::isSuccessful($parsed['status'] ?? null);

            return $locked->fresh();
        });

        if ($syncAfterCommit) {
            $this->moduleSyncService->markPaid($updatedTransaction);
        }

        return ['ok' => true, 'message' => 'Webhook processed'];
    }

    public function buildCheckoutPage(Transaction $transaction): string
    {
        $gateway = $this->gatewayManager->gatewayForTransaction($transaction);
        $driver = $this->gatewayManager->driver($gateway);

        return $driver->renderCheckoutPage($transaction, $gateway);
    }

    public function handleBrowserCallback(string $gatewaySlug, Transaction $transaction, Request $request): array
    {
        $gateway = $this->gatewayManager->gatewayBySlug($gatewaySlug);
        $driver = $this->gatewayManager->driver($gateway);
        $verification = $driver->verifyPayment($transaction, $request->all(), $gateway);

        $updatedTransaction = DB::transaction(function () use ($transaction, $verification) {
            $locked = Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if (!PaymentStatus::isSuccessful($locked->payment_status_code)) {
                $status = $verification['status'] ?? PaymentStatus::FAILED;
                $locked->update([
                    'payment_status' => PaymentStatus::toLegacy($status),
                    'payment_status_code' => $status,
                    'gateway_transaction_id' => $verification['gateway_transaction_id'] ?? $locked->gateway_transaction_id,
                    'gateway_order_id' => $verification['gateway_order_id'] ?? $locked->gateway_order_id,
                    'bank_refrance_no' => $verification['gateway_transaction_id'] ?? $locked->bank_refrance_no,
                    'bank_response' => json_encode($verification['raw_response'] ?? $request->all()),
                    'processed_at' => PaymentStatus::isTerminal($status) ? now() : $locked->processed_at,
                ]);
            }

            return $locked->fresh();
        });

        if (PaymentStatus::isSuccessful($updatedTransaction->payment_status_code)) {
            $this->moduleSyncService->markPaid($updatedTransaction);
        }

        return [
            'success' => PaymentStatus::isSuccessful($updatedTransaction->payment_status_code),
            'transaction' => $updatedTransaction,
        ];
    }

    protected function resolveTransactionFromPayload(array $payload, ?Member $member = null): ?Transaction
    {
        $nestedPaymentResponse = $payload['payment_response'] ?? [];
        if (is_string($nestedPaymentResponse)) {
            $decoded = json_decode($nestedPaymentResponse, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $nestedPaymentResponse = $decoded;
            }
        }

        $reference = $payload['transaction_id']
            ?? $payload['merchant_order_id']
            ?? $payload['txnid']
            ?? $payload['gateway_order_id']
            ?? $payload['razorpay_order_id']
            ?? $payload['order_id']
            ?? ($nestedPaymentResponse['txnid'] ?? null)
            ?? ($nestedPaymentResponse['merchantTxnId'] ?? null);

        if (!$reference) {
            return null;
        }

        return $this->findByReference($reference, $member);
    }

    protected function findByReference($reference, ?Member $member = null): ?Transaction
    {
        $query = Transaction::query();

        if ($member) {
            $query->where('member_id', $member->MemberID ?: $member->SC_ID);
        }

        return $query->where(function ($builder) use ($reference) {
            $builder->where('order_id', $reference)
                ->orWhere('transID', $reference)
                ->orWhere('gateway_order_id', $reference);

            if (is_numeric($reference)) {
                $builder->orWhere('id', (int) $reference);
            }
        })->latest('id')->first();
    }

    protected function findByGatewayOrderId(?string $gatewayOrderId): ?Transaction
    {
        if (!$gatewayOrderId) {
            return null;
        }

        return Transaction::where('gateway_order_id', $gatewayOrderId)
            ->orWhere('transID', $gatewayOrderId)
            ->orWhere('order_id', $gatewayOrderId)
            ->latest('id')
            ->first();
    }

    protected function buildResponse(bool $success, ?Transaction $transaction, bool $alreadyProcessed = false, ?string $message = null): array
    {
        $member = $transaction
            ? Member::where('MemberID', $transaction->member_id)
                ->orWhere('SC_ID', $transaction->member_id)
                ->first()
            : null;

        return [
            'success' => $success,
            'already_processed' => $alreadyProcessed,
            'message' => $message ?: ($success ? 'Payment verified successfully' : 'Payment verification failed'),
            'payment_status_code' => $transaction->payment_status_code ?? PaymentStatus::FAILED,
            'gateway_slug' => $transaction->gateway_slug ?? null,
            'transaction_id' => $transaction->id ?? null,
            'data' => [
                'MemberName' => $member->DisplayName ?? '',
                'MemberID' => $member->MemberID ?? '',
                'MemberSCID' => $member->SC_ID ?? '',
                'paid_amount' => (float) ($transaction->amount ?? 0),
                'reference_number' => $transaction->gateway_transaction_id
                    ?? $transaction->bank_refrance_no
                    ?? $transaction->transID
                    ?? '',
                'orderId' => $transaction->gateway_order_id
                    ?? $transaction->transID
                    ?? $transaction->order_id
                    ?? '',
                'Status' => $success ? 'Success' : 'Failed',
            ],
        ];
    }
}
