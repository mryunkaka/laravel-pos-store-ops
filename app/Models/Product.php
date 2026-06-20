<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'category_id',
        'stock',
        'minimum_stock',
        'buying_price',
        'selling_price',
        'discount',
        'discount_type',
        'wholesale_price',
        'wholesale_qty',
        'tax_rate',
        'image',
        'buying_date',
        'expire_date',
    ];

    protected $with = ['category', 'stockMovements'];

    protected $casts = [
        'buying_price' => 'float',
        'minimum_stock' => 'integer',
        'selling_price' => 'float',
        'discount' => 'float',
        'discount_type' => 'string',
        'wholesale_price' => 'float',
        'wholesale_qty' => 'integer',
        'tax_rate' => 'float',
    ];

    public function category(){
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'product_id');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? false, function ($query, $search) {
            return $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        });
    }
}
