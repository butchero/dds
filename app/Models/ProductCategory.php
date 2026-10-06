<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ProductCategory extends Model
{
    use HasSlug;

    protected $fillable = ['parent_id', 'name', 'slug', 'description', 'sort'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'attribute_product_category')
            ->orderBy('attributes.sort')
            ->orderBy('attributes.id');
    }

    /**
     * @return list<int>
     */
    public function selfAndAncestorIds(): array
    {
        $ids = [];
        $currentId = (int) $this->id;

        while (count($ids) < 20) {
            if (in_array($currentId, $ids, true)) {
                break;
            }

            $ids[] = $currentId;
            $parentId = self::query()->whereKey($currentId)->value('parent_id');

            if ($parentId === null) {
                break;
            }

            $currentId = (int) $parentId;
        }

        return $ids;
    }

    /**
     * This category and its parents, from the root down to this one.
     *
     * @return Collection<int, self>
     */
    public function lineage(): Collection
    {
        $ids = array_reverse($this->selfAndAncestorIds());
        $categories = self::query()->whereKey($ids)->get()->keyBy('id');

        return collect($ids)
            ->map(fn (int $id): ?self => $categories->get($id))
            ->filter()
            ->values();
    }

    /**
     * Characteristics assigned to this category or one of its parents.
     *
     * @return Collection<int, Attribute>
     */
    public function characteristicsForProduct(): Collection
    {
        return Attribute::query()
            ->whereHas('categories', fn ($query) => $query->whereIn('product_categories.id', $this->selfAndAncestorIds()))
            ->with('options')
            ->orderBy('sort')
            ->orderBy('id')
            ->get();
    }
}
