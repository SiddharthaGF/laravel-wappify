<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Enums\Exceptions;

/**
 * @phpstan-type const positive-int
 */
enum ExceptionCodes: int
{
    case BASE_EXCEPTION = 500;
    case CAST_TO_INTERACTIVE_EXCEPTION = 502;
    case CAST_TO_MEDIA_EXCEPTION = 504;
    case CAST_TO_TEXT_EXCEPTION = 501;
    case PROPERTY_NO_EXISTS_EXCEPTION = 400;
    case UNKNOWN_MESSAGE_TYPE_EXCEPTION = 505;
}
