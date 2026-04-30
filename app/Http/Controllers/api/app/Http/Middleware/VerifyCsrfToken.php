<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'member/card-recharge-response','member/subscription-response', 'sbi-sucess', 'app-sbi-sucess', 'sbi/payment/fail', 'app/sbi/payment/fail'
    ];
}
