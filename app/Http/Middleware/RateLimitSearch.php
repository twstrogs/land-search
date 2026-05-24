<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitSearch
{
    public function handle(Request $request, Closure $next, int $maxAttempts = 30, int $decayMinutes = 1): Response
    {
        $key = 'search_rate_limit:' . $request->ip();
        
        if (!auth()->check()) {
            $key .= ':' . $request->session()->getId();
        }

        $attempts = cache()->get($key, 0);
        
        if ($attempts >= $maxAttempts) {
            return response()->json([
                'error' => 'Too many search requests. Please wait a moment.',
                'retry_after' => $decayMinutes * 60,
            ], 429);
        }

        cache()->put($key, $attempts + 1, now()->addMinutes($decayMinutes));

        return $next($request);
    }
}
