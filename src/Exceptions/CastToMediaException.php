<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Exceptions;

use AiluraCode\Wappify\Enums\Exceptions\ExceptionCodes;
use AiluraCode\Wappify\Enums\Exceptions\ExceptionMessages;
use Exception;

final class CastToMediaException extends Exception
{
    public function __construct(
        ExceptionMessages $message = ExceptionMessages::CAST_TO_MEDIA_EXCEPTION,
        ExceptionCodes $code = ExceptionCodes::CAST_TO_MEDIA_EXCEPTION
    ) {
        parent::__construct($message->value, $code->value);
    }
}
