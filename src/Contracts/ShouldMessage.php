<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Contracts;

use AiluraCode\Wappify\Contracts\Messages\ShouldEditMessage;
use AiluraCode\Wappify\Enums\MessageType;
use Exception;
use stdClass;

/**
 * Implemented by the Eloquent `WhatsApp` model.
 *
 * The DTO hierarchy under `AiluraCode\Wappify\Entities` does NOT implement this
 * interface: row-level attributes (wamid, profile, from, timestamp) live on the
 * model only, and the DTOs would have to lie about their type to satisfy it.
 */
interface ShouldMessage
{
    public function getFrom(): string;

    public function getMessage(): stdClass;

    public function getProfile(): string;

    public function getTimestamp(): int;

    public function getType(): MessageType;

    /**
     * @see https://developers.facebook.com/docs/whatsapp/cloud-api/reference/messages/#mensages
     */
    public function getWamId(): string;

    public function setFrom(string $from): void;

    public function setMessage(ShouldEditMessage $message): void;

    public function setProfile(string $profile): void;

    public function setTimestamp(int $timestamp): void;

    public function setType(MessageType $type): void;

    public function setWamId(string $wamid): void;

    /**
     * Validate if exists a property in an object.
     *
     * @throws Exception If the property does not exist
     */
    public function validateProperty(stdClass $object, string $property): string;
}
