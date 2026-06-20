<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'discount',
        'min_purchase',
        'max_discount',
        'start_date',
        'end_date',
        'max_use',
        'used_count',
        'is_active',
    ];

    protected $casts = [
        'discount' => 'float',
        'min_purchase' => 'integer',
        'max_discount' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'max_use' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Scope active vouchers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now());
    }

    /**
     * Check if voucher can be used.
     */
    public function canUse(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (now()->lt($this->start_date) || now()->gt($this->end_date)) {
            return false;
        }

        if ($this->max_use && $this->used_count >= $this->max_use) {
            return false;
        }

        return true;
    }
}
