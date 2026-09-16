<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Support;

use AiluraCode\Wappify\Data\IncomingMessageData;
use AiluraCode\Wappify\Data\StatusUpdatePayload;
use AiluraCode\Wappify\Data\WhatsappAccountConfig;
use AiluraCode\Wappify\Enums\MessageStatusType;
use AiluraCode\Wappify\Enums\MessageType;
use AiluraCode\Wappify\Models\Whatsapp;
use Exception;
use InvalidArgumentException;
use Netflie\WhatsAppCloudApi\Request\RequestWithBody;
use Netflie\WhatsAppCloudApi\Response;

/**
 * Pure payload mapping extracted verbatim from `Wappify` statics.
 *
 * Framework-free: maps raw JSON and transport responses to
 * `IncomingMessageData` / `StatusUpdatePayload` and back to an
 * unsaved `Whatsapp` model. Never touches HTTP, queues, or config
 * beyond the account profile lookup in `fromResponse`.
 */
final class PayloadMapper
{
    /**
     * Build the model data from an inbound message payload.
     *
     * This path only handles inbound messages. Status payloads are handled by
     * the lifecycle path, which never persists a new row.
     *
     * @param array<string, mixed> $json
     *
     * @throws Exception
     */
    public static function fromDecoded(array $json): IncomingMessageData
    {
        $entry = self::readFirstMap(self::readKey($json, 'entry'));
        $changes = self::readFirstMap(self::readKey($entry, 'changes'));
        $value = self::readKey($changes, 'value');
        if (! is_array($value)) {
            throw new Exception('Invalid payload');
        }
        $message = self::readFirstMap(self::readKey($value, 'messages'));
        if (! is_array($message)) {
            throw new Exception('Invalid payload');
        }

        $type = self::stringOrEmpty($message['type'] ?? null);
        $rawPayload = $message[$type] ?? [];
        $payloadObject = (object) (is_array($rawPayload) ? $rawPayload : []);
        $contact = self::readFirstMap(self::readKey($value, 'contacts'));

        return new IncomingMessageData(
            self::stringOrEmpty($message['id'] ?? null),
            self::stringOrEmpty(self::readKey(self::readKey($contact, 'profile'), 'name')),
            self::stringOrEmpty($message['from'] ?? null),
            MessageType::from($type),
            $payloadObject,
            self::intOrZero($message['timestamp'] ?? null),
        );
    }

    /**
     * @throws InvalidArgumentException|Exception
     */
    public static function fromJson(string $payload): IncomingMessageData
    {
        $json = json_decode($payload, true);
        if (! is_array($json) || json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid payload');
        }

        $decoded = [];
        foreach ($json as $key => $value) {
            if (! is_string($key)) {
                throw new InvalidArgumentException('Invalid payload');
            }
            $decoded[$key] = $value;
        }

        return self::fromDecoded($decoded);
    }

    /**
     * @throws Exception
     */
    public static function fromResponse(Response $response, string $account = 'default'): IncomingMessageData
    {
        $request = $response->request();
        if (! $request instanceof RequestWithBody) {
            throw new Exception('Response has no request body.');
        }

        $whatsappRequest = $request->body();
        $body = $response->decodedBody();

        $type = self::stringOrEmpty($whatsappRequest['type'] ?? null);

        $rawPayload = $whatsappRequest[$type] ?? [];
        $message = is_array($rawPayload) ? $rawPayload : [];

        $wamid = self::stringOrEmpty(self::readId(self::readFirstMap(self::readKey($body, 'messages'))));
        $from = self::stringOrEmpty(self::readWaId(self::readFirstMap(self::readKey($body, 'contacts'))));
        $profile = WhatsappAccountConfig::fromConfig($account)->profile;

        return new IncomingMessageData(
            $wamid,
            $profile,
            $from,
            MessageType::from($type),
            (object) $message,
            time(),
        );
    }

    /**
     * Build the lifecycle data from an inbound status payload.
     *
     * Returns null when the payload carries no status, so callers can fall back
     * to the inbound-message path. Status handling never creates a record.
     *
     * @throws InvalidArgumentException
     */
    public static function statusFromJson(string $payload): ?StatusUpdatePayload
    {
        $json = json_decode($payload, true);
        if (! is_array($json) || json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid payload');
        }
        $entry = self::readFirstMap(self::readKey($json, 'entry'));
        $changes = self::readFirstMap(self::readKey($entry, 'changes'));
        $value = self::readKey($changes, 'value');
        if (! is_array($value)) {
            return null;
        }
        $status = self::readFirstMap(self::readKey($value, 'statuses'));

        if (! is_array($status)) {
            return null;
        }

        $rawStatus = self::stringOrEmpty($status['status'] ?? null);
        $mapped = MessageStatusType::tryFrom($rawStatus) ?? MessageStatusType::WAITING;

        return new StatusUpdatePayload(
            self::stringOrEmpty($status['id'] ?? null),
            $mapped,
        );
    }

    public static function toModel(IncomingMessageData $data): Whatsapp
    {
        return (new Whatsapp())->newInstance([
            'wamid' => $data->wamid,
            'profile' => $data->profile,
            'from' => $data->from,
            'type' => $data->type,
            'message' => $data->message,
            'timestamp' => $data->timestamp,
        ]);
    }

    private static function intOrZero(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * @return array<mixed>|null
     */
    private static function readFirstMap(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        foreach ($value as $entry) {
            if (is_array($entry)) {
                return $entry;
            }
        }

        return null;
    }

    private static function readId(mixed $entry): mixed
    {
        return self::readKey($entry, 'id');
    }

    private static function readKey(mixed $container, string $key): mixed
    {
        return is_array($container) ? ($container[$key] ?? null) : null;
    }

    private static function readWaId(mixed $entry): mixed
    {
        return self::readKey($entry, 'wa_id');
    }

    private static function stringOrEmpty(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
