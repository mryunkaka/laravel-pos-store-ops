<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashClosingDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cash_closing_id',
        'cash_shift_id',
        'shift_sales',
        'shift_cash',
        'shift_non_cash',
        'shift_void',
        'shift_refund',
        'shift_cash_expected',
        'shift_cash_actual',
        'shift_cash_difference'
    ];

    protected $casts = [
        'shift_sales' => 'decimal:2',
        'shift_cash' => 'decimal:2',
        'shift_non_cash' => 'decimal:2',
        'shift_void' => 'decimal:2',
        'shift_refund' => 'decimal:2',
        'shift_cash_expected' => 'decimal:2',
        'shift_cash_actual' => 'decimal:2',
        'shift_cash_difference' => 'decimal:2'
    ];

    // Relasi
    public function cashClosing()
    {
        return $this->belongsTo(CashClosing::class, 'cash_closing_id');
    }

    public function cashShift()
    {
        return $this->belongsTo(CashShift::class, 'cash_shift_id');
    }
}
