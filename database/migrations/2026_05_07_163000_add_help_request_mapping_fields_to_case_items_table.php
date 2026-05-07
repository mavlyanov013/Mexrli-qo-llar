<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_items', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('location');
            $table->string('source')->nullable()->after('category');
            $table->foreignId('created_from_request_id')->nullable()->after('source')->constrained('help_requests')->nullOnDelete();
            $table->index(['source', 'created_from_request_id']);
        });
    }

    public function down(): void
    {
        Schema::table('case_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_from_request_id');
            $table->dropIndex(['source', 'created_from_request_id']);
            $table->dropColumn(['phone', 'source']);
        });
    }
};
