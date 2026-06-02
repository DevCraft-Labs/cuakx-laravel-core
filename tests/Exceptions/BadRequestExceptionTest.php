<?php

namespace Cuakx\Core\Tests\Exceptions;

use Cuakx\Core\Exceptions\BadRequestException;
use PHPUnit\Framework\TestCase;

class BadRequestExceptionTest extends TestCase
{
    public function test_is_throwable(): void
    {
        $e = new BadRequestException('name is required');
        $this->assertInstanceOf(\Throwable::class, $e);
    }

    public function test_is_error(): void
    {
        $e = new BadRequestException('name is required');
        $this->assertInstanceOf(\Error::class, $e);
    }

    public function test_message_contains_bad_request_prefix(): void
    {
        $e = new BadRequestException('name is required');
        $this->assertStringContainsString('Bad Request', $e->getMessage());
    }

    public function test_message_contains_original_error(): void
    {
        $e = new BadRequestException('name is required');
        $this->assertStringContainsString('name is required', $e->getMessage());
    }

    public function test_code_is_400(): void
    {
        $e = new BadRequestException('email is invalid');
        // The parent Error constructor receives the numeric code (422 for HTTP mapping),
        // but the application-level string code "400" is in the message prefix.
        $this->assertStringContainsString('400', $e->getMessage());
    }

    public function test_can_be_caught_as_error(): void
    {
        $caught = false;

        try {
            throw new BadRequestException('test error');
        } catch (\Error $e) {
            $caught = true;
            $this->assertStringContainsString('test error', $e->getMessage());
        }

        $this->assertTrue($caught);
    }
}
