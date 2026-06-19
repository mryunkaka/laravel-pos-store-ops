<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashShift extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'location_id',
        'start_time',
        'end_time',
        'opening_balance',
        'closing_balance',
        'total_sales',
        'total_cash',
        'total_non_cash',
        'total_void',
        'total_refund',
        'status',
        'approved_by',
        'approved_at',
        'notes',
        'closing_notes'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'approved_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'total_cash' => 'decimal:2',
        'total_non_cash' => 'decimal:2',
        'total_void' => 'decimal:2',
        'total_refund' => 'decimal:2'
    ];

    // Relasi
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function details()
    {
        return $this->hasMany(CashShiftDetail::class, 'cash_shift_id');
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    // Scope untuk mendapatkan shift aktif user
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope untuk mendapatkan shift user tertentu
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope untuk mendapatkan shift yang belum ditutup
    public function scopeUnclosed($query)
    {
        return $query->where('status', 'active');
    }
}
