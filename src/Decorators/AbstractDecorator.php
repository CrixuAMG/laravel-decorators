<?php

namespace CrixuAMG\Decorators\Decorators;

use Illuminate\Database\Eloquent\Model;
use CrixuAMG\Decorators\Caches\AbstractCache;
use CrixuAMG\Decorators\Contracts\RepositoryContract;
use CrixuAMG\Decorators\Repositories\AbstractRepository;

abstract class AbstractDecorator implements RepositoryContract
{
    /**
     * @var AbstractCache|AbstractRepository
     */
    protected $next;

    /**
     * AbstractDecorator constructor.
     *
     * @param AbstractRepository $next
     *
     * @throws \Throwable
     */
    public function __construct($next)
    {
        $allowedNextClasses = [
            AbstractDecorator::class,
            AbstractCache::class,
            AbstractRepository::class,
        ];

        throw_unless(
            \in_array(get_parent_class($next), $allowedNextClasses, true),
            'Class does not implement any allowed parent classes.',
            \UnexpectedValueException::class,
            500
        );

        $this->next = $next;
    }

    /**
     * @param $page
     *
     * @return mixed
     */
    public function index($page)
    {
        return $this->next->index($page);
    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function show(Model $model)
    {
        return $this->next->show($model);
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function store(array $data)
    {
        return $this->next->store($data);
    }

    /**
     * @param Model $model
     * @param array $data
     *
     * @return mixed
     */
    public function update(Model $model, array $data)
    {
        return $this->next->update($model, $data);
    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function delete(Model $model)
    {
        return $this->next->delete($model);
    }
}
