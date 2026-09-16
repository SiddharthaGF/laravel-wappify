<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Enums;

enum MessageType: string
{
    case AUDIO = 'audio';
    case CONTACT = 'contact';
    case CONTACTS = 'contacts';
    case DOCUMENT = 'document';
    case IMAGE = 'image';
    case INTERACTIVE = 'interactive';
    case LOCATION = 'location';
    case STICKER = 'sticker';
    case TEMPLATE = 'template';
    case TEXT = 'text';
    case VIDEO = 'video';
}
