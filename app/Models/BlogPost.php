<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image',
        'category',
        'is_featured',
        'author',
        'tags',
        'status',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'tags' => 'array',
        'published_at' => 'datetime',
    ];
}
