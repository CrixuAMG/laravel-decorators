<?php

namespace CrixuAMG\Decorators\Traits;

use Closure;
use Illuminate\Support\Facades\DB;

/**
 * Trait HasTransactions
 *
 * @package CrixuAMG\Decorators\Traits
 */
trait HasTransactions
{
    /**
     * @param Closure $callback The callback to execute in the transaction
     * @param int      $attempts The amount of attempts
     *
     * @return mixed
     */
    public function transaction(Closure $callback, int $attempts = 1)
    {
        return DB::transaction($callback, $attempts);
    }
}
