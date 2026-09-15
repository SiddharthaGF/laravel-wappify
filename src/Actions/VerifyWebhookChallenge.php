<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Actions;

use AiluraCode\Wappify\Data\WhatsappAccountConfig;
use InvalidArgumentException;
use Netflie\WhatsAppCloudApi\WebHook;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Verify the GET webhook challenge for an account.
 *
 * Unknown accounts fail via `fromConfig` (mapped to 404 by the caller);
 * a mismatched token aborts with 403. Nothing is persisted.
 */
final class VerifyWebhookChallenge
{
    /**
     * @param array<string, mixed> $query
     */
    public function __construct(
        private array $query,
        private string $account = 'default',
    ) {}

    /**
     * @throws InvalidArgumentException When the account is unknown.
     * @throws HttpException            When the verify token mismatches.
     */
    public function __invoke(): string
    {
        $config = WhatsappAccountConfig::fromConfig($this->account);

        $token = $this->query['hub_verify_token'] ?? null;

        if (! is_string($token) || $config->verify_token === '' || ! hash_equals($config->verify_token, $token)) {
            abort(403, 'Invalid verify token.');
        }

        return (new WebHook())->verify($this->query, $config->verify_token);
    }
}
