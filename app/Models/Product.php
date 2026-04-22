<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'short_description',
        'detailed_description',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /** Lowest positive variant price (excludes add-ons seeded at ₹0). */
    public function fromPrice(): float
    {
        $priced = $this->variants->filter(fn (ProductVariant $v) => (float) $v->price > 0);

        if ($priced->isEmpty()) {
            return 0.0;
        }

        return (float) $priced->sortBy('price')->first()->price;
    }
}
