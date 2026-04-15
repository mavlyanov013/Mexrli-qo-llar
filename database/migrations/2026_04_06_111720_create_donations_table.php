<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('case_id')
                ->nullable()
                ->constrained('case_items')
                ->nullOnDelete();

            $table->string('donor_name')->nullable();
            $table->string('donor_email')->nullable();

            $table->decimal('amount', 14, 2);
            $table->string('currency', 10)->default('USD');
            $table->string('type')->default('one_time');
            $table->text('message')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('status')->default('completed');

            $table->timestamps();

            $table->index('status');
            $table->index('type');
            $table->index('currency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
