<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isAuthenticatedAdmin = Auth::check();
        if (! $isAuthenticatedAdmin) {
            return response()->json(['message' => 'Request rejected because the user is not authorized'], 401);
        }

        return $next($request);
    }
}
