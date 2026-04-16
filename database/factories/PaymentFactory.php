<?php

namespace Database\Factories;

use App\Models\Donation;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $provider = $this->faker->randomElement(['paycom', 'click', 'paynet', 'uzumbank']);
        $status = $this->faker->randomElement(['pending', 'success', 'cancelled', 'failed']);

        return [
            'legacy_mongo_id' => null,
            'legacy_local_id' => null,

            'provider' => $provider,
            'transaction_id' => strtoupper($provider) . '_' . $this->faker->unique()->numerify('##########'),
            'status' => $status,
            'category' => 'donation',

            'payer_reference' => null,

            'amount' => $this->faker->numberBetween(50000, 5000000),
            'currency' => 'UZS',
            'refunded_amount' => 0,

            'external_id' => $this->faker->optional()->numerify('########'),
            'service_id' => $this->faker->optional()->numerify('#####'),

            'provider_time_ms' => now()->subDays(rand(0, 90))->getTimestampMs(),
            'provider_create_time' => now()->subDays(rand(0, 90))->getTimestampMs(),
            'provider_perform_time' => $status === 'success' ? now()->subDays(rand(0, 60))->getTimestampMs() : null,
            'provider_cancel_time' => $status === 'cancelled' ? now()->subDays(rand(0, 30))->getTimestampMs() : null,

            'live_mode' => true,
            'payload' => [
                'source' => 'factory',
            ],
            'raw_information' => null,

            'donation_id' => Donation::query()->inRandomOrder()->value('id'),

            'created_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'updated_at' => now(),
        ];
    }
}
