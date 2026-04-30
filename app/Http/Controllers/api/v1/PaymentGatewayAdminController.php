<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\UpsertPaymentGatewayRequest;
use App\Models\PaymentGateway;
use App\Models\PaymentWebhookLog;
use App\Models\Transaction;
use App\Services\Payments\PaymentTransactionService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentGatewayAdminController extends Controller
{
    public function __construct(protected PaymentTransactionService $paymentTransactionService)
    {
    }

    public function indexGateways()
    {
        return response()->json([
            'status' => true,
            'data' => PaymentGateway::orderByDesc('is_active')->orderBy('name')->get(),
        ]);
    }

    public function storeGateway(UpsertPaymentGatewayRequest $request)
    {
        $gateway = PaymentGateway::create($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Payment gateway created successfully.',
            'data' => $gateway,
        ], 201);
    }

    public function updateGateway(UpsertPaymentGatewayRequest $request, PaymentGateway $gateway)
    {
        $gateway->update($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Payment gateway updated successfully.',
            'data' => $gateway->fresh(),
        ]);
    }

    public function activateGateway(PaymentGateway $gateway)
    {
        $gateway->update([
            'is_active' => true,
            'status' => 'active',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Payment gateway activated successfully.',
            'data' => $gateway->fresh(),
        ]);
    }

    public function transactions(Request $request)
    {
        $query = Transaction::query()->latest('id');

        if ($request->filled('gateway_slug')) {
            $query->where('gateway_slug', $request->gateway_slug);
        }

        if ($request->filled('payment_status_code')) {
            $query->where('payment_status_code', $request->payment_status_code);
        }

        if ($request->filled('module')) {
            $query->where('payment_type', $request->module);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        return response()->json([
            'status' => true,
            'data' => $query->paginate((int) $request->query('limit', 20)),
        ]);
    }

    public function webhookLogs(Request $request)
    {
        $query = PaymentWebhookLog::query()->latest('id');

        if ($request->filled('gateway_slug')) {
            $query->where('gateway_slug', $request->gateway_slug);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'status' => true,
            'data' => $query->paginate((int) $request->query('limit', 20)),
        ]);
    }

    public function retryTransaction(Request $request, string $reference)
    {
        $member = $request->user();
        return response()->json($this->paymentTransactionService->retry($member, $reference));
    }

    public function downloadReport(Request $request): StreamedResponse
    {
        $transactions = Transaction::query()
            ->when($request->filled('gateway_slug'), fn ($query) => $query->where('gateway_slug', $request->gateway_slug))
            ->when($request->filled('payment_status_code'), fn ($query) => $query->where('payment_status_code', $request->payment_status_code))
            ->latest('id')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="payment-transactions-report.csv"',
        ];

        return response()->stream(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Gateway', 'Module', 'Amount', 'Legacy Status', 'Normalized Status', 'Gateway Order ID', 'Gateway Transaction ID', 'Created At']);

            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    $transaction->id,
                    $transaction->gateway_name,
                    $transaction->payment_type ?: $transaction->type,
                    $transaction->amount,
                    $transaction->payment_status,
                    $transaction->payment_status_code,
                    $transaction->gateway_order_id,
                    $transaction->gateway_transaction_id,
                    $transaction->created_at,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
