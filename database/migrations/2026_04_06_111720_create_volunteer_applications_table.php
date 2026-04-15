<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteer_applications', function (Blueprint $table) {
            $table->id();

            $table->string('full_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('city')->nullable();

            $table->string('role_interest')->default('other');
            $table->text('experience')->nullable();
            $table->text('motivation')->nullable();
            $table->string('availability')->default('flexible');
            $table->string('status')->default('pending');

            $table->timestamps();

            $table->index('email');
            $table->index('status');
            $table->index('role_interest');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_applications');
    }
};
