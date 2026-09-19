<?php

namespace App\Services;

use App\Models\Order;
use App\Models\StoreSetting;
use App\Models\WhatsappMessageLog;
use Illuminate\Support\Facades\Crypt;

class WhatsappNotificationService
{
    public function sendTestMessage(string $phone): WhatsappMessageLog
    {
        $normalizedPhone = $this->normalizePhone($phone);
        $message = 'Test WhatsApp manual dari ' . StoreSetting::current()->store_name;
        $url = $normalizedPhone ? $this->manualChatUrl($normalizedPhone, $message) : null;

        return WhatsappMessageLog::create([
            'order_id' => null,
            'phone' => $normalizedPhone ?: '-',
            'status' => $url ? 'manual' : 'skipped',
            'message' => $message,
            'response_payload' => $url ? ['mode' => 'manual', 'url' => $url] : null,
            'error_message' => $url ? null : 'Nomor WhatsApp tidak valid.',
        ]);
    }

    public function sendOrderPaid(Order $order): ?WhatsappMessageLog
    {
        $order->loadMissing(['customer', 'details.product.category', 'payments']);
        $url = $this->manualUrl($order);

        if (!$url) {
            return WhatsappMessageLog::create([
                'order_id' => $order->id,
                'phone' => $this->normalizePhone((string) ($order->customer?->phone ?? '')) ?: '-',
                'status' => 'skipped',
                'error_message' => $order->customer?->phone
                    ? 'URL invoice belum tersedia.'
                    : 'Nomor WhatsApp customer kosong atau tidak valid.',
            ]);
        }

        $log = WhatsappMessageLog::create([
            'order_id' => $order->id,
            'phone' => $this->normalizePhone((string) $order->customer->phone),
            'status' => 'manual',
            'message' => $this->buildOrderMessage($order, $order->invoice_url),
            'response_payload' => ['mode' => 'manual', 'url' => $url],
        ]);

        try {
            AuditService::log('invoice', 'whatsapp_manual', $order, null, [
                'phone' => $log->phone,
            ], "Manual WhatsApp link prepared for {$order->invoice_no}");
        } catch (\Throwable) {
            // Message log remains source of truth if audit storage is unavailable.
        }

        return $log;
    }

    public function sendOrderText(Order $order, ?StoreSetting $setting = null): ?WhatsappMessageLog
    {
        $order->loadMissing(['customer', 'details.product.category', 'payments']);
        $url = $this->manualTextUrl($order, $setting);

        if (!$url) {
            return WhatsappMessageLog::create([
                'order_id' => $order->id,
                'phone' => $this->normalizePhone((string) ($order->customer?->phone ?? '')) ?: '-',
                'status' => 'skipped',
                'error_message' => 'Nomor WhatsApp customer kosong atau tidak valid.',
            ]);
        }

        $message = $this->buildOrderMessage($order, null, $setting);
        $log = WhatsappMessageLog::create([
            'order_id' => $order->id,
            'phone' => $this->normalizePhone((string) $order->customer->phone),
            'status' => 'manual',
            'message' => $message,
            'response_payload' => ['mode' => 'manual_text', 'url' => $url],
        ]);

        try {
            AuditService::log('invoice', 'whatsapp_manual_text', $order, null, [
                'phone' => $log->phone,
            ], "Manual WhatsApp text link prepared for {$order->invoice_no}");
        } catch (\Throwable) {
            // Message log remains source of truth if audit storage is unavailable.
        }

        return $log;
    }

    public function manualUrl(Order $order, ?StoreSetting $setting = null): ?string
    {
        $order->loadMissing(['customer', 'details.product.category', 'payments']);
        $phone = $this->normalizePhone((string) ($order->customer?->phone ?? ''));
        $invoiceUrl = trim((string) $order->invoice_url);

        if (!$phone || !$this->isTmp0DocumentUrl($invoiceUrl)
            || $order->invoice_upload_status !== 'uploaded'
            || ($order->invoice_expires_at && $order->invoice_expires_at->isPast())) {
            return null;
        }

        return $this->manualChatUrl($phone, $this->buildOrderMessage($order, $invoiceUrl, $setting));
    }

    public function manualTextUrl(Order $order, ?StoreSetting $setting = null): ?string
    {
        $order->loadMissing(['customer', 'details.product.category', 'payments']);
        $phone = $this->normalizePhone((string) ($order->customer?->phone ?? ''));

        if (!$phone) {
            return null;
        }

        return $this->manualChatUrl($phone, $this->buildOrderMessage($order, null, $setting));
    }

    public function buildOrderMessage(Order $order, ?string $invoiceUrl = null, ?StoreSetting $setting = null): string
    {
        $setting ??= StoreSetting::current();
        $customerName = trim((string) ($order->customer?->name ?? 'Pelanggan')) ?: 'Pelanggan';
        $orderDate = ($order->order_date ?: $order->created_at)->locale('id')->translatedFormat('l, d/m/Y');
        $status = $order->due_amount <= 0 ? 'LUNAS' : 'BELUM LUNAS';
        $lines = [
            $this->greeting() . ' ' . $customerName,
            '',
            "No. Pesanan anda {$order->invoice_no} pada {$orderDate}",
            '',
        ];

        foreach ($order->details as $detail) {
            $product = $detail->product;
            $lines[] = '----------------------------------';
            $lines[] = 'Produk : ' . ($product?->name ?: 'Produk');
            $lines[] = 'Bahan : ' . ($product?->material ?: ($product?->category?->name ?: ''));
            $lines[] = 'Jml. : ' . $detail->quantity;
            $lines[] = 'Harga : ' . $this->money($detail->unit_price);
            $lines[] = 'Ukuran : ' . ($product?->print_size ?: '');
            $lines[] = 'Keterangan : ' . ($product?->print_notes ?: '');
        }

        $lines[] = '----------------------------------';
        $lines[] = '';
        $lines[] = 'TOTAL ORDER : ' . $this->money($order->total);
        $lines[] = 'TOTAL BAYAR : ' . $this->money($order->pay_amount);
        $lines[] = 'SISA PEMBAYARAN : ' . $this->money(max($order->due_amount, 0));
        $lines[] = '';
        $lines[] = 'Status Pembayaran : ' . $status;

        if ($invoiceUrl) {
            $lines[] = '';
            $lines[] = 'Untuk Nota/Invoice order klik link berikut :';
            $lines[] = $invoiceUrl;
        }

        if (trim((string) $setting->whatsapp_payment_instructions) !== '') {
            $lines[] = '';
            $lines[] = trim($setting->whatsapp_payment_instructions);
        }

        return implode("\n", $lines);
    }

    public function invoiceUrl(Order $order): string
    {
        $setting = StoreSetting::current();
        $baseUrl = rtrim($setting->whatsapp_invoice_base_url ?: config('app.url'), '/');

        return $baseUrl . '/e-invoice-mobile/' . $this->invoiceToken($order);
    }

    public function orderFromToken(string $token): Order
    {
        $base64 = strtr($token, '-_', '+/');
        $encrypted = base64_decode(str_pad($base64, strlen($base64) + (4 - strlen($base64) % 4) % 4, '=', STR_PAD_RIGHT));
        $orderId = Crypt::decryptString($encrypted);

        return Order::with(['customer', 'details.product'])->findOrFail($orderId);
    }

    public function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . ltrim($digits, '0');
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return preg_match('/^628\d{7,13}$/', $digits) ? $digits : null;
    }

    private function isTmp0DocumentUrl(string $url): bool
    {
        $parts = parse_url($url);

        return ($parts['scheme'] ?? '') === 'https'
            && ($parts['host'] ?? '') === 'tmp0.cc'
            && preg_match('#^/d/[A-Za-z0-9_-]+$#', $parts['path'] ?? '') === 1
            && !isset($parts['query'])
            && !isset($parts['fragment']);
    }

    private function manualChatUrl(string $phone, string $message): string
    {
        return 'https://api.whatsapp.com/send/?' . http_build_query([
            'phone' => $phone,
            'text' => $message,
            'type' => 'phone_number',
            'app_absent' => '0',
        ]);
    }

    private function invoiceToken(Order $order): string
    {
        return rtrim(strtr(base64_encode(Crypt::encryptString((string) $order->id)), '+/', '-_'), '=');
    }

    private function greeting(): string
    {
        $hour = (int) now()->format('H');

        return match (true) {
            $hour < 11 => 'Selamat Pagi',
            $hour < 15 => 'Selamat Siang',
            $hour < 18 => 'Selamat Sore',
            default => 'Selamat Malam',
        };
    }

    private function money(float|int|string|null $amount): string
    {
        return number_format((float) $amount, 0, ',', '.');
    }
}