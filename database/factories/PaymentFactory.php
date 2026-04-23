<?php

namespace Database\Factories;

use App\Models\Donation;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as FakerFactory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $faker = FakerFactory::create();

        $provider = $faker->randomElement(['paycom', 'click', 'paynet', 'uzumbank']);
        $status = $faker->randomElement(['pending', 'success', 'failed', 'cancelled']);

        return [
            'legacy_mongo_id' => null,
            'legacy_local_id' => null,
            'provider' => $provider,
            'transaction_id' => (string) $faker->unique()->numerify('TXN##########'),
            'status' => $status,
            'category' => $faker->randomElement(['donation', 'general', 'medical']),
            'payer_reference' => $faker->numerify('########'),
            'amount' => $faker->numberBetween(50000, 5000000),
            'currency' => 'UZS',
            'refunded_amount' => 0,
            'external_id' => (string) $faker->optional()->numerify('EXT########'),
            'service_id' => (string) $faker->optional()->numerify('SRV#####'),
            'provider_time_ms' => round(microtime(true) * 1000),
            'provider_create_time' => now()->timestamp * 1000,
            'provider_perform_time' => $status === 'success' ? now()->timestamp * 1000 : null,
            'provider_cancel_time' => $status === 'cancelled' ? now()->timestamp * 1000 : null,
            'live_mode' => false,
            'payload' => [
                'provider' => $provider,
                'source' => 'factory',
            ],
            'raw_information' => null,
            'donation_id' => Donation::query()->inRandomOrder()->value('id'),
            'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
            'updated_at' => now(),
        ];
    }
}
