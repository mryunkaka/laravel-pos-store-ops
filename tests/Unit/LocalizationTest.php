<?php

namespace Tests\Unit;

use Tests\TestCase;

class LocalizationTest extends TestCase
{
    public function test_rupiah_formatter_uses_indonesian_format(): void
    {
        $this->assertSame('Rp 1.500.000', format_rupiah(1500000));
        $this->assertSame('Rp 0', format_rupiah(null));
    }

    public function test_application_timezone_is_asia_singapore(): void
    {
        $this->assertSame('Asia/Singapore', config('app.timezone'));
    }
}
