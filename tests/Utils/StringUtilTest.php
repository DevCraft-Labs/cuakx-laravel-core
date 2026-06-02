<?php

namespace Cuakx\Core\Tests\Utils;

use Cuakx\Core\Utils\StringUtil;
use PHPUnit\Framework\TestCase;

class StringUtilTest extends TestCase
{
    // -------------------------------------------------------------------------
    // toCamelCase
    // -------------------------------------------------------------------------

    public function test_toCamelCase_from_kebab(): void
    {
        $this->assertSame('helloWorld', StringUtil::toCamelCase('hello-world'));
    }

    public function test_toCamelCase_from_snake(): void
    {
        $this->assertSame('helloWorld', StringUtil::toCamelCase('hello_world'));
    }

    public function test_toCamelCase_from_spaces(): void
    {
        $this->assertSame('helloWorld', StringUtil::toCamelCase('Hello World'));
    }

    public function test_toCamelCase_single_word(): void
    {
        $this->assertSame('hello', StringUtil::toCamelCase('Hello'));
    }

    // -------------------------------------------------------------------------
    // toSnakeCase
    // -------------------------------------------------------------------------

    public function test_toSnakeCase_from_spaces(): void
    {
        $this->assertSame('hello_world', StringUtil::toSnakeCase('hello world'));
    }

    public function test_toSnakeCase_from_camel(): void
    {
        $this->assertSame('hello_world', StringUtil::toSnakeCase('helloWorld'));
    }

    public function test_toSnakeCase_from_pascal(): void
    {
        $this->assertSame('hello_world', StringUtil::toSnakeCase('HelloWorld'));
    }

    // -------------------------------------------------------------------------
    // toKebabCase
    // -------------------------------------------------------------------------

    public function test_toKebabCase_from_spaces(): void
    {
        $this->assertSame('hello-world', StringUtil::toKebabCase('hello world'));
    }

    public function test_toKebabCase_from_camel(): void
    {
        $this->assertSame('hello-world', StringUtil::toKebabCase('helloWorld'));
    }

    public function test_toKebabCase_from_snake(): void
    {
        $this->assertSame('hello-world', StringUtil::toKebabCase('hello_world'));
    }

    // -------------------------------------------------------------------------
    // capitalize
    // -------------------------------------------------------------------------

    public function test_capitalize_lowercase(): void
    {
        $this->assertSame('Hello', StringUtil::capitalize('hello'));
    }

    public function test_capitalize_empty_string(): void
    {
        $this->assertSame('', StringUtil::capitalize(''));
    }

    public function test_capitalize_already_capitalized(): void
    {
        $this->assertSame('Hello', StringUtil::capitalize('Hello'));
    }

    // -------------------------------------------------------------------------
    // maskString
    // -------------------------------------------------------------------------

    public function test_maskString_defaults(): void
    {
        // 'password123' = 11 chars. maskEnd=4 leaves last 4 ('d123'), masks first 7.
        $this->assertSame('*******d123', StringUtil::maskString('password123'));
    }

    public function test_maskString_custom_mask_char(): void
    {
        $this->assertSame('######7890', StringUtil::maskString('1234567890', '#'));
    }

    public function test_maskString_with_start_offset(): void
    {
        $this->assertSame('ab**ef', StringUtil::maskString('abcdef', '*', 2, 2));
    }

    public function test_maskString_empty_string(): void
    {
        $this->assertSame('', StringUtil::maskString(''));
    }

    // -------------------------------------------------------------------------
    // randomString
    // -------------------------------------------------------------------------

    public function test_randomString_correct_length(): void
    {
        $this->assertSame(10, strlen(StringUtil::randomString(10)));
    }

    public function test_randomString_zero_length(): void
    {
        $this->assertSame('', StringUtil::randomString(0));
    }

    public function test_randomString_lowercase_only(): void
    {
        $result = StringUtil::randomString(50);
        $this->assertMatchesRegularExpression('/^[a-z]+$/', $result);
    }

    public function test_randomString_alphanumeric(): void
    {
        // Run a large sample to ensure digits can appear.
        $result = StringUtil::randomString(200, true);
        $this->assertMatchesRegularExpression('/^[a-z0-9]+$/', $result);
    }

    public function test_randomString_mixed_case(): void
    {
        $result = StringUtil::randomString(200, false, true);
        $this->assertMatchesRegularExpression('/^[a-zA-Z]+$/', $result);
    }

    // -------------------------------------------------------------------------
    // generateGuidV4
    // -------------------------------------------------------------------------

    public function test_generateGuidV4_format(): void
    {
        $uuid = StringUtil::generateGuidV4();
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid
        );
    }

    public function test_generateGuidV4_is_unique(): void
    {
        $this->assertNotSame(StringUtil::generateGuidV4(), StringUtil::generateGuidV4());
    }

    // -------------------------------------------------------------------------
    // generateGuidV7
    // -------------------------------------------------------------------------

    public function test_generateGuidV7_format(): void
    {
        $uuid = StringUtil::generateGuidV7();
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid
        );
    }

    public function test_generateGuidV7_is_unique(): void
    {
        $this->assertNotSame(StringUtil::generateGuidV7(), StringUtil::generateGuidV7());
    }

    // -------------------------------------------------------------------------
    // toBase64 / fromBase64
    // -------------------------------------------------------------------------

    public function test_toBase64_encoding(): void
    {
        $this->assertSame('aGVsbG8=', StringUtil::toBase64('hello'));
        $this->assertSame('Q3Vha3g=', StringUtil::toBase64('Cuakx'));
    }

    public function test_fromBase64_decoding(): void
    {
        $this->assertSame('hello', StringUtil::fromBase64('aGVsbG8='));
        $this->assertSame('Cuakx', StringUtil::fromBase64('Q3Vha3g='));
    }

    public function test_base64_roundtrip(): void
    {
        $original = 'sumber-teknik-service-2026';
        $this->assertSame($original, StringUtil::fromBase64(StringUtil::toBase64($original)));
    }
}
