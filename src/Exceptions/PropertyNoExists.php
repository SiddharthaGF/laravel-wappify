<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Exceptions;

use AiluraCode\Wappify\Enums\Exceptions\ExceptionCodes;
use AiluraCode\Wappify\Enums\Exceptions\ExceptionMessages;
use Exception;

final class PropertyNoExists extends Exception
{
    public function __construct(
        private readonly object $object,
        private readonly string $property,
    ) {
        $message = ExceptionMessages::PROPERTY_NO_EXISTS_EXCEPTION->value;
        $code = ExceptionCodes::PROPERTY_NO_EXISTS_EXCEPTION->value;
        $message = str_replace('%property%', $this->property, $message);
        $message = str_replace('%object%', get_class($this->object), $message);
        parent::__construct($message, $code);
    }
}
