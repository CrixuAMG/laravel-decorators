<?php

namespace CrixuAMG\Decorators\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Trait Transactionable
 *
 * @package CrixuAMG\Decorators\Traits
 */
trait Transactionable
{
    /**
     * @param \Closure $callback The callback to execute in the transaction
     * @param int      $attempts The amount of attempts
     *
     * @return mixed
     */
    public function transaction(\Closure $callback, int $attempts = 1)
    {
        return DB::transaction($callback, $attempts);
    }
}
