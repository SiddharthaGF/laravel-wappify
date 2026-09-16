<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Entities\Media;

use AiluraCode\Wappify\Exceptions\PropertyNoExists;
use stdClass;

final class AudioMessage extends BaseMultimediaMessage
{
    private bool $voice;

    /**
     * @throws PropertyNoExists
     */
    public function __construct(stdClass $media)
    {
        parent::__construct($media);
        $this->voice = (bool) ($this->validateProperty($media, 'voice'));
    }

    public function isVoice(): bool
    {
        return $this->voice;
    }
}
