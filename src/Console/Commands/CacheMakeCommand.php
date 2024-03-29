<?php

namespace CrixuAMG\Decorators\Console\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class CacheMakeCommand extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'decorators:cache';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new cache';
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Cache';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../../stubs/cache.stub';
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
        return $rootNamespace . '\Caches';
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

        $name = $this->getNameInput();

        // Fill the cache tags
        $name = "'" . Str::snake(Str::plural(explode($this->type, str_replace('\\', '.', $name))[0])) . "'";

        $name = str_replace([
            '._',
            "/_",
        ], '.', $name);

        $stub = str_replace('DummyCacheTags', $name, $stub);

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
