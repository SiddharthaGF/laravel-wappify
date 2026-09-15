<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests\Feature;

use AiluraCode\Wappify\Jobs\DownloadMediaJob;
use AiluraCode\Wappify\Models\Whatsapp;
use AiluraCode\Wappify\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class DownloadMediaCtorTest extends TestCase
{
    use DatabaseMigrations;

    public function test_bad_mime_rejected(): void
    {
        $id = $this->insertMessage('wamid.ctor.sh', 'document', [
            'id' => 'media-evil',
            'sha256' => 'abc123',
            'mime_type' => 'application/x-sh',
            'name' => 'evil.sh',
        ]);

        try {
            (new DownloadMediaJob($id))->handle();
            $this->fail('Expected the disallowed MIME type to fail the job.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('application/x-sh', $exception->getMessage());
        }

        $message = Whatsapp::query()->find($id);
        $this->assertInstanceOf(Whatsapp::class, $message);
        $this->assertSame(0, DB::table('media')->count());
    }

    public function test_ctor_never_throws(): void
    {
        $id = $this->insertMessage('wamid.ctor.text', 'text', ['body' => 'hello']);

        $job = new DownloadMediaJob($id);

        $this->assertInstanceOf(DownloadMediaJob::class, $job);
        $this->assertSame(3, $job->tries);
    }

    private function insertMessage(string $wamid, string $type, mixed $message): int
    {
        return (int) (DB::table('whatsapp')->insertGetId([
            'wamid' => $wamid,
            'profile' => 'default',
            'from' => '593960800736',
            'type' => $type,
            'message' => json_encode($message),
            'timestamp' => 1714177199,
            'state' => 'waiting',
        ]));
    }
}
