<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashShiftDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cash_shift_id',
        'order_id',
        'transaction_type',
        'amount',
        'payment_type',
        'description',
        'transaction_time'
    ];

    protected $casts = [
        'transaction_time' => 'datetime',
        'amount' => 'decimal:2'
    ];

    // Relasi
    public function cashShift()
    {
        return $this->belongsTo(CashShift::class, 'cash_shift_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Helper method
    public function isSale()
    {
        return $this->transaction_type === 'sale';
    }

    public function isRefund()
    {
        return $this->transaction_type === 'refund';
    }

    public function isVoid()
    {
        return $this->transaction_type === 'void';
    }

    public function isCashIn()
    {
        return $this->transaction_type === 'cash_in';
    }

    public function isCashOut()
    {
        return $this->transaction_type === 'cash_out';
    }
}
