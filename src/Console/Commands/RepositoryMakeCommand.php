<?php

namespace CrixuAMG\Decorators\Console\Commands;

use Symfony\Component\Console\Input\InputOption;

class RepositoryMakeCommand extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'decorators:repository';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository';
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../../stubs/repository.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Repositories';
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
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
            $stub,
        );
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
