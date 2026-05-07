<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('help_requests', function (Blueprint $table) {
            $table->string('category')->nullable()->after('support_type');
            $table->text('description')->nullable()->after('situation_description');
            $table->jsonb('attachments')->nullable()->after('photos');
        });
    }

    public function down(): void
    {
        Schema::table('help_requests', function (Blueprint $table) {
            $table->dropColumn(['category', 'description', 'attachments']);
        });
    }
};
