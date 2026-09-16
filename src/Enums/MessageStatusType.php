<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Enums;

enum MessageStatusType: string
{
    case DELIVERED = StateNames::DELIVERED;
    case READ = StateNames::READ;
    case SENT = StateNames::SENT;
    case WAITING = StateNames::WAITING;
}
