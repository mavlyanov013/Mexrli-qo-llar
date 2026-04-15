<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_items', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->unsignedSmallInteger('age')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('location')->nullable();
            $table->string('condition')->nullable();
            $table->text('story')->nullable();
            $table->text('short_description')->nullable();

            $table->decimal('goal_amount', 14, 2)->default(0);
            $table->decimal('raised_amount', 14, 2)->default(0);

            $table->string('urgency')->default('medium');
            $table->string('category')->default('illness');
            $table->string('status')->default('active');

            $table->jsonb('medical_documents')->nullable();
            $table->jsonb('updates')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_urgent')->default(false);

            $table->timestamps();

            $table->index('status');
            $table->index('category');
            $table->index('urgency');
            $table->index('is_featured');
            $table->index('is_urgent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_items');
    }
};
