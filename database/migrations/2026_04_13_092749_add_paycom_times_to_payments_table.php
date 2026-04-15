<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->bigInteger('create_time')->default(0)->after('status');
            $table->bigInteger('perform_time')->default(0)->after('create_time');
            $table->bigInteger('cancel_time')->default(0)->after('perform_time');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['create_time', 'perform_time', 'cancel_time']);
        });
    }
};
