<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('author');
            $table->timestamp('published_at')->nullable()->after('status');
            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropIndex(['status', 'published_at']);
            $table->dropColumn(['status', 'published_at']);
        });
    }
};
