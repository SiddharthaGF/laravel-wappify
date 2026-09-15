<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Jobs;

use AiluraCode\Wappify\Actions\DownloadMessageMedia;
use AiluraCode\Wappify\Data\WhatsappAccountConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Throwable;

final class DownloadMediaJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 5;

    public int $tries = 3;

    private readonly string $account;

    private readonly string $collection;

    private ?DownloadMessageMedia $command = null;

    private readonly ?string $name;

    /** @var array<int, int> */
    private array $retryBackoff;

    private readonly int $whatsappId;

    public function __construct(
        int $whatsappId,
        string $collection = 'default',
        ?string $name = null,
        string $account = 'default',
    ) {
        $this->whatsappId = $whatsappId;
        $this->collection = $collection !== 'default' ? $collection : Config::string('wappify.spatie.collection', 'default');
        $this->name = $name;
        $this->account = $account;

        $queue = WhatsappAccountConfig::fromConfig($this->account)->queue;
        $this->tries = $queue->tries;
        $this->timeout = $queue->timeout;
        $this->retryBackoff = $queue->backoff;
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return $this->retryBackoff;
    }

    public function failed(Throwable $exception): void
    {
        $this->command()->failed($exception);
    }

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->command()->__invoke();
    }

    private function command(): DownloadMessageMedia
    {
        return $this->command ??= app(DownloadMessageMedia::class, [
            'whatsappId' => $this->whatsappId,
            'collection' => $this->collection,
            'name' => $this->name,
            'account' => $this->account,
        ]);
    }
}
