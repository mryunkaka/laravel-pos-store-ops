<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'buying_price',
        'discount',
        'discount_type',
        'total',
    ];

    protected $casts = [
        'unit_price' => 'float',
        'buying_price' => 'float',
        'discount' => 'float',
        'discount_type' => 'string',
        'total' => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function salesReturnDetails()
    {
        return $this->hasMany(SalesReturnDetail::class, 'order_detail_id');
    }
}
