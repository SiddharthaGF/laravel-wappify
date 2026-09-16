<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Entities\Media;

use AiluraCode\Wappify\Concern\IsEditable;
use AiluraCode\Wappify\Exceptions\PropertyNoExists;
use stdClass;

final class StickerMessage extends BaseMultimediaMessage
{
    use IsEditable;

    private bool $animated;

    /**
     * @throws PropertyNoExists
     */
    public function __construct(stdClass $media)
    {
        parent::__construct($media);
        $this->animated = (bool) ($this->validateProperty($media, 'animated'));
    }

    public function isAnimated(): bool
    {
        return $this->animated;
    }
}
