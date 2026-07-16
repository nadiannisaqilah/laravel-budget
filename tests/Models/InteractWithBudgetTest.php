<?php

declare(strict_types=1);

namespace Inisiatif\LaravelBudget\Tests\Models;

use Inisiatif\LaravelBudget\BudgetConfig;
use Inisiatif\LaravelBudget\LaravelBudget;
use Inisiatif\LaravelBudget\Tests\TestCase;
use Inisiatif\LaravelBudget\Contracts\HasBudget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inisiatif\LaravelBudget\Database\Factories\BudgetFactory;

final class InteractWithBudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_return_valid_value(): void
    {
        BudgetFactory::new()->createOne([
            LaravelBudget::getTotalAmountColumnName() => 2000,
            LaravelBudget::getUsageAmountColumnName() => 1000,
            LaravelBudget::getLegacyUsageAmountColumnName() => 1000,
            LaravelBudget::getIsOverAmountColumnName() => false,
        ]);

        /** @var HasBudget $budget */
        $budget = LaravelBudget::getBudgetModel()->newQuery()->first();

        $this->assertSame(1000.0, $budget->getBalance());
        $this->assertSame(1000.0, $budget->getUsageAmount());
        $this->assertSame(1000.0, $budget->getLegacyUsageAmount());
        $this->assertSame(2000.0, $budget->getTotalAmount());
        $this->assertSame(2000.0, $budget->getTotalUsageAmount());
        $this->assertFalse($budget->isLimitReached());
        $this->assertFalse($budget->isOver());
    }

    public function test_can_return_get_version_value(): void
    {
        BudgetFactory::new()->createOne([
            LaravelBudget::getVersionColumnName() => now()->year,
        ]);

        /** @var HasBudget $budget */
        $budget = LaravelBudget::getBudgetModel()->newQuery()->first();

        $this->assertSame(now()->year, (int) $budget->getVersion());
    }

    public function test_can_return_get_version_json_value(): void
    {
        config()->set('budget.version_column_type', 'json');
        config()->set('budget.version_column_name', 'metadata');
        config()->set('budget.version_json_column_path', 'metadata->implementation->year');

        $config = new BudgetConfig(config('budget'));

        $this->app->singleton(BudgetConfig::class, fn () => $config);

        $this->remigrate();

        BudgetFactory::new()->createOne([
            LaravelBudget::getVersionColumnName() => json_encode([
                'implementation' => [
                    'year' => 2024,
                ],
            ]),
        ]);

        /** @var HasBudget $budget */
        $budget = LaravelBudget::getBudgetModel()->newQuery()->first();

        $this->assertSame(2024, (int) $budget->getVersion());
    }
}
