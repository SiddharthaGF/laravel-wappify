<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Concern;

use AiluraCode\Wappify\Contracts\Messages\ShouldMultimediaMessage;
use AiluraCode\Wappify\Contracts\Messages\ShouldTextMessage;
use AiluraCode\Wappify\Entities\Interactive\InteractiveMessage;
use AiluraCode\Wappify\Entities\Media\AudioMessage;
use AiluraCode\Wappify\Entities\Media\DocumentMessage;
use AiluraCode\Wappify\Entities\Media\ImageMessage;
use AiluraCode\Wappify\Entities\Media\StickerMessage;
use AiluraCode\Wappify\Entities\Media\VideoMessage;
use AiluraCode\Wappify\Entities\TextMessage;
use AiluraCode\Wappify\Enums\MessageType;
use AiluraCode\Wappify\Exceptions\CastToInteractiveException;
use AiluraCode\Wappify\Exceptions\CastToMediaException;
use AiluraCode\Wappify\Exceptions\CastToTextException;
use AiluraCode\Wappify\Exceptions\PropertyNoExists;
use AiluraCode\Wappify\Exceptions\UnknownMessageTypeException;

/**
 * Provides the deprecated transformation API over the typed message hierarchy.
 *
 * Every public `to*()`/`is*()` member is kept as a thin shim for one release
 * cycle and is scheduled for removal in the next major version. Prefer the
 * typed child models in `AiluraCode\Wappify\Models\Messages`.
 */
trait IsTransformable
{
    /**
     * Check if the message is an audio message.
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function isAudio(): bool
    {
        return $this->hasType(MessageType::AUDIO);
    }

    /**
     * Check if the message is a button reply message.
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function isButtonReply(): bool
    {
        return $this->isInteractive() && isset($this->message->button_reply);
    }

    /**
     * Check if the message is a contact message.
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function isContact(): bool
    {
        return $this->hasType(MessageType::CONTACT);
    }

    /**
     * Check if the message is a document message.
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function isDocument(): bool
    {
        return $this->hasType(MessageType::DOCUMENT);
    }

    /**
     * Check if the message is an image message.
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function isImage(): bool
    {
        return $this->hasType(MessageType::IMAGE);
    }

    /**
     * Check if the message is an interactive message.
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function isInteractive(): bool
    {
        return $this->hasType(MessageType::INTERACTIVE);
    }

    /**
     * Check if the message is a location message.
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function isLocation(): bool
    {
        return $this->hasType(MessageType::LOCATION);
    }

    /**
     * Check if the message is a media message.
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function isMedia(): bool
    {
        return $this->hasType(MessageType::IMAGE)
            || $this->hasType(MessageType::VIDEO)
            || $this->hasType(MessageType::AUDIO)
            || $this->hasType(MessageType::DOCUMENT)
            || $this->hasType(MessageType::STICKER);
    }

    /**
     * Check if the message is a sticker message.
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function isSticker(): bool
    {
        return $this->hasType(MessageType::STICKER);
    }

    /**
     * Check if the message is a text message.
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function isText(): bool
    {
        return $this->hasType(MessageType::TEXT);
    }

    /**
     * Check if the message is a video message.
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function isVideo(): bool
    {
        return $this->hasType(MessageType::VIDEO);
    }

    /**
     * Cast the message to an audio message.
     *
     * @throws CastToMediaException
     * @throws PropertyNoExists
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function toAudio(): AudioMessage
    {
        if (! $this->isAudio()) {
            throw new CastToMediaException();
        }

        return new AudioMessage($this->getMessage());
    }

    /**
     * Cast the message to a document message.
     *
     * @throws CastToMediaException
     * @throws PropertyNoExists
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function toDocument(): DocumentMessage
    {
        if (! $this->isDocument()) {
            throw new CastToMediaException();
        }

        return new DocumentMessage($this->getMessage());
    }

    /**
     * Cast the message to an image message.
     *
     * @throws CastToMediaException
     * @throws PropertyNoExists
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function toImage(): ImageMessage
    {
        if (! $this->isImage()) {
            throw new CastToMediaException();
        }

        return new ImageMessage($this->getMessage());
    }

    /**
     * Cast the message to an interactive message.
     *
     * @throws CastToInteractiveException
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function toInteractive(): InteractiveMessage
    {
        if (! $this->isInteractive()) {
            throw new CastToInteractiveException();
        }

        return new InteractiveMessage($this);
    }

    /**
     * Cast the message to a media message.
     *
     * @throws CastToMediaException|PropertyNoExists|UnknownMessageTypeException
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function toMedia(): ShouldMultimediaMessage
    {
        if (! $this->isMedia()) {
            throw new CastToMediaException();
        }

        return match ($this->getType()) {
            MessageType::IMAGE => $this->toImage(),
            MessageType::VIDEO => $this->toVideo(),
            MessageType::AUDIO => $this->toAudio(),
            MessageType::DOCUMENT => $this->toDocument(),
            MessageType::STICKER => $this->toSticker(),
            default => throw new CastToMediaException(),
        };
    }

    /**
     * Cast the message to a sticker message.
     *
     * @throws CastToMediaException
     * @throws PropertyNoExists
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function toSticker(): StickerMessage
    {
        if (! $this->isSticker()) {
            throw new CastToMediaException();
        }

        return new StickerMessage($this->getMessage());
    }

    /**
     * Cast the message to a text message.
     *
     * @throws CastToTextException
     * @throws PropertyNoExists
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function toText(): ShouldTextMessage
    {
        if (! $this->isText()) {
            throw new CastToTextException();
        }

        return new TextMessage($this->getMessage());
    }

    /**
     * Cast the message to a video message.
     *
     * @throws CastToMediaException
     * @throws PropertyNoExists
     *
     * @deprecated Use the typed message models instead. Removed in the next major version.
     */
    public function toVideo(): VideoMessage
    {
        if (! $this->isVideo()) {
            throw new CastToMediaException();
        }

        return new VideoMessage($this->getMessage());
    }

    /**
     * Compare the raw discriminator value with a mapped type.
     *
     * Tolerant by design: unknown or legacy values stay raw strings and simply
     * do not match, so predicates never throw.
     */
    private function hasType(MessageType $type): bool
    {
        $value = $this->type;

        if ($value instanceof MessageType) {
            return $value === $type;
        }

        return $value === $type->value;
    }
}
