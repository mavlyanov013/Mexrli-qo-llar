<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('cover_image')->nullable();

            $table->string('category')->default('news');
            $table->boolean('is_featured')->default(false);
            $table->string('author')->nullable();

            $table->jsonb('tags')->nullable();

            $table->timestamps();

            $table->index('slug');
            $table->index('category');
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
