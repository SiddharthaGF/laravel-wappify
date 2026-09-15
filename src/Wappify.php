<?php

declare(strict_types=1);

namespace AiluraCode\Wappify;

use AiluraCode\Wappify\Data\IncomingMessageData;
use AiluraCode\Wappify\Data\StatusUpdatePayload;
use AiluraCode\Wappify\Models\Whatsapp;
use AiluraCode\Wappify\Support\PayloadMapper;
use Exception;
use Netflie\WhatsAppCloudApi\Response;

/**
 * Deprecated entry-point facade over the command layer.
 *
 * Every static delegates to `PayloadMapper` and stays behavior-identical,
 * but new code MUST use the commands in `src/Actions` (or `PayloadMapper`
 * directly) instead.
 *
 * @deprecated Will be removed in v2.0. Use `AiluraCode\Wappify\Support\PayloadMapper` and the commands in `AiluraCode\Wappify\Actions` instead.
 */
final class Wappify
{
    /**
     * @deprecated Will be removed in v2.0.
     */
    public function __construct(
        private readonly Whatsapp $whatsapp
    ) {}

    /**
     * @throws Exception
     *
     * @deprecated Will be removed in v2.0. Use `PayloadMapper::fromJson()` instead.
     */
    public static function catch(string $payload): self
    {
        trigger_error('Wappify::catch() is deprecated and will be removed in v2.0. Use PayloadMapper::fromJson() instead.', E_USER_DEPRECATED);

        return new self(PayloadMapper::toModel(PayloadMapper::fromJson($payload)));
    }

    /**
     * @deprecated Will be removed in v2.0. Use `PayloadMapper::toModel()` instead.
     */
    public static function createFromModel(IncomingMessageData $data): self
    {
        trigger_error('Wappify::createFromModel() is deprecated and will be removed in v2.0. Use PayloadMapper::toModel() instead.', E_USER_DEPRECATED);

        return new self(PayloadMapper::toModel($data));
    }

    /**
     * Build the model from an inbound message payload.
     *
     * This method only handles inbound messages. Status payloads are handled by
     * the lifecycle path, which never persists a new row.
     *
     * @throws Exception
     *
     * @deprecated Will be removed in v2.0. Use `PayloadMapper::fromJson()` instead.
     */
    public static function payloadToModel(string $payload): IncomingMessageData
    {
        trigger_error('Wappify::payloadToModel() is deprecated and will be removed in v2.0. Use PayloadMapper::fromJson() instead.', E_USER_DEPRECATED);

        return PayloadMapper::fromJson($payload);
    }

    /**
     * Build the lifecycle data from an inbound status payload.
     *
     * Returns null when the payload carries no status, so callers can fall back
     * to the inbound-message path. Status handling never creates a record.
     *
     * @deprecated Will be removed in v2.0. Use `PayloadMapper::statusFromJson()` instead.
     */
    public static function payloadToStatus(string $payload): ?StatusUpdatePayload
    {
        trigger_error('Wappify::payloadToStatus() is deprecated and will be removed in v2.0. Use PayloadMapper::statusFromJson() instead.', E_USER_DEPRECATED);

        return PayloadMapper::statusFromJson($payload);
    }

    /**
     * @deprecated Will be removed in v2.0. Use the send commands in `AiluraCode\Wappify\Actions` instead.
     */
    public static function raise(Response $response): self
    {
        trigger_error('Wappify::raise() is deprecated and will be removed in v2.0. Use the send commands in AiluraCode\\Wappify\\Actions instead.', E_USER_DEPRECATED);

        return new self(PayloadMapper::toModel(PayloadMapper::fromResponse($response)));
    }

    /**
     * @deprecated Will be removed in v2.0. Use `PayloadMapper::fromResponse()` instead.
     */
    public static function responseToModel(Response $response, string $account = 'default'): IncomingMessageData
    {
        trigger_error('Wappify::responseToModel() is deprecated and will be removed in v2.0. Use PayloadMapper::fromResponse() instead.', E_USER_DEPRECATED);

        return PayloadMapper::fromResponse($response, $account);
    }

    /**
     * @deprecated Will be removed in v2.0.
     */
    public function get(): Whatsapp
    {
        return $this->whatsapp;
    }
}
