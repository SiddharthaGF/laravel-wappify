<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests\Feature;

use AiluraCode\Wappify\Jobs\ReceiveMessageJob;
use AiluraCode\Wappify\Models\Whatsapp;
use AiluraCode\Wappify\Support\PayloadMapper;
use AiluraCode\Wappify\Tests\TestCase;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

final class IdempotentIngestTest extends TestCase
{
    use DatabaseMigrations;

    public function test_race_no_exception(): void
    {
        $wamid = 'wamid.race.001';
        $this->insertMessage($wamid);

        $duplicate = null;

        try {
            Whatsapp::query()->create([
                'wamid' => $wamid,
                'profile' => 'default',
                'from' => '593960800736',
                'type' => 'text',
                'message' => ['body' => 'race'],
                'timestamp' => 1714177199,
            ]);
        } catch (QueryException $exception) {
            $duplicate = $exception;
        }

        $this->assertInstanceOf(QueryException::class, $duplicate);

        $resolved = (new ReceiveMessageJob('{"object":"whatsapp_business_account"}'))->resolveDuplicateWrite($duplicate, $wamid);

        $this->assertSame($wamid, $resolved->wamid);
        $this->assertSame(1, DB::table('whatsapp')->where('wamid', $wamid)->count());
    }

    public function test_redelivery_single_row(): void
    {
        $payload = (string) (file_get_contents(__DIR__ . '/../../stubs/messageText.stub.json'));
        $data = PayloadMapper::fromJson($payload);
        $job = new ReceiveMessageJob($payload);

        $first = $job->storeMessage($data);
        $second = $job->storeMessage($data);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, DB::table('whatsapp')->where('wamid', $data->wamid)->count());
    }

    private function insertMessage(string $wamid): void
    {
        DB::table('whatsapp')->insert([
            'wamid' => $wamid,
            'profile' => 'default',
            'from' => '593960800736',
            'type' => 'text',
            'message' => json_encode(['body' => 'hello']),
            'timestamp' => 1714177199,
            'state' => 'waiting',
        ]);
    }
}
