<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_reports', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('period');
            $table->string('type')->default('monthly');

            $table->decimal('total_received', 14, 2)->default(0);
            $table->decimal('total_spent', 14, 2)->default(0);
            $table->decimal('medical_spending', 14, 2)->default(0);
            $table->decimal('operations_spending', 14, 2)->default(0);

            $table->string('document_url')->nullable();
            $table->jsonb('breakdown')->nullable();

            $table->timestamps();

            $table->index('type');
            $table->index('period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_reports');
    }
};
