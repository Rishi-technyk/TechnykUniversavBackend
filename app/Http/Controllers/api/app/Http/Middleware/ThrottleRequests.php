<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;

class ThrottleRequests
{
    /**
     * The rate limiter instance.
     *
     * @var \Illuminate\Cache\RateLimiter
     */
    protected $limiter;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Cache\RateLimiter  $limiter
     * @return void
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle($request, Closure $next, $maxAttempts = 300, $decayMinutes = 2)
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $response = $this->buildLockoutResponse($request, $key, $this->limiter->availableIn($key));

            return $this->addHeaders(
                $response,
                $maxAttempts,
                $this->limiter->retryAfter($key)
            );
        }

        $this->limiter->hit($key, $decayMinutes);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    /**
     * Create a "too many attempts" response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $key
     * @param  int  $retryAfter
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function buildLockoutResponse(Request $request, $key, $retryAfter)
    {
        $seconds = $retryAfter % 60;
        $minutes = ($retryAfter - $seconds) / 60;
		Session::flash('error', 'Too many login attempts. Please try again in ' . $minutes . ' minutes and ' . $seconds . ' seconds.');

        return new Response('Too many login attempts. Please try again in ' . $minutes . ' minutes and ' . $seconds . ' seconds.', 429);
    }

    /**
     * Calculate the number of remaining attempts.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @return int
     */
    protected function calculateRemainingAttempts($key, $maxAttempts)
    {
        return $maxAttempts - $this->limiter->attempts($key) + 1;
    }

    /**
     * Add the limit header information to the given response.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @param  int  $maxAttempts
     * @param  int  $remainingAttempts
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function addHeaders(Response $response, $maxAttempts, $remainingAttempts)
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remainingAttempts - 1),
            'X-RateLimit-Reset' => $this->limiter->availableIn($this->resolveRequestSignature()),
        ]);

        return $response;
    }

    /**
     * Resolve request signature.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return string
     */
    protected function resolveRequestSignature(Request $request = null)
    {
        return $request ? sha1(
            $request->method() . '|' . $request->server('SERVER_NAME') . '|' . $request->path() . '|' . $request->ip()
        ) : '';
    }
}
