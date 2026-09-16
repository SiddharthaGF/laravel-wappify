<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Entities;

use AiluraCode\Wappify\Concern\IsEditable;
use AiluraCode\Wappify\Concern\IsTexteable;
use AiluraCode\Wappify\Contracts\Messages\ShouldTextMessage;
use AiluraCode\Wappify\Exceptions\PropertyNoExists;
use stdClass;

final class TextMessage extends BaseMessage implements ShouldTextMessage
{
    use IsEditable;
    use IsTexteable;

    public string $body;

    /**
     * @throws PropertyNoExists
     */
    public function __construct(stdClass $message)
    {
        $this->body = $this->validateProperty($message, 'body');
    }
}
