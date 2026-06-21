<?php

namespace Cuakx\Core\Utils\Auth\Session\Exception;

use Cuakx\Core\Exceptions\BaseException;
use Cuakx\Core\Utils\Auth\Session\AuthenticationUtil;

/**
 * Thrown upon token not found inside cache.
 *
 * Thrown at:
 * {@see AuthenticationUtil::getCacheSessionByToken()}
 */
class UnauthorizedException extends BaseException
{
    public function __construct()
    {
        parent::__construct(
            "Unauthorized",
            "403",
            null
        );
    }
}