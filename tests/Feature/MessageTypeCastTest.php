<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests\Feature;

use AiluraCode\Wappify\Casts\CastsMessageType;
use AiluraCode\Wappify\Enums\MessageType;
use AiluraCode\Wappify\Models\Whatsapp;
use AiluraCode\Wappify\States\MessageState;
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

    /**
     * The model owned a `$casts` property before the Laravel 12 upgrade. This
     * guards the `casts()` method migration: the map must stay wired to the
     * model, not just to the cast class in isolation.
     */
    public function test_model_cast_map_covers_message_type_and_state(): void
    {
        $casts = (new Whatsapp())->getCasts();

        $this->assertSame(CastsMessageType::class, $casts['type']);
        $this->assertSame(MessageState::class, $casts['state']);
        $this->assertSame('object', $casts['message']);
    }

    public function test_model_hydrates_known_alias_to_enum(): void
    {
        $model = new Whatsapp();
        $model->setRawAttributes(['type' => MessageType::VIDEO->value]);

        $this->assertSame(MessageType::VIDEO, $model->type);
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
