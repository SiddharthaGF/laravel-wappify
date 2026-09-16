<?php

declare(strict_types=1);

use AiluraCode\Wappify\Http\Controllers\ChatController;
use AiluraCode\Wappify\Http\Controllers\MessagesController;
use AiluraCode\Wappify\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

$apiPath = config('wappify.api.path', 'wappify');
assert(is_string($apiPath));
$apiName = config('wappify.api.name', 'wappify');
assert(is_string($apiName));
$resourcesMiddleware = config('wappify.api.middleware_resources', []);
assert(is_array($resourcesMiddleware));
$webhooksMiddleware = config('wappify.api.middleware_webhooks', []);
assert(is_array($webhooksMiddleware));

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
