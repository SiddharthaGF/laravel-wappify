<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Models\Messages;

use AiluraCode\Wappify\Models\Whatsapp;
use Parental\HasParent;

/**
 * Base class for the typed message children selected by the `type` discriminator.
 *
 * Every concrete child inherits the parent table and the Parental `HasParent`
 * concern, which scopes child queries to the child's alias.
 */
abstract class Message extends Whatsapp
{
    use HasParent;

    /**
     * Share the root model's morph class across every typed child.
     *
     * Parental resolves a child's morph class by instantiating its direct
     * parent, which is this abstract class and cannot be instantiated. All
     * children share the `whatsapp` table, so media and other morph relations
     * must use the root model's morph class.
     */
    final public function getMorphClass(): string
    {
        return (new Whatsapp())->getMorphClass();
    }
}
