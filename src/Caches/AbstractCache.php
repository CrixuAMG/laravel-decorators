<?php

namespace CrixuAMG\Decorators\Caches;

use CrixuAMG\Decorators\Contracts\RepositoryContract;
use CrixuAMG\Decorators\Repositories\AbstractRepository;

/**
 * Class AbstractCache
 *
 * @package CrixuAMG\Decorators\Caches
 */
abstract class AbstractCache implements RepositoryContract
{
    /**
     * @var AbstractRepository
     */
    protected $next;
    /**
     * @var array
     */
    protected $cacheTags;

    /**
     * AbstractCache constructor.
     *
     * @param AbstractRepository $next
     */
    public function __construct(AbstractRepository $next)
    {
        $this->next = $next;
    }
}