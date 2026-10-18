<?php

namespace App\Http\Controllers;

use App\Services\Applications\Api\ApiResponse;
use Throwable;

abstract class Controller
{
    protected function handleRequest(callable $callback)
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            // Catch Errors as well as Exceptions so every failure returns a JSON body
            // instead of a HTML error page (keeps the gateway/client JSON parsing intact).
            return ApiResponse::error($e);
        }
    }
}
