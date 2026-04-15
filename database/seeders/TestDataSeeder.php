<?php

namespace Database\Seeders;

use App\Models\Donation;
use App\Models\CaseItem;
use App\Models\FinancialReport;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $cases = CaseItem::factory()->count(12)->create();

        Donation::factory()->count(80)->create([
            'case_id' => fn () => $cases->random()->id,
        ]);

        FinancialReport::factory()->count(6)->create();

        CaseItem::create([
            'name' => 'Ali Valiyev',
            'age' => 8,
            'photo_url' => 'https://picsum.photos/800/600?random=101',
            'location' => 'Toshkent',
            'condition' => 'Shoshilinch yurak operatsiyasi kerak',
            'story' => 'Bola uzoq vaqtdan beri davolanmoqda...',
            'short_description' => 'Ali uchun operatsiya xarajatlariga yordam kerak',
            'goal_amount' => 25000000,
            'raised_amount' => 12000000,
            'urgency' => 'high',
            'category' => 'surgery',
            'status' => 'active',
            'medical_documents' => [
                ['name' => 'xulosa.pdf', 'url' => 'https://example.com/xulosa.pdf']
            ],
            'updates' => [
                ['date' => now()->toDateString(), 'text' => 'Yangi tahlillar yuklandi']
            ],
            'is_featured' => true,
            'is_urgent' => true,
        ]);
    }
}
