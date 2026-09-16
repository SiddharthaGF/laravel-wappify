<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Contracts\Messages;

interface ShouldMultimediaMessage extends ShouldEditMessage
{
    public function getId(): string;

    public function getMimeType(): string;

    public function getSha256(): string;

    public function setId(string $id): void;

    public function setMimeType(string $mimeType): void;

    public function setSha256(string $sha256): void;
}
