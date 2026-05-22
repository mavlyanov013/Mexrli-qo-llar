<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $textColumns = [
        'blog_posts' => ['title', 'excerpt', 'content'],
        'case_items' => ['name', 'location', 'condition', 'story', 'short_description'],
        'team_members' => ['name', 'position'],
        'faqs' => ['question', 'answer'],
        'treatment_processes' => ['title', 'description'],
        'partners' => ['name', 'description'],
    ];

    public function up(): void
    {
        foreach ($this->textColumns as $table => $fields) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $fields) {
                foreach ($fields as $field) {
                    if (! Schema::hasColumn($table, "{$field}_uz")) {
                        $column = str_contains($field, 'content') || in_array($field, ['story', 'answer', 'description'], true)
                            ? 'text'
                            : 'string';

                        if ($column === 'string') {
                            $blueprint->string("{$field}_uz")->nullable();
                            $blueprint->string("{$field}_oz")->nullable();
                            $blueprint->string("{$field}_ru")->nullable();
                        } else {
                            $blueprint->text("{$field}_uz")->nullable();
                            $blueprint->text("{$field}_oz")->nullable();
                            $blueprint->text("{$field}_ru")->nullable();
                        }
                    }
                }
            });

            foreach ($fields as $field) {
                if (! Schema::hasColumn($table, $field)) {
                    continue;
                }

                DB::table($table)
                    ->select('id', $field)
                    ->orderBy('id')
                    ->chunkById(100, function ($rows) use ($table, $field) {
                        foreach ($rows as $row) {
                            if ($row->{$field} === null || $row->{$field} === '') {
                                continue;
                            }

                            DB::table($table)
                                ->where('id', $row->id)
                                ->update([
                                    "{$field}_uz" => $row->{$field},
                                ]);
                        }
                    });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->textColumns as $table => $fields) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $fields) {
                foreach ($fields as $field) {
                    foreach (['uz', 'oz', 'ru'] as $suffix) {
                        $column = "{$field}_{$suffix}";

                        if (Schema::hasColumn($table, $column)) {
                            $blueprint->dropColumn($column);
                        }
                    }
                }
            });
        }
    }
};
