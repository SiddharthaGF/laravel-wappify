<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Concern;

/**
 * Provides functions to manipulate a WhatsApp text message.
 */
trait IsTexteable
{
    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }
}
