<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests\Feature;

use AiluraCode\Wappify\Console\Commands\RunWhatsappQueue;
use AiluraCode\Wappify\Data\WhatsappQueueConfig;
use AiluraCode\Wappify\Tests\TestCase;

final class QueueConfigTest extends TestCase
{
    public function test_flags_match_config(): void
    {
        config()->set('wappify.accounts.default.queue', [
            'connection' => 'redis',
            'name' => 'wappify-test',
            'tries' => 7,
            'timeout' => 9,
            'backoff' => [2, 4, 6],
        ]);

        $queue = WhatsappQueueConfig::fromArray(config('wappify.accounts.default.queue'));
        $this->assertSame([2, 4, 6], $queue->backoff);

        $this->assertSame(
            [
                '--queue' => 'wappify-test',
                '--tries' => 7,
                '--timeout' => 9,
                '--backoff' => '2,4,6',
                '--name' => 'wappify-test',
            ],
            RunWhatsappQueue::workerFlags($queue)
        );
    }
}
