<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Jobs;

use AiluraCode\Wappify\Data\WhatsappAccountConfig;
use AiluraCode\Wappify\Exceptions\CastToMediaException;
use AiluraCode\Wappify\Exceptions\PropertyNoExists;
use AiluraCode\Wappify\Exceptions\UnknownMessageTypeException;
use AiluraCode\Wappify\Models\Whatsapp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Netflie\WhatsAppCloudApi\Response\ResponseException;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

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

    private readonly ?string $name;

    private ?string $resolvedFileName = null;

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
        $this->collection = $collection !== 'default' ? $collection : Config::string('wappify.spatie.collection', 'default');
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

    public function failed(Throwable $exception): void
    {
        Log::error('DownloadMediaJob failed permanently', ['whatsapp_id' => $this->whatsappId, 'exception' => $exception]);

        if ($this->resolvedFileName === null) {
            return;
        }

        $whatsapp = Whatsapp::query()->find($this->whatsappId);

        if (! $whatsapp instanceof Whatsapp) {
            return;
        }

        $whatsapp->getMedia($this->collection)
            ->where('file_name', $this->resolvedFileName)
            ->each(static fn (Media $media): bool => (bool) $media->delete());
    }

    /**
     * @throws ResponseException
     * @throws Throwable
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     * @throws UnknownMessageTypeException
     * @throws CastToMediaException
     * @throws PropertyNoExists
     */
    public function handle(): void
    {
        try {
            $whatsapp = Whatsapp::query()->find($this->whatsappId);

            if (! $whatsapp instanceof Whatsapp) {
                throw new ModelNotFoundException("WhatsApp row $this->whatsappId not found.");
            }

            $media = $whatsapp->toMedia();
            $mimeType = $media->getMimeType();

            if (! self::isAllowedMimeType($mimeType)) {
                throw new RuntimeException("Unsupported media MIME type \"$mimeType\".");
            }

            $fullName = ($this->name ?? $this->formatWamId($whatsapp->getWamId())) . '.' . self::extensionFor($mimeType);
            $this->resolvedFileName = $fullName;

            $response = whatsapp($this->account)->downloadMedia($media->getId());
            $whatsapp->addMediaFromStream($response->body())
                ->usingFileName($fullName)
                ->toMediaCollection($this->collection);
        } catch (Throwable $throwable) {
            Log::error('DownloadMediaJob failed', ['whatsapp_id' => $this->whatsappId, 'collection' => $this->collection, 'exception' => $throwable]);

            throw $throwable;
        }
    }

    private static function extensionFor(string $mimeType): string
    {
        $extension = explode('/', $mimeType)[1] ?? null;

        if (! is_string($extension) || $extension === '') {
            throw new RuntimeException("Cannot derive a file extension from MIME type \"$mimeType\".");
        }

        return $extension;
    }

    private static function isAllowedMimeType(string $mimeType): bool
    {
        if (str_starts_with($mimeType, 'image/') || str_starts_with($mimeType, 'audio/') || str_starts_with($mimeType, 'video/')) {
            return true;
        }

        return $mimeType === 'application/pdf';
    }

    private function formatWamId(string $wamId): string
    {
        return rtrim(ltrim($wamId, 'wamid.'), '=');
    }
}
