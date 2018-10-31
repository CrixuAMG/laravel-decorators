<?php

namespace CrixuAMG\Decorators\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteDecoratorMatchMissingException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     *
     * @param  Request
     *
     * @return JsonResponse
     */
    public function render($request)
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors'  => [],
        ], $this->getCode());
    }
}
