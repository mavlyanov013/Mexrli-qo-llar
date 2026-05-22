<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $page = DB::table('pages')->where('slug', 'about')->first();

        if ($page) {
            $legacyTeam = DB::table('sections')
                ->where('page_id', $page->id)
                ->where('type', 'team')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ($legacyTeam as $index => $section) {
                DB::table('team_members')->insert([
                    'name' => $section->title ?? 'Team member',
                    'position' => $section->subtitle,
                    'photo' => $section->image ?? null,
                    'sort_order' => $section->sort_order ?? $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
