<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        BlogPost::truncate();

        BlogPost::create([
            'title' => 'Amina uchun kerakli mablag‘ning bir qismi yig‘ildi',
            'slug' => Str::slug('Amina uchun kerakli mablag‘ning bir qismi yig‘ildi'),
            'excerpt' => 'Donorlar yordami bilan Amina operatsiyasi uchun kerakli summaning muhim qismi yig‘ildi.',
            'content' => 'Amina uchun ochilgan xayriya kampaniyasi davom etmoqda. Hozirgacha bir nechta donorlar tomonidan muhim mablag‘ ajratildi va operatsiya sanasi bo‘yicha tayyorgarlik boshlandi.',
            'cover_image' => 'https://images.unsplash.com/photo-1516574187841-cb9cc2ca948b?q=80&w=1200&auto=format&fit=crop',
            'category' => 'success_story',
            'is_featured' => true,
            'author' => 'Mexrli Qo‘llar jamoasi',
            'tags' => ['amina', 'donation', 'health'],
        ]);

        BlogPost::create([
            'title' => 'Yangi hamkorlar bilan tibbiy yordam dasturi kengaymoqda',
            'slug' => Str::slug('Yangi hamkorlar bilan tibbiy yordam dasturi kengaymoqda'),
            'excerpt' => 'Loyiha doirasida yangi klinikalar va ijtimoiy tashkilotlar bilan hamkorlik boshlandi.',
            'content' => 'Yangi hamkorliklar tufayli yordam ko‘rsatish jarayoni tezlashadi va ko‘proq oilalarga ko‘mak berish imkoniyati yaratiladi.',
            'cover_image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=1200&auto=format&fit=crop',
            'category' => 'update',
            'is_featured' => false,
            'author' => 'Admin',
            'tags' => ['partners', 'program', 'update'],
        ]);

        BlogPost::create([
            'title' => 'Bu oyda 12 nafar bolaga yordam berildi',
            'slug' => Str::slug('Bu oyda 12 nafar bolaga yordam berildi'),
            'excerpt' => 'Platforma orqali oy davomida bir nechta oilalarga zarur yordam ko‘rsatildi.',
            'content' => 'O‘tgan oy yakunlari bo‘yicha 12 nafar bolaga turli yo‘nalishlarda yordam berildi. Mablag‘larning katta qismi to‘g‘ridan-to‘g‘ri davolanish xarajatlariga yo‘naltirildi.',
            'cover_image' => 'https://images.unsplash.com/photo-1509099863731-ef4bff19e808?q=80&w=1200&auto=format&fit=crop',
            'category' => 'news',
            'is_featured' => false,
            'author' => 'Mexrli Qo‘llar',
            'tags' => ['monthly', 'impact', 'children'],
        ]);
    }
}
