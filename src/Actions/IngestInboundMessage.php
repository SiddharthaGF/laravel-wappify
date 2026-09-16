<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Actions;

use AiluraCode\Wappify\Data\IncomingMessageData;
use AiluraCode\Wappify\Data\WhatsappAccountConfig;
use AiluraCode\Wappify\Enums\MessageType;
use AiluraCode\Wappify\Exceptions\UnknownMessageTypeException;
use AiluraCode\Wappify\Jobs\DownloadMediaJob;
use AiluraCode\Wappify\Models\Whatsapp;
use AiluraCode\Wappify\Support\PayloadMapper;
use AiluraCode\Wappify\WhatsAppCloudApi;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Config;
use Netflie\WhatsAppCloudApi\Response\ResponseException;

/**
 * Persist an inbound message payload exactly once, then chain mark-read
 * and automatic media download.
 *
 * Redelivery returns the existing row; a lost unique-constraint race
 * resolves to the stored row; any other failure rethrows.
 */
final class IngestInboundMessage
{
    public function __construct(
        private readonly string $payload,
        private readonly string $account = 'default',
    ) {}

    /**
     * @throws UnknownMessageTypeException|ResponseException|Exception
     */
    public function __invoke(): Whatsapp
    {
        $whatsapp = $this->store(PayloadMapper::fromJson($this->payload));

        WhatsAppCloudApi::forAccount($this->account)->markMessageAsRead($whatsapp->getWamId());

        $canDownload = (bool) Config::get('wappify.download.automatic');
        if (! $canDownload) {
            return $whatsapp;
        }

        if (in_array($whatsapp->getType(), [
            MessageType::AUDIO,
            MessageType::DOCUMENT,
            MessageType::IMAGE,
            MessageType::STICKER,
            MessageType::VIDEO,
        ], true)) {
            $queue = WhatsappAccountConfig::fromConfig($this->account)->queue;
            DownloadMediaJob::dispatch($whatsapp->id)
                ->onQueue($queue->name)
                ->onConnection($queue->connection);
        }

        return $whatsapp;
    }

    /**
     * Resolve a lost unique-constraint race as a successful duplicate.
     *
     * @throws QueryException When the failure is not a duplicate key.
     */
    public function resolveDuplicateWrite(QueryException $exception, string $wamid): Whatsapp
    {
        if (! self::isDuplicateKey($exception)) {
            throw $exception;
        }

        $existing = Whatsapp::query()->where('wamid', $wamid)->first();

        if (! $existing instanceof Whatsapp) {
            throw $exception;
        }

        return $existing;
    }

    public function store(IncomingMessageData $data): Whatsapp
    {
        try {
            return Whatsapp::query()->firstOrCreate(
                ['wamid' => $data->wamid],
                [
                    'profile' => $data->profile,
                    'from' => $data->from,
                    'type' => $data->type->value,
                    'message' => (array) $data->message,
                    'timestamp' => $data->timestamp,
                ]
            );
        } catch (QueryException $exception) {
            return $this->resolveDuplicateWrite($exception, $data->wamid);
        }
    }

    private static function isDuplicateKey(QueryException $exception): bool
    {
        if ((string) $exception->getCode() === '23000') {
            return true;
        }

        $errorInfo = $exception->errorInfo;

        if (! is_array($errorInfo)) {
            return false;
        }

        $code = $errorInfo[0] ?? null;

        return is_scalar($code) && (string) $code === '23000';
    }
}
