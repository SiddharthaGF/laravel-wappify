<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Data;

final class WhatsappQueueConfig
{
    /**
     * @param array<int, int> $backoff
     */
    public function __construct(
        public string $connection,
        public string $name,
        public int $tries,
        public int $timeout,
        public array $backoff = [1, 5, 15],
    ) {}

    public static function fromArray(mixed $raw): self
    {
        return new self(
            self::stringOrEmpty(self::readKey($raw, 'connection')),
            self::stringOrEmpty(self::readKey($raw, 'name')),
            self::intOrZero(self::readKey($raw, 'tries')),
            self::intOrZero(self::readKey($raw, 'timeout')),
            self::intList(self::readKey($raw, 'backoff')),
        );
    }

    /**
     * @return array<int, int>
     */
    private static function intList(mixed $value): array
    {
        if (! is_array($value)) {
            return [1, 5, 15];
        }

        $out = [];

        foreach ($value as $entry) {
            if (is_numeric($entry)) {
                $out[] = (int) $entry;
            }
        }

        return $out === [] ? [1, 5, 15] : $out;
    }

    private static function intOrZero(mixed $value): int
    {
        return is_scalar($value) ? (int) $value : 0;
    }

    private static function readKey(mixed $container, string $key): mixed
    {
        if (! is_array($container)) {
            return null;
        }

        return $container[$key] ?? null;
    }

    private static function stringOrEmpty(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
