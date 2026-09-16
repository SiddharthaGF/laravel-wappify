<?php

declare(strict_types=1);

use AiluraCode\Wappify\Http\Controllers\ChatController;
use AiluraCode\Wappify\Http\Controllers\MessagesController;
use AiluraCode\Wappify\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

$apiPath = Config::string('wappify.api.path', 'wappify');
$apiName = Config::string('wappify.api.name', 'wappify');
/** @var array<int, string> $resourcesMiddleware */
$resourcesMiddleware = Config::array('wappify.api.middleware_resources');
/** @var array<int, string> $webhooksMiddleware */
$webhooksMiddleware = Config::array('wappify.api.middleware_webhooks');

Route::name($apiName . '.')
    ->prefix($apiPath)
    ->middleware($resourcesMiddleware)
    ->group(
        function (): void {
            Route::prefix('messages')->group(
                function (): void {
                    Route::get('/', [MessagesController::class, 'index'])->name('messages.index');
                    Route::get('/{id}', [MessagesController::class, 'show'])->name('messages.show');
                    Route::delete('/{id}', [MessagesController::class, 'destroy'])->name('messages.destroy');
                }
            );

            Route::prefix('chat')->group(
                function (): void {
                    Route::get('/{from}', [ChatController::class, 'chat'])->name('chat');
                    Route::get('/me', [ChatController::class, 'me'])->name('chat.me');
                    Route::get('/you', [ChatController::class, 'you'])->name('chat.you');
                }
            );
        }
    );

Route::name($apiName . '.')
    ->prefix($apiPath)
    ->middleware($webhooksMiddleware)
    ->group(
        function (): void {
            Route::prefix('webhook')->group(
                function (): void {
                    Route::get('/{account}', [WebhookController::class, 'webhook'])->name('webhook');
                    Route::post('/{account}', [WebhookController::class, 'receive'])->name('receive');
                }
            );
        }
    );
