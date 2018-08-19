<?php

namespace CrixuAMG\Decorators\Test\Providers;

use CrixuAMG\Decorators\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class TestRepository
 * @package CrixuAMG\Decorators\Test\Providers
 */
class TestRepository extends AbstractRepository implements TestContract
{
    /**
     * TestRepository constructor.
     */
    public function __construct()
    {
        $this->setModel(new TestModel);
    }

    /**
     * Returns the index
     *
     * @return mixed
     */
    public function index()
    {
        return new Collection();
    }

    /**
     * Create a new model
     *
     * @param array $data
     *
     * @return mixed
     */
    public function store(array $data)
    {
        return new TestModel();
    }
}
