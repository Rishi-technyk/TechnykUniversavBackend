<?php
// app/Http/Middleware/SecurityHeadersMiddleware.php

namespace App\Http\Middleware;

use Closure;

class SecurityHeadersMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $headers = $response->headers;

        $headers->set('X-Powered-By', 'Star\'s Vynus team');

        // Set Cache-Control headers
        $headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $headers->set('Pragma', 'no-cache');
        $headers->set('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
        $headers->set('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');

        // Set X-XSS-Protection header
        $headers->set('X-XSS-Protection', '1; mode=block');

        // Set X-Content-Type-Options header
        $headers->set('X-Content-Type-Options', 'nosniff');

        // Set Strict-Transport-Security header
        $headers->set('Strict-Transport-Security', 'max-age=2592000; includeSubDomains');

        // Set Referrer-Policy header
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Set Content-Security-Policy header
        //$headers->set('Content-Security-Policy', "script-src *; object-src 'none'; base-uri 'none';");
		
		$nonce = base64_encode(random_bytes(16));
		$csp = "script-src 'self' https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com 'unsafe-inline' 'unsafe-eval';";
		$headers->set('Content-Security-Policy', $csp);
		$headers->set('X-Content-Security-Policy', $csp);
		$headers->set('X-WebKit-CSP', $csp);

        return $response;
    }
}
