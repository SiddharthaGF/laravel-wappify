<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests\Feature;

use AiluraCode\Wappify\Actions\SendTextMessage;
use AiluraCode\Wappify\Tests\TestCase;
use AiluraCode\Wappify\WhatsAppCloudApi;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Netflie\WhatsAppCloudApi\Message\TextMessage;
use Netflie\WhatsAppCloudApi\Request\MessageRequest\RequestTextMessage;
use Netflie\WhatsAppCloudApi\Response;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi as BaseTransport;
use ReflectionClass;
use ReflectionMethod;

final class SinglePersistTest extends TestCase
{
    use DatabaseMigrations;

    public function test_one_text_send_creates_one_row(): void
    {
        $transport = new FakeWhatsAppTransport();

        (new SendTextMessage('593960800736', 'hello'))($transport);

        $this->assertSame(1, $transport->sent);
        $this->assertSame(1, DB::table('whatsapp')->count());
        $this->assertDatabaseHas('whatsapp', [
            'wamid' => 'wamid.fake.text.1',
            'from' => '593960800736',
        ]);
    }

    public function test_sdk_send_writes_no_row(): void
    {
        $reflection = new ReflectionClass(WhatsAppCloudApi::class);

        $declared = [];
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (str_starts_with($method->getName(), 'send')
                && $method->getDeclaringClass()->getName() === WhatsAppCloudApi::class) {
                $declared[] = $method->getName();
            }
        }

        $this->assertSame([], $declared, 'SDK subclass must be pure transport: no send* overrides may self-persist.');
        $this->assertSame(0, DB::table('whatsapp')->count());
    }
}

final class FakeWhatsAppTransport extends BaseTransport
{
    public int $sent = 0;

    public function __construct() {}

    public function sendTextMessage(string $to, string $text, bool $preview_url = false): Response
    {
        $this->sent++;

        $request = new RequestTextMessage(new TextMessage($to, $text, $preview_url), 'fake-token', 'fake-number-id');

        return new Response(
            $request,
            (string) json_encode([
                'messages' => [['id' => 'wamid.fake.text.1']],
                'contacts' => [['wa_id' => $to]],
            ]),
            200
        );
    }
}
