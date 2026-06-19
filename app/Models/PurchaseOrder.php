<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'po_number',
        'po_date',
        'expected_delivery_date',
        'status',
        'sub_total',
        'vat',
        'total',
        'cancel_reason',
        'created_by',
    ];

    protected $casts = [
        'po_date' => 'date',
        'expected_delivery_date' => 'date',
        'sub_total' => 'decimal:2',
        'vat' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details()
    {
        return $this->hasMany(PurchaseOrderDetail::class, 'purchase_order_id');
    }

    public function receivings()
    {
        return $this->hasMany(PurchaseReceiving::class, 'purchase_order_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPendingQuantityAttribute()
    {
        $received = $this->receivings()->where('status', 'completed')->sum(function ($receiving) {
            return $receiving->details->sum('received_quantity');
        });
        return $this->details->sum('quantity') - $received;
    }

    public function canBeCompleted()
    {
        return $this->status === 'pending' && $this->pending_quantity === 0;
    }
}
