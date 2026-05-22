<?php

namespace Database\Seeders;

use App\Models\Partner;
use Database\Seeders\Concerns\BuildsLocalizedRows;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    use BuildsLocalizedRows;

    public function run(): void
    {
        Partner::truncate();

        $partners = [
            [
                'logo_url' => 'https://dummyimage.com/240x120/f3f4f6/111827&text=Mehr+Foundation',
                'website' => 'https://mehr-foundation.uz',
                'type' => 'foundation',
                'is_featured' => true,
                'fields' => [
                    'name' => $this->localizedRow('Mehr Foundation', 'Фонд Mehr'),
                    'description' => $this->localizedRow(
                        'Bolalar va oilalarni qo‘llab-quvvatlovchi ijtimoiy hamkor.',
                        'Социальный партнёр, поддерживающий детей и семьи.'
                    ),
                ],
            ],
            [
                'logo_url' => 'https://dummyimage.com/240x120/f3f4f6/111827&text=Care+Med+Clinic',
                'website' => 'https://caremed.uz',
                'type' => 'medical',
                'is_featured' => true,
                'fields' => [
                    'name' => $this->localizedRow('Care Med Clinic', 'Клиника Care Med'),
                    'description' => $this->localizedRow(
                        'Tibbiy maslahat va hamkorlik xizmatlari.',
                        'Медицинские консультации и партнёрские услуги.'
                    ),
                ],
            ],
            [
                'logo_url' => 'https://dummyimage.com/240x120/f3f4f6/111827&text=Open+Hearts',
                'website' => 'https://openhearts.uz',
                'type' => 'ngo',
                'is_featured' => false,
                'fields' => [
                    'name' => $this->localizedRow('Open Hearts', 'Open Hearts'),
                    'description' => $this->localizedRow(
                        'Xayriya va fundraising bo‘yicha hamkor tashkilot.',
                        'Благотворительная организация по сбору средств.'
                    ),
                ],
            ],
        ];

        foreach ($partners as $partner) {
            $fields = $partner['fields'];
            unset($partner['fields']);

            Partner::create($this->mergeLocalizedFields(array_merge($partner, [
                'is_active' => true,
            ]), $fields));
        }
    }
}
