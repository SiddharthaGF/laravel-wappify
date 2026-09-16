<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\States;

use AiluraCode\Wappify\Models\Whatsapp;
use Spatie\ModelStates\Exceptions\InvalidConfig;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * Base lifecycle state for typed messages.
 *
 * Waiting -> Sent -> Delivered -> Read. Read is terminal because no transition
 * is configured out of it.
 *
 * @extends State<Whatsapp>
 */
abstract class MessageState extends State
{
    /**
     * @throws InvalidConfig
     */
    final public static function config(): StateConfig
    {
        return parent::config()
            ->default(Waiting::class)
            ->allowTransition(Waiting::class, Sent::class)
            ->allowTransition(Sent::class, Delivered::class)
            ->allowTransition(Delivered::class, Read::class);
    }
}
