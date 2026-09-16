<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Data;

use AiluraCode\Wappify\Enums\MessageType;
use stdClass;

final class IncomingMessageData
{
    public function __construct(
        public string $wamid,
        public string $profile,
        public string $from,
        public MessageType $type,
        public stdClass $message,
        public int $timestamp,
    ) {}
}
