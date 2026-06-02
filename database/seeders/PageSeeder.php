<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Section;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Mexrli Insonlar',
                'content' => 'Home page',
                'meta_title' => 'Mexrli Insonlar',
                'meta_description' => 'Yordam va ko‘mak platformasi',
                'is_active' => true,
                'published_at' => now(),
            ]
        );

        Section::updateOrCreate(
            [
                'page_id' => $page->id,
                'type' => 'hero',
                'sort_order' => 1,
            ],
            [
                'title' => 'Mexrli Insonlar',
                'subtitle' => 'Birgalikda yaxshilik qilamiz',
                'content' => 'Bu yerga keyin Base44 dagi matnlarni ko‘chirasiz.',
                'is_active' => true,
                'extra' => [
                    'button_text' => 'Bog‘lanish',
                    'button_link' => '/contact',
                ],
            ]
        );

        Section::updateOrCreate(
            [
                'page_id' => $page->id,
                'type' => 'about',
                'sort_order' => 2,
            ],
            [
                'title' => 'Biz haqimizda',
                'subtitle' => 'Loyiha haqida qisqacha',
                'content' => 'Bu section ham keyin frontend bilan moslanadi.',
                'is_active' => true,
            ]
        );
    }
}
