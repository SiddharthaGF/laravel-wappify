<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Concern;

use AiluraCode\Wappify\Entities\BaseMessage;

/**
 * Provides a real `toArray()` for message DTOs.
 *
 * `IsMessageable::setMessage()` writes a DTO's editable keys into the stored
 * payload, so this method must enumerate the DTO's actual typed properties
 * (including parent traits) instead of returning an identity-mapped array.
 *
 * @phpstan-require-extends BaseMessage
 */
trait IsEditable
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($property) => is_string($property), ARRAY_FILTER_USE_KEY);
    }
}
