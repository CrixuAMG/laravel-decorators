<?php

namespace CrixuAMG\Decorators;

class Handler
{
    private $handler;

    public function __construct($chain)
    {
        if(!empty($chain))
        {
            $this->handler = static::makeChain($chain);
        }
    }

    public static function makeChain($chain)
    {
        $chain = is_array($chain)
            ? $chain
            : [$chain];

        return $chain 
            ? self::handlerFactory($chain)
            : [];
    }

    public static function handlerFactory(array $chain)
    {
        $instance = null;

        $reversedChain = array_reverse($chain);
        foreach($reversedChain as $class)
        {
            $instance = new $class($instance);
        }

        return $instance;
    }
}
