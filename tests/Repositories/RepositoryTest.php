<?php

namespace CrixuAMG\Decorators\Test\Repositories;

use CrixuAMG\Decorators\Caches\Cache;
use CrixuAMG\Decorators\Decorator;
use CrixuAMG\Decorators\Test\Providers\TestCache;
use CrixuAMG\Decorators\Test\Providers\TestContract;
use CrixuAMG\Decorators\Test\Providers\TestDecorator;
use CrixuAMG\Decorators\Test\Providers\TestModel;
use CrixuAMG\Decorators\Test\Providers\TestRepository;
use CrixuAMG\Decorators\Test\TestCase;
use CrixuAMG\Decorators\Traits\HasForwarding;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class RepositoryTest
 * @package CrixuAMG\Decorators\Test\Repositories
 */
class RepositoryTest extends TestCase
{
    use HasForwarding;
    /**
     * @var
     */
    private $instance;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        Cache::enabled(true);
        $this->decorator = app(Decorator::class);
        $this->decorator->decorate(TestContract::class, [
            TestRepository::class,
            TestCache::class,
            TestDecorator::class,
        ]);

        $this->instance = app(TestContract::class);

        $this->setNext($this->instance);
    }

    /**
     * @test
     */
    public function it_can_get_index_results()
    {
        $result = $this->forward('index');

        $this->assertInstanceOf(Collection::class, $result);
    }

    /**
     * @test
     */
    public function it_can_store_a_new_model()
    {
        $result = $this->forward('store', []);

        $this->assertInstanceOf(TestModel::class, $result);
    }
}
