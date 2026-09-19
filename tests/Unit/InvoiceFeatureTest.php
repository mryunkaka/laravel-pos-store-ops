<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Services\InvoiceService;
use App\Services\Tmp0Service;
use App\Services\WhatsappNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InvoiceFeatureTest extends TestCase
{
    public function test_indonesian_phone_is_normalized_once(): void
    {
        $service = new WhatsappNotificationService;

        self::assertSame('6281234567890', $service->normalizePhone('0812 3456-7890'));
        self::assertSame('6281234567890', $service->normalizePhone('+62 (812) 3456-7890'));
        self::assertSame('6281234567890', $service->normalizePhone('6281234567890'));
        self::assertNull($service->normalizePhone('626281234567890'));
    }

    public function test_manual_whatsapp_url_contains_encoded_dynamic_message(): void
    {
        [$order, $setting] = $this->sampleOrder();
        $service = new WhatsappNotificationService;

        $url = $service->manualUrl($order, $setting);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $message = $query['text'] ?? '';

        self::assertStringStartsWith('https://api.whatsapp.com/send/?phone=6281234567890&text=', $url);
        self::assertStringContainsString('&type=phone_number&app_absent=0', $url);
        self::assertStringContainsString('Padli', $message);
        self::assertStringContainsString('Rabu, 13/05/2026', $message);
        self::assertStringContainsString('https://tmp0.cc/d/AbC123', $message);
        self::assertStringContainsString('Produk : Banner', $message);
    }

    public function test_manual_text_url_omits_invoice_link(): void
    {
        [$order, $setting] = $this->sampleOrder();
        $order->invoice_upload_status = 'failed';
        $order->invoice_url = null;

        $url = (new WhatsappNotificationService)->manualTextUrl($order, $setting);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $message = $query['text'] ?? '';

        self::assertStringStartsWith('https://api.whatsapp.com/send/?phone=6281234567890&text=', $url);
        self::assertStringContainsString('&type=phone_number&app_absent=0', $url);
        self::assertStringContainsString('Produk : Banner', $message);
        self::assertStringNotContainsString('https://tmp0.cc/d/AbC123', $message);
    }

    public function test_receipt_invoice_whatsapp_action_is_always_active(): void
    {
        [$order] = $this->sampleOrder();
        $order->id = 14;

        $view = $this->view('pos.print-receipt', [
            'order' => $order,
            'orderDetails' => $order->details,
        ]);

        $view->assertSee(route('order.invoiceWhatsapp', 14), false);
        $view->assertSee('Kirim WhatsApp + Invoice PDF');
        $view->assertDontSee('title="Invoice PDF belum tersedia atau link tmp0.cc error"', false);
        $view->assertDontSee('onclick="window.open', false);
    }

    public function test_invoice_whatsapp_error_does_not_redirect_to_receipt(): void
    {
        [$order] = $this->sampleOrder();
        $order->id = 15;
        $order->order_status = 'pending';

        $view = $this->view('pos.print-receipt', [
            'order' => $order,
            'orderDetails' => $order->details,
        ]);

        $view->assertSee(route('order.invoiceWhatsapp', 15), false);
        $view->assertDontSee(route('order.printReceipt', 15), false);
    }

    public function test_pos_checkout_opens_receipt_before_invoice_delivery(): void
    {
        $source = (string) file_get_contents(resource_path('views/pos/index.blade.php'));

        self::assertStringContainsString("const receiptWindow = window.open('about:blank', '_blank');", $source);
        self::assertStringContainsString('receiptWindow.location.href = data.receipt_url;', $source);
        self::assertStringContainsString('Memproses struk...', $source);
        self::assertStringNotContainsString('data.invoice_whatsapp_url', $source);
    }

    public function test_order_completion_does_not_start_invoice_delivery_automatically(): void
    {
        $source = (string) file_get_contents(app_path('Http/Controllers/Dashboard/OrderController.php'));

        self::assertStringNotContainsString('ProcessCompletedOrderInvoice::dispatch', $source);
        self::assertStringNotContainsString("use App\\Jobs\\ProcessCompletedOrderInvoice;", $source);
    }

    public function test_paid_pending_order_can_start_invoice_delivery_before_completion(): void
    {
        [$order] = $this->sampleOrder();
        $order->order_status = 'pending';
        $order->pay_amount = 20000;
        $order->due_amount = -10000;

        $invoice = new InvoiceService(new Tmp0Service);

        self::assertTrue($invoice->paymentConfirmed($order));
    }

    public function test_unpaid_or_cancelled_order_cannot_start_invoice_delivery(): void
    {
        [$order] = $this->sampleOrder();
        $invoice = new InvoiceService(new Tmp0Service);

        $order->pay_amount = 0;
        self::assertFalse($invoice->paymentConfirmed($order));

        $order->pay_amount = 20000;
        $order->order_status = 'cancelled';
        self::assertFalse($invoice->paymentConfirmed($order));
    }

    public function test_invoice_whatsapp_route_is_dedicated_from_receipt_route(): void
    {
        $route = app('router')->getRoutes()->getByName('order.invoiceWhatsapp');

        self::assertSame('orders/invoice/whatsapp/{order_id}', $route->uri());
        self::assertSame('App\\Http\\Controllers\\Dashboard\\InvoiceController@whatsapp', $route->getActionName());
        self::assertContains('permission:orders.menu', $route->gatherMiddleware());
    }

    public function test_invoice_pdf_renderer_returns_a4_pdf_bytes(): void
    {
        [$order, $setting] = $this->sampleOrder();
        $service = new InvoiceService(new Tmp0Service);
        $renderer = new \ReflectionMethod($service, 'renderPdf');
        $renderer->setAccessible(true);

        $pdf = $renderer->invoke($service, $order, $setting);

        self::assertStringStartsWith('%PDF-', $pdf);
        self::assertGreaterThan(1000, strlen($pdf));
    }

    public function test_tmp0_upload_accepts_document_response_and_uses_30_day_expiration(): void
    {
        Config::set('services.tmp0.upload_url', 'https://tmp0.cc/api/v1/upload');
        Config::set('services.tmp0.expiration', '30d');
        Config::set('services.tmp0.timeout', 5);

        Http::fake(['*' => Http::response([
            'success' => true,
            'fileId' => 'AbC123',
            'url' => '/d/AbC123',
            'fullUrl' => 'https://tmp0.cc/d/AbC123',
            'fileInfo' => ['expires' => '30d'],
        ], 200)]);

        $path = tempnam(sys_get_temp_dir(), 'invoice-').'.pdf';
        file_put_contents($path, "%PDF-1.4\n%%EOF\n");

        try {
            $result = (new Tmp0Service)->upload($path, 'invoice-TRX-1.pdf');
        } finally {
            @unlink($path);
        }

        self::assertTrue($result['success'], json_encode($result));
        self::assertSame('https://tmp0.cc/d/AbC123', $result['url']);
        self::assertSame('AbC123', $result['file_id']);
        self::assertSame(1, Http::recorded()->count());
        Http::assertSent(function (Request $request) {
            return str_contains($request->header('Content-Type')[0] ?? '', 'multipart/form-data')
                && str_contains($request->body(), 'name="expires"')
                && str_contains($request->body(), '30d');
        });
        self::assertContains($result['expires_at']->diffInDays(now()), [29, 30]);
    }

    public function test_tmp0_http_failure_returns_safe_failure_result(): void
    {
        Config::set('services.tmp0.upload_url', 'https://tmp0.cc/api/v1/upload');
        Config::set('services.tmp0.expiration', '30d');
        Http::fake(['*' => Http::response(['success' => false], 503)]);

        $path = tempnam(sys_get_temp_dir(), 'invoice-').'.pdf';
        file_put_contents($path, "%PDF-1.4\n%%EOF\n");

        try {
            $result = (new Tmp0Service)->upload($path, 'invoice-TRX-1.pdf');
        } finally {
            @unlink($path);
        }

        self::assertFalse($result['success']);
        self::assertSame('tmp0.cc menolak upload invoice.', $result['message']);
    }

    public function test_tmp0_rejects_invalid_pdf_without_uploading(): void
    {
        Config::set('services.tmp0.upload_url', 'https://tmp0.cc/api/v1/upload');
        Http::fake();

        $path = tempnam(sys_get_temp_dir(), 'invoice-').'.pdf';
        file_put_contents($path, 'not-a-pdf');

        try {
            $result = (new Tmp0Service)->upload($path, 'invoice-TRX-1.pdf');
        } finally {
            @unlink($path);
        }

        self::assertFalse($result['success']);
        self::assertSame('File invoice bukan PDF yang valid.', $result['message']);
        Http::assertNothingSent();
    }

    public function test_manual_whatsapp_url_is_not_created_without_invoice_url(): void
    {
        [$order, $setting] = $this->sampleOrder();
        $order->invoice_url = null;

        self::assertNull((new WhatsappNotificationService)->manualUrl($order, $setting));
    }

    private function sampleOrder(): array
    {
        $customer = new Customer(['name' => 'Padli', 'phone' => '081234567890']);
        $product = new Product([
            'name' => 'Banner',
            'material' => 'Flexi',
            'print_size' => '3 X 1 M',
            'print_notes' => 'Contoh order',
        ]);
        $detail = new OrderDetails(['quantity' => 1, 'unit_price' => 150000, 'total' => 150000]);
        $detail->setRelation('product', $product);

        $order = new Order([
            'invoice_no' => 'TRX-1',
            'order_date' => Carbon::parse('2026-05-13 16:00:00'),
            'total' => 150000,
            'sub_total' => 150000,
            'discount' => 0,
            'tax_total' => 0,
            'service_charge' => 0,
            'pay_amount' => 100000,
            'due_amount' => 50000,
            'invoice_upload_status' => 'uploaded',
            'invoice_url' => 'https://tmp0.cc/d/AbC123',
            'invoice_expires_at' => now()->addDays(30),
        ]);
        $order->created_at = Carbon::parse('2026-05-13 16:00:00');
        $order->setRelation('customer', $customer);
        $order->setRelation('details', collect([$detail]));
        $order->setRelation('payments', collect());

        $setting = new StoreSetting([
            'store_name' => 'Toko Contoh',
            'whatsapp_payment_instructions' => '',
        ]);

        return [$order, $setting];
    }
}
