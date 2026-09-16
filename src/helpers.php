<?php

declare(strict_types=1);

use AiluraCode\Wappify\Actions\VerifyWebhookChallenge;
use Illuminate\Http\Request;

/**
 * @deprecated Will be removed in v2.0. Use `AiluraCode\Wappify\Actions\VerifyWebhookChallenge` instead.
 */
function webhook(Request $request, string $account = 'default'): string
{
    trigger_error('webhook() is deprecated and will be removed in v2.0. Use AiluraCode\\Wappify\\Actions\\VerifyWebhookChallenge instead.', E_USER_DEPRECATED);

    return app(VerifyWebhookChallenge::class, [
        'query' => $request->query->all(),
        'account' => $account,
    ])();
}
