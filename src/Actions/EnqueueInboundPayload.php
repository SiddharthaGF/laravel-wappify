<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Actions;

use AiluraCode\Wappify\Data\WhatsappAccountConfig;
use AiluraCode\Wappify\Jobs\ReceiveMessageJob;
use InvalidArgumentException;

/**
 * Dispatch the raw inbound payload onto the account queue and acknowledge.
 *
 * Never parses the payload; unknown accounts fail via `fromConfig` before
 * anything is dispatched.
 */
final class EnqueueInboundPayload
{
    public function __construct(
        private string $payload,
        private string $account = 'default',
    ) {}

    /**
     * @throws InvalidArgumentException When the account is unknown.
     *
     * @return array{message: string}
     */
    public function __invoke(): array
    {
        $queue = WhatsappAccountConfig::fromConfig($this->account)->queue;

        ReceiveMessageJob::dispatch($this->payload, $this->account)
            ->onQueue($queue->name)
            ->onConnection($queue->connection);

        return ['message' => 'Message received'];
    }
}
