<?php

namespace CrixuAMG\Decorators\Test\Http\Controllers;

use CrixuAMG\Decorators\Http\Controllers\AbstractController;
use CrixuAMG\Decorators\Test\Http\Resources\TestResource;
use CrixuAMG\Decorators\Test\Providers\TestCache;
use CrixuAMG\Decorators\Test\Providers\TestContract;
use CrixuAMG\Decorators\Test\Providers\TestDecorator;
use CrixuAMG\Decorators\Test\Providers\TestModel;
use CrixuAMG\Decorators\Test\Providers\TestRepository;
use Illuminate\Database\Eloquent\Model;

class TestController extends AbstractController implements TestContract
{
    public function __construct()
    {
        $this->setup(
            [
                'contract'  => TestContract::class,
                'arguments' => [
                    TestRepository::class,
                    TestCache::class,
                    TestDecorator::class,
                ],
                'model'     => new TestModel(),
            ],
            TestResource::class
        );
    }

    public function index()
    {
        return $this->forwardResourceful(__FUNCTION__);
    }

    public function show(Model $model, ...$relations)
    {
        return $this->forwardResourceful(__FUNCTION__, $model, ...$relations);
    }

    public function store(array $data)
    {
        return $this->forwardResourceful(__FUNCTION__, $data);
    }

    public function update(Model $model, array $data)
    {
        return $this->forwardResourceful(__FUNCTION__, $model, $data);
    }

    public function destroy(Model $model)
    {
        return $this->forwardResourceful(__FUNCTION__, $model);
    }

    public function definition()
    {
        return $this->forwardResourceful(__FUNCTION__);
    }

    public function get(int $number): int
    {
        return $this->forwardResourceful(__FUNCTION__, $number);
    }

    public function getWithoutCacheParameters(int $number): int
    {
        return $this->forwardResourceful(__FUNCTION__, $number);
    }
}