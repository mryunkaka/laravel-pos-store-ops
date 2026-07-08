<?php

namespace App\Services;

use App\Models\Order;
use App\Models\StoreSetting;
use App\Models\WhatsappMessageLog;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsappNotificationService
{
    public function sendTestMessage(string $phone): WhatsappMessageLog
    {
        $setting = StoreSetting::current();
        $normalizedPhone = $this->normalizePhone($phone);
        $message = "Test WhatsApp Bot {$setting->store_name}\n\nJika pesan ini diterima, konfigurasi WhatsApp Cloud API sudah tersambung.";

        $log = WhatsappMessageLog::create([
            'order_id' => null,
            'phone' => $normalizedPhone,
            'status' => 'pending',
            'message' => $message,
        ]);

        if (!$setting->whatsapp_enabled || !$setting->whatsapp_access_token || !$setting->whatsapp_phone_number_id) {
            $log->update([
                'status' => 'failed',
                'error_message' => 'Konfigurasi WhatsApp belum lengkap atau status masih nonaktif.',
            ]);

            return $log;
        }

        return $this->sendText($log, $setting, $normalizedPhone, $message);
    }

    public function sendOrderPaid(Order $order): ?WhatsappMessageLog
    {
        $setting = StoreSetting::current();
        $order->loadMissing(['customer', 'details.product']);

        if (!$setting->whatsapp_enabled || !$setting->whatsapp_access_token || !$setting->whatsapp_phone_number_id) {
            return null;
        }

        if (!$order->customer?->phone) {
            return WhatsappMessageLog::create([
                'order_id' => $order->id,
                'phone' => '-',
                'status' => 'skipped',
                'error_message' => 'Nomor WhatsApp customer kosong.',
            ]);
        }

        $phone = $this->normalizePhone($order->customer->phone);
        $message = $this->buildOrderMessage($order, $setting);

        $log = WhatsappMessageLog::create([
            'order_id' => $order->id,
            'phone' => $phone,
            'status' => 'pending',
            'message' => $message,
        ]);

        return $this->sendText($log, $setting, $phone, $message);
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

    private function buildOrderMessage(Order $order, StoreSetting $setting): string
    {
        $greeting = $this->greeting();
        $customerName = $order->customer->name ?? 'Pelanggan';
        $date = $order->created_at->locale('id')->translatedFormat('l, d/m/Y');
        $status = $order->due_amount <= 0 ? 'LUNAS' : 'BELUM LUNAS';
        $lines = [];

        $lines[] = "{$greeting} {$customerName}";
        $lines[] = '';
        $lines[] = "No. Pesanan anda {$order->invoice_no} pada {$date}";
        $lines[] = '';

        foreach ($order->details as $detail) {
            $product = $detail->product;
            $lines[] = '----------------------------------';
            $lines[] = 'Produk : ' . ($product->name ?? 'Produk');
            $lines[] = 'Bahan : ' . ($product->material ?: ($product->category->name ?? '-'));
            $lines[] = 'Jml. : ' . $detail->quantity;
            $lines[] = 'Harga : ' . $this->money($detail->total);
            $lines[] = 'Ukuran : ' . ($product->print_size ?: '-');
            $lines[] = 'Keterangan : ' . ($product->print_notes ?: '-');
        }

        $lines[] = '----------------------------------';
        $lines[] = '';
        $lines[] = 'TOTAL ORDER : ' . $this->money($order->total);
        $lines[] = 'TOTAL BAYAR : ' . $this->money($order->pay_amount);
        $lines[] = 'SISA PEMBAYARAN : ' . $this->money(max($order->due_amount, 0));
        $lines[] = '';
        $lines[] = "Status Pembayaran : {$status}";
        $lines[] = '';
        $lines[] = 'Untuk Nota/Invoice order klik link berikut :';
        $lines[] = $this->invoiceUrl($order);

        if ($setting->whatsapp_payment_instructions) {
            $lines[] = '';
            $lines[] = trim($setting->whatsapp_payment_instructions);
        }

        return implode("\n", $lines);
    }

    private function endpoint(StoreSetting $setting): string
    {
        $version = $setting->whatsapp_api_version ?: 'v20.0';

        return "https://graph.facebook.com/{$version}/{$setting->whatsapp_phone_number_id}/messages";
    }

    private function sendText(WhatsappMessageLog $log, StoreSetting $setting, string $phone, string $message): WhatsappMessageLog
    {
        try {
            $response = Http::withToken($setting->whatsapp_access_token)
                ->timeout(10)
                ->acceptJson()
                ->post($this->endpoint($setting), [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $phone,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => true,
                        'body' => $message,
                    ],
                ]);

            $payload = $response->json() ?: ['body' => $response->body()];

            if ($response->successful()) {
                $log->update([
                    'status' => 'sent',
                    'message_id' => $payload['messages'][0]['id'] ?? null,
                    'response_payload' => $payload,
                    'sent_at' => now(),
                ]);
            } else {
                $log->update([
                    'status' => 'failed',
                    'error_message' => $payload['error']['message'] ?? $response->body(),
                    'response_payload' => $payload,
                ]);
            }
        } catch (\Throwable $exception) {
            $log->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);

            Log::warning('WhatsApp message failed', [
                'order_id' => $log->order_id,
                'phone' => $phone,
                'error' => $exception->getMessage(),
            ]);
        }

        return $log;
    }

    private function invoiceToken(Order $order): string
    {
        return rtrim(strtr(base64_encode(Crypt::encryptString((string) $order->id)), '+/', '-_'), '=');
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (Str::startsWith($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        return $digits;
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

    private function money(float $amount): string
    {
        return number_format($amount, 0, ',', '.');
    }
}
