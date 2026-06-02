<?php

namespace Cuakx\Core\Utils;

use Ramsey\Uuid\Uuid;

/**
 * StringUtil provides static utility functions for common string operations.
 * All methods are static and can be used without instantiation.
 *
 * PHP port of the Cuakx TypeScript StringUtil class.
 */
class StringUtil
{
    /**
     * Converts a string to camelCase format.
     *
     * Splits on spaces, hyphens, and underscores, lowercases the first word,
     * and capitalises the first letter of every subsequent word.
     *
     * @example
     * StringUtil::toCamelCase("hello-world");   // "helloWorld"
     * StringUtil::toCamelCase("hello_world");   // "helloWorld"
     * StringUtil::toCamelCase("Hello World");   // "helloWorld"
     *
     * @param string $str The string to convert.
     *
     * @return string The string in camelCase format.
     */
    public static function toCamelCase(string $str): string
    {
        $words = preg_split('/[\s\-_]+/', trim($str));

        if (empty($words)) {
            return $str;
        }

        $result = mb_strtolower($words[0]);

        for ($i = 1; $i < count($words); $i++) {
            if (!empty($words[$i])) {
                $result .= ucfirst(mb_strtolower($words[$i]));
            }
        }

        return $result;
    }

    /**
     * Converts a string to snake_case format.
     *
     * Inserts an underscore before uppercase letters in camelCase/PascalCase
     * inputs, then replaces spaces and hyphens with underscores.
     *
     * @example
     * StringUtil::toSnakeCase("hello world");  // "hello_world"
     * StringUtil::toSnakeCase("helloWorld");   // "hello_world"
     * StringUtil::toSnakeCase("HelloWorld");   // "hello_world"
     *
     * @param string $str The string to convert.
     *
     * @return string The string in snake_case format.
     */
    public static function toSnakeCase(string $str): string
    {
        $str = preg_replace('/([a-z])([A-Z])/', '$1_$2', $str);
        $str = preg_replace('/[\s\-]+/', '_', $str);

        return mb_strtolower($str);
    }

    /**
     * Converts a string to kebab-case format.
     *
     * Inserts a hyphen before uppercase letters in camelCase/PascalCase
     * inputs, then replaces spaces and underscores with hyphens.
     *
     * @example
     * StringUtil::toKebabCase("hello world");  // "hello-world"
     * StringUtil::toKebabCase("helloWorld");   // "hello-world"
     * StringUtil::toKebabCase("Hello_World");  // "hello-world"
     *
     * @param string $str The string to convert.
     *
     * @return string The string in kebab-case format.
     */
    public static function toKebabCase(string $str): string
    {
        $str = preg_replace('/([a-z])([A-Z])/', '$1-$2', $str);
        $str = preg_replace('/[\s_]+/', '-', $str);

        return mb_strtolower($str);
    }

    /**
     * Capitalises the first character of a string.
     *
     * Unlike PHP's ucfirst(), this method is multibyte-safe.
     *
     * @example
     * StringUtil::capitalize("hello");  // "Hello"
     * StringUtil::capitalize("hELLO");  // "HELLO" (only first char is changed)
     * StringUtil::capitalize("");       // ""
     *
     * @param string $str The string to capitalise.
     *
     * @return string The string with the first character uppercased.
     */
    public static function capitalize(string $str): string
    {
        if (empty($str)) {
            return $str;
        }

        return mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1);
    }

    /**
     * Masks a portion of a string with a specified character.
     *
     * Characters between $maskStart and ($length - $maskEnd) are replaced
     * with the $mask character. The leading $maskStart characters and trailing
     * $maskEnd characters are left as-is.
     *
     * @example
     * StringUtil::maskString("password123", "*", 0, 4);  // "*******123"  (7 masked, last 4 visible)
     * StringUtil::maskString("1234567890", "#");          // "######7890"  (first 6 masked)
     * StringUtil::maskString("abcdef", "*", 2, 2);       // "ab**ef"
     *
     * @param string $str       The string to mask.
     * @param string $mask      The character to use for masking (default: "*").
     * @param int    $maskStart The number of leading characters to leave unmasked (default: 0).
     * @param int    $maskEnd   The number of trailing characters to leave unmasked (default: 4).
     *
     * @return string The masked string.
     */
    public static function maskString(
        string $str,
        string $mask = '*',
        int $maskStart = 0,
        int $maskEnd = 4
    ): string {
        if (empty($str)) {
            return $str;
        }

        $length          = mb_strlen($str);
        $maskStartIndex  = max(0, $maskStart);
        $unmaskedEnd     = max(0, $maskEnd);
        $maskEndIndex    = max($maskStartIndex, $length - $unmaskedEnd);

        $before = mb_substr($str, 0, $maskStartIndex);
        $masked = str_repeat($mask, $maskEndIndex - $maskStartIndex);
        $after  = mb_substr($str, $maskEndIndex);

        return $before . $masked . $after;
    }

    /**
     * Generates a random string of specified length.
     *
     * Uses cryptographically secure random_int() for character selection.
     *
     * @example
     * StringUtil::randomString(10);              // "abcdefghij"  (lowercase only)
     * StringUtil::randomString(10, true);        // "abc1def2gh"  (lowercase + digits)
     * StringUtil::randomString(10, true, true);  // "aBc1DeF2gH"  (mixed case + digits)
     *
     * @param int  $length       The length of the random string to generate.
     * @param bool $alphanumeric Whether to include digits 0-9 (default: false).
     * @param bool $mixedCase    Whether to include uppercase A-Z (default: false).
     *
     * @return string A random string of the specified length.
     */
    public static function randomString(
        int $length,
        bool $alphanumeric = false,
        bool $mixedCase = false
    ): string {
        if ($length <= 0) {
            return '';
        }

        $chars = 'abcdefghijklmnopqrstuvwxyz';

        if ($alphanumeric) {
            $chars .= '0123456789';
        }

        if ($mixedCase) {
            $chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        $result = '';
        $max    = mb_strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, $max)];
        }

        return $result;
    }

    /**
     * Generates a UUID v4 (random).
     *
     * @example
     * StringUtil::generateGuidV4();  // "550e8400-e29b-41d4-a716-446655440000"
     *
     * @return string A UUID v4 string.
     */
    public static function generateGuidV4(): string
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * Generates a UUID v7 (Unix timestamp-ordered).
     *
     * UUID v7 values are monotonically increasing and sortable by creation time,
     * making them suitable for use as database primary keys.
     *
     * @example
     * StringUtil::generateGuidV7();  // "069a8f59-2d5a-7fb7-8b68-8be3d2e4f3a1"
     *
     * @return string A UUID v7 string.
     */
    public static function generateGuidV7(): string
    {
        return Uuid::uuid7()->toString();
    }

    /**
     * Encodes a string to Base64.
     *
     * @example
     * StringUtil::toBase64("hello");  // "aGVsbG8="
     * StringUtil::toBase64("Cuakx");  // "Q3Vha3g="
     *
     * @param string $str The string to encode.
     *
     * @return string The Base64 encoded string.
     */
    public static function toBase64(string $str): string
    {
        return base64_encode($str);
    }

    /**
     * Decodes a Base64 encoded string back to plain text.
     *
     * @example
     * StringUtil::fromBase64("aGVsbG8=");  // "hello"
     * StringUtil::fromBase64("Q3Vha3g=");  // "Cuakx"
     *
     * @param string $encoded The Base64 encoded string to decode.
     *
     * @return string The decoded string.
     */
    public static function fromBase64(string $encoded): string
    {
        return base64_decode($encoded);
    }
}
