<?php

namespace CrixuAMG\Decorators\Exceptions;

class DefinitionTraitNotSetOnModelException extends BaseException
{
    protected $message = 'Definition trait was not defined on the requested model.';
}
