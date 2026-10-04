<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Models\Concerns\PresentsBlocks;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class News extends Model
{
    use HasSlug;
    use PresentsBlocks;

    protected $table = 'news';

    protected $fillable = ['title', 'slug', 'excerpt', 'image', 'blocks', 'published_at', 'is_published'];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'published_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image ? Storage::disk('public')->url($this->image) : null);
    }
}
