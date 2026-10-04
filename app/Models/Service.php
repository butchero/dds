<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Models\Concerns\PresentsBlocks;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasSlug;
    use PresentsBlocks;

    protected $fillable = ['service_category_id', 'name', 'slug', 'blocks', 'is_published', 'sort'];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }
}
