<?php

namespace Cuakx\Core\Tests\DTO;

use Cuakx\Core\DTO\BaseResponseDTO;
use Cuakx\Core\Tests\TestCase;
use Illuminate\Http\JsonResponse;

/**
 * Uses orchestra/testbench so that response() and json() helpers are available.
 */
class BaseResponseDTOTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Constructor / shape
    // -------------------------------------------------------------------------

    public function test_constructor_defaults(): void
    {
        $dto = new BaseResponseDTO();

        $this->assertTrue($dto->success);
        $this->assertSame('', $dto->code);
        $this->assertSame('', $dto->message);
        $this->assertNull($dto->data);
    }

    public function test_constructor_with_values(): void
    {
        $data = (object) ['id' => 1];
        $dto  = new BaseResponseDTO(false, '404', 'Not found', $data);

        $this->assertFalse($dto->success);
        $this->assertSame('404', $dto->code);
        $this->assertSame('Not found', $dto->message);
        $this->assertSame($data, $dto->data);
    }

    // -------------------------------------------------------------------------
    // ::success()
    // -------------------------------------------------------------------------

    public function test_success_returns_json_response(): void
    {
        $response = BaseResponseDTO::success('Created successfully');
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_success_payload_shape(): void
    {
        $response = BaseResponseDTO::success('OK');
        $payload  = $response->getData(true);

        $this->assertTrue($payload['success']);
        $this->assertSame('200', $payload['code']);
        $this->assertSame('OK', $payload['message']);
        $this->assertNull($payload['data']);
    }

    public function test_success_with_data(): void
    {
        $data     = (object) ['id' => 42, 'name' => 'Alice'];
        $response = BaseResponseDTO::success('Fetched', $data);
        $payload  = $response->getData(true);

        $this->assertSame(42, $payload['data']['id']);
        $this->assertSame('Alice', $payload['data']['name']);
    }

    public function test_success_with_custom_code(): void
    {
        $response = BaseResponseDTO::success('Created', null, '201');
        $payload  = $response->getData(true);

        $this->assertSame('201', $payload['code']);
    }

    // -------------------------------------------------------------------------
    // ::error()
    // -------------------------------------------------------------------------

    public function test_error_returns_json_response(): void
    {
        $response = BaseResponseDTO::error('400', 'Bad request');
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_error_payload_shape(): void
    {
        $response = BaseResponseDTO::error('404', 'Resource not found');
        $payload  = $response->getData(true);

        $this->assertFalse($payload['success']);
        $this->assertSame('404', $payload['code']);
        $this->assertSame('Resource not found', $payload['message']);
        $this->assertNull($payload['data']);
    }
}
