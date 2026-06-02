<?php

namespace Cuakx\Core\DTO;

use Illuminate\Http\JsonResponse;

/**
 * All API responses are standardized using this DTO.
 *
 * @property bool        $success Indicates if the request was successful.
 * @property string      $code    A code representing the result of the request.
 * @property string      $message A human-readable message providing more details about the result.
 * @property object|null $data    An optional object containing any data returned by the request.
 */
class BaseResponseDTO
{
    public bool $success;
    public string $code;
    public string $message;
    public ?object $data;

    public function __construct(
        bool $success = true,
        string $code = '',
        string $message = '',
        ?object $data = null
    ) {
        $this->success = $success;
        $this->code    = $code;
        $this->message = $message;
        $this->data    = $data;
    }

    /**
     * Creates a standardized error response.
     *
     * @param string $code    A code representing the error (e.g. "400", "404", "500").
     * @param string $message Message providing more details about the error.
     *
     * @return JsonResponse
     */
    public static function error(string $code, string $message): JsonResponse
    {
        return response()->json(new BaseResponseDTO(false, $code, $message));
    }

    /**
     * Creates a standardized success response.
     *
     * @param string      $message Message providing more details about the success.
     * @param object|null $data    An optional object containing any data returned by the request.
     * @param string      $code    HTTP/application code (default: "200").
     *
     * @return JsonResponse
     */
    public static function success(string $message, ?object $data = null, string $code = "00"): JsonResponse
    {
        return response()->json(new BaseResponseDTO(true, $code, $message, $data));
    }
}
