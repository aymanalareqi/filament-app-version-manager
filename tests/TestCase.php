<?php

namespace Alareqi\FilamentAppVersionManager\Tests;

use Alareqi\FilamentAppVersionManager\FilamentAppVersionManagerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            FilamentAppVersionManagerServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        // Set up auth configuration for testing
        config()->set('auth.guards.admin', [
            'driver' => 'session',
            'provider' => 'users',
        ]);

        config()->set('auth.providers.users', [
            'driver' => 'eloquent',
            'model' => \Illuminate\Foundation\Auth\User::class,
        ]);

        $migration = include __DIR__ . '/../database/migrations/create_app_versions_table.php';
        $migration->up();
    }
}
