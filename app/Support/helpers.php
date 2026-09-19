<?php

if (! function_exists('format_rupiah')) {
    function format_rupiah($amount): string
    {
        $amount = (float) ($amount ?? 0);
        $sign = $amount < 0 ? '-' : '';

        return $sign . 'Rp ' . number_format(abs($amount), 0, ',', '.');
    }
}
