<?php

namespace CrixuAMG\Decorators\Traits;

/**
 * Trait SmartReturns
 *
 * @package CrixuAMG\Decorators\Traits
 */
trait SmartReturns
{
    /**
     * @param array $smartArguments
     * @param callable $normalResponse
     *
     * @return mixed
     */
    public function smartReturnOr(array $smartArguments, callable $normalResponse)
    {
        return $this->smartReturn(...$smartArguments) ?: $normalResponse();
    }

    /**
     * @param mixed ...$arguments
     *
     * @return mixed
     */
    public function smartReturn(...$arguments)
    {
        $method = request()->__smart;

        return $method && is_callable(get_called_class(), $method)
            ? $this->$method(...$arguments)
            : false;
    }
}
