<?php

namespace CrixuAMG\Decorators\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BaseException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     *
     * @param Request
     *
     * @return JsonResponse
     */
    public function render(): JsonResponse
    {
        $message = $this->getMessage();
        $code = $this->getCode() ?? 500;

        return response()->json([
            'message' => $message,
            'errors'  => [],
        ], $code);
    }
}
