<?php

declare(strict_types=1);

namespace Inisiatif\LaravelBudget\Tests\Http\Controllers;

use Inisiatif\LaravelBudget\BudgetConfig;
use Inisiatif\LaravelBudget\LaravelBudget;
use Inisiatif\LaravelBudget\Tests\TestCase;
use Inisiatif\LaravelBudget\Contracts\HasBudget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inisiatif\LaravelBudget\Database\Factories\BudgetFactory;

final class FetchOneBudgetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_show_budget_using_code(): void
    {
        /** @var HasBudget $budget */
        $budget = BudgetFactory::new([
            'code' => 'CODE001',
        ])->createOne();

        $response = $this->getJson('/budget/CODE001')->assertSuccessful();

        $this->assertSame($budget->getId(), $response->json('data.id'));
        $this->assertSame($budget->getCode(), $response->json('data.code'));
        $this->assertSame($budget->getDescription(), $response->json('data.description'));
        $this->assertSame($budget->getTotalAmount(), (float) $response->json('data.total_amount'));
        $this->assertSame($budget->getUsageAmount(), (float) $response->json('data.usage_amount'));
        $this->assertSame($budget->getBalance(), (float) $response->json('data.balance_amount'));
        $this->assertSame($budget->isOver(), $response->json('data.is_over'));
        $this->assertSame($budget->isLimitReached(), $response->json('data.is_limit_reached'));
        $this->assertSame($budget->getVersion(), (int) $response->json('data.version'));
    }

    public function test_can_show_budget_using_id(): void
    {
        /** @var HasBudget $budget */
        $budget = BudgetFactory::new([
            'code' => 'CODE001',
        ])->createOne();

        $response = $this->getJson('/budget/1')->assertSuccessful();

        $this->assertSame($budget->getId(), $response->json('data.id'));
        $this->assertSame($budget->getCode(), $response->json('data.code'));
        $this->assertSame($budget->getDescription(), $response->json('data.description'));
        $this->assertSame($budget->getTotalAmount(), (float) $response->json('data.total_amount'));
        $this->assertSame($budget->getUsageAmount(), (float) $response->json('data.usage_amount'));
        $this->assertSame($budget->getBalance(), (float) $response->json('data.balance_amount'));
        $this->assertSame($budget->isOver(), $response->json('data.is_over'));
        $this->assertSame($budget->isLimitReached(), $response->json('data.is_limit_reached'));
        $this->assertSame($budget->getVersion(), (int) $response->json('data.version'));
    }

    public function test_return_not_found_when_budget_not_exists(): void
    {
        BudgetFactory::new()->createOne();

        $this->getJson('/budget/9')->assertNotFound();
        $this->getJson('/budget/CODE')->assertNotFound();
    }

    public function test_can_show_budget_using_version_json(): void
    {
        config()->set('budget.version_column_type', 'json');
        config()->set('budget.version_column_name', 'metadata');
        config()->set('budget.version_json_column_path', 'metadata->implementation->year');

        $config = new BudgetConfig(config('budget'));

        $this->app->singleton(BudgetConfig::class, fn () => $config);

        $this->remigrate();

        /** @var HasBudget $budget */
        $budget = BudgetFactory::new([
            'code' => 'CODE001',
            LaravelBudget::getVersionColumnName() => json_encode([
                'implementation' => [
                    'year' => now()->year,
                ],
            ]),
        ])->createOne();

        $response = $this->getJson('/budget/1')->assertSuccessful();

        $this->assertSame($budget->getId(), $response->json('data.id'));
        $this->assertSame($budget->getCode(), $response->json('data.code'));
        $this->assertSame($budget->getDescription(), $response->json('data.description'));
        $this->assertSame($budget->getTotalAmount(), (float) $response->json('data.total_amount'));
        $this->assertSame($budget->getUsageAmount(), (float) $response->json('data.usage_amount'));
        $this->assertSame($budget->getBalance(), (float) $response->json('data.balance_amount'));
        $this->assertSame($budget->isOver(), $response->json('data.is_over'));
        $this->assertSame($budget->isLimitReached(), $response->json('data.is_limit_reached'));
        $this->assertSame($budget->getVersion(), $response->json('data.version'));
    }
}
