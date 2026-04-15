<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('help_requests', function (Blueprint $table) {
            $table->id();

            $table->string('full_name');
            $table->string('phone');
            $table->string('city')->nullable();
            $table->text('situation_description');
            $table->string('support_type')->default('medical_treatment');

            $table->jsonb('medical_documents')->nullable();
            $table->jsonb('photos')->nullable();

            $table->boolean('consent_given')->default(false);
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();

            $table->foreignId('case_id')
                ->nullable()
                ->constrained('case_items')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('status');
            $table->index('support_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('help_requests');
    }
};
