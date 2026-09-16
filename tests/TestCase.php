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
        $config = $app['config'];

        $config->set('database.default', 'testing');
        $config->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            WappifyServiceProvider::class,
        ];
    }
}
