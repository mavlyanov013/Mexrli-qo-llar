<?php

namespace Database\Seeders;

use App\Models\CaseItem;
use App\Models\Donation;
use App\Models\FinancialReport;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET CONSTRAINTS ALL DEFERRED');

        Payment::query()->delete();
        Donation::query()->delete();
        FinancialReport::query()->delete();
        CaseItem::query()->delete();
        User::query()->where('email', 'admin@mexrli.local')->delete();

        $featuredCase = CaseItem::create([
            'name' => 'Aliya',
            'age' => 7,
            'photo_url' => 'https://images.unsplash.com/photo-1519345182560-3f2917c472ef?q=80&w=1200&auto=format&fit=crop',
            'location' => 'Toshkent',
            'condition' => 'Shoshilinch yurak operatsiyasi kerak',
            'story' => 'Aliya tug‘ma yurak nuqsoni bilan yashamoqda. Shifokorlar operatsiyani imkon qadar tezroq qilish kerakligini aytishgan.',
            'short_description' => 'Aliyaga zudlik bilan yurak operatsiyasi uchun yordam kerak.',
            'goal_amount' => 15000000,
            'raised_amount' => 8200000,
            'urgency' => 'high',
            'category' => 'surgery',
            'status' => 'active',
            'medical_documents' => [
                ['name' => 'echo-report.pdf', 'url' => 'https://example.com/docs/aliya-echo-report.pdf'],
            ],
            'updates' => [
                ['date' => now()->subDays(2)->toDateString(), 'text' => 'Yangi tibbiy xulosa yuklandi'],
            ],
            'is_featured' => true,
            'is_urgent' => true,
        ]);

        $cases = CaseItem::factory()
            ->count(11)
            ->create();

        $allCases = CaseItem::query()->get();

        $donations = collect();

        for ($i = 0; $i < 80; $i++) {
            $case = $i < 20 ? $featuredCase : $allCases->random();

            $status = fake()->randomElement([
                'completed',
                'completed',
                'completed',
                'completed',
                'pending',
                'failed',
            ]);

            $donation = Donation::factory()->create([
                'case_id' => $case->id,
                'status' => $status,
                'amount' => fake()->numberBetween(50000, 3000000),
                'currency' => 'UZS',
                'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
                'updated_at' => now(),
            ]);

            $donations->push($donation);
        }

        foreach ($allCases as $case) {
            $sum = Donation::query()
                ->where('case_id', $case->id)
                ->where('status', 'completed')
                ->sum('amount');

            $goal = (float) $case->goal_amount;
            $raised = min($sum, $goal);

            $case->update([
                'raised_amount' => $raised,
            ]);
        }

        foreach ($donations as $donation) {
            $paymentStatus = match ($donation->status) {
                'completed' => 'success',
                'pending' => 'pending',
                'failed' => 'failed',
                default => 'cancelled',
            };

            Payment::factory()->create([
                'donation_id' => $donation->id,
                'payer_reference' => (string) $donation->id,
                'amount' => $donation->amount,
                'currency' => $donation->currency,
                'status' => $paymentStatus,
                'created_at' => $donation->created_at,
                'updated_at' => now(),
            ]);
        }

//        FinancialReport::factory()->count(6)->create();

        User::create([
            'name' => 'Admin',
            'email' => 'admin@mexrli.local',
            'password' => Hash::make('admin12345'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
    }
}
