<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Contracts\Messages;

interface ShouldEditMessage
{
    /**
     * Convert the content message to an array.
     *
     * @return array<string, mixed> The message as an array
     */
    public function toArray(): array;
}
