<?php

namespace Database\Seeders;

use App\Models\CaseItem;
use Illuminate\Database\Seeder;

class CaseSeeder extends Seeder
{
    public function run(): void
    {
        CaseItem::truncate();

        CaseItem::create([
            'name' => 'Amina',
            'age' => 6,
            'photo_url' => 'https://images.unsplash.com/photo-1519345182560-3f2917c472ef?q=80&w=1200&auto=format&fit=crop',
            'location' => 'Toshkent',
            'condition' => 'Yurak operatsiyasi kerak',
            'story' => 'Amina tug‘ma yurak nuqsoni bilan tug‘ilgan. Oilasi operatsiya xarajatlarini qoplay olmayapti.',
            'short_description' => 'Aminaga shoshilinch yurak operatsiyasi uchun yordam kerak.',
            'goal_amount' => 12000,
            'raised_amount' => 4200,
            'urgency' => 'critical',
            'category' => 'medical',
            'status' => 'active',
            'medical_documents' => [
                ['name' => 'Echo report', 'url' => '/docs/amina-echo.pdf'],
            ],
            'updates' => [
                ['title' => 'Tahlillar topshirildi', 'date' => now()->subDays(3)->toDateString()],
            ],
            'is_featured' => true,
            'is_urgent' => true,
        ]);

        CaseItem::create([
            'name' => 'Jasur',
            'age' => 9,
            'photo_url' => 'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?q=80&w=1200&auto=format&fit=crop',
            'location' => 'Samarqand',
            'condition' => 'Eshitish apparati kerak',
            'story' => 'Jasur eshitishida jiddiy muammo bor. Maxsus apparat yordamida u normal o‘qishni davom ettira oladi.',
            'short_description' => 'Jasurga eshitish apparati uchun mablag‘ yig‘ilmoqda.',
            'goal_amount' => 3500,
            'raised_amount' => 1800,
            'urgency' => 'high',
            'category' => 'medical',
            'status' => 'active',
            'medical_documents' => [
                ['name' => 'Audiogram', 'url' => '/docs/jasur-audiogram.pdf'],
            ],
            'updates' => [
                ['title' => 'Shifokor konsultatsiyasi o‘tkazildi', 'date' => now()->subDays(5)->toDateString()],
            ],
            'is_featured' => true,
            'is_urgent' => true,
        ]);

        CaseItem::create([
            'name' => 'Zarina',
            'age' => 4,
            'photo_url' => 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?q=80&w=1200&auto=format&fit=crop',
            'location' => 'Andijon',
            'condition' => 'Reabilitatsiya kursi kerak',
            'story' => 'Zarina uchun bir necha bosqichli reabilitatsiya kursi tavsiya qilingan.',
            'short_description' => 'Zarinaning reabilitatsiyasi uchun yordam kerak.',
            'goal_amount' => 5000,
            'raised_amount' => 2600,
            'urgency' => 'medium',
            'category' => 'rehabilitation',
            'status' => 'active',
            'medical_documents' => [
                ['name' => 'Rehab plan', 'url' => '/docs/zarina-rehab.pdf'],
            ],
            'updates' => [
                ['title' => '1-bosqich reja tasdiqlandi', 'date' => now()->subDays(7)->toDateString()],
            ],
            'is_featured' => false,
            'is_urgent' => false,
        ]);
    }
}
