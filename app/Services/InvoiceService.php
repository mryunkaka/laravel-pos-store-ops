<?php

namespace App\Services;

use App\Models\Order;
use App\Models\StoreSetting;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class InvoiceService
{
    public function __construct(private readonly Tmp0Service $tmp0) {}

    public function process(Order $order): array
    {
        if ($this->hasValidUpload($order)) {
            return [
                'success' => true,
                'message' => 'Invoice sudah tersedia.',
                'url' => $order->invoice_url,
            ];
        }

        try {
            $this->generate($order);

            return $this->upload($order);
        } catch (Throwable $exception) {
            Log::warning('Invoice processing failed', [
                'order_id' => $order->id,
                'invoice_no' => $order->invoice_no,
                'error' => $exception->getMessage(),
            ]);

            try {
                $order->update([
                    'invoice_upload_status' => 'failed',
                    'invoice_error' => 'Invoice belum berhasil diproses.',
                ]);
            } catch (Throwable $updateException) {
                Log::warning('Invoice failure status could not be saved', [
                    'order_id' => $order->id,
                    'error' => $updateException->getMessage(),
                ]);
            }

            return [
                'success' => false,
                'message' => 'Invoice belum berhasil diproses.',
            ];
        }
    }

    public function paymentConfirmed(Order $order): bool
    {
        return ! in_array($order->order_status, ['cancelled', 'void'], true)
            && (float) $order->pay_amount > 0;
    }

    public function generate(Order $order): array
    {
        if (! $this->paymentConfirmed($order)) {
            throw new \RuntimeException('Invoice hanya dapat dibuat setelah konfirmasi pembayaran.');
        }

        $disk = Storage::disk('local');
        if ($order->invoice_pdf_path && $disk->exists($order->invoice_pdf_path)) {
            if ($order->invoice_upload_status === null) {
                $order->update(['invoice_upload_status' => 'generated']);
            }

            return [
                'success' => true,
                'path' => $order->invoice_pdf_path,
                'created' => false,
            ];
        }

        $order->loadMissing(['customer', 'cashier', 'details.product', 'payments']);
        $setting = StoreSetting::current();
        $filename = $this->filename($order);
        $path = 'invoices/'.$filename;
        $pdf = $this->renderPdf($order, $setting);

        if (! $disk->put($path, $pdf)) {
            throw new \RuntimeException('PDF invoice tidak dapat disimpan.');
        }

        $order->update([
            'invoice_pdf_path' => $path,
            'invoice_upload_status' => 'generated',
            'invoice_file_id' => null,
            'invoice_url' => null,
            'invoice_expires_at' => null,
            'invoice_generated_at' => now(),
            'invoice_uploaded_at' => null,
            'invoice_error' => null,
        ]);

        try {
            AuditService::log('invoice', 'generate', $order, null, [
                'invoice_pdf_path' => $path,
            ], "Invoice {$order->invoice_no} PDF generated");
        } catch (Throwable $exception) {
            Log::warning('Invoice generate audit log failed', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);
        }

        Log::info('Invoice PDF generated', [
            'order_id' => $order->id,
            'invoice_no' => $order->invoice_no,
            'path' => $path,
        ]);

        return [
            'success' => true,
            'path' => $path,
            'created' => true,
        ];
    }

    public function hasValidUpload(Order $order): bool
    {
        $parts = parse_url(trim((string) $order->invoice_url));

        return $order->invoice_upload_status === 'uploaded'
            && ($parts['scheme'] ?? '') === 'https'
            && ($parts['host'] ?? '') === 'tmp0.cc'
            && preg_match('#^/d/[A-Za-z0-9_-]+$#', $parts['path'] ?? '') === 1
            && ! isset($parts['query'], $parts['fragment'])
            && $order->invoice_expires_at?->isFuture();
    }

    public function upload(Order $order): array
    {
        if (! $this->paymentConfirmed($order)) {
            return [
                'success' => false,
                'message' => 'Invoice hanya tersedia setelah konfirmasi pembayaran.',
            ];
        }

        $wasFailed = $order->invoice_upload_status === 'failed';
        $generated = $this->generate($order);
        $path = $generated['path'];
        $order->refresh();
        $order->update([
            'invoice_upload_status' => 'pending',
            'invoice_file_id' => null,
            'invoice_url' => null,
            'invoice_expires_at' => null,
            'invoice_uploaded_at' => null,
            'invoice_error' => null,
        ]);

        Log::info('Invoice upload started', [
            'order_id' => $order->id,
            'invoice_no' => $order->invoice_no,
            'retry' => $wasFailed,
        ]);

        $result = $this->tmp0->upload(
            Storage::disk('local')->path($path),
            $this->filename($order)
        );

        if (! $result['success']) {
            $order->update([
                'invoice_upload_status' => 'failed',
                'invoice_error' => $result['message'],
            ]);

            try {
                AuditService::log('invoice', 'upload_failed', $order, null, [
                    'invoice_upload_status' => 'failed',
                ], "Invoice {$order->invoice_no} upload failed");
            } catch (Throwable $exception) {
                Log::warning('Invoice failure audit log failed', [
                    'order_id' => $order->id,
                    'error' => $exception->getMessage(),
                ]);
            }

            return $result;
        }

        $order->update([
            'invoice_upload_status' => 'uploaded',
            'invoice_file_id' => $result['file_id'],
            'invoice_url' => $result['url'],
            'invoice_expires_at' => $result['expires_at'],
            'invoice_uploaded_at' => now(),
            'invoice_error' => null,
        ]);

        try {
            AuditService::log('invoice', 'upload_success', $order, null, [
                'invoice_file_id' => $result['file_id'],
                'invoice_url' => $result['url'],
            ], "Invoice {$order->invoice_no} uploaded");
        } catch (Throwable $exception) {
            Log::warning('Invoice audit log failed', ['order_id' => $order->id, 'error' => $exception->getMessage()]);
        }

        Log::info('Invoice upload success', [
            'order_id' => $order->id,
            'invoice_no' => $order->invoice_no,
            'file_id' => $result['file_id'],
            'url' => $result['url'],
        ]);

        return $result;
    }

    public function localPath(Order $order): ?string
    {
        if (! $order->invoice_pdf_path) {
            return null;
        }

        $path = Storage::disk('local')->path($order->invoice_pdf_path);

        return is_file($path) ? $path : null;
    }

    private function renderPdf(Order $order, StoreSetting $setting): string
    {
        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('invoices.pdf', [
            'order' => $order,
            'setting' => $setting,
            'logoDataUri' => $this->logoDataUri($setting),
        ])->render(), 'UTF-8');
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function logoDataUri(StoreSetting $setting): ?string
    {
        $path = $setting->logo
            ? Storage::disk('public')->path($setting->logo)
            : public_path('assets/images/logo.png');
        if (! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
    }

    private function filename(Order $order): string
    {
        $invoiceNo = preg_replace('/[^A-Za-z0-9._-]/', '-', (string) $order->invoice_no);

        return 'invoice-'.trim($invoiceNo, '-').'.pdf';
    }
}
