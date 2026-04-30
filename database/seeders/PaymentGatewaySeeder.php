<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        PaymentGateway::updateOrCreate(
            ['slug' => 'razorpay'],
            [
                'name' => 'Razorpay',
                'environment' => env('PAYMENT_DEFAULT_GATEWAY_ENV', 'test'),
                'is_active' => true,
                'key_id' => config('services.razorpay.key'),
                'key_secret' => config('services.razorpay.secret'),
                'status' => 'active',
            ]
        );
    }
}
