<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Enums;

enum MessageStatusType: string
{
    case DELIVERED = 'delivered';
    case READ = 'read';
    case SENT = 'sent';
    case WAITING = 'waiting';
}
