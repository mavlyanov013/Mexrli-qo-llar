<?php

namespace Database\Factories;

use App\Models\CaseItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as FakerFactory;

class CaseItemFactory extends Factory
{
    protected $model = CaseItem::class;

    public function definition(): array
    {
        $faker = FakerFactory::create();

        $goal = $faker->numberBetween(5000000, 50000000);
        $raised = $faker->numberBetween(500000, $goal);

        $name = $faker->name();
        $location = $faker->city();
        $condition = $faker->randomElement([
            'Yurak operatsiyasi kerak',
            'Onkologik davolanish',
            'Reabilitatsiya',
            'Shoshilinch operatsiya',
            'Nogironlik aravachasi kerak',
        ]);
        $story = $faker->paragraphs(3, true);
        $shortDescription = $faker->sentence(12);

        return [
            'name' => $name,
            'name_uz' => $name,
            'name_oz' => $name,
            'location' => $location,
            'location_uz' => $location,
            'location_oz' => $location,
            'condition' => $condition,
            'condition_uz' => $condition,
            'condition_oz' => $condition,
            'story' => $story,
            'story_uz' => $story,
            'story_oz' => $story,
            'short_description' => $shortDescription,
            'short_description_uz' => $shortDescription,
            'short_description_oz' => $shortDescription,
            'age' => $faker->optional()->numberBetween(1, 75),
            'photo_url' => 'https://picsum.photos/800/600?random=' . rand(1, 9999),
            'goal_amount' => $goal,
            'raised_amount' => $raised,
            'urgency' => $faker->randomElement(['low', 'medium', 'high']),
            'category' => $faker->randomElement([
                'illness',
                'surgery',
                'rehabilitation',
                'disability',
                'emergency',
            ]),
            'status' => $faker->randomElement([
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
                    'text' => $faker->sentence(),
                ],
            ],
            'is_featured' => $faker->boolean(30),
            'is_urgent' => $faker->boolean(40),
            'created_at' => $faker->dateTimeBetween('-2 months', 'now'),
            'updated_at' => now(),
        ];
    }
}
