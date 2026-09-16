<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests\Feature;

use AiluraCode\Wappify\Casts\CastsMessageType;
use AiluraCode\Wappify\Enums\MessageType;
use AiluraCode\Wappify\Models\Whatsapp;
use AiluraCode\Wappify\Tests\TestCase;

final class MessageTypeCastTest extends TestCase
{
    public function test_get_returns_enum_for_mapped_value(): void
    {
        $cast = new CastsMessageType();

        $this->assertSame(
            MessageType::VIDEO,
            $cast->get(new Whatsapp(), 'type', 'video', [])
        );
    }

    public function test_get_returns_raw_string_for_unmapped_value(): void
    {
        $cast = new CastsMessageType();

        $this->assertSame(
            'reaction',
            $cast->get(new Whatsapp(), 'type', 'reaction', [])
        );
    }

    public function test_set_stores_enum_value_as_string(): void
    {
        $cast = new CastsMessageType();

        $this->assertSame(
            'image',
            $cast->set(new Whatsapp(), 'type', MessageType::IMAGE, [])
        );
    }
}
