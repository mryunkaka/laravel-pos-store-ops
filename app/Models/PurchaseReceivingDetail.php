<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReceivingDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_receiving_id',
        'purchase_order_detail_id',
        'product_id',
        'received_quantity',
        'rejected_quantity',
        'notes',
    ];

    protected $casts = [
        'received_quantity' => 'integer',
        'rejected_quantity' => 'integer',
    ];

    public function receiving()
    {
        return $this->belongsTo(PurchaseReceiving::class, 'purchase_receiving_id');
    }

    public function purchaseOrderDetail()
    {
        return $this->belongsTo(PurchaseOrderDetail::class, 'purchase_order_detail_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
