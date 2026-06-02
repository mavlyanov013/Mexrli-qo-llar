<?php

namespace Database\Seeders;

use App\Models\CaseItem;
use App\Models\Donation;
use App\Models\Faq;
use App\Models\FinancialReport;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\TreatmentProcess;
use App\Models\User;
use App\Services\AboutContentService;
use Database\Seeders\Concerns\BuildsLocalizedRows;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    use BuildsLocalizedRows;

    public function run(): void
    {
        $faker = FakerFactory::create('uz_UZ');

        Payment::query()->delete();
        Donation::query()->delete();
        TreatmentProcess::query()->delete();
        FinancialReport::query()->delete();
        Faq::query()->delete();
        CaseItem::query()->delete();
        TeamMember::query()->delete();

        $featuredCase = $this->createFeaturedCase();

        CaseItem::factory()->count(10)->create()->each(function (CaseItem $case) use ($faker) {
            $this->applyRandomLocalizedCase($case, $faker);
        });

        $allCases = CaseItem::query()->get();

        for ($i = 0; $i < 60; $i++) {
            $case = $i < 15 ? $featuredCase : $allCases->random();

            $status = $faker->randomElement(['completed', 'completed', 'completed', 'pending', 'failed']);

            $donation = Donation::factory()->create([
                'case_id' => $case->id,
                'status' => $status,
                'amount' => $faker->numberBetween(100000, 5000000),
                'currency' => 'UZS',
                'type' => 'one_time',
                'created_at' => $faker->dateTimeBetween('-90 days', 'now'),
            ]);

            Payment::factory()->create([
                'donation_id' => $donation->id,
                'payer_reference' => (string) $donation->id,
                'amount' => $donation->amount,
                'currency' => $donation->currency,
                'status' => $status === 'completed' ? 'success' : ($status === 'pending' ? 'pending' : 'failed'),
                'provider' => $faker->randomElement(['paycom', 'click', 'uzumbank']),
                'created_at' => $donation->created_at,
            ]);
        }

        foreach ($allCases as $case) {
            $sum = Donation::query()
                ->where('case_id', $case->id)
                ->where('status', 'completed')
                ->sum('amount');

            $goal = (float) $case->goal_amount;
            $case->update(['raised_amount' => min($sum, $goal > 0 ? $goal : $sum)]);
        }

        $this->seedTreatmentProcesses($featuredCase, $faker);
        $this->seedFaqs();
        $this->seedFinancialReports($faker);
        $this->seedAboutContent();
        $this->seedContactInfo();
        $this->seedAdminUsers();
    }

    private function createFeaturedCase(): CaseItem
    {
        $data = $this->mergeLocalizedFields([
            'age' => 8,
            'photo_url' => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=800&h=600&fit=crop',
            'goal_amount' => 25000000,
            'raised_amount' => 12500000,
            'urgency' => 'high',
            'category' => 'surgery',
            'status' => 'active',
            'medical_documents' => [
                ['name' => 'tibbiy-xulosa.pdf', 'url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf'],
            ],
            'updates' => [
                ['date' => now()->subDays(3)->toDateString(), 'title' => 'Tahlillar yuklandi', 'content' => 'Yangi tahlil natijalari qo‘shildi'],
            ],
            'is_featured' => true,
            'is_urgent' => true,
        ], [
            'name' => $this->localizedRow('Ali Valiyev', 'Али Валиев'),
            'location' => $this->localizedRow('Toshkent', 'Ташкент'),
            'condition' => $this->localizedRow('Yurak operatsiyasi kerak', 'Нужна операция на сердце'),
            'story' => $this->localizedRow(
                'Ali 8 yoshda. Oilasi uzoq vaqtdan beri davolanish xarajatlarini qoplashga harakat qilmoqda.',
                'Али 8 лет. Семья давно пытается покрыть расходы на лечение.'
            ),
            'short_description' => $this->localizedRow(
                'Ali uchun shoshilinch yurak operatsiyasi kerak.',
                'Срочно нужна операция на сердце для Али.'
            ),
        ]);

        return CaseItem::create($data);
    }

    private function applyRandomLocalizedCase(CaseItem $case, $faker): void
    {
        $name = $faker->firstName() . ' ' . $faker->lastName();

        $case->update($this->mergeLocalizedFields([], [
            'name' => $this->localizedRow($name),
            'location' => $this->localizedRow($faker->city(), $faker->city()),
            'condition' => $this->localizedRow('Tibbiy yordam kerak', 'Нужна медицинская помощь'),
            'short_description' => $this->localizedRow($faker->sentence(8), $faker->sentence(8)),
            'story' => $this->localizedRow($faker->paragraph(3), $faker->paragraph(3)),
        ]));
    }

    private function seedTreatmentProcesses(CaseItem $case, $faker): void
    {
        $steps = [
            ['Diagnostika', 'Birinchi tekshiruvlar va tahlillar o‘tkazildi', 1],
            ['Jarayon boshlandi', 'Shifokorlar davolanish rejasini tasdiqladi', 2],
            ['Operatsiya oldi', 'Operatsiya sanasi kelishildi', 3],
        ];

        foreach ($steps as [$title, $desc, $order]) {
            TreatmentProcess::create($this->mergeLocalizedFields([
                'case_id' => $case->id,
                'sort_order' => $order,
                'images' => [
                    'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=400&h=300&fit=crop',
                ],
            ], [
                'title' => $this->localizedRow($title, $title),
                'description' => $this->localizedRow($desc, $desc),
            ]));
        }
    }

    private function seedFaqs(): void
    {
        $items = [
            ['Xayriya qanday ishlaydi?', 'Siz istalgan holatni tanlab, onlayn yoki naqd to‘lov qilishingiz mumkin.', 'general'],
            ['Pul qayerga ketadi?', 'Mablag‘ to‘g‘ridan-to‘g‘ri tasdiqlangan holatlar uchun ajratiladi.', 'finance'],
            ['Kimlar yordam olishi mumkin?', 'Tibbiy yordamga muhtoj oilalar ariza topshirishi mumkin.', 'help'],
        ];

        foreach ($items as $index => [$q, $a, $cat]) {
            Faq::create($this->mergeLocalizedFields([
                'category' => $cat,
                'order' => $index + 1,
                'is_active' => true,
            ], [
                'question' => $this->localizedRow($q, $q),
                'answer' => $this->localizedRow($a, $a),
            ]));
        }
    }

    private function seedFinancialReports($faker): void
    {
        for ($m = 1; $m <= 6; $m++) {
            $received = $faker->numberBetween(50000000, 200000000);
            $spent = $faker->numberBetween(30000000, (int) ($received * 0.85));

            FinancialReport::create([
                'title' => now()->subMonths(6 - $m)->format('F Y') . ' hisoboti',
                'period' => now()->subMonths(6 - $m)->format('Y-m'),
                'type' => 'monthly',
                'total_received' => $received,
                'total_spent' => $spent,
                'medical_spending' => (int) ($spent * 0.6),
                'operations_spending' => (int) ($spent * 0.15),
                'document_url' => null,
                'breakdown' => [
                    ['label' => 'Tibbiy yordam', 'amount' => (int) ($spent * 0.6)],
                    ['label' => 'Operatsion xarajatlar', 'amount' => (int) ($spent * 0.15)],
                ],
            ]);
        }
    }

    private function seedAboutContent(): void
    {
        Setting::updateOrCreate(
            ['key' => AboutContentService::BANK_KEY],
            ['value' => json_encode([
                'bank_uz' => 'Kapitalbank',
                'bank_oz' => 'Капиталбанк',
                'bank_ru' => 'Капиталбанк',
                'account_uzs' => '20208000123456789012',
                'mfo_bik' => '01158',
            ], JSON_UNESCAPED_UNICODE)]
        );

        Setting::updateOrCreate(
            ['key' => AboutContentService::LEGAL_KEY],
            ['value' => json_encode([
                'org_name_uz' => '«Mexrli Insonlar» xayriya jamg‘armasi',
                'org_name_oz' => '«Меҳрли Қўллар» хайрия жамғармаси',
                'org_name_ru' => 'Благотворительный фонд «Mexrli Insonlar»',
                'legal_address_uz' => 'Toshkent sh., Yunusobod tumani',
                'legal_address_oz' => 'Тошкент ш., Юнусобод тумани',
                'legal_address_ru' => 'г. Ташкент, Юнусабадский район',
                'inn' => '305123456',
            ], JSON_UNESCAPED_UNICODE)]
        );

        TeamMember::create($this->mergeLocalizedFields([
            'photo' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=200&h=200&fit=crop',
            'sort_order' => 1,
        ], [
            'name' => $this->localizedRow('Sardor Karimov', 'Сардор Каримов'),
            'position' => $this->localizedRow('Bosh direktor', 'Генеральный директор'),
        ]));

        TeamMember::create($this->mergeLocalizedFields([
            'photo' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=200&h=200&fit=crop',
            'sort_order' => 2,
        ], [
            'name' => $this->localizedRow('Dilnoza Rahimova', 'Дилноза Рахимова'),
            'position' => $this->localizedRow('Dasturlar bo‘yicha', 'По программам'),
        ]));
    }

    private function seedContactInfo(): void
    {
        Setting::updateOrCreate(
            ['key' => \App\Services\ContactInfoService::KEY],
            ['value' => json_encode([
                'address' => "Toshkent sh., Amir Temur ko'chasi 108",
                'phone' => '+998 71 200 00 00',
                'email' => 'info@mehrli.uz',
                'map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2994.348!2d69.240!3d41.311!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDHCsDE4JzQwLjAiTiA2OcKwMTQnMjQuMCJF!5e0!3m2!1suz!2s!4v1',
                'map_lat' => 41.3111,
                'map_lng' => 69.2797,
            ], JSON_UNESCAPED_UNICODE)]
        );
    }

    private function seedAdminUsers(): void
    {
        $superRole = Role::query()->where('name', 'super_admin')->first();
        $editorRole = Role::query()->where('name', 'editor')->first();

        User::query()->whereIn('email', ['admin@mexrli.local', 'editor@mexrli.local'])->delete();

        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@mexrli.local',
            'password' => Hash::make('admin12345'),
            'is_admin' => true,
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        $editor = User::create([
            'name' => 'Editor',
            'email' => 'editor@mexrli.local',
            'password' => Hash::make('editor12345'),
            'is_admin' => false,
            'role' => 'editor',
            'email_verified_at' => now(),
        ]);

        if ($superRole) {
            $admin->roles()->syncWithoutDetaching([$superRole->id]);
        }

        if ($editorRole) {
            $editor->roles()->syncWithoutDetaching([$editorRole->id]);
        }
    }
}
