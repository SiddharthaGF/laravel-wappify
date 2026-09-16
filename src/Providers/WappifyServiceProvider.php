<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Providers;

use AiluraCode\Wappify\Console\Commands\RunWhatsappQueue;
use AiluraCode\Wappify\Http\Middleware\AuthMiddleware;
use AiluraCode\Wappify\Http\Middleware\FacebookMiddleware;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class WappifyServiceProvider extends ServiceProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $config = $this->app->make(ConfigRepository::class);
        $rawPrefix = $config->get('wappify.api.prefix', 'api');
        $prefix = is_scalar($rawPrefix) ? (string) $rawPrefix : 'api';
        Route::middleware('api')
            ->prefix($prefix)
            ->group(function (): void {
                $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
            });
        $this->publishes([
            __DIR__ . '/../../config/wappify.php' => config_path('wappify.php'),
        ], 'config');

        $stub = __DIR__ . '/../../database/migrations/_create_wappify_table.php';
        $file = database_path('migrations/' . date('Y_m_d_His', time()) . '_create_wappify_table.php');
        if (glob(database_path('migrations/*_create_wappify_table.php')) === []) {
            $this->publishes([$stub => $file], 'migrations');
        }
        if ($this->app->runningInConsole()) {
            $this->commands([
                RunWhatsappQueue::class,
            ]);
        }
        $router = $this->app->make('router');
        if (! $router instanceof Router) {
            return;
        }
        $rawFacebookMiddleware = $config->get('wappify.middleware.facebook.name');
        $rawAuthMiddleware = $config->get('wappify.middleware.auth.name');
        $facebookMiddleware = is_scalar($rawFacebookMiddleware) ? (string) $rawFacebookMiddleware : '';
        $authMiddleware = is_scalar($rawAuthMiddleware) ? (string) $rawAuthMiddleware : '';
        $router->aliasMiddleware($facebookMiddleware, FacebookMiddleware::class);
        $router->aliasMiddleware($authMiddleware, AuthMiddleware::class);
        Gate::define('delete-whatsapp', static fn (mixed $user, mixed $message): bool => false);
    }

    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->mergeConfigFrom(__DIR__ . '/../../config/wappify.php', 'wappify');
    }
}
