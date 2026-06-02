<?php

namespace Cuakx\Core\Constant;

class CommonCodes
{
    public const SUCCESS = "00";
    public const GENERAL_ERROR = "999";

    // HTTP Codes

     // 1xx Informational
    public const HTTP_CONTINUE = "100";
    public const HTTP_SWITCHING_PROTOCOLS = "101";
    public const HTTP_PROCESSING = "102";

    // 2xx Success
    public const HTTP_OK = "200";
    public const HTTP_CREATED = "201";
    public const HTTP_ACCEPTED = "202";
    public const HTTP_NON_AUTHORITATIVE_INFORMATION = "203";
    public const HTTP_NO_CONTENT = "204";
    public const HTTP_RESET_CONTENT = "205";
    public const HTTP_PARTIAL_CONTENT = "206";

    // 3xx Redirection
    public const HTTP_MULTIPLE_CHOICES = "300";
    public const HTTP_MOVED_PERMANENTLY = "301";
    public const HTTP_FOUND = "302";
    public const HTTP_SEE_OTHER = "303";
    public const HTTP_NOT_MODIFIED = "304";
    public const HTTP_TEMPORARY_REDIRECT = "307";
    public const HTTP_PERMANENT_REDIRECT = "308";

    // 4xx Client Errors
    public const HTTP_BAD_REQUEST = "400";
    public const HTTP_UNAUTHORIZED = "401";
    public const HTTP_PAYMENT_REQUIRED = "402";
    public const HTTP_FORBIDDEN = "403";
    public const HTTP_NOT_FOUND = "404";
    public const HTTP_METHOD_NOT_ALLOWED = "405";
    public const HTTP_NOT_ACCEPTABLE = "406";
    public const HTTP_REQUEST_TIMEOUT = "408";
    public const HTTP_CONFLICT = "409";
    public const HTTP_GONE = "410";
    public const HTTP_LENGTH_REQUIRED = "411";
    public const HTTP_PRECONDITION_FAILED = "412";
    public const HTTP_PAYLOAD_TOO_LARGE = "413";
    public const HTTP_URI_TOO_LONG = "414";
    public const HTTP_UNSUPPORTED_MEDIA_TYPE = "415";
    public const HTTP_UNPROCESSABLE_ENTITY = "422";
    public const HTTP_TOO_MANY_REQUESTS = "429";

    // 5xx Server Errors
    public const HTTP_INTERNAL_SERVER_ERROR = "500";
    public const HTTP_NOT_IMPLEMENTED = "501";
    public const HTTP_BAD_GATEWAY = "502";
    public const HTTP_SERVICE_UNAVAILABLE = "503";
    public const HTTP_GATEWAY_TIMEOUT = "504";
}
