<?php

declare(strict_types=1);

namespace Inisiatif\LaravelBudget\Tests\Http\Controllers;

use Inisiatif\LaravelBudget\BudgetConfig;
use Inisiatif\LaravelBudget\LaravelBudget;
use Inisiatif\LaravelBudget\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inisiatif\LaravelBudget\Database\Factories\BudgetFactory;

final class FilterBudgetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_filter_budget(): void
    {
        BudgetFactory::new()->count(2)->create();

        $response = $this->getJson('/budget')->assertSuccessful();

        $this->assertCount(2, $response->json('data'));
    }

    public function test_can_filter_budget_using_version(): void
    {
        BudgetFactory::new([
            LaravelBudget::getVersionColumnName() => now()->year,
        ])->count(2)->create();
        BudgetFactory::new([
            LaravelBudget::getVersionColumnName() => now()->subYear()->year,
        ])->count(2)->create();

        $response = $this->getJson('/budget?version='.now()->year)->assertSuccessful();

        $this->assertCount(2, $response->json('data'));
    }

    public function test_can_filter_budget_using_code(): void
    {
        BudgetFactory::new([
            LaravelBudget::getCodeColumnName() => 'CODE'.now()->year,
        ])->count(2)->create();
        BudgetFactory::new()->count(2)->create();

        $response = $this->getJson('/budget?code='.now()->year)->assertSuccessful();

        $this->assertCount(2, $response->json('data'));
    }

    public function test_can_filter_budget_using_description(): void
    {
        BudgetFactory::new([
            LaravelBudget::getDescriptionColumnName() => 'Foo Bar',
        ])->count(2)->create();
        BudgetFactory::new()->count(2)->create();

        $response = $this->getJson('/budget?description=Foo')->assertSuccessful();

        $this->assertCount(2, $response->json('data'));
    }

    public function test_can_filter_budget_using_version_json(): void
    {
        config()->set('budget.version_column_type', 'json');
        config()->set('budget.version_column_name', 'metadata');
        config()->set('budget.version_json_column_path', 'metadata->implementation->year');

        $config = new BudgetConfig(config('budget'));

        $this->app->singleton(BudgetConfig::class, fn () => $config);

        $this->remigrate();

        $budget = BudgetFactory::new([
            LaravelBudget::getDescriptionColumnName() => 'Foo Bar',
            LaravelBudget::getVersionColumnName() => json_encode([
                'implementation' => [
                    'year' => 2024,
                ],
            ]),
        ])->count(2)->create();

        BudgetFactory::new()->count(2)->create();

        $response = $this->getJson('/budget?description=Foo')->assertSuccessful()->json('data');

        $this->assertCount(2, $response);
        $this->assertSame($budget[0]->getVersion(), $response[0]['version']);
    }
}
