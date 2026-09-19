<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\InvoiceService;
use App\Services\WhatsappNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function pdf(int $order_id, InvoiceService $invoice): Response|RedirectResponse
    {
        $order = Order::findOrFail($order_id);
        if (! $invoice->paymentConfirmed($order)) {
            return redirect()->route('order.orderDetails', $order->id)
                ->with('error', 'Invoice hanya tersedia setelah konfirmasi pembayaran.');
        }

        try {
            $invoice->generate($order);
            $path = $invoice->localPath($order->refresh());
            if ($path === null) {
                return redirect()->route('order.orderDetails', $order->id)
                    ->with('error', 'File PDF invoice belum tersedia.');
            }

            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="invoice-'.$this->safeFilename($order->invoice_no).'.pdf"',
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Local invoice PDF request failed', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->route('order.orderDetails', $order->id)
                ->with('error', 'PDF invoice belum berhasil dibuat.');
        }
    }

    public function generate(int $order_id, InvoiceService $invoice): RedirectResponse
    {
        $order = Order::findOrFail($order_id);
        if (! $invoice->paymentConfirmed($order)) {
            return redirect()->route('order.orderDetails', $order->id)
                ->with('error', 'Invoice hanya tersedia setelah konfirmasi pembayaran.');
        }

        try {
            $invoice->generate($order);

            return redirect()->route('order.orderDetails', $order->id)
                ->with('success', 'PDF invoice berhasil dibuat.');
        } catch (\Throwable $exception) {
            Log::warning('Invoice PDF generation failed', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->route('order.orderDetails', $order->id)
                ->with('error', 'PDF invoice belum berhasil dibuat.');
        }
    }

    public function upload(int $order_id, InvoiceService $invoice): RedirectResponse
    {
        $order = Order::findOrFail($order_id);
        if (! $invoice->paymentConfirmed($order)) {
            return redirect()->route('order.orderDetails', $order->id)
                ->with('error', 'Invoice hanya tersedia setelah konfirmasi pembayaran.');
        }

        $isRetry = $order->invoice_upload_status === 'failed';

        try {
            $result = $invoice->upload($order);
            if ($result['success']) {
                return redirect()->route('order.orderDetails', $order->id)
                    ->with('success', $isRetry ? 'Invoice berhasil di-upload ulang.' : 'Invoice berhasil di-upload.');
            }

            return redirect()->route('order.orderDetails', $order->id)
                ->with('error', 'Transaksi tetap tersimpan, tetapi invoice belum berhasil di-upload.');
        } catch (\Throwable $exception) {
            Log::warning('Invoice upload request failed', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);

            $order->update([
                'invoice_upload_status' => 'failed',
                'invoice_error' => 'Invoice belum berhasil di-upload.',
            ]);

            return redirect()->route('order.orderDetails', $order->id)
                ->with('error', 'Transaksi tetap tersimpan, tetapi invoice belum berhasil di-upload.');
        }
    }

    public function whatsapp(
        int $order_id,
        InvoiceService $invoice,
        WhatsappNotificationService $whatsapp
    ): RedirectResponse {
        $order = Order::with(['customer', 'details.product', 'payments'])->findOrFail($order_id);
        $details = fn (string $message) => redirect()->route('order.orderDetails', $order->id)
            ->with('error', $message);

        if (! $invoice->paymentConfirmed($order)) {
            return $details('WhatsApp invoice hanya tersedia setelah konfirmasi pembayaran.');
        }

        if (! $whatsapp->manualTextUrl($order)) {
            return $details('Nomor WhatsApp pelanggan belum tersedia atau tidak valid.');
        }

        if (! $whatsapp->manualUrl($order)) {
            try {
                $result = $invoice->upload($order);
            } catch (\Throwable $exception) {
                Log::warning('Invoice upload for WhatsApp request failed', [
                    'order_id' => $order->id,
                    'error' => $exception->getMessage(),
                ]);

                $order->update([
                    'invoice_upload_status' => 'failed',
                    'invoice_error' => 'Invoice belum berhasil di-upload.',
                ]);

                return $details('Transaksi tetap tersimpan, tetapi invoice belum berhasil di-upload.');
            }

            if (data_get($result, 'success') !== true) {
                return $details('Transaksi tetap tersimpan, tetapi invoice belum berhasil di-upload.');
            }

            $order->refresh();
        }

        $log = $whatsapp->sendOrderPaid($order);
        $url = data_get($log, 'response_payload.url');

        if (! $log || $log->status !== 'manual' || ! $url) {
            return $details('Invoice belum tersedia atau nomor WhatsApp tidak valid.');
        }

        return redirect()->away($url);
    }

    public function whatsappText(int $order_id, WhatsappNotificationService $whatsapp): RedirectResponse
    {
        $order = Order::with(['customer', 'details.product', 'payments'])->findOrFail($order_id);
        if (in_array($order->order_status, ['cancelled', 'void'], true)) {
            return redirect()->route('order.printReceipt', $order->id)
                ->with('error', 'WhatsApp tidak tersedia untuk order yang dibatalkan atau di-void.');
        }

        $log = $whatsapp->sendOrderText($order);
        $url = data_get($log, 'response_payload.url');

        if (! $log || $log->status !== 'manual' || ! $url) {
            return redirect()->route('order.printReceipt', $order->id)
                ->with('error', 'Nomor WhatsApp pelanggan belum tersedia atau tidak valid.');
        }

        return redirect()->away($url);
    }

    private function safeFilename(string $invoiceNo): string
    {
        return trim((string) preg_replace('/[^A-Za-z0-9._-]/', '-', $invoiceNo), '-');
    }
}
