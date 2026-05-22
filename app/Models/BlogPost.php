<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use HasLocalizedAttributes;

    public array $localizedAttributes = [
        'title',
        'excerpt',
        'content',
    ];

    protected $fillable = [
        'title',
        'title_uz',
        'title_oz',
        'title_ru',
        'slug',
        'excerpt',
        'excerpt_uz',
        'excerpt_oz',
        'excerpt_ru',
        'content',
        'content_uz',
        'content_oz',
        'content_ru',
        'cover_image',
        'category',
        'is_featured',
        'author',
        'tags',
        'status',
        'published_at',
    ];

    protected static function booted(): void
    {
        static::saving(function (BlogPost $post) {
            if ($post->status === 'published' && empty($post->published_at)) {
                $post->published_at = now();
            }
        });
    }

    protected $casts = [
        'is_featured' => 'boolean',
        'tags' => 'array',
        'published_at' => 'datetime',
    ];
}
