<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'adjustment_number',
        'adjustment_date',
        'type',
        'quantity',
        'old_stock',
        'new_stock',
        'reason',
        'status',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'quantity' => 'integer',
        'old_stock' => 'integer',
        'new_stock' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getAdjustmentTypeAttribute()
    {
        return $this->type === 'in' ? 'increase' : 'decrease';
    }
}
