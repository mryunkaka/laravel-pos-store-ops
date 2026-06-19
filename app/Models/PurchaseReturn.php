<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturn extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_receiving_id',
        'supplier_id',
        'created_by',
        'return_number',
        'return_date',
        'description',
        'total',
        'discount',
        'grand_total',
        'status'
    ];

    protected $casts = [
        'return_date' => 'date',
        'total' => 'decimal:2',
        'discount' => 'decimal:2',
        'grand_total' => 'decimal:2'
    ];

    // Relasi
    public function purchaseReceiving()
    {
        return $this->belongsTo(PurchaseReceiving::class, 'purchase_receiving_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return $this->hasMany(PurchaseReturnDetail::class, 'purchase_return_id');
    }

    // Helper method
    public function getPendingQuantityAttribute()
    {
        return $this->details()->sum('quantity');
    }
}
