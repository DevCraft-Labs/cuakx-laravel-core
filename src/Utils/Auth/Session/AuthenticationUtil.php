<?php

namespace Cuakx\Core\Utils\Auth\Session;

use Cuakx\Core\Constant\CommonConstants;
use Cuakx\Core\Utils\Auth\Session\Exception\UnauthorizedException;
use Cuakx\Core\Utils\Auth\Session\Model\UserSession;
use Cuakx\Core\Utils\Redis\RedisRepository;
use Cuakx\Core\Utils\StringUtil;
use DateTime;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * This class contains utilities for making authentication within cuakx project.
 *
 * <ol>
 *     <li>The default Redis key for tokens is uma_cache_sessions.</li>
 *     <li>The database driver stores tokens in the shared cache_token schema.</li>
 * </ol>
 */
class AuthenticationUtil
{
    public const REDIS_CACHE_NAME = 'uma_cache_sessions';
    public const DEFAULT_SESSION_SECONDS = 15 * 60;

    private string $driver;
    private string $databaseConnection;
    private string $databaseTable;

    public function __construct(
        ?string $driver = null,
        ?string $databaseConnection = null,
        ?string $databaseTable = null,
    ) {
        $this->driver = strtolower($driver ?? (string) env('CUAKX_AUTH_SESSION_DRIVER', 'redis'));
        $this->databaseConnection = $databaseConnection ?? (string) env('CUAKX_AUTH_SESSION_CONNECTION', 'cache_token');
        $this->databaseTable = $databaseTable ?? (string) env('CUAKX_AUTH_SESSION_TABLE', 'cache_tbl_auth_sessions');

        if (! in_array($this->driver, ['redis', 'database'], true)) {
            throw new InvalidArgumentException("Unsupported auth session driver [{$this->driver}].");
        }
    }

    private function userSessionRepository() {
        return new class extends RedisRepository {
            public function __construct(string $connection = 'default')
            {
                parent::__construct(AuthenticationUtil::REDIS_CACHE_NAME, AuthenticationUtil::DEFAULT_SESSION_SECONDS, $connection);
            }
        };
    }

    /**
     * This function take UserSession instance, and return as generated token.
     *
     * @param UserSession $user_session
     * @return string
     */
    public function issueToken(UserSession $user_session): string {
        $token = StringUtil::generateGuidV7();

        if ($this->driver === 'database') {
            $this->databaseSessions()->upsert([
                [
                    'token' => $token,
                    'payload' => json_encode($this->sessionPayload($user_session), JSON_THROW_ON_ERROR),
                    'expires_at' => $user_session->expired_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ], ['token'], ['payload', 'expires_at', 'updated_at']);
        } else {
            $this->userSessionRepository()->set($token, $user_session);
        }

        return $token;
    }

    /**
     * Typically get session by token
     *
     * @param string $token
     * @return UserSession
     */
    public function getCacheSessionByToken(string $token): UserSession {
        $token = str_replace("Bearer ", "", $token);
        $result_set = $this->driver === 'database'
            ? $this->databaseSessionPayload($token)
            : $this->userSessionRepository()->get($token);

        if(!$result_set){
            throw new UnauthorizedException();
        }

        if ($result_set instanceof UserSession) {
            return $result_set;
        }

        if (is_array($result_set)) {
            return $this->hydrateUserSession($result_set);
        }

        throw new UnauthorizedException();
    }

    public function distinguishSessionByToken(string $token): void {
        $token = str_replace("Bearer ", "", $token);

        if ($this->driver === 'database') {
            $this->databaseSessions()->where('token', $token)->delete();
            return;
        }

        $this->userSessionRepository()->delete($token);
    }

    private function databaseSessions(): \Illuminate\Database\Query\Builder
    {
        return DB::connection($this->databaseConnection)->table($this->databaseTable);
    }

    /** @return array<string, mixed>|null */
    private function databaseSessionPayload(string $token): ?array
    {
        $record = $this->databaseSessions()
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->first(['payload']);

        if ($record === null) {
            $this->databaseSessions()->where('token', $token)->delete();
            return null;
        }

        $payload = json_decode((string) $record->payload, true);

        return is_array($payload) ? $payload : null;
    }

    /** @return array<string, int|string> */
    private function sessionPayload(UserSession $session): array
    {
        return [
            'user_id' => $session->user_id,
            'role_id' => $session->role_id,
            'access_id' => $session->access_id,
            'organization_id' => $session->organization_id,
            'user_name' => $session->user_name,
            'organization_name' => $session->organization_name,
            'issued_at' => $session->issued_at,
            'expired_at' => $session->expired_at,
        ];
    }

    /**
     * Rebuild UserSession from Redis-deserialized associative array.
     *
     * @param array<string, mixed> $payload
     */
    private function hydrateUserSession(array $payload): UserSession
    {
        $issuedAt = isset($payload['issued_at'])
            ? DateTime::createFromFormat(CommonConstants::DATE_DEFAULT_FORMAT, (string) $payload['issued_at'])
            : false;

        if ($issuedAt === false) {
            $issuedAt = new DateTime();
        }

        $session = new UserSession(
            (int) ($payload['user_id'] ?? 0),
            (string) ($payload['role_id'] ?? ''),
            (int) ($payload['access_id'] ?? 0),
            (string) ($payload['organization_id'] ?? ''),
            (string) ($payload['user_name'] ?? ''),
            (string) ($payload['organization_name'] ?? ''),
            $issuedAt
        );

        if (isset($payload['expired_at'])) {
            $expiredAt = DateTime::createFromFormat(
                CommonConstants::DATE_DEFAULT_FORMAT,
                (string) $payload['expired_at']
            );

            if ($expiredAt !== false) {
                $session->setExpiredAt($expiredAt);
            }
        }

        return $session;
    }
}