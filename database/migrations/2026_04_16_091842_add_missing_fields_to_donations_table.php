<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            if (!Schema::hasColumn('donations', 'service_type')) {
                $table->string('service_type', 100)->nullable()->after('case_id');
            }

            if (!Schema::hasColumn('donations', 'legacy_payment_id')) {
                $table->unsignedBigInteger('legacy_payment_id')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            if (Schema::hasColumn('donations', 'service_type')) {
                $table->dropColumn('service_type');
            }

            if (Schema::hasColumn('donations', 'legacy_payment_id')) {
                $table->dropColumn('legacy_payment_id');
            }
        });
    }
};
