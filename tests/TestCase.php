<?php

namespace CrixuAMG\Decorators\Test;

use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Class TestCase
 *
 * @package CrixuAMG\Decorators\Test
 */
abstract class TestCase extends Orchestra
{
    /**
     *
     */
    public function setUp(): void
    {
        parent::setUp();
    }
}
