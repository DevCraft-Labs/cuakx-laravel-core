<?php

namespace Cuakx\Core\Utils\Auth\Session;

use Cuakx\Core\Constant\CommonConstants;
use Cuakx\Core\Utils\Auth\Session\Exception\UnauthorizedException;
use Cuakx\Core\Utils\Auth\Session\Model\UserSession;
use Cuakx\Core\Utils\Redis\RedisRepository;
use Cuakx\Core\Utils\StringUtil;
use DateTime;

/**
 * This class contains utilities for making authentication within cuakx project.
 *
 * <ol>
 *     <li>The default key for token is uma_cache_sessions</li>
 * </ol>
 */
class AuthenticationUtil
{
    private function userSessionRepository() {
        return new class extends RedisRepository {
            private const CACHE_NAME = "uma_cache_sessions";
            private const DEFAULT_SESSION = 15 * 60; // 15 minutes

            public function __construct(string $connection = 'default')
            {
                parent::__construct(self::CACHE_NAME, self::DEFAULT_SESSION, $connection);
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

        $this->userSessionRepository()->set($token, $user_session);

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
        $result_set = $this->userSessionRepository()->get($token);

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

        $this->userSessionRepository()->delete($token);
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