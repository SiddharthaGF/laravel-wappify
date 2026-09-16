<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Jobs;

use AiluraCode\Wappify\Actions\DownloadMessageMedia;

use AiluraCode\Wappify\Data\WhatsappAccountConfig;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Throwable;
use UnexpectedValueException;

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
        $this->collection = $collection !== 'default' ? $collection : (string) config('wappify.spatie.collection', 'default');
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

    /**
     * @throws BindingResolutionException
     */
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

    /**
     * @throws BindingResolutionException
     */
    private function command(): DownloadMessageMedia
    {
        $command = $this->command ?? App::make(DownloadMessageMedia::class, [
            'whatsappId' => $this->whatsappId,
            'collection' => $this->collection,
            'name' => $this->name,
            'account' => $this->account,
        ]);
        if (! $command instanceof DownloadMessageMedia) {
            throw new UnexpectedValueException('Cannot resolve DownloadMessageMedia command.');
        }

        return $this->command = $command;
    }
}
