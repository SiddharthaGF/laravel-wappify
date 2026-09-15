<?php

declare(strict_types=1);

use AiluraCode\Wappify\Actions\VerifyWebhookChallenge;
use AiluraCode\Wappify\Data\WhatsappAccountConfig;
use AiluraCode\Wappify\WhatsAppCloudApi;
use Illuminate\Http\Request;

/**
 * @deprecated Will be removed in v2.0. Resolve the transport via the account config instead.
 */
function whatsapp(string $account = 'default'): WhatsAppCloudApi
{
    trigger_error('whatsapp() is deprecated and will be removed in v2.0.', E_USER_DEPRECATED);

    $config = WhatsappAccountConfig::fromConfig($account);

    return new WhatsAppCloudApi([
        'from_phone_number_id' => $config->number_id,
        'access_token' => $config->token,
    ]);
}

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
