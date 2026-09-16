<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests\Feature;

use AiluraCode\Wappify\Http\Controllers\ChatController;
use AiluraCode\Wappify\Http\Controllers\MessagesController;
use AiluraCode\Wappify\Http\Controllers\WebhookController;
use AiluraCode\Wappify\Tests\TestCase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;

final class RouteRegistrationTest extends TestCase
{
    public function test_expected_routes_are_registered_with_exact_parity(): void
    {
        $routes = RouteFacade::getRoutes();

        foreach ($this->expectedRoutes() as $expected) {
            $name = $expected['name'];
            $route = $routes->getByName($name);

            $this->assertInstanceOf(Route::class, $route, "$name should be registered");

            $this->assertSame(
                $expected['methods'],
                $route->methods(),
                "$name should expose the expected HTTP verbs"
            );
            $this->assertSame(
                $expected['uri'],
                $route->uri(),
                "$name should resolve to the expected URI"
            );
            $this->assertSame(
                $expected['controller'],
                $route->getControllerClass(),
                "$name should point at the expected controller"
            );
            $this->assertSame(
                $expected['action'],
                $route->getActionMethod(),
                "$name should point at the expected controller method"
            );
        }
    }

    /**
     * Full expected route table in registration order.
     *
     * URIs include the outer `api` prefix applied by the service provider.
     *
     * @return array<int, array{
     *     name: string,
     *     methods: array<int, string>,
     *     uri: string,
     *     controller: class-string,
     *     action: string
     * }>
     */
    private function expectedRoutes(): array
    {
        return [
            [
                'name' => 'wappify.messages.index',
                'methods' => ['GET', 'HEAD'],
                'uri' => 'api/whatsapp/messages',
                'controller' => MessagesController::class,
                'action' => 'index',
            ],
            [
                'name' => 'wappify.messages.show',
                'methods' => ['GET', 'HEAD'],
                'uri' => 'api/whatsapp/messages/{id}',
                'controller' => MessagesController::class,
                'action' => 'show',
            ],
            [
                'name' => 'wappify.messages.destroy',
                'methods' => ['DELETE'],
                'uri' => 'api/whatsapp/messages/{id}',
                'controller' => MessagesController::class,
                'action' => 'destroy',
            ],
            [
                'name' => 'wappify.chat',
                'methods' => ['GET', 'HEAD'],
                'uri' => 'api/whatsapp/chat/{from}',
                'controller' => ChatController::class,
                'action' => 'chat',
            ],
            [
                'name' => 'wappify.chat.me',
                'methods' => ['GET', 'HEAD'],
                'uri' => 'api/whatsapp/chat/me',
                'controller' => ChatController::class,
                'action' => 'me',
            ],
            [
                'name' => 'wappify.chat.you',
                'methods' => ['GET', 'HEAD'],
                'uri' => 'api/whatsapp/chat/you',
                'controller' => ChatController::class,
                'action' => 'you',
            ],
            [
                'name' => 'wappify.webhook',
                'methods' => ['GET', 'HEAD'],
                'uri' => 'api/whatsapp/webhook/{account}',
                'controller' => WebhookController::class,
                'action' => 'webhook',
            ],
            [
                'name' => 'wappify.receive',
                'methods' => ['POST'],
                'uri' => 'api/whatsapp/webhook/{account}',
                'controller' => WebhookController::class,
                'action' => 'receive',
            ],
        ];
    }
}
