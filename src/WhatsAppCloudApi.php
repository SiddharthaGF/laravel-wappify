<?php

declare(strict_types=1);

namespace AiluraCode\Wappify;

use AiluraCode\Wappify\Data\WhatsappAccountConfig;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi as WhatsAppCloudApiBase;

/**
 * Package transport: pure passthrough to the Netflie base.
 *
 * This class MUST NOT persist anything. Outbound persistence is owned
 * exclusively by the send commands in `src/Actions` (single-owner rule).
 */
final class WhatsAppCloudApi extends WhatsAppCloudApiBase
{
    /**
     * Resolve the transport for an account from the package config.
     */
    public static function forAccount(string $account = 'default'): self
    {
        $config = WhatsappAccountConfig::fromConfig($account);

        return new self([
            'from_phone_number_id' => $config->number_id,
            'access_token' => $config->token,
        ]);
    }
}
