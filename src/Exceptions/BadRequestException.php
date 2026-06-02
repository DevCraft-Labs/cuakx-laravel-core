<?php

namespace Cuakx\Core\Exceptions;

/**
 * Thrown when the incoming request fails input validation.
 *
 * Maps to HTTP 400 Bad Request. Typically raised by
 * {@see \Cuakx\Core\Http\Controllers\BaseController::baseValidator()}
 * with the first failing field's validation message.
 */
class BadRequestException extends BaseException
{
    /**
     * @param string $error_messages The first validation error message returned by the validator.
     */
    public function __construct(string $error_messages)
    {
        parent::__construct(
            "Bad Request => {$error_messages}",
            "400",
            null
        );
    }
}
