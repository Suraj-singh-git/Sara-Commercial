<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\PublicStorage;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'max_products',
        'is_active',
        'sort_order',
        'menu_image_path',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** Number of ancestor levels; root = 0. */
    public function depth(): int
    {
        $depth = 0;
        $parentId = $this->parent_id;
        while ($parentId) {
            $depth++;
            $parentId = static::query()->whereKey($parentId)->value('parent_id');
            if ($depth > 20) {
                break;
            }
        }

        return $depth;
    }

    public function menuImageUrl(): ?string
    {
        if (! $this->menu_image_path) {
            return null;
        }

        return PublicStorage::url($this->menu_image_path);
    }
}
