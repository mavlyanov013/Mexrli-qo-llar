<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('logo_url')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->default('corporate');
            $table->boolean('is_featured')->default(false);

            $table->timestamps();

            $table->index('type');
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
