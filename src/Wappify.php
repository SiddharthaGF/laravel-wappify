<?php

declare(strict_types=1);

namespace AiluraCode\Wappify;

use AiluraCode\Wappify\Data\IncomingMessageData;
use AiluraCode\Wappify\Data\StatusUpdatePayload;
use AiluraCode\Wappify\Models\Whatsapp;
use AiluraCode\Wappify\Support\PayloadMapper;
use Exception;
use Netflie\WhatsAppCloudApi\Response;

final class Wappify
{
    public function __construct(
        private readonly Whatsapp $whatsapp
    ) {}

    /**
     * @throws Exception
     */
    public static function catch(string $payload): self
    {
        return self::createFromModel(PayloadMapper::fromJson($payload));
    }

    public static function createFromModel(IncomingMessageData $data): self
    {
        return new self(PayloadMapper::toModel($data));
    }

    /**
     * Build the model from an inbound message payload.
     *
     * This method only handles inbound messages. Status payloads are handled by
     * the lifecycle path, which never persists a new row.
     *
     * @throws Exception
     */
    public static function payloadToModel(string $payload): IncomingMessageData
    {
        return PayloadMapper::fromJson($payload);
    }

    /**
     * Build the lifecycle data from an inbound status payload.
     *
     * Returns null when the payload carries no status, so callers can fall back
     * to the inbound-message path. Status handling never creates a record.
     */
    public static function payloadToStatus(string $payload): ?StatusUpdatePayload
    {
        return PayloadMapper::statusFromJson($payload);
    }

    public static function raise(Response $response): self
    {
        return self::createFromModel(PayloadMapper::fromResponse($response));
    }

    public static function responseToModel(Response $response, string $account = 'default'): IncomingMessageData
    {
        return PayloadMapper::fromResponse($response, $account);
    }

    public function get(): Whatsapp
    {
        return $this->whatsapp;
    }
}
