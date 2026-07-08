<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use App\Services\WhatsappNotificationService;

class InvoiceMobileController extends Controller
{
    public function show(string $token, WhatsappNotificationService $whatsapp)
    {
        $order = $whatsapp->orderFromToken($token);

        return view('invoices.mobile', [
            'order' => $order,
            'setting' => StoreSetting::current(),
        ]);
    }
}
