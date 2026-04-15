<?php

namespace Database\Factories;

use App\Models\CaseItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class CaseItemFactory extends Factory
{
    protected $model = CaseItem::class;

    public function definition(): array
    {
        $goal = $this->faker->numberBetween(5000000, 50000000);
        $raised = $this->faker->numberBetween(500000, $goal);

        return [
            'name' => $this->faker->name(),
            'age' => $this->faker->optional()->numberBetween(1, 75),
            'photo_url' => 'https://picsum.photos/800/600?random=' . rand(1, 9999),
            'location' => $this->faker->city(),
            'condition' => $this->faker->randomElement([
                'Yurak operatsiyasi kerak',
                'Onkologik davolanish',
                'Reabilitatsiya',
                'Shoshilinch operatsiya',
                'Nogironlik aravachasi kerak',
            ]),
            'story' => $this->faker->paragraphs(3, true),
            'short_description' => $this->faker->sentence(12),
            'goal_amount' => $goal,
            'raised_amount' => $raised,
            'urgency' => $this->faker->randomElement(['low', 'medium', 'high']),
            'category' => $this->faker->randomElement([
                'illness',
                'surgery',
                'rehabilitation',
                'disability',
                'emergency',
            ]),
            'status' => $this->faker->randomElement([
                'active',
                'completed',
                'paused',
            ]),
            'medical_documents' => [
                [
                    'name' => 'analysis.pdf',
                    'url' => 'https://example.com/files/analysis.pdf',
                ],
            ],
            'updates' => [
                [
                    'date' => now()->subDays(rand(1, 20))->toDateString(),
                    'text' => $this->faker->sentence(),
                ],
            ],
            'is_featured' => $this->faker->boolean(30),
            'is_urgent' => $this->faker->boolean(40),
            'created_at' => $this->faker->dateTimeBetween('-2 months', 'now'),
            'updated_at' => now(),
        ];
    }
}
