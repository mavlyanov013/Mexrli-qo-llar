<?php

namespace Database\Factories;

use App\Models\FinancialReport;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as FakerFactory;

class FinancialReportFactory extends Factory
{
    protected $model = FinancialReport::class;

    public function definition(): array
    {
        $faker = FakerFactory::create();

        $income = $faker->numberBetween(10000000, 150000000);
        $expense = $faker->numberBetween(5000000, $income);

        return [
            'month' => $faker->numberBetween(1, 12),
            'year' => now()->year,
            'total_income' => $income,
            'total_expense' => $expense,
            'total_helped_cases' => $faker->numberBetween(5, 60),
            'report_file' => null,
            'notes' => $faker->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
