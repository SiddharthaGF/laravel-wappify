<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Concern;

use AiluraCode\Wappify\Contracts\Messages\ShouldEditMessage;
use AiluraCode\Wappify\Enums\MessageType;
use AiluraCode\Wappify\Exceptions\UnknownMessageTypeException;
use stdClass;

/**
 * Provides typed accessors for the row-level message attributes on the Eloquent
 * `WhatsApp` model.
 *
 * Each accessor reads through the typed properties surfaced by the model's
 * `@property` and `casts` configuration. PHPStan resolves the return types from
 * those declarations, and the PHP runtime enforces the declared return types at
 * call time.
 */
trait IsMessageable
{
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * Returns the typed message payload. The `object` cast turns the JSON column
     * into a `stdClass` so consumers can read nested properties off it.
     */
    public function getMessage(): stdClass
    {
        return $this->message;
    }

    public function getProfile(): string
    {
        return $this->profile;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * Returns the enum for mapped discriminators. Unknown or legacy values are
     * hydrated as raw strings, and asking for the enum on them throws.
     *
     * @throws UnknownMessageTypeException
     */
    public function getType(): MessageType
    {
        $type = $this->type;

        if ($type instanceof MessageType) {
            return $type;
        }

        throw new UnknownMessageTypeException($type);
    }

    public function getWamId(): string
    {
        return $this->wamid;
    }

    public function setFrom(string $from): void
    {
        $this->from = $from;
    }

    /**
     * Copies each field from a DTO payload into the stored `stdClass`.
     */
    public function setMessage(ShouldEditMessage $message): void
    {
        $target = $this->getMessage();

        foreach ($message->toArray() as $key => $value) {
            $target->{$key} = $value;
        }
    }

    public function setProfile(string $profile): void
    {
        $this->profile = $profile;
    }

    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function setType(MessageType $type): void
    {
        $this->type = $type;
    }

    public function setWamId(string $wamid): void
    {
        $this->wamid = $wamid;
    }
}
