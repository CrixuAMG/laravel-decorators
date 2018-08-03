<?php

namespace CrixuAMG\Decorators\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Class InvalidMatchableProviderException
 * @package CrixuAMG\Decorators\Exceptions
 */
class InvalidMatchableProviderException extends Exception
{
    /**
     * InvalidMatchableProviderException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = null, int $code = 500, Throwable $previous = null)
    {
        parent::__construct($message ?? $this->getExceptionMessage(), $code, $previous);
    }

    /**
     * @return string
     */
    public function getExceptionMessage(): string
    {
        return 'Provider for route matching is invalid.';
    }

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
