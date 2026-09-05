<?php

namespace Cuakx\Core\Tests\Utils\Auth\Session;

use Cuakx\Core\Tests\TestCase;
use Cuakx\Core\Utils\Auth\Session\AuthenticationUtil;
use Cuakx\Core\Utils\Auth\Session\Exception\UnauthorizedException;
use Cuakx\Core\Utils\Auth\Session\Model\UserSession;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Mockery;

class AuthenticationUtilTest extends TestCase
{
    public function test_issueToken_returns_guid_v7_and_persists_session(): void
    {
        $connection = Mockery::mock();

        Redis::shouldReceive('connection')
            ->once()
            ->with('default')
            ->andReturn($connection);

        $connection->shouldReceive('setex')
            ->once()
            ->withArgs(function (string $key, int $ttl, string $payload): bool {
                return str_starts_with($key, 'uma_cache_sessions:')
                    && $ttl === 900
                    && $payload !== '';
            })
            ->andReturn(true);

        $util = new AuthenticationUtil();
        $session = new UserSession(
            1,
            'ADMIN',
            99,
            'ORG-1',
            'Yosua',
            'Sumber Teknik',
            new DateTime('2026-07-04 00:00:00')
        );

        $token = $util->issueToken($session);

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $token
        );
    }

    public function test_getCacheSessionByToken_throws_unauthorized_when_not_found(): void
    {
        $connection = Mockery::mock();

        Redis::shouldReceive('connection')
            ->once()
            ->with('default')
            ->andReturn($connection);

        $connection->shouldReceive('get')
            ->once()
            ->with('uma_cache_sessions:token-123')
            ->andReturn(null);

        $util = new AuthenticationUtil();

        $this->expectException(UnauthorizedException::class);
        $util->getCacheSessionByToken('Bearer token-123');
    }

    public function test_getCacheSessionByToken_hydrates_array_payload_to_user_session(): void
    {
        $connection = Mockery::mock();

        Redis::shouldReceive('connection')
            ->once()
            ->with('default')
            ->andReturn($connection);

        $connection->shouldReceive('get')
            ->once()
            ->with('uma_cache_sessions:token-456')
            ->andReturn('{"user_id":10,"role_id":"STAFF","access_id":5,"organization_id":"ORG-2","user_name":"Budi","organization_name":"Sumber Teknik","issued_at":"2026-07-04 09:30:00","expired_at":"2026-07-04 09:45:00"}');

        $util = new AuthenticationUtil();
        $result = $util->getCacheSessionByToken('Bearer token-456');

        $this->assertInstanceOf(UserSession::class, $result);
        $this->assertSame(10, $result->user_id);
        $this->assertSame('STAFF', $result->role_id);
        $this->assertSame('ORG-2', $result->organization_id);
        $this->assertSame('2026-07-04 09:30:00', $result->issued_at);
        $this->assertSame('2026-07-04 09:45:00', $result->expired_at);
    }

    public function test_distinguishSessionByToken_deletes_session_cache(): void
    {
        $connection = Mockery::mock();

        Redis::shouldReceive('connection')
            ->once()
            ->with('default')
            ->andReturn($connection);

        $connection->shouldReceive('del')
            ->once()
            ->with('uma_cache_sessions:token-abc')
            ->andReturn(1);

        $util = new AuthenticationUtil();
        $util->distinguishSessionByToken('token-abc');

        $this->assertTrue(true);
    }

    public function test_database_driver_issues_reads_and_revokes_sessions(): void
    {
        $connection = (string) config('database.default');
        $table = 'cache_tbl_auth_sessions_test';
        Schema::connection($connection)->create($table, function ($table): void {
            $table->string('token', 128)->primary();
            $table->json('payload');
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        try {
            $util = new AuthenticationUtil(driver: 'database', databaseConnection: $connection, databaseTable: $table);
            $token = $util->issueToken(new UserSession(10, 'STAFF', 5, 'ORG-2', 'Budi', 'Sumber Teknik', new DateTime()));

            $session = $util->getCacheSessionByToken("Bearer {$token}");
            $this->assertSame(10, $session->user_id);
            $this->assertSame('ORG-2', $session->organization_id);

            $util->distinguishSessionByToken($token);
            $this->assertSame(0, DB::connection($connection)->table($table)->count());
        } finally {
            Schema::connection($connection)->dropIfExists($table);
        }
    }

    public function test_database_driver_rejects_and_removes_expired_sessions(): void
    {
        $connection = (string) config('database.default');
        $table = 'cache_tbl_auth_sessions_expired_test';
        Schema::connection($connection)->create($table, function ($table): void {
            $table->string('token', 128)->primary();
            $table->json('payload');
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        try {
            DB::connection($connection)->table($table)->insert([
                'token' => 'expired-token',
                'payload' => json_encode(['user_id' => 10]),
                'expires_at' => now()->subSecond(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $util = new AuthenticationUtil(driver: 'database', databaseConnection: $connection, databaseTable: $table);
            $this->expectException(UnauthorizedException::class);
            try {
                $util->getCacheSessionByToken('expired-token');
            } finally {
                $this->assertSame(0, DB::connection($connection)->table($table)->count());
            }
        } finally {
            Schema::connection($connection)->dropIfExists($table);
        }
    }
}
