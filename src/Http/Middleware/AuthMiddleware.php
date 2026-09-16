<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (SymfonyResponse) $next
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $isAuthenticatedAdmin = Auth::check();
        if (! $isAuthenticatedAdmin) {
            return Response::json(['message' => 'Request rejected because the user is not authorized'], 401);
        }

        return $next($request);
    }
}
