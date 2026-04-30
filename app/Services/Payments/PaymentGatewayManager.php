<?php

namespace App\Services\Payments;

use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Services\Payments\Gateways\CashfreeService;
use App\Services\Payments\Gateways\EasebuzzService;
use App\Services\Payments\Gateways\PaynimoService;
use App\Services\Payments\Gateways\RazorpayService;
use Illuminate\Support\Arr;

class PaymentGatewayManager
{
    protected array $drivers = [
        'razorpay' => RazorpayService::class,
        'cashfree' => CashfreeService::class,
        'easebuzz' => EasebuzzService::class,
        'paynimo' => PaynimoService::class,
    ];

    public function activeGateway(): PaymentGateway
    {
        $gateway = PaymentGateway::active()->first();

        if ($gateway) {
            return $gateway;
        }

        return $this->fallbackGateway(config('payments.default_gateway', 'razorpay'));
    }

    public function gatewayForTransaction(Transaction $transaction): PaymentGateway
    {
        $slug = $transaction->gateway_slug ?: null;

        if ($slug) {
            return PaymentGateway::where('slug', $slug)->first()
                ?: $this->fallbackGateway($slug);
        }

        return $this->activeGateway();
    }

    public function gatewayBySlug(string $slug): PaymentGateway
    {
        return PaymentGateway::where('slug', $slug)->first()
            ?: $this->fallbackGateway($slug);
    }

    public function driver(PaymentGateway|string $gateway)
    {
        $slug = $gateway instanceof PaymentGateway ? $gateway->slug : $gateway;
        $class = Arr::get($this->drivers, $slug);

        if (!$class) {
            throw new \InvalidArgumentException("Unsupported payment gateway [{$slug}]");
        }

        return app($class);
    }

    protected function fallbackGateway(string $slug): PaymentGateway
    {
        $slug = strtolower(trim($slug));

        $gateway = match ($slug) {
            'razorpay' => new PaymentGateway([
                'name' => 'Razorpay',
                'slug' => 'razorpay',
                'environment' => 'test',
                'is_active' => true,
                'key_id' => config('services.razorpay.key'),
                'key_secret' => config('services.razorpay.secret'),
                'status' => 'active',
            ]),
            'cashfree' => new PaymentGateway([
                'name' => 'CashFree',
                'slug' => 'cashfree',
                'environment' => env('CASHFREE_ENV', 'test') === 'production' ? 'live' : 'test',
                'is_active' => true,
                'app_id' => env('CASHFREE_APP_ID'),
                'key_secret' => env('CASHFREE_SECRET_KEY'),
                'webhook_secret' => env('CASHFREE_WEBHOOK_SECRET'),
                'status' => 'active',
            ]),
            'easebuzz' => new PaymentGateway([
                'name' => 'Easebuzz',
                'slug' => 'easebuzz',
                'environment' => env('EASEBUZZ_ENV', 'test'),
                'is_active' => true,
                'key_id' => env('EASEBUZZ_KEY'),
                'salt' => env('EASEBUZZ_SALT'),
                'status' => 'active',
            ]),
            'paynimo' => new PaymentGateway([
                'name' => 'Paynimo',
                'slug' => 'paynimo',
                'environment' => env('PAYNIMO_ENV', 'test'),
                'is_active' => true,
                'merchant_id' => env('PAYNIMO_MERCHANT_ID'),
                'key_secret' => env('PAYNIMO_API_KEY'),
                'encryption_key' => env('PAYNIMO_ENCRYPTION_KEY'),
                'salt' => env('PAYNIMO_SALT'),
                'status' => 'active',
            ]),
            default => null,
        };

        if (!$gateway) {
            throw new \InvalidArgumentException("Unsupported payment gateway [{$slug}]");
        }

        $hasCredential = match ($slug) {
            'razorpay' => filled($gateway->key_id) && filled($gateway->key_secret),
            'cashfree' => filled($gateway->app_id) && filled($gateway->key_secret),
            'easebuzz' => filled($gateway->key_id) && filled($gateway->salt),
            'paynimo' => filled($gateway->merchant_id) && filled($gateway->key_secret),
            default => false,
        };

        if (!$hasCredential) {
            throw new \RuntimeException("No active payment gateway found and fallback credentials for [{$slug}] are missing.");
        }

        return $gateway;
    }
}
