<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Jobs;

use AiluraCode\Wappify\Wappify;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Netflie\WhatsAppCloudApi\Message\Error\InvalidMessage;
use Netflie\WhatsAppCloudApi\Message\Media\LinkID;
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
        $documentLink = $this->resolveDocumentUrl();
        $documentName = $this->document->name;
        $documentCaption = "Document: $documentName";
        $linkId = new LinkID($documentLink);
        $response = whatsapp($this->account)->sendDocument(
            $this->from,
            $linkId,
            $documentName,
            $documentCaption
        );
        Wappify::raise($response)->get()->save();
    }

    /**
     * Resolve the public document URL verbatim, without host rewrites.
     */
    public function resolveDocumentUrl(): string
    {
        return $this->document->getUrl();
    }
}
