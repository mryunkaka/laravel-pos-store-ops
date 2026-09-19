<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReference extends Model
{
    protected $table = 'product_references';

    protected $guarded = [];

    protected $casts = [
        'buying_price' => 'float',
        'minimum_stock' => 'integer',
        'selling_price' => 'float',
        'discount' => 'float',
        'wholesale_price' => 'float',
        'wholesale_qty' => 'integer',
        'tax_rate' => 'float',
        'source_created_at' => 'datetime',
        'source_updated_at' => 'datetime',
        'archived_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $with = ['category'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'product_id');
    }

    public static function syncFromProduct(Product $product): self
    {
        $reference = static::ensureFromProduct($product);

        if ($reference->archived_at !== null) {
            return $reference;
        }

        $product->loadMissing('category');
        $reference->forceFill(self::snapshotFromProduct($product));
        $reference->saveQuietly();

        return $reference;
    }

    public static function ensureFromProduct(Product $product): self
    {
        $product->loadMissing('category');

        $reference = static::firstOrNew(['id' => $product->getKey()]);

        if (! $reference->exists) {
            $reference->forceFill(self::snapshotFromProduct($product));
            $reference->saveQuietly();
        }

        return $reference;
    }

    public static function archiveFromProduct(Product $product): self
    {
        $reference = static::syncFromProduct($product);
        $archivedAt = now();

        $reference->forceFill([
            'archived_at' => $archivedAt,
            'deleted_at' => $archivedAt,
        ])->saveQuietly();

        return $reference;
    }

    private static function snapshotFromProduct(Product $product): array
    {
        return [
            'name' => $product->name,
            'material' => $product->material,
            'print_size' => $product->print_size,
            'print_notes' => $product->print_notes,
            'slug' => $product->slug,
            'code' => $product->code,
            'category_id' => $product->category_id,
            'category_name' => $product->category?->name,
            'stock' => $product->stock ?? 0,
            'minimum_stock' => $product->minimum_stock ?? 0,
            'buying_price' => $product->buying_price,
            'selling_price' => $product->selling_price,
            'discount' => $product->discount ?? 0,
            'discount_type' => $product->discount_type ?? 'fixed',
            'wholesale_price' => $product->wholesale_price,
            'wholesale_qty' => $product->wholesale_qty,
            'tax_rate' => $product->tax_rate ?? 0,
            'image' => $product->image,
            'buying_date' => $product->buying_date,
            'expire_date' => $product->expire_date,
            'source_created_at' => $product->created_at,
            'source_updated_at' => $product->updated_at,
            'created_at' => $product->created_at ?? now(),
            'updated_at' => $product->updated_at ?? now(),
        ];
    }
}
