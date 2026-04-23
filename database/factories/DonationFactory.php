<?php

namespace Database\Factories;

use App\Models\CaseItem;
use App\Models\Donation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as FakerFactory;

class DonationFactory extends Factory
{
    protected $model = Donation::class;

    public function definition(): array
    {
        $faker = FakerFactory::create();

        return [
            'case_id' => CaseItem::query()->inRandomOrder()->value('id'),
            'donor_name' => $faker->name(),
            'donor_email' => $faker->safeEmail(),
            'amount' => $faker->numberBetween(50000, 5000000),
            'currency' => 'UZS',
            'type' => $faker->randomElement([
                'one_time',
                'monthly',
            ]),
            'message' => $faker->optional()->sentence(),
            'is_anonymous' => $faker->boolean(20),
            'status' => $faker->randomElement([
                'completed',
                'pending',
                'failed',
            ]),
            'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
            'updated_at' => now(),
        ];
    }
}
