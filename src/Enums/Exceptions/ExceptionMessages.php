<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Enums\Exceptions;

enum ExceptionMessages: string
{
    case BASE_EXCEPTION = 'An error occurred.';
    case CAST_TO_INTERACTIVE_EXCEPTION = 'The message could not be cast to interactive.';
    case CAST_TO_MEDIA_EXCEPTION = 'The message could not be cast to media.';
    case CAST_TO_TEXT_EXCEPTION = 'The message could not be cast to text.';
    case PROPERTY_NO_EXISTS_EXCEPTION = 'The property "%property%" does not exist in the object "%object%".';
    case UNKNOWN_MESSAGE_TYPE_EXCEPTION = 'The message type "%type%" is not recognized.';
}
