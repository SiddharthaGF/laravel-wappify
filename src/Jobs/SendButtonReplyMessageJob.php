<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Jobs;

use AiluraCode\Wappify\Actions\SendButtonReplyMessage;
use AiluraCode\Wappify\Data\MessageButtons;
use AiluraCode\Wappify\Data\WhatsappAccountConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class SendButtonReplyMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 5;

    public int $tries = 3;

    /** @var array<int, int> */
    private array $retryBackoff = [1, 5, 15];

    public function __construct(
        private readonly string $from,
        private readonly string $message,
        private readonly MessageButtons $buttons,
        private readonly string $account = 'default'
    ) {
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

    public function handle(): void
    {
        // The command owns failure semantics (logs once, then rethrows for
        // tries/backoff), so this wrapper delegates without logging again.
        app(SendButtonReplyMessage::class, [
            'to' => $this->from,
            'message' => $this->message,
            'buttons' => $this->buttons,
            'account' => $this->account,
        ])();
    }
}
