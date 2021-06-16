<?php

namespace CrixuAMG\Decorators\Console\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class DecoratorMakeCommand
 *
 * @package CrixuAMG\Decorators\Console\Commands
 */
class DecoratorMakeCommand extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'decorators:decorator';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new decorator';
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Decorator';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/decorator.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Decorators';
    }

    /**
     * @return string
     */
    protected function getNameInput()
    {
        $name = trim($this->argument('name'));

        // Check if the string is set, and if not, set it
        if (stripos($name, $this->type) === false) {
            $name .= $this->type;
        }

        return $name;
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $this->replaceContract($stub, $name);

        $stub = parent::replaceClass($stub, $name);

        return str_replace(
            [

                'RootNamespace\\',
                'dummy:command',
            ],
            [
                $this->rootNamespace(),
                $this->option('command'),
            ],
            $stub
        );
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

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            [
                'command',
                null,
                InputOption::VALUE_OPTIONAL,
                'The terminal command that should be assigned.',
                'command:name',
            ],
        ];
    }
}
