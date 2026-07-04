<?php

namespace Cuakx\Core\Tests\Utils\Auth\XToken;

use Cuakx\Core\Utils\Auth\XToken\XTokenUtil;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class XTokenUtilTest extends TestCase
{
    private ?string $previousAppKey = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousAppKey = getenv('APP_KEY') !== false ? (string) getenv('APP_KEY') : null;
        putenv('APP_KEY=test-app-key');
        $_ENV['APP_KEY'] = 'test-app-key';
        $_SERVER['APP_KEY'] = 'test-app-key';
    }

    protected function tearDown(): void
    {
        if ($this->previousAppKey === null) {
            putenv('APP_KEY');
            unset($_ENV['APP_KEY'], $_SERVER['APP_KEY']);
        } else {
            putenv('APP_KEY=' . $this->previousAppKey);
            $_ENV['APP_KEY'] = $this->previousAppKey;
            $_SERVER['APP_KEY'] = $this->previousAppKey;
        }

        parent::tearDown();
    }

    public function test_validateToken_returns_true_for_valid_token(): void
    {
        $request = new Request(['id' => 10, 'name' => 'cuakx']);
        $token = hash_hmac('sha512', json_encode($request->all()), 'test-app-key');

        $this->assertTrue(XTokenUtil::validateToken($token, $request));
    }

    public function test_validateToken_returns_false_for_invalid_token(): void
    {
        $request = new Request(['id' => 10, 'name' => 'cuakx']);

        $this->assertFalse(XTokenUtil::validateToken('invalid-token', $request));
    }
}
