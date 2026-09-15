<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Jobs;

use AiluraCode\Wappify\Actions\SendTextMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Netflie\WhatsAppCloudApi\Response\ResponseException;

final class SendTextMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $from,
        private readonly string $text,
        private readonly string $account = 'default',
    ) {}

    /**
     * @throws ResponseException
     */
    public function handle(): void
    {
        app(SendTextMessage::class, [
            'to' => $this->from,
            'text' => $this->text,
            'account' => $this->account,
        ])();
    }
}
