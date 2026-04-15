<?php

namespace Database\Factories;

use App\Models\FinancialReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class FinancialReportFactory extends Factory
{
    protected $model = FinancialReport::class;

    public function definition(): array
    {
        $income = $this->faker->numberBetween(10000000, 150000000);
        $expense = $this->faker->numberBetween(5000000, $income);

        return [
            'month' => $this->faker->numberBetween(1, 12),
            'year' => now()->year,
            'total_income' => $income,
            'total_expense' => $expense,
            'total_helped_cases' => $this->faker->numberBetween(5, 60),
            'report_file' => null,
            'notes' => $this->faker->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
