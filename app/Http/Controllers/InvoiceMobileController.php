<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use App\Services\WhatsappNotificationService;

class InvoiceMobileController extends Controller
{
    public function show(string $token, WhatsappNotificationService $whatsapp)
    {
        try {
            $order = $whatsapp->orderFromToken($token);
        } catch (\Throwable) {
            abort(404);
        }

        abort_unless($order->order_status === 'complete', 404);

        return view('invoices.mobile', [
            'order' => $order,
            'setting' => StoreSetting::current(),
        ]);
    }
}
