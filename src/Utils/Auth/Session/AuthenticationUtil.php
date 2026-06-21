<?php

namespace Cuakx\Core\Utils\Auth\Session;

use Cuakx\Core\Utils\Auth\Session\Exception\UnauthorizedException;
use Cuakx\Core\Utils\Auth\Session\Model\UserSession;
use Cuakx\Core\Utils\Redis\RedisRepository;
use Cuakx\Core\Utils\StringUtil;

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

        return $result_set;
    }

    public function distinguishSessionByToken(string $token): void {
        $this->userSessionRepository()->delete($token);
    }
}