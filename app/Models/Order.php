<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_date',
        'order_status',
        'cancel_reason',
        'void_reason',
        'voided_by',
        'voided_at',
        'cancelled_by',
        'cancelled_at',
        'total_products',
        'sub_total',
        'discount',
        'discount_type',
        'service_charge',
        'tax_total',
        'tax_type',
        'invoice_no',
        'total',
        'payment_type',
        'pay_amount',
        'due_amount',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'sub_total' => 'float',
        'discount' => 'float',
        'discount_type' => 'string',
        'service_charge' => 'float',
        'tax_total' => 'float',
        'tax_type' => 'string',
        'total' => 'float',
        'pay_amount' => 'float',
        'due_amount' => 'float',
        'voided_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Check if order is in a final state (cannot be modified).
     */
    public function isFinalized(): bool
    {
        return in_array($this->order_status, ['complete', 'cancelled', 'void']);
    }

    /**
     * Check if order can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return $this->order_status === 'pending';
    }

    /**
     * Check if order can be voided.
     */
    public function canBeVoided(): bool
    {
        return $this->order_status === 'complete';
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function details()
    {
        return $this->hasMany(OrderDetails::class);
    }

    public function salesReturns()
    {
        return $this->hasMany(SalesReturn::class);
    }
}
