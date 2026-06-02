<?php

namespace Cuakx\Core\Exceptions;

use Cuakx\Core\Utils\Console;
use Error;
use Throwable;

/**
 * Abstract base class for all Cuakx domain exceptions.
 *
 * Extends PHP's native {@see Error} so instances can be caught as either
 * {@see \Error} or {@see \Throwable}. Carries a custom string $code
 * (e.g. "400", "404") alongside a human-readable message and optional
 * contextual $data payload.
 *
 * Every subclass exception is automatically logged to the console at
 * ERROR level upon construction.
 */
abstract class BaseException extends Error
{
    protected $code;
    protected $message;
    protected $data = null;

    /**
     * @param string     $message Human-readable description of the error.
     * @param string     $code    Application-level error code (e.g. "400", "404").
     * @param mixed|null $data    Optional contextual data attached to the error.
     */
    public function __construct(string $message = "", string $code = "", $data = null)
    {
        $this->code    = $code;
        $this->message = $message;
        $this->data    = $data;

        Console::writeLine("Exception [{$code}]: {$message}", 'e');

        parent::__construct("Error [{$code}] : {$message}", 422, null);
    }
}
