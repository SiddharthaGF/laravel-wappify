<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Entities\Interactive;

use AiluraCode\Wappify\Entities\BaseMessage;
use AiluraCode\Wappify\Exceptions\PropertyNoExists;
use AiluraCode\Wappify\Models\Whatsapp;
use RuntimeException;
use stdClass;

final class InteractiveMessage extends BaseMessage
{
    public string $interactiveType;

    private ?stdClass $buttonReply;

    public function __construct(Whatsapp $whatsapp)
    {
        $payload = $whatsapp->getMessage();
        $this->interactiveType = is_string($payload->type ?? null) ? $payload->type : '';
        $button = $payload->button_reply ?? null;
        $this->buttonReply = $button instanceof stdClass ? $button : null;
    }

    /**
     * @throws PropertyNoExists
     */
    public function getButtonReplyId(): string
    {
        return $this->validateProperty($this->requireButtonReply(), 'id');
    }

    /**
     * @throws PropertyNoExists
     */
    public function getButtonReplyTitle(): string
    {
        return $this->validateProperty($this->requireButtonReply(), 'title');
    }

    public function getInteractiveType(): string
    {
        return $this->interactiveType;
    }

    public function isButtonReply(): bool
    {
        return $this->interactiveType === 'button_reply';
    }

    private function requireButtonReply(): stdClass
    {
        if ($this->buttonReply === null) {
            throw new RuntimeException('Interactive message has no button_reply payload.');
        }

        return $this->buttonReply;
    }
}
