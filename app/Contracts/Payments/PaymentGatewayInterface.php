<?php

namespace App\Contracts\Payments;

use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function slug(): string;

    public function createOrder(Transaction $transaction, PaymentGateway $gateway, array $context = []): array;

    public function verifyPayment(Transaction $transaction, array $payload, PaymentGateway $gateway): array;

    public function verifyWebhookSignature(Request $request, PaymentGateway $gateway): bool;

    public function parseWebhook(Request $request, PaymentGateway $gateway): array;

    public function renderCheckoutPage(Transaction $transaction, PaymentGateway $gateway): string;
}
