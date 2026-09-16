<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Exceptions;

use AiluraCode\Wappify\Enums\Exceptions\ExceptionCodes;
use AiluraCode\Wappify\Enums\Exceptions\ExceptionMessages;
use Exception;

/**
 * Thrown when a typed accessor is used on a row whose discriminator is unknown.
 *
 * Hydration and persistence never throw this: the tolerant `type` cast keeps the
 * raw string. Only consumers asking for a `MessageType` explicitly on an
 * unmapped row get this exception.
 */
final class UnknownMessageTypeException extends Exception
{
    public function __construct(mixed $type)
    {
        $value = is_string($type) ? $type : gettype($type);

        $message = str_replace(
            '%type%',
            $value,
            ExceptionMessages::UNKNOWN_MESSAGE_TYPE_EXCEPTION->value
        );

        parent::__construct($message, ExceptionCodes::UNKNOWN_MESSAGE_TYPE_EXCEPTION->value);
    }
}
