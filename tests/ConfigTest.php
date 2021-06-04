<?php

namespace CrixuAMG\Decorators\Test;

use CrixuAMG\Decorators\Services\ConfigResolver;

class ConfigTest extends TestCase
{
    /** @test */
    public function a_value_can_be_retrieved_from_the_config()
    {
        $this->assertEquals('paginate', ConfigResolver::get('default_index_method', 'paginate', true));
    }
}
