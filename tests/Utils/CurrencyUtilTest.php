<?php

namespace Cuakx\Core\Tests\Utils;

use Cuakx\Core\Utils\CurrencyUtil;
use PHPUnit\Framework\TestCase;

class CurrencyUtilTest extends TestCase
{
    // -------------------------------------------------------------------------
    // toRupiah
    // -------------------------------------------------------------------------

    public function test_toRupiah_whole_number(): void
    {
        $this->assertSame('Rp. 500,00', CurrencyUtil::toRupiah(500));
    }

    public function test_toRupiah_thousands(): void
    {
        $this->assertSame('Rp. 75.000,50', CurrencyUtil::toRupiah(75000.5));
    }

    public function test_toRupiah_millions(): void
    {
        $this->assertSame('Rp. 1.500.000,00', CurrencyUtil::toRupiah(1500000));
    }

    public function test_toRupiah_zero(): void
    {
        $this->assertSame('Rp. 0,00', CurrencyUtil::toRupiah(0));
    }

    // -------------------------------------------------------------------------
    // fromRupiah
    // -------------------------------------------------------------------------

    public function test_fromRupiah_whole_number(): void
    {
        $this->assertSame(500.0, CurrencyUtil::fromRupiah('Rp. 500,00'));
    }

    public function test_fromRupiah_thousands(): void
    {
        $this->assertSame(75000.5, CurrencyUtil::fromRupiah('Rp. 75.000,50'));
    }

    public function test_fromRupiah_millions(): void
    {
        $this->assertSame(1500000.0, CurrencyUtil::fromRupiah('Rp. 1.500.000,00'));
    }

    public function test_fromRupiah_zero(): void
    {
        $this->assertSame(0.0, CurrencyUtil::fromRupiah('Rp. 0,00'));
    }

    // -------------------------------------------------------------------------
    // Roundtrip
    // -------------------------------------------------------------------------

    public function test_roundtrip(): void
    {
        $original = 4750000.75;
        $formatted = CurrencyUtil::toRupiah($original);
        $parsed    = CurrencyUtil::fromRupiah($formatted);

        $this->assertEqualsWithDelta($original, $parsed, 0.01);
    }
}
