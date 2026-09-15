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

final class ChatPaginationTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        // See ResourceAuthTest: reproduce the production boot order in Testbench.
        app(Kernel::class);
        $router = app('router');

        if (! $router instanceof Router) {
            $this->fail('Router is unavailable.');
        }

        $router->aliasMiddleware('auth', AuthMiddleware::class);
    }

    public function test_pages_bounded_with_meta(): void
    {
        for ($i = 0; $i < 60; $i++) {
            DB::table('whatsapp')->insert([
                'wamid' => 'wamid.page.' . $i,
                'profile' => 'default',
                'from' => '593960800736',
                'type' => 'text',
                'message' => json_encode(['body' => 'hello ' . $i]),
                'timestamp' => 1714177199 + $i,
                'state' => 'waiting',
            ]);
        }

        $user = (new User())->forceFill(['id' => 1]);

        $response = $this->actingAs($user)->getJson('/api/whatsapp/chat/593960800736');

        $response->assertOk();
        $response->assertJsonCount(25, 'data');
        $response->assertJsonPath('total', 60);
        $response->assertJsonPath('per_page', 25);
        $response->assertJsonPath('current_page', 1);
    }
}
