<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests\Feature;

use AiluraCode\Wappify\Jobs\SendDocumentMessageJob;
use AiluraCode\Wappify\Tests\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;
use SplFileInfo;

final class DocumentUrlTest extends TestCase
{
    public function test_no_ngrok_rewrite(): void
    {
        $this->assertSame([], $this->ngrokSnippetsInSrc());

        config()->set('filesystems.disks.public.url', 'http://cactu-pachanoi.test/storage');
        config()->set('media-library.url_generator', DefaultUrlGenerator::class);
        config()->set('media-library.path_generator', DefaultPathGenerator::class);

        $media = (new Media([
            'disk' => 'public',
            'conversions_disk' => 'public',
            'collection_name' => 'default',
            'name' => 'contract',
            'file_name' => 'contract.pdf',
            'mime_type' => 'application/pdf',
            'size' => 8,
        ]))->forceFill(['id' => 7]);

        $job = new SendDocumentMessageJob('593960800736', $media);

        $this->assertStringContainsString('.test', $job->resolveDocumentUrl());
        $this->assertStringNotContainsString('ngrok', $job->resolveDocumentUrl());
        $this->assertSame($media->getUrl(), $job->resolveDocumentUrl());
    }

    /**
     * @return array<int, string>
     */
    private function ngrokSnippetsInSrc(): array
    {
        $hits = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../../src'));

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || $file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            if (str_contains((string) (file_get_contents($file->getPathname())), 'ngrok')) {
                $hits[] = $file->getPathname();
            }
        }

        return $hits;
    }
}
