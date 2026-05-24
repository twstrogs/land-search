<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventPostEnumeration
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('posts/*') && !auth()->check()) {
            if (!$this->isFromSearchResult($request)) {
                abort(403, 'Direct access to posts is not allowed.');
            }
        }

        return $next($request);
    }

    private function isFromSearchResult(Request $request): bool
    {
        $referer = $request->headers->get('referer');
        $sessionId = $request->session()->getId();
        
        return $referer && str_contains($referer, url('/search'));
    }
}
