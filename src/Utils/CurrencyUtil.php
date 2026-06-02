<?php

namespace Cuakx\Core\Utils;

/**
 * Utility class for Indonesian Rupiah (IDR) currency formatting and parsing.
 *
 * Uses dot (.) as the thousands separator and comma (,) as the decimal
 * separator, consistent with the Indonesian locale convention.
 */
class CurrencyUtil
{
    /**
     * Formats a numeric value as an Indonesian Rupiah string.
     *
     * @example
     * CurrencyUtil::toRupiah(1500000);    // "Rp. 1.500.000,00"
     * CurrencyUtil::toRupiah(75000.5);    // "Rp. 75.000,50"
     * CurrencyUtil::toRupiah(500);        // "Rp. 500,00"
     *
     * @param float $amount The numeric value to format.
     *
     * @return string The formatted Rupiah string.
     */
    public static function toRupiah(float $amount): string
    {
        return 'Rp. ' . number_format($amount, 2, ',', '.');
    }

    /**
     * Parses an Indonesian Rupiah string back into a float value.
     *
     * Strips the "Rp." prefix and all formatting characters (spaces, thousand-separator
     * dots), then replaces the decimal comma with a dot before casting to float.
     *
     * @example
     * CurrencyUtil::fromRupiah("Rp. 1.500.000,00");  // 1500000.0
     * CurrencyUtil::fromRupiah("Rp. 75.000,50");      // 75000.5
     * CurrencyUtil::fromRupiah("Rp. 500,00");         // 500.0
     *
     * @param string $rupiah The Rupiah-formatted string to parse.
     *
     * @return float The numeric value.
     */
    public static function fromRupiah(string $rupiah): float
    {
        // Remove the "Rp." prefix, spaces, and thousand-separator dots.
        $cleaned = preg_replace('/[Rp.\s]/u', '', $rupiah);

        // Replace the decimal comma with a standard dot.
        $cleaned = str_replace(',', '.', $cleaned);

        return (float) $cleaned;
    }
}
