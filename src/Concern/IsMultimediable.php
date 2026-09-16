<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Concern;

/**
 * Provides functions to manipulate a media resource.
 */
trait IsMultimediable
{
    private string $id;
    private string $mimeType;
    private string $sha256;

    public function getId(): string
    {
        return $this->id;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getSha256(): string
    {
        return $this->sha256;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function setSha256(string $sha256): void
    {
        $this->sha256 = $sha256;
    }
}
