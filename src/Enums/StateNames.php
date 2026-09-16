<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Enums;

/**
 * Single source of the lifecycle state literals.
 *
 * Each literal is declared once here so the state classes can assign
 * `public static $name = StateNames::WAITING;` without duplicating strings and
 * without using PHP 8.2-only enum-case fetches inside constant expressions.
 */
final class StateNames
{
    public const DELIVERED = 'delivered';
    public const READ = 'read';
    public const SENT = 'sent';
    public const WAITING = 'waiting';
}
