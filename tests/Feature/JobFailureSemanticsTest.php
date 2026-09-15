<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests\Feature;

use AiluraCode\Wappify\Data\MessageButtons;
use AiluraCode\Wappify\Jobs\ReceiveMessageJob;
use AiluraCode\Wappify\Jobs\SendButtonReplyMessageJob;
use AiluraCode\Wappify\Tests\TestCase;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

final class JobFailureSemanticsTest extends TestCase
{
    public function test_button_reply_job_bubbles_transient_failure(): void
    {
        $spy = Log::spy();

        try {
            (new SendButtonReplyMessageJob('593960800736', 'hello', new MessageButtons([])))->handle();
            $this->fail('Expected the transient send failure to bubble up.');
        } catch (Throwable) {
            // Expected: logged, then rethrown for tries/backoff.
        }

        $spy->shouldHaveReceived('error')->once();
    }

    public function test_no_dd_and_bubbles(): void
    {
        $this->assertSame([], $this->forbiddenSnippetsInSrc());

        $spy = Log::spy();

        try {
            (new ReceiveMessageJob('not-json'))->handle();
            $this->fail('Expected the transient payload failure to bubble up.');
        } catch (InvalidArgumentException) {
            // Expected: logged, then rethrown for tries/backoff.
        }

        $spy->shouldHaveReceived('error')->once();
    }

    /**
     * @return array<int, string>
     */
    private function forbiddenSnippetsInSrc(): array
    {
        $hits = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../../src'));

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || $file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = (string) (file_get_contents($file->getPathname()));

            foreach (['dd(', 'echo ', '$_GET'] as $snippet) {
                if (str_contains($contents, $snippet)) {
                    $hits[] = $file->getPathname() . ':' . $snippet;
                }
            }
        }

        return $hits;
    }
}
