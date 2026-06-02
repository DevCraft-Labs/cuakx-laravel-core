<?php

namespace Cuakx\Core\Http\Controllers;

use Cuakx\Core\Exceptions\BadRequestException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Abstract base controller for all API controllers in Cuakx services.
 *
 * Provides shared request validation and other common functionality that
 * every service controller should inherit.
 */
abstract class BaseController extends Controller
{
    /**
     * Validates the incoming request against the given rules.
     *
     * Runs Laravel's validator with the provided rules and optional custom messages.
     * On the first validation failure, a {@see BadRequestException} is thrown
     * so the caller never needs to inspect a validator result manually — the
     * exception handler takes care of converting it to an HTTP 400 response.
     *
     * Usage:
     * ```php
     * $this->baseValidator($request, [
     *     'name'     => 'required|string|max:255',
     *     'email'    => 'required|string|email|max:255|unique:uma_tbl_users',
     *     'password' => 'required|string|min:8',
     *     'address'  => 'required|string|max:255',
     * ]);
     * ```
     *
     * @param Request $request          The incoming HTTP request.
     * @param array   $rules            Validation rules keyed by field name.
     * @param array   $custom_messages  Optional override messages for specific rules,
     *                                  keyed as "field.rule" (e.g. ['email.unique' => 'Email taken.']).
     *
     * @return bool Returns true when all rules pass.
     *
     * @throws BadRequestException When one or more validation rules fail.
     *                             The exception message contains the first failing field's error.
     */
    protected function baseValidator(Request $request, array $rules, array $custom_messages = []): bool
    {
        $v = validator($request->all(), $rules, $custom_messages);

        if ($v->fails()) {
            throw new BadRequestException($v->errors()->first());
        }

        return true;
    }
}
