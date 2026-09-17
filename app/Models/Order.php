<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id',
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

    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function details()
    {
        return $this->hasMany(OrderDetails::class);
    }

    public function salesReturns()
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function payments()
    {
        return $this->hasMany(CashShiftDetail::class)
            ->where('transaction_type', 'sale')
            ->orderBy('transaction_time')
            ->orderBy('id');
    }

    public function paymentHistoryText(): string
    {
        $payments = $this->relationLoaded('payments')
            ? $this->payments
            : $this->payments()->get();

        if ($payments->isEmpty()) {
            return $this->payment_type ?? '-';
        }

        $labels = [
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            'debit' => 'Debit',
            'transfer' => 'Transfer',
            'ewallet' => 'E-Wallet',
        ];
        $dueIndex = 0;
        $lastDueId = $payments
            ->filter(fn ($payment) => str_contains(strtolower($payment->description ?? ''), 'piutang'))
            ->last()?->id;

        $parts = $payments->map(function ($payment) use ($labels, &$dueIndex, $lastDueId) {
            $isDuePayment = str_contains(strtolower($payment->description ?? ''), 'piutang');
            $label = $isDuePayment ? 'Piutang ' . (++$dueIndex) : ($labels[$payment->payment_type] ?? ucfirst($payment->payment_type));
            $suffix = $isDuePayment && $payment->id === $lastDueId && $this->due_amount <= 0 ? ' (Lunas)' : '';

            return $label . ' Rp ' . number_format((float) $payment->amount, 0, ',', '.') . $suffix;
        });

        $missingPaidHistory = max((float) $this->pay_amount - (float) $payments->sum('amount'), 0);
        if ($missingPaidHistory > 0) {
            $parts->push('Piutang ' . (++$dueIndex) . ' Rp ' . number_format($missingPaidHistory, 0, ',', '.') . ($this->due_amount <= 0 ? ' (Lunas)' : ''));
        }

        return $parts->implode(', ');
    }
}
