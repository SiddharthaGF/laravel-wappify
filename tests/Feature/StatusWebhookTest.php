<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests\Feature;

use AiluraCode\Wappify\Jobs\ReceiveMessageJob;
use AiluraCode\Wappify\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Throwable;

final class StatusWebhookTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @throws Throwable
     */
    public function test_status_webhook_advances_existing_row_without_inserting(): void
    {
        $this->insertMessage('wamid.status.sent', 'waiting');
        $before = DB::table('whatsapp')->count();

        (new ReceiveMessageJob($this->statusPayload('wamid.status.sent', 'sent')))->handle();

        $this->assertSame('sent', DB::table('whatsapp')->where('wamid', 'wamid.status.sent')->value('state'));
        $this->assertSame($before, DB::table('whatsapp')->count());
        $this->assertSame(0, DB::table('whatsapp')->where('type', 'status')->count());
    }

    /**
     * @throws Throwable
     */
    public function test_status_webhook_advances_through_delivered(): void
    {
        $this->insertMessage('wamid.status.delivered', 'sent');

        (new ReceiveMessageJob($this->statusPayload('wamid.status.delivered', 'delivered')))->handle();

        $this->assertSame('delivered', DB::table('whatsapp')->where('wamid', 'wamid.status.delivered')->value('state'));
    }

    /**
     * @throws Throwable
     */
    public function test_status_webhook_ignores_unknown_wamid_and_inserts_nothing(): void
    {
        $before = DB::table('whatsapp')->count();

        (new ReceiveMessageJob($this->statusPayload('wamid.status.unknown', 'sent')))->handle();

        $this->assertSame($before, DB::table('whatsapp')->count());
    }

    private function insertMessage(string $wamid, string $state): void
    {
        DB::table('whatsapp')->insert([
            'wamid' => $wamid,
            'profile' => 'default',
            'from' => '593960800736',
            'type' => 'text',
            'message' => json_encode(['body' => 'hello']),
            'timestamp' => 1714177199,
            'state' => $state,
        ]);
    }

    private function statusPayload(string $wamid, string $status): string
    {
        return (string) (json_encode([
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => '240297092497020',
                    'changes' => [
                        [
                            'value' => [
                                'messaging_product' => 'whatsapp',
                                'metadata' => [
                                    'display_phone_number' => '15551335615',
                                    'phone_number_id' => '255764720944895',
                                ],
                                'statuses' => [
                                    [
                                        'id' => $wamid,
                                        'status' => $status,
                                        'timestamp' => '1714177199',
                                        'recipient_id' => '593960800736',
                                    ],
                                ],
                            ],
                            'field' => 'messages',
                        ],
                    ],
                ],
            ],
        ]));
    }
}
