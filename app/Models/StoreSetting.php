<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_name',
        'address',
        'phone',
        'logo',
        'default_tax_rate',
        'currency',
    ];

    protected $casts = [
        'default_tax_rate' => 'float',
    ];

    public static function current(): self
    {
        return self::firstOrCreate([], [
            'store_name' => 'POS Shop',
            'currency' => 'IDR',
            'default_tax_rate' => 0,
        ]);
    }
}
