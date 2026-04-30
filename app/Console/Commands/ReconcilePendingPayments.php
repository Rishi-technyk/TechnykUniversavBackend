<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\Payments\PaymentTransactionService;
use App\Support\Payments\PaymentStatus;
use Illuminate\Console\Command;

class ReconcilePendingPayments extends Command
{
    protected $signature = 'payments:reconcile-pending {--hours=6}';

    protected $description = 'Reconcile initiated, pending, or processing payments that may be stuck.';

    public function handle(PaymentTransactionService $paymentTransactionService): int
    {
        $hours = (int) $this->option('hours');

        $transactions = Transaction::query()
            ->whereIn('payment_status_code', [
                PaymentStatus::INITIATED,
                PaymentStatus::PENDING,
                PaymentStatus::PROCESSING,
            ])
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderBy('id')
            ->get();

        foreach ($transactions as $transaction) {
            $paymentTransactionService->verify(null, [
                'merchant_order_id' => $transaction->order_id,
                'gateway_order_id' => $transaction->gateway_order_id,
            ]);

            $this->line("Reconciled transaction {$transaction->id}");
        }

        $this->info('Pending payment reconciliation completed.');

        return self::SUCCESS;
    }
}
