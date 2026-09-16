<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Enums;

/**
 * @method static MessageType TEXT()
 * @method static MessageType IMAGE()
 * @method static MessageType AUDIO()
 * @method static MessageType DOCUMENT()
 * @method static MessageType VIDEO()
 * @method static MessageType LOCATION()
 * @method static MessageType CONTACT()
 * @method static MessageType STICKER()
 * @method static MessageType INTERACTIVE()
 * @method static MessageType CONTACTS()
 */
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
