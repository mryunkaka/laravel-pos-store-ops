<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function receivingDetails()
    {
        return $this->hasMany(PurchaseReceivingDetail::class, 'purchase_order_detail_id');
    }

    public function getPendingQuantityAttribute()
    {
        return $this->quantity - $this->receivingDetails()->sum('received_quantity');
    }

    public function canBeReceived()
    {
        return $this->pending_quantity > 0;
    }
}
