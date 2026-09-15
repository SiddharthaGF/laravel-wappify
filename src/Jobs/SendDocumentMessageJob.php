<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Jobs;

use AiluraCode\Wappify\Actions\SendDocumentMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Netflie\WhatsAppCloudApi\Message\Error\InvalidMessage;
use Netflie\WhatsAppCloudApi\Response\ResponseException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class SendDocumentMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $from,
        private readonly Media $document,
        private readonly string $account = 'default',
    ) {}

    /**
     * @throws ResponseException
     * @throws InvalidMessage
     */
    public function handle(): void
    {
        app(SendDocumentMessage::class, [
            'to' => $this->from,
            'document' => $this->document,
            'account' => $this->account,
        ])();
    }

    /**
     * Resolve the public document URL verbatim, without host rewrites.
     */
    public function resolveDocumentUrl(): string
    {
        return app(SendDocumentMessage::class, [
            'to' => $this->from,
            'document' => $this->document,
            'account' => $this->account,
        ])->resolveDocumentUrl();
    }
}
