<?php

declare(strict_types=1);

use AiluraCode\Wappify\Data\WhatsappAccountConfig;
use AiluraCode\Wappify\WhatsAppCloudApi;
use Illuminate\Http\Request;
use Netflie\WhatsAppCloudApi\WebHook;

function whatsapp(string $account = 'default'): WhatsAppCloudApi
{
    $config = WhatsappAccountConfig::fromConfig($account);

    return new WhatsAppCloudApi([
        'from_phone_number_id' => $config->number_id,
        'access_token' => $config->token,
    ]);
}

function webhook(Request $request, string $account = 'default'): string
{
    $config = WhatsappAccountConfig::fromConfig($account);

    $query = $request->query->all();
    $token = $query['hub_verify_token'] ?? null;

    if (! is_string($token) || $config->verify_token === '' || ! hash_equals($config->verify_token, $token)) {
        abort(403, 'Invalid verify token.');
    }

    return (new WebHook())->verify($query, $config->verify_token);
}
