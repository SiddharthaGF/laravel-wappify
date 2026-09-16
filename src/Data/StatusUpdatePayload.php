<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Data;

use AiluraCode\Wappify\Enums\MessageStatusType;

final class StatusUpdatePayload
{
    public function __construct(
        public string $wamid,
        public MessageStatusType $status,
    ) {}
}
