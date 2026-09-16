<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Concern;

use AiluraCode\Wappify\Exceptions\PropertyNoExists;
use stdClass;
use Stringable;

/**
 * Validates that a property exists on an object and returns its value.
 */
trait IsValidable
{
    /**
     * @throws PropertyNoExists If the property does not exist or is not string-coercible
     */
    public function validateProperty(stdClass $object, string $property): string
    {
        if (! property_exists($object, $property)) {
            throw new PropertyNoExists($object, $property);
        }

        $value = $object->$property;

        if (! is_scalar($value) && ! $value instanceof Stringable) {
            throw new PropertyNoExists($object, $property);
        }

        return (string) $value;
    }
}
