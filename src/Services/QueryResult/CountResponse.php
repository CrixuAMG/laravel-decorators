<?php

namespace CrixuAMG\Decorators\Services\QueryResult;

/**
 *
 */
class CountResponse
{
    /**
     * @var
     */
    private $count;

    public function __construct(int $count)
    {
        $this->count = $count;
    }

    /**
     * @param mixed $count
     */
    public static function setCount($count): self
    {
        return new self($count);
    }

    /**
     * @return array
     */
    public function toResponse(): array
    {
        return ['count' => $this->count];
    }
}
