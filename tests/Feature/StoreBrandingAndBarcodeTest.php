<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Services\WhatsappNotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class StoreBrandingAndBarcodeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_store_settings_form_exposes_google_maps_url(): void
    {
        $user = \App\Models\User::factory()->create();
        $user->givePermissionTo(Permission::pluck('name')->all());
        $this->actingAs($user);
        $setting = new StoreSetting([
            'store_name' => 'Toko Dinamis',
            'google_maps_url' => 'https://maps.google.com/?q=Toko+Dinamis',
        ]);

        $this->view('settings.store', ['setting' => $setting, 'errors' => new ViewErrorBag()])
            ->assertSee('google_maps_url', false)
            ->assertSee('https://maps.google.com/?q=Toko+Dinamis', false);
    }

    public function test_receipt_uses_store_branding_and_clickable_map_link(): void
    {
        [$order, $detail] = $this->sampleOrder();
        $order->id = 1;
        $setting = new StoreSetting([
            'store_name' => 'Toko Dinamis',
            'address' => 'Jl. Contoh No. 1',
            'phone' => '081234567890',
            'google_maps_url' => 'https://maps.google.com/?q=Toko+Dinamis',
        ]);

        $this->view('pos.print-receipt', [
            'order' => $order,
            'orderDetails' => collect([$detail]),
            'setting' => $setting,
            'errors' => new ViewErrorBag(),
        ])
            ->assertSee('Toko Dinamis', false)
            ->assertSee('Jl. Contoh No. 1', false)
            ->assertSee('081234567890', false)
            ->assertSee('https://maps.google.com/?q=Toko+Dinamis', false)
            ->assertDontSee('POS SHOP', false)
            ->assertDontSee('123 Commerce Avenue', false);
    }

    public function test_whatsapp_message_omits_store_header_and_contains_payment_breakdown(): void
    {
        [$order] = $this->sampleOrder();
        $setting = new StoreSetting([
            'store_name' => 'Toko Dinamis',
            'address' => 'Jl. Contoh No. 1',
            'phone' => '081234567890',
            'google_maps_url' => 'https://maps.google.com/?q=Toko+Dinamis',
        ]);

        $message = (new WhatsappNotificationService)->buildOrderMessage($order, null, $setting);

        self::assertStringNotContainsString('Toko Dinamis', $message);
        self::assertStringNotContainsString('Jl. Contoh No. 1', $message);
        self::assertStringNotContainsString('081234567890', $message);
        self::assertStringNotContainsString('https://maps.google.com/?q=Toko+Dinamis', $message);
        self::assertStringContainsString('*Subtotal:*', $message);
        self::assertStringContainsString('*Diskon:*', $message);
        self::assertStringContainsString('*Pajak/PPN:*', $message);
        self::assertStringContainsString('*Biaya lainnya:*', $message);
        self::assertStringContainsString('*Pembayaran:*', $message);
        self::assertStringContainsString('*Kembalian:*', $message);
    }

    public function test_barcode_camera_controls_are_present_on_pos_and_product_create(): void
    {
        self::assertStringContainsString('id="start_pos_barcode_camera"', file_get_contents(resource_path('views/pos/index.blade.php')));
        self::assertStringContainsString('barcode-camera.js', file_get_contents(resource_path('views/pos/index.blade.php')));
        self::assertStringContainsString('id="start_barcode_camera"', file_get_contents(resource_path('views/products/create.blade.php')));
        self::assertStringContainsString('barcode-camera.js', file_get_contents(resource_path('views/products/create.blade.php')));
        self::assertStringContainsString('id="product_barcode_camera_video"', file_get_contents(resource_path('views/products/create.blade.php')));
    }

    public function test_product_create_exposes_camera_and_gallery_image_choices(): void
    {
        $source = file_get_contents(resource_path('views/products/create.blade.php'));

        self::assertStringContainsString('id="start_product_image_camera"', $source);
        self::assertStringContainsString('id="product_image_camera_video"', $source);
        self::assertStringContainsString('id="capture_product_image"', $source);
        self::assertStringContainsString('id="image"', $source);
        self::assertStringContainsString('accept="image/*"', $source);
        self::assertStringContainsString('product-image-camera.js', $source);
    }

    public function test_invoice_pdf_uses_dynamic_store_link_and_full_payment_breakdown(): void
    {
        [$order] = $this->sampleOrder();
        $setting = new StoreSetting([
            'store_name' => 'Toko Dinamis',
            'address' => 'Jl. Contoh No. 1',
            'phone' => '081234567890',
            'google_maps_url' => 'https://maps.google.com/?q=Toko+Dinamis',
        ]);

        $this->view('invoices.pdf', [
            'order' => $order,
            'setting' => $setting,
            'logoDataUri' => null,
        ])
            ->assertSee('https://maps.google.com/?q=Toko+Dinamis', false)
            ->assertSee('-Rp 10.000', false)
            ->assertSee('Pajak/PPN', false)
            ->assertSee('Biaya lainnya', false)
            ->assertSee('Kembalian', false);
    }

    public function test_pos_payment_modal_uses_rupiah_format(): void
    {
        $source = file_get_contents(resource_path('views/pos/index.blade.php'));

        self::assertStringContainsString('{{ format_rupiah(Cart::total()) }}', $source);
    }

    public function test_visual_invoice_uses_store_branding_and_payment_breakdown(): void
    {
        [$order, $detail] = $this->sampleOrder();
        $setting = new StoreSetting([
            'store_name' => 'Toko Dinamis',
            'address' => 'Jl. Contoh No. 1',
            'phone' => '081234567890',
            'google_maps_url' => 'https://maps.google.com/?q=Toko+Dinamis',
        ]);

        $this->view('orders.invoice-order', [
            'order' => $order,
            'orderDetails' => collect([$detail]),
            'setting' => $setting,
        ])
            ->assertSee('Toko Dinamis', false)
            ->assertSee('https://maps.google.com/?q=Toko+Dinamis', false)
            ->assertSee('Subtotal', false)
            ->assertSee('Pajak/PPN', false)
            ->assertSee('Biaya lainnya', false)
            ->assertSee('Kembalian', false);
    }

    private function sampleOrder(): array
    {
        $customer = new Customer(['name' => 'Pelanggan', 'phone' => '081234567890']);
        $product = new Product(['name' => 'Produk Contoh']);
        $detail = new OrderDetails(['quantity' => 1, 'unit_price' => 100000, 'total' => 100000]);
        $detail->setRelation('product', $product);

        $order = new Order([
            'invoice_no' => 'INV-BRANDING-1',
            'order_date' => Carbon::parse('2026-09-19 16:00:00'),
            'sub_total' => 100000,
            'discount' => 10000,
            'tax_total' => 9000,
            'vat' => 9000,
            'service_charge' => 5000,
            'total' => 104000,
            'pay_amount' => 110000,
            'due_amount' => -6000,
            'payment_type' => 'Tunai Rp 110.000',
        ]);
        $order->created_at = Carbon::parse('2026-09-19 16:00:00');
        $order->setRelation('customer', $customer);
        $order->setRelation('details', collect([$detail]));
        $order->setRelation('payments', collect());

        return [$order, $detail];
    }
}
