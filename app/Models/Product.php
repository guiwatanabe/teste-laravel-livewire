<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'category_id',
        'name',
        'description',
        'price',
    ];

    /**
     * Get the brand that owns the product.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope to filter products by search term.
     */
    public function scopeSearch(Builder $query, ?string $search = null): Builder
    {
        $search = trim((string) $search);

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        return $query;
    }


    /**
     * Scope to filter products by category IDs.
     */
    public function scopeByCategories(Builder $query, ?array $categoryIds = null): Builder
    {
        if (! empty($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }

        return $query;
    }

    /**
     * Scope to filter products by brand IDs.
     */
    public function scopeByBrands(Builder $query, ?array $brandIds = null): Builder
    {
        if (! empty($brandIds)) {
            $query->whereIn('brand_id', $brandIds);
        }

        return $query;
    }

    /**
     * Scope to filter products by search, categories, and brands.
     */
    public function scopeFilter(Builder $query, ?string $search = null, ?array $categoryIds = null, ?array $brandIds = null): Builder
    {
        return $query->search($search)
            ->byCategories($categoryIds)
            ->byBrands($brandIds);
    }
}
