<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Entities\Media;

use AiluraCode\Wappify\Exceptions\PropertyNoExists;
use stdClass;

final class ImageMessage extends BaseMultimediaMessage
{
    /**
     * @throws PropertyNoExists
     */
    public function __construct(stdClass $media)
    {
        parent::__construct($media);
    }
}
