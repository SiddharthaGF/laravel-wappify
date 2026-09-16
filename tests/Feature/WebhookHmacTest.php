<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests\Feature;

use AiluraCode\Wappify\Jobs\ReceiveMessageJob;
use AiluraCode\Wappify\Tests\TestCase;
use Illuminate\Support\Facades\Bus;

final class WebhookHmacTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('wappify.accounts.default.app_secret', 'test-app-secret');
        config()->set('wappify.accounts.default.verify_token', 'test-verify-token');
        config()->set('cache.default', 'array');
    }

    public function test_accepts_valid(): void
    {
        Bus::fake();

        $body = '{"object":"whatsapp_business_account"}';

        $response = $this->call(
            'POST',
            '/api/whatsapp/webhook/default',
            [],
            [],
            [],
            $this->serverWithSignature($this->signature($body)),
            $body
        );

        $response->assertOk();
        Bus::assertDispatched(ReceiveMessageJob::class);
    }

    public function test_preserves_get_challenge(): void
    {
        Bus::fake();

        $response = $this->get('/api/whatsapp/webhook/default?hub.mode=subscribe&hub.verify_token=test-verify-token&hub.challenge=hello123');

        $response->assertOk();
        $response->assertSee('hello123', false);
        Bus::assertNothingDispatched();
    }

    public function test_rejects_forged(): void
    {
        Bus::fake();

        $body = '{"object":"whatsapp_business_account"}';

        $response = $this->call(
            'POST',
            '/api/whatsapp/webhook/default',
            [],
            [],
            [],
            $this->serverWithSignature('sha256=deadbeefdeadbeefdeadbeefdeadbeefdeadbeefdeadbeefdeadbeefdeadbeef'),
            $body
        );

        $response->assertUnauthorized();
        Bus::assertNothingDispatched();
    }

    public function test_rejects_missing(): void
    {
        Bus::fake();

        $response = $this->call(
            'POST',
            '/api/whatsapp/webhook/default',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"object":"whatsapp_business_account"}'
        );

        $response->assertUnauthorized();
        Bus::assertNothingDispatched();
    }

    /**
     * @return array<string, string>
     */
    private function serverWithSignature(string $signature): array
    {
        return [
            'CONTENT_TYPE' => 'application/json',
            // A spoofed Facebook UA must NOT bypass verification: the UA allowlist is dead.
            'HTTP_USER_AGENT' => 'facebookplatform/1.0 (+http://developers.facebook.com)',
            'HTTP_X_HUB_SIGNATURE_256' => $signature,
        ];
    }

    private function signature(string $body): string
    {
        return 'sha256=' . hash_hmac('sha256', $body, 'test-app-secret');
    }
}
