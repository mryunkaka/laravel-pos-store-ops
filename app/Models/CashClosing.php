<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashClosing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'location_id',
        'closing_date',
        'closing_time',
        'closed_at',
        'total_sales',
        'total_cash',
        'total_non_cash',
        'total_void',
        'total_refund',
        'total_due',
        'cash_expected',
        'cash_actual',
        'cash_difference',
        'status',
        'approved_by',
        'approved_at',
        'notes'
    ];

    protected $casts = [
        'closing_date' => 'date',
        'closing_time' => 'datetime',
        'closed_at' => 'datetime',
        'total_sales' => 'decimal:2',
        'total_cash' => 'decimal:2',
        'total_non_cash' => 'decimal:2',
        'total_void' => 'decimal:2',
        'total_refund' => 'decimal:2',
        'total_due' => 'decimal:2',
        'cash_expected' => 'decimal:2',
        'cash_actual' => 'decimal:2',
        'cash_difference' => 'decimal:2'
    ];

    // Relasi
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function details()
    {
        return $this->hasMany(CashClosingDetail::class, 'cash_closing_id');
    }

    // Helper methods
    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function isVerified()
    {
        return $this->status === 'verified';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    // Scope untuk mendapatkan closing tertentu
    public function scopeForDate($query, $date)
    {
        return $query->where('closing_date', $date);
    }

    // Scope untuk mendapatkan closing yang sudah diverifikasi
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }
}
