<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Data;

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

final class WhatsappAccountConfig
{
    public function __construct(
        public string $profile,
        public string $number_id,
        public string $token,
        public WhatsappQueueConfig $queue,
        public string $app_secret = '',
        public string $verify_token = '',
    ) {}

    public static function fromConfig(string $account): self
    {
        $raw = Config::get('wappify.accounts.' . $account);

        if (! is_array($raw)) {
            throw new InvalidArgumentException("Unknown WhatsApp account \"$account\".");
        }

        $profile = $raw['profile'] ?? null;
        $numberId = $raw['number_id'] ?? null;
        $token = $raw['token'] ?? null;
        $queue = WhatsappQueueConfig::fromArray($raw['queue'] ?? null);
        $appSecret = $raw['app_secret'] ?? null;
        $verifyToken = $raw['verify_token'] ?? null;

        return new self(
            self::stringOrEmpty($profile),
            self::stringOrEmpty($numberId),
            self::stringOrEmpty($token),
            $queue,
            self::stringOrEmpty($appSecret),
            self::stringOrEmpty($verifyToken),
        );
    }

    private static function stringOrEmpty(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
