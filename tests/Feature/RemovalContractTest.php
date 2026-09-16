<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests\Feature;

use AiluraCode\Wappify\Concern\IsTransformable;
use AiluraCode\Wappify\Contracts\Messages\ShouldStatusChangeMessage;
use AiluraCode\Wappify\Entities\ChangeStatusMessage;
use AiluraCode\Wappify\Enums\Exceptions\ExceptionCodes;
use AiluraCode\Wappify\Enums\Exceptions\ExceptionMessages;
use AiluraCode\Wappify\Enums\MessageType;
use AiluraCode\Wappify\Exceptions\CastToInteractiveException;
use AiluraCode\Wappify\Tests\TestCase;
use ReflectionClass;
use ReflectionEnum;

final class RemovalContractTest extends TestCase
{
    public function test_cast_to_image_exception_entries_are_gone(): void
    {
        $this->assertFalse((new ReflectionEnum(ExceptionCodes::class))->hasCase('CAST_TO_IMAGE_EXCEPTION'));
        $this->assertFalse((new ReflectionEnum(ExceptionMessages::class))->hasCase('CAST_TO_IMAGE_EXCEPTION'));
    }

    public function test_download_allowed_config_key_is_gone(): void
    {
        $this->assertNull(config('wappify.accounts.default.download.allowed'));
        $this->assertSame(true, config('wappify.accounts.default.download.automatic'));
    }

    public function test_interactive_exception_uses_interactive_defaults(): void
    {
        $exception = new CastToInteractiveException();

        $this->assertSame(ExceptionMessages::CAST_TO_INTERACTIVE_EXCEPTION->value, $exception->getMessage());
        $this->assertSame(ExceptionCodes::CAST_TO_INTERACTIVE_EXCEPTION->value, $exception->getCode());
    }

    public function test_message_type_loses_status_case_and_downloadable_helper(): void
    {
        $enum = new ReflectionEnum(MessageType::class);

        $this->assertFalse($enum->hasCase('STATUS'));
        $this->assertFalse($enum->hasMethod('isDownloadable'));
    }

    public function test_status_entities_are_deleted(): void
    {
        $this->assertFalse(class_exists(ChangeStatusMessage::class));
        $this->assertFalse(interface_exists(ShouldStatusChangeMessage::class));
    }

    public function test_transformation_surface_drops_status_helpers(): void
    {
        $reflection = new ReflectionClass(IsTransformable::class);

        foreach (['toStatus', 'isStatus', 'getStatus', 'hasStatus'] as $method) {
            $this->assertFalse($reflection->hasMethod($method), $method);
        }
    }
}
