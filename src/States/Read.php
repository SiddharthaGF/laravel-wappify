<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\States;

use AiluraCode\Wappify\Enums\StateNames;

/**
 * Terminal state: no transition is configured out of it.
 */
final class Read extends MessageState
{
    public static string $name = StateNames::READ;
}
