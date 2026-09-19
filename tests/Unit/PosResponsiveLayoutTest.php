<?php

namespace Tests\Unit;

use Tests\TestCase;

class PosResponsiveLayoutTest extends TestCase
{
    public function test_pos_cart_uses_reserved_responsive_column_instead_of_sticky_overlay(): void
    {
        $source = (string) file_get_contents(resource_path('views/pos/index.blade.php'));

        self::assertStringContainsString('class="container-fluid pos-page"', $source);
        self::assertStringContainsString('pos-catalog-column', $source);
        self::assertStringContainsString('pos-cart-column', $source);
        self::assertStringContainsString('pos-cart-card', $source);
        self::assertStringNotContainsString('sticky-top', $source);
        self::assertStringNotContainsString('z-index: 100', $source);
    }

    public function test_camera_scanner_reports_secure_context_and_api_failures(): void
    {
        $source = (string) file_get_contents(public_path('assets/js/barcode-camera.js'));

        self::assertStringContainsString('window.isSecureContext', $source);
        self::assertStringContainsString('Browser tidak mendukung akses kamera.', $source);
        self::assertStringContainsString('Arahkan kamera ke barcode.', $source);
        self::assertStringContainsString('NotFoundError', $source);
        self::assertStringNotContainsString("if (!button || !video || !input || !navigator.mediaDevices?.getUserMedia)", $source);
    }

    public function test_authenticated_layout_is_not_indexable(): void
    {
        $source = (string) file_get_contents(resource_path('views/dashboard/body/main.blade.php'));

        self::assertStringContainsString('name="description"', $source);
        self::assertStringContainsString('name="robots" content="noindex, nofollow"', $source);
    }
}
