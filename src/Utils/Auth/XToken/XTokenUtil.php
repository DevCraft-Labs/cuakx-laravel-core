<?php

namespace Cuakx\Core\Utils\Auth\XToken;

use Illuminate\Http\Request;

class XTokenUtil
{
    public static function validateToken(string $token, Request $request): bool {
        $stringified_request = json_encode($request->all());

        // If request is empty, and json_encode returns '[]', we need to convert it to '{}' to match the expected hash.
        if($stringified_request == '[]') {
            $stringified_request = '{}';
        }

        $hash = hash_hmac('sha512', $stringified_request, env('APP_KEY'));

        return hash_equals($hash, $token);
    }
}