<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Actions;

use AiluraCode\Wappify\Models\Whatsapp;
use AiluraCode\Wappify\Support\PayloadMapper;
use AiluraCode\Wappify\WhatsAppCloudApi;
use Exception;
use Netflie\WhatsAppCloudApi\Message\Error\InvalidMessage;
use Netflie\WhatsAppCloudApi\Message\Media\LinkID;
use Netflie\WhatsAppCloudApi\Response\ResponseException;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi as BaseTransport;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Send one document message and persist exactly one row.
 *
 * The document URL resolves verbatim via `Media::getUrl` — no host rewrites.
 */
final class SendDocumentMessage
{
    public function __construct(
        private readonly string $to,
        private readonly Media  $document,
        private readonly string $account = 'default',
    ) {}

    /**
     * @throws ResponseException|InvalidMessage|Exception
     */
    public function __invoke(?BaseTransport $transport = null): Whatsapp
    {
        $transport ??= WhatsAppCloudApi::forAccount($this->account);

        $documentName = $this->document->name;
        $response = $transport->sendDocument(
            $this->to,
            new LinkID($this->resolveDocumentUrl()),
            $documentName,
            "Document: $documentName"
        );

        $model = PayloadMapper::toModel(PayloadMapper::fromResponse($response, $this->account));
        $model->save();

        return $model;
    }

    /**
     * Resolve the public document URL verbatim, without host rewrites.
     */
    public function resolveDocumentUrl(): string
    {
        return $this->document->getUrl();
    }
}
