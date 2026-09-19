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
        'google_maps_url',
        'logo',
        'default_tax_rate',
        'currency',
        'whatsapp_invoice_base_url',
        'whatsapp_payment_instructions',
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
            'whatsapp_payment_instructions' => "TRANSFER PEMBAYARAN :\n\nBRI : 018001104535507\nBNI : 1918990066\nMANDIRI : 1590012252697\n\nAtas Nama : APONG MAMAH HALIMAH",
        ]);
    }
}
