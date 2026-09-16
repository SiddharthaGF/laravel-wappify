<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Enums;

/**
 * @method static MessageStatusType READ()
 * @method static MessageStatusType SENT()
 * @method static MessageStatusType DELIVERED()
 */
enum MessageStatusType: string
{
    case DELIVERED = StateNames::DELIVERED;
    case READ = StateNames::READ;
    case SENT = StateNames::SENT;
    case WAITING = StateNames::WAITING;
}
