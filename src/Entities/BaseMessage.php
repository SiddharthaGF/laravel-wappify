<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Entities;

use AiluraCode\Wappify\Concern\IsValidable;

/**
 * Base class for the typed message payload DTOs.
 *
 * Each DTO carries a single inbound payload and exposes the relevant accessors
 * (text body, media identifiers, interactive subtype). Row-level concerns like
 * `wamid`, `profile`, and `from` belong exclusively to the Eloquent `WhatsApp`
 * model, so the DTO hierarchy does NOT implement `ShouldMessage`.
 */
abstract class BaseMessage
{
    use IsValidable;
}
