<?php

declare(strict_types=1);

namespace Inisiatif\LaravelBudget\Database\Seeders;

use Illuminate\Database\Seeder;
use Inisiatif\LaravelBudget\LaravelBudget;
use Inisiatif\LaravelBudget\Models\Budget;

final class UpdateNullAmountUsageBudgetSeeder extends Seeder
{
    public function run(): void
    {
        $usageAmountColumn = LaravelBudget::getUsageAmountColumnName();
        $legacyUsageAmountColumn = LaravelBudget::getLegacyUsageAmountColumnName();

        Budget::query()
            ->whereNull($usageAmountColumn)
            ->update([
                $usageAmountColumn => 0,
            ]);

        Budget::query()
            ->whereNull($legacyUsageAmountColumn)
            ->update([
                $legacyUsageAmountColumn => 0,
            ]);
    }
}
