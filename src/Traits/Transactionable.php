<?php

namespace CrixuAMG\Decorators\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Trait Transactionable
 * @package CrixuAMG\Decorators\Traits
 */
trait Transactionable
{
    /**
     * @param \Closure $callback
     * @param int      $attempts
     *
     * @return mixed
     */
    public function transaction(\Closure $callback, int $attempts = 1)
    {
        return DB::transaction($callback, $attempts);
    }
}