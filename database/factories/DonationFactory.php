<?php

namespace Database\Factories;

use App\Models\Donation;
use App\Models\CaseItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonationFactory extends Factory
{
    protected $model = Donation::class;

    public function definition(): array
    {
        return [
            'case_id' => CaseItem::query()->inRandomOrder()->value('id'),

            'donor_name' => $this->faker->name(),
            'donor_email' => $this->faker->safeEmail(),

            'amount' => $this->faker->numberBetween(50000, 5000000),
            'currency' => 'UZS',

            'type' => $this->faker->randomElement([
                'one_time',
                'monthly',
            ]),

            'message' => $this->faker->optional()->sentence(),
            'is_anonymous' => $this->faker->boolean(20),

            'status' => $this->faker->randomElement([
                'completed',
                'pending',
                'failed',
            ]),

            'created_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'updated_at' => now(),
        ];
    }
}
