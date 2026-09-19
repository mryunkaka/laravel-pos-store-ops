<?php

namespace Tests\Unit;

use Tests\TestCase;

class AttendanceStyleTest extends TestCase
{
    public function test_custom_radio_css_uses_normal_control_dimensions(): void
    {
        foreach ([
            public_path('assets/css/backend.css'),
            public_path('assets/css/backend-plugin.min.css'),
        ] as $path) {
            $css = file_get_contents($path);

            self::assertIsString($css);
            self::assertStringNotContainsString('height: 12.5rem', $css);
            self::assertStringNotContainsString('height:12.5rem', $css);
            self::assertStringNotContainsString('top: 11.5rem', $css);
            self::assertStringNotContainsString('top:11.5rem', $css);
        }
    }
}
