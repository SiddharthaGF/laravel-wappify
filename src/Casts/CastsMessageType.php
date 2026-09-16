<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Casts;

use AiluraCode\Wappify\Enums\MessageType;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Single owner of the `type` discriminator column.
 *
 * Returns the matching MessageType for known aliases, and the raw string for
 * unknown or legacy values so hydration and persistence never throw.
 *
 * @implements CastsAttributes<MessageType|string, MessageType|string>
 */
final class CastsMessageType implements CastsAttributes
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): MessageType|string
    {
        $raw = is_string($value) ? $value : '';

        return MessageType::tryFrom($raw) ?? $raw;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value instanceof MessageType) {
            return $value->value;
        }

        return is_string($value) ? $value : '';
    }
}
