<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesReturnDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sales_return_id',
        'order_detail_id',
        'product_id',
        'quantity',
        'condition',
        'unit_price',
        'total',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class);
    }

    public function orderDetail()
    {
        return $this->belongsTo(OrderDetails::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
