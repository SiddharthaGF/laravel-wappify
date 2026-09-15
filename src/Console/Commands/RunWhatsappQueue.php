<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Console\Commands;

use AiluraCode\Wappify\Data\WhatsappAccountConfig;
use AiluraCode\Wappify\Data\WhatsappQueueConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

final class RunWhatsappQueue extends Command
{
    /**
     * The console command description.
     */
    protected $description = 'Run the queue for whatsapp messages.';
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'wappify:queue';

    /**
     * @return array<string, int|string>
     */
    public static function workerFlags(WhatsappQueueConfig $queue): array
    {
        return [
            '--queue' => $queue->name,
            '--tries' => $queue->tries,
            '--timeout' => $queue->timeout,
            '--backoff' => implode(',', $queue->backoff),
            '--name' => $queue->name,
        ];
    }

    public function handle(): void
    {
        $queue = WhatsappAccountConfig::fromConfig('default')->queue;
        $this->line('Running queue');
        Artisan::call('queue:listen', self::workerFlags($queue));
    }
}
