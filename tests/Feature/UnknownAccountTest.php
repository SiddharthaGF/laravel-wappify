<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests\Feature;

use AiluraCode\Wappify\Tests\TestCase;
use Illuminate\Support\Facades\Bus;

final class UnknownAccountTest extends TestCase
{
    public function test_unknown_account_never_dispatches(): void
    {
        Bus::fake();

        config()->set('wappify.accounts.default.app_secret', 'test-app-secret');
        config()->set('wappify.accounts.default.verify_token', 'test-verify-token');

        $get = $this->get('/api/whatsapp/webhook/ghost?hub.mode=subscribe&hub.verify_token=test-verify-token&hub.challenge=hello123');
        $get->assertNotFound();

        $post = $this->call(
            'POST',
            '/api/whatsapp/webhook/ghost',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X_HUB_SIGNATURE_256' => 'sha256=deadbeef'],
            '{"object":"whatsapp_business_account"}'
        );
        $post->assertNotFound();

        Bus::assertNothingDispatched();
    }
}
