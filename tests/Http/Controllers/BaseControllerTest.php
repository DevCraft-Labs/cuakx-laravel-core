<?php

namespace Cuakx\Core\Tests\Http\Controllers;

use Cuakx\Core\Exceptions\BadRequestException;
use Cuakx\Core\Http\Controllers\BaseController;
use Cuakx\Core\Tests\TestCase;
use Illuminate\Http\Request;

/**
 * A minimal concrete controller used only for testing BaseController.
 */
class StubController extends BaseController
{
    /**
     * Expose the protected baseValidator so tests can call it directly.
     */
    public function validate(Request $request, array $rules, array $messages = []): bool
    {
        return $this->baseValidator($request, $rules, $messages);
    }
}

class BaseControllerTest extends TestCase
{
    private StubController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new StubController();
    }

    // -------------------------------------------------------------------------
    // Passing validation
    // -------------------------------------------------------------------------

    public function test_baseValidator_passes_with_valid_data(): void
    {
        $request = Request::create('/', 'POST', [
            'name'  => 'Alice',
            'email' => 'alice@example.com',
        ]);

        $result = $this->controller->validate($request, [
            'name'  => 'required|string',
            'email' => 'required|email',
        ]);

        $this->assertTrue($result);
    }

    public function test_baseValidator_passes_with_empty_rules(): void
    {
        $request = Request::create('/', 'POST', []);
        $result  = $this->controller->validate($request, []);

        $this->assertTrue($result);
    }

    // -------------------------------------------------------------------------
    // Failing validation — throws BadRequestException
    // -------------------------------------------------------------------------

    public function test_baseValidator_throws_on_missing_required_field(): void
    {
        $this->expectException(BadRequestException::class);

        $request = Request::create('/', 'POST', []);
        $this->controller->validate($request, ['name' => 'required']);
    }

    public function test_baseValidator_throws_on_invalid_email(): void
    {
        $this->expectException(BadRequestException::class);

        $request = Request::create('/', 'POST', ['email' => 'not-an-email']);
        $this->controller->validate($request, ['email' => 'required|email']);
    }

    public function test_baseValidator_exception_message_contains_field_error(): void
    {
        $request = Request::create('/', 'POST', []);

        try {
            $this->controller->validate($request, ['name' => 'required']);
            $this->fail('Expected BadRequestException was not thrown.');
        } catch (BadRequestException $e) {
            $this->assertStringContainsString('name', strtolower($e->getMessage()));
        }
    }

    // -------------------------------------------------------------------------
    // Custom messages
    // -------------------------------------------------------------------------

    public function test_baseValidator_uses_custom_message(): void
    {
        $request = Request::create('/', 'POST', []);

        try {
            $this->controller->validate($request, ['name' => 'required'], [
                'name.required' => 'Nama wajib diisi.',
            ]);
            $this->fail('Expected BadRequestException was not thrown.');
        } catch (BadRequestException $e) {
            $this->assertStringContainsString('Nama wajib diisi.', $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Only first error is reported
    // -------------------------------------------------------------------------

    public function test_baseValidator_reports_first_error_only(): void
    {
        $request = Request::create('/', 'POST', []);

        try {
            $this->controller->validate($request, [
                'name'  => 'required',
                'email' => 'required|email',
            ]);
            $this->fail('Expected BadRequestException was not thrown.');
        } catch (BadRequestException $e) {
            // Should contain exactly one validation sentence — no newlines / concatenation.
            $this->assertStringNotContainsString("\n", $e->getMessage());
        }
    }
}
