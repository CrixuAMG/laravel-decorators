<?php

namespace CrixuAMG\Decorators\Services;

use Symfony\Component\HttpFoundation\Response;

abstract class AbstractValidator
{
    protected $responseCode;

    abstract public function validate(): bool;

    public function getResponseCode(): int
    {
        return $this->responseCode ?? Response::HTTP_FORBIDDEN;
    }
}
