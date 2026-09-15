<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Http\Middleware;

use AiluraCode\Wappify\Data\WhatsappAccountConfig;
use Closure;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class FacebookMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('post')) {
            return $next($request);
        }

        $account = $request->route('account', 'default');

        if (! is_string($account) || $account === '') {
            return response()->json(['message' => 'Unknown WhatsApp account'], 404);
        }

        try {
            $secret = WhatsappAccountConfig::fromConfig($account)->app_secret;
        } catch (InvalidArgumentException) {
            return response()->json(['message' => 'Unknown WhatsApp account'], 404);
        }

        $signature = $request->header('X-Hub-Signature-256', '');

        if (! is_string($signature) || ! self::isValidSignature($request->getContent(), $signature, $secret)) {
            return response()->json(['message' => 'Invalid webhook signature'], 401);
        }

        return $next($request);
    }

    private static function isValidSignature(string $body, string $signature, string $secret): bool
    {
        if ($secret === '' || ! str_starts_with($signature, 'sha256=')) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $body, $secret);

        return hash_equals($expected, $signature);
    }
}
