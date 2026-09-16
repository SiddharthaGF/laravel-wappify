<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests;

use AiluraCode\Wappify\Providers\WappifyServiceProvider;

/**
 * @internal
 *
 * @coversNothing
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app->environment([
            'database.default' => 'testing',
            'database.connections.testing' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
            ],
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            WappifyServiceProvider::class,
        ];
    }
}
