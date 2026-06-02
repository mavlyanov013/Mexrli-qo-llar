<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Database\Seeders\Concerns\BuildsLocalizedRows;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogPostSeeder extends Seeder
{
    use BuildsLocalizedRows;

    public function run(): void
    {
        BlogPost::truncate();

        $posts = [
            [
                'slug' => 'amina-yordam-qismi',
                'cover_image' => 'https://images.unsplash.com/photo-1516574187841-cb9cc2ca948b?q=80&w=1200&auto=format&fit=crop',
                'category' => 'success_story',
                'is_featured' => true,
                'author' => 'Mexrli Insonlar jamoasi',
                'tags' => ['amina', 'donation', 'health'],
                'fields' => [
                    'title' => $this->localizedRow('Amina uchun kerakli mablag‘ning bir qismi yig‘ildi'),
                    'excerpt' => $this->localizedRow('Donorlar yordami bilan Amina operatsiyasi uchun kerakli summaning muhim qismi yig‘ildi.'),
                    'content' => $this->localizedRow(
                        'Amina uchun ochilgan xayriya kampaniyasi davom etmoqda. Hozirgacha bir nechta donorlar tomonidan muhim mablag‘ ajratildi va operatsiya sanasi bo‘yicha tayyorgarlik boshlandi.'
                    ),
                ],
            ],
            [
                'slug' => 'yangi-hamkorlar',
                'cover_image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=1200&auto=format&fit=crop',
                'category' => 'announcement',
                'is_featured' => false,
                'author' => 'Admin',
                'tags' => ['partners', 'program', 'update'],
                'fields' => [
                    'title' => $this->localizedRow('Yangi hamkorlar bilan tibbiy yordam dasturi kengaymoqda'),
                    'excerpt' => $this->localizedRow('Loyiha doirasida yangi klinikalar va ijtimoiy tashkilotlar bilan hamkorlik boshlandi.'),
                    'content' => $this->localizedRow(
                        'Yangi hamkorliklar tufayli yordam ko‘rsatish jarayoni tezlashadi va ko‘proq oilalarga ko‘mak berish imkoniyati yaratiladi.'
                    ),
                ],
            ],
            [
                'slug' => 'oy-yordam-12-bola',
                'cover_image' => 'https://images.unsplash.com/photo-1509099863731-ef4bff19e808?q=80&w=1200&auto=format&fit=crop',
                'category' => 'news',
                'is_featured' => false,
                'author' => 'Mexrli Insonlar',
                'tags' => ['monthly', 'impact', 'children'],
                'fields' => [
                    'title' => $this->localizedRow('Bu oyda 12 nafar bolaga yordam berildi'),
                    'excerpt' => $this->localizedRow('Platforma orqali oy davomida bir nechta oilalarga zarur yordam ko‘rsatildi.'),
                    'content' => $this->localizedRow(
                        'O‘tgan oy yakunlari bo‘yicha 12 nafar bolaga turli yo‘nalishlarda yordam berildi. Mablag‘larning katta qismi to‘g‘ridan-to‘g‘ri davolanish xarajatlariga yo‘naltirildi.'
                    ),
                ],
            ],
        ];

        foreach ($posts as $post) {
            $fields = $post['fields'];
            unset($post['fields']);

            BlogPost::create($this->mergeLocalizedFields(array_merge($post, [
                'status' => 'published',
                'published_at' => now(),
            ]), $fields));
        }
    }
}
