<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use App\Services\WhatsappNotificationService;

class InvoiceMobileController extends Controller
{
    public function show(string $token, WhatsappNotificationService $whatsapp)
    {
        $order = $whatsapp->orderFromToken($token);
        abort_unless($order->order_status === 'complete', 404);

        return view('invoices.mobile', [
            'order' => $order,
            'setting' => StoreSetting::current(),
        ]);
    }
}
