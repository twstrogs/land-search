<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureSearchSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $sessionKey = 'search_session_' . $request->session()->getId();
        $searchResults = Cache::get($sessionKey, []);

        if ($request->is('search/post/*')) {
            $token = $request->route('token');
            
            if (!$token || !$this->validateSearchToken($token, $sessionKey)) {
                abort(403, 'Access denied. Please perform a search first.');
            }
        }

        return $next($request);
    }

    private function validateSearchToken(string $token, string $sessionKey): bool
    {
        $expectedToken = Cache::get("{$sessionKey}_token");
        
        if (!$expectedToken) {
            return false;
        }

        return hash_equals($expectedToken, $token);
    }
}
