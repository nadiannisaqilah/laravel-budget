<?php

declare(strict_types=1);

namespace Inisiatif\LaravelBudget\Tests\Http\Controllers;

use Illuminate\Support\Str;
use Inisiatif\LaravelBudget\BudgetConfig;
use Inisiatif\LaravelBudget\LaravelBudget;
use Inisiatif\LaravelBudget\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inisiatif\LaravelBudget\Database\Factories\BudgetFactory;

final class FilterCurrentBudgetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_filter_current_budget(): void
    {
        BudgetFactory::new([
            LaravelBudget::getVersionColumnName() => now()->year,
        ])->count(2)->create();
        BudgetFactory::new([
            LaravelBudget::getVersionColumnName() => now()->subYear()->year,
        ])->count(2)->create();

        $response = $this->getJson('/budget/current')->assertSuccessful();

        $this->assertCount(2, $response->json('data'));
    }

    public function test_can_filter_current_budget_using_code(): void
    {
        BudgetFactory::new([
            LaravelBudget::getVersionColumnName() => now()->year,
            LaravelBudget::getCodeColumnName() => 'CODE'.Str::random(),
        ])->count(2)->create();
        BudgetFactory::new([
            LaravelBudget::getVersionColumnName() => now()->subYear()->year,
            LaravelBudget::getCodeColumnName() => 'CODE'.Str::random(),
        ])->count(2)->create();

        $response = $this->getJson('/budget/current?code=CODE')->assertSuccessful();

        $this->assertCount(2, $response->json('data'));
    }

    public function can_filter_budget_using_description(): void
    {
        BudgetFactory::new([
            LaravelBudget::getVersionColumnName() => now()->year,
            LaravelBudget::getDescriptionColumnName() => 'Foo Bar',
        ])->count(2)->create();
        BudgetFactory::new([
            LaravelBudget::getDescriptionColumnName() => 'Foo Bar',
        ])->count(2)->create();

        $response = $this->getJson('/budget/current?description=Foo')->assertSuccessful();

        $this->assertCount(2, $response->json('data'));
    }

    public function test_can_filter_current_budget_using_json(): void
    {
        config()->set('budget.version_column_type', 'json');
        config()->set('budget.version_column_name', 'metadata');
        config()->set('budget.version_json_column_path', 'metadata->implementation->year');

        $config = new BudgetConfig(config('budget'));

        $this->app->singleton(BudgetConfig::class, fn () => $config);

        $this->remigrate();

        $budget = BudgetFactory::new([
            LaravelBudget::getVersionColumnName() => now()->year,
            LaravelBudget::getCodeColumnName() => 'CODE'.Str::random(),
            LaravelBudget::getVersionColumnName() => json_encode([
                'implementation' => [
                    'year' => now()->year,
                ],
            ]),
        ])->count(2)->create();
        BudgetFactory::new([
            LaravelBudget::getCodeColumnName() => 'CODE'.Str::random(),
            LaravelBudget::getVersionColumnName() => json_encode([
                'implementation' => [
                    'year' => now()->subYear()->year,
                ],
            ]),
        ])->count(2)->create();

        $response = $this->getJson('/budget/current?code=CODE')->assertSuccessful();

        $this->assertCount(2, $response->json('data'));
        $this->assertSame($budget[0]->getVersion(), $response->json('data.0.version'));
    }
}
