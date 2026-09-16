<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Netflie\WhatsAppCloudApi\Message\ButtonReply\Button;
use Traversable;

/**
 * @implements IteratorAggregate<int, Button>
 */
final class MessageButtons implements Countable, IteratorAggregate
{
    /**
     * @param array<int, Button> $items
     */
    public function __construct(public array $items) {}

    /**
     * @return array<int, Button>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int, Button>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }
}
