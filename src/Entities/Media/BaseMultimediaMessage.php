<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Entities\Media;

use AiluraCode\Wappify\Concern\IsEditable;
use AiluraCode\Wappify\Concern\IsMultimediable;
use AiluraCode\Wappify\Contracts\Messages\ShouldMultimediaMessage;
use AiluraCode\Wappify\Entities\BaseMessage;
use AiluraCode\Wappify\Exceptions\PropertyNoExists;
use stdClass;

abstract class BaseMultimediaMessage extends BaseMessage implements ShouldMultimediaMessage
{
    use IsEditable;
    use IsMultimediable;

    /**
     * @throws PropertyNoExists
     */
    public function __construct(stdClass $media)
    {
        $this->id = $this->validateProperty($media, 'id');
        $this->sha256 = $this->validateProperty($media, 'sha256');
        $this->mimeType = $this->validateProperty($media, 'mime_type');
    }
}
