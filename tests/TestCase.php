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

        $migration = include __DIR__ . '/../database/migrations/create_app_versions_table.php';
        $migration->up();
    }
}
