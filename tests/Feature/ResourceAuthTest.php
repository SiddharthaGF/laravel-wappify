<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests\Feature;

use AiluraCode\Wappify\Http\Middleware\AuthMiddleware;
use AiluraCode\Wappify\Tests\TestCase;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class ResourceAuthTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provisionTestStack();
    }

    public function test_anon_401(): void
    {
        $response = $this->getJson('/api/whatsapp/messages');

        $response->assertUnauthorized();
        $response->assertJson(['message' => 'Request rejected because the user is not authorized']);
    }

    public function test_owner_200(): void
    {
        Gate::define('delete-whatsapp', static fn (): bool => true);

        $id = $this->insertMessage('wamid.auth.owner');

        $response = $this->actingAs($this->user())->deleteJson('/api/whatsapp/messages/' . $id);

        $response->assertOk();
        $this->assertDatabaseMissing('whatsapp', ['wamid' => 'wamid.auth.owner']);
    }

    public function test_stranger_403(): void
    {
        $id = $this->insertMessage('wamid.auth.stranger');

        $response = $this->actingAs($this->user())->deleteJson('/api/whatsapp/messages/' . $id);

        $response->assertForbidden();
        $this->assertDatabaseHas('whatsapp', ['wamid' => 'wamid.auth.stranger']);
    }

    private function insertMessage(string $wamid): int
    {
        return (int) (DB::table('whatsapp')->insertGetId([
            'wamid' => $wamid,
            'profile' => 'default',
            'from' => '593960800736',
            'type' => 'text',
            'message' => json_encode(['body' => 'hello']),
            'timestamp' => 1714177199,
            'state' => 'waiting',
        ]));
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
