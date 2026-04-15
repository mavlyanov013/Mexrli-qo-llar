<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        Partner::truncate();

        Partner::create([
            'name' => 'Mehr Foundation',
            'logo_url' => 'https://dummyimage.com/240x120/f3f4f6/111827&text=Mehr+Foundation',
            'website' => 'https://mehr-foundation.uz',
            'description' => 'Bolalar va oilalarni qo‘llab-quvvatlovchi ijtimoiy hamkor.',
            'type' => 'foundation',
            'is_featured' => true,
        ]);

        Partner::create([
            'name' => 'Care Med Clinic',
            'logo_url' => 'https://dummyimage.com/240x120/f3f4f6/111827&text=Care+Med+Clinic',
            'website' => 'https://caremed.uz',
            'description' => 'Tibbiy maslahat va hamkorlik xizmatlari.',
            'type' => 'medical',
            'is_featured' => true,
        ]);

        Partner::create([
            'name' => 'Open Hearts',
            'logo_url' => 'https://dummyimage.com/240x120/f3f4f6/111827&text=Open+Hearts',
            'website' => 'https://openhearts.uz',
            'description' => 'Xayriya va fundraising bo‘yicha hamkor tashkilot.',
            'type' => 'ngo',
            'is_featured' => false,
        ]);
    }
}
