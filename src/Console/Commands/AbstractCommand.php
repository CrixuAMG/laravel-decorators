<?php

namespace CrixuAMG\Decorators\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

abstract class AbstractCommand extends GeneratorCommand
{
    protected $type;

    /**
     * @return string
     */
    protected function getNameInput()
    {
        $name = trim($this->argument('name'));

        // Check if the string is set, and if not, set it
        if ($this->type && stripos($name, $this->type) === false) {
            $name .= $this->type;
        }

        return $name;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'name',
                InputArgument::REQUIRED,
                'The name of the command.',
            ],
        ];
    }

    protected function replaceContract(string &$stub, $name)
    {
        $contractNamespace = str_replace(
            Str::plural($this->type),
            'Contracts',
            str_replace($this->type, 'Contract', $name)
        );
        $namespaceParts = array_reverse(explode('\\', $contractNamespace));
        $contractClassname = reset($namespaceParts);

        $stub = str_replace('DummyContractNamespace', $contractNamespace, $stub);
        $stub = str_replace('DummyContractClass', $contractClassname, $stub);
    }
}
