<?php

declare(strict_types=1);

namespace Inisiatif\LaravelBudget\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Inisiatif\LaravelBudget\LaravelBudget;
use Illuminate\Contracts\Config\Repository;
use Inisiatif\LaravelBudget\LaravelBudgetServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelBudgetServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        \tap($app->make('config'), static function (Repository $config): void {
            $config->set('database.default', 'testing');

            $config->set('database.connections.testing', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);

            $config->set('budget.connection', 'testing');
            $config->set('budget.migration', true);
        });
    }

    protected function defineRoutes($router): void
    {
        $router->group([], static function (): void {
            LaravelBudget::routes();
        });
    }

    /**
     * Remigrate after mid-test config changes that affect schema.
     * Ends any open RefreshDatabase transaction first (SQLite cannot VACUUM inside one).
     */
    protected function remigrate(): void
    {
        $database = $this->app->make('db')->connection('testing');

        while ($database->transactionLevel() > 0) {
            $database->rollBack();
        }

        $this->artisan('migrate:fresh', ['--database' => 'testing']);

        $this->beginDatabaseTransaction();
    }
}
