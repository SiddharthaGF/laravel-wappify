<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests\Feature;

use AiluraCode\Wappify\Http\Middleware\AuthMiddleware;
use AiluraCode\Wappify\Models\Whatsapp;
use AiluraCode\Wappify\Tests\TestCase;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class DestroyMediaTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provisionTestStack();
        Gate::define('delete-whatsapp', static fn (): bool => true);
    }

    public function test_atomic_media_first(): void
    {
        $id = $this->insertMessageWithMedia('wamid.destroy.rollback');

        Media::deleting(static function (): never {
            throw new RuntimeException('media delete failed');
        });

        $response = $this->actingAs($this->user())->deleteJson('/api/whatsapp/messages/' . $id . '?withMedia=1');

        $response->assertServerError();
        $this->assertDatabaseHas('whatsapp', ['wamid' => 'wamid.destroy.rollback']);
    }

    public function test_media_deleted_before_row(): void
    {
        $id = $this->insertMessageWithMedia('wamid.destroy.order');

        /** @var array<int, string> $order */
        $order = [];
        Media::deleted(static function () use (&$order): void {
            $order[] = 'media';
        });
        Whatsapp::deleted(static function () use (&$order): void {
            $order[] = 'row';
        });

        $response = $this->actingAs($this->user())->deleteJson('/api/whatsapp/messages/' . $id . '?withMedia=1');

        $response->assertOk();
        $this->assertSame(['media', 'row'], $order);
        $this->assertDatabaseMissing('whatsapp', ['wamid' => 'wamid.destroy.order']);
        $this->assertSame(0, DB::table('media')->count());
    }

    private function insertMessageWithMedia(string $wamid): int
    {
        $id = (int) (DB::table('whatsapp')->insertGetId([
            'wamid' => $wamid,
            'profile' => 'default',
            'from' => '593960800736',
            'type' => 'text',
            'message' => json_encode(['body' => 'hello']),
            'timestamp' => 1714177199,
            'state' => 'waiting',
        ]));

        DB::table('media')->insert([
            'model_type' => Whatsapp::class,
            'model_id' => $id,
            'collection_name' => 'default',
            'name' => 'note',
            'file_name' => 'note.txt',
            'disk' => 'public',
            'size' => 10,
            'manipulations' => '[]',
            'custom_properties' => '[]',
            'generated_conversions' => '[]',
            'responsive_images' => '[]',
        ]);

        return $id;
    }

    /**
     * Reproduce the production boot order in Testbench.
     *
     * Testbench constructs the HTTP kernel lazily on the first request, so its
     * constructor sync overwrites the package `auth` alias with the framework
     * default. Forcing construction first and re-applying the package alias
     * restores the production order (package provider boot wins). The media
     * model config stands in for the host app's Spatie provider.
     */
    private function provisionTestStack(): void
    {
        app(Kernel::class);
        $router = app('router');

        if (! $router instanceof Router) {
            $this->fail('Router is unavailable.');
        }

        $router->aliasMiddleware('auth', AuthMiddleware::class);
        config()->set('media-library.media_model', Media::class);
    }

    private function user(): User
    {
        return (new User())->forceFill(['id' => 1]);
    }
}
