<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Entities\Media;

use AiluraCode\Wappify\Concern\IsEditable;
use AiluraCode\Wappify\Exceptions\PropertyNoExists;
use stdClass;

final class DocumentMessage extends BaseMultimediaMessage
{
    use IsEditable;

    private string $name;

    /**
     * @throws PropertyNoExists
     */
    public function __construct(stdClass $media)
    {
        parent::__construct($media);
        $this->name = $this->validateProperty($media, 'name');
    }

    public function getName(): string
    {
        return $this->name;
    }
}
