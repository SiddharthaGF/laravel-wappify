<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\States;

use AiluraCode\Wappify\Enums\StateNames;

final class Sent extends MessageState
{
    public static string $name = StateNames::SENT;
}
