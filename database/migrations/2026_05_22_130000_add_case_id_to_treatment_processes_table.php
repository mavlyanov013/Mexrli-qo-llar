<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_processes', function (Blueprint $table) {
            $table->foreignId('case_id')
                ->after('id')
                ->constrained('case_items')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('treatment_processes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('case_id');
        });
    }
};
