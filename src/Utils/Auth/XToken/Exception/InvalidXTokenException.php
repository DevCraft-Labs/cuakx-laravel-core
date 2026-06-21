<?php

namespace Cuakx\Core\Utils\Auth\XToken\Exception;

use Cuakx\Core\Exceptions\BaseException;

class InvalidXTokenException extends BaseException
{
    public function __construct()
    {
        parent::__construct(
            "Unauthorized: Given X-Token is invalid.",
            "403",
            null
        );
    }
}