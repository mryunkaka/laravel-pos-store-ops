<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'reference_type',
        'reference_id',
        'reference_user_id',
        'quantity',
        'unit_price',
        'description',
        'is_complete',
    ];

    protected $casts = [
        'is_complete' => 'boolean',
        'unit_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'reference_user_id');
    }

    // Helper method to record stock movement
    public static function recordOut(Product $product, $quantity, $description = null, $user = null, $referenceType = null, $referenceId = null)
    {
        return self::create([
            'product_id' => $product->id,
            'type' => 'out',
            'reference_type' => $referenceType ?? Product::class,
            'reference_id' => $referenceId ?? $product->id,
            'quantity' => -$quantity,
            'unit_price' => $product->selling_price,
            'description' => $description,
            'reference_user_id' => $user?->id,
        ]);
    }

    public static function recordIn(Product $product, $quantity, $description = null, $user = null, $referenceType = null, $referenceId = null)
    {
        return self::create([
            'product_id' => $product->id,
            'type' => 'in',
            'reference_type' => $referenceType ?? Product::class,
            'reference_id' => $referenceId ?? $product->id,
            'quantity' => $quantity,
            'unit_price' => $product->buying_price,
            'description' => $description,
            'reference_user_id' => $user?->id,
        ]);
    }

    public static function recordAdjustment(Product $product, $quantity, $description, $user = null, $referenceType = null, $referenceId = null)
    {
        $type = $quantity > 0 ? 'adjustment_in' : 'adjustment_out';
        return self::create([
            'product_id' => $product->id,
            'type' => $type,
            'reference_type' => $referenceType ?? Product::class,
            'reference_id' => $referenceId ?? $product->id,
            'quantity' => abs($quantity),
            'unit_price' => $product->buying_price,
            'description' => $description,
            'reference_user_id' => $user?->id,
        ]);
    }

    // Scope to filter by type
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Scope to filter by product
    public function scopeOfProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    // Scope to filter by date range
    public function scopeOfDateRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }
}
