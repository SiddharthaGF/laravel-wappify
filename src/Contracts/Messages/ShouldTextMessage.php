<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Contracts\Messages;

interface ShouldTextMessage extends ShouldEditMessage
{
    public function getBody(): string;

    public function setBody(string $body): void;
}
