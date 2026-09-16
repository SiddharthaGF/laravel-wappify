<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Actions;

use AiluraCode\Wappify\Models\Whatsapp;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Netflie\WhatsAppCloudApi\Response\ResponseException;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

/**
 * Download a message's media attachment into the media library.
 *
 * Only image/audio/video/pdf MIME types are accepted; anything else is
 * rejected before touching the network. `failed()` removes a partially
 * attached file after the worker exhausts retries.
 */
final class DownloadMessageMedia
{
    private ?string $resolvedFileName = null;

    public function __construct(
        private int $whatsappId,
        private string $collection = 'default',
        private ?string $name = null,
        private string $account = 'default',
    ) {
        $this->collection = $collection !== 'default' ? $collection : Config::string('wappify.spatie.collection', 'default');
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
    public function __invoke(): void
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

            $response = WhatsAppCloudApi::forAccount($this->account)->downloadMedia($media->getId());
            $whatsapp->addMediaFromStream($response->body())
                ->usingFileName($fullName)
                ->toMediaCollection($this->collection);
        } catch (Throwable $throwable) {
            Log::error('DownloadMediaJob failed', ['whatsapp_id' => $this->whatsappId, 'collection' => $this->collection, 'exception' => $throwable]);

            throw $throwable;
        }
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
