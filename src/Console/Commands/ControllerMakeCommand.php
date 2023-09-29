<?php

namespace CrixuAMG\Decorators\Console\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ControllerMakeCommand extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'decorators:controller';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new controller';
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $type = $this->option('web') ? 'web' : 'api';

        return $this->option('request')
            ? __DIR__ . '/../../stubs/' . $type . '/' . 'controller_requests.stub'
            : __DIR__ . '/../../stubs/' . $type . '/' . 'controller.stub';
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
        return $rootNamespace . '\Http\Controllers';
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
        $stub = parent::replaceClass($stub, $name);

        $module = $this->option('module')
            ? $this->option('module') . "\\"
            : '';
        $model = $this->option('model');

        // Fill the controller tags
        $name = Str::snake(str_replace('\\', '.', $module . $model));

        $name = str_replace('._', '.', $name);

        $stub = str_replace('DummyDecoratorConfig', $name, $stub);

        $stub = str_replace('DummyModule', $module, $stub);

        $modelVariable = Str::camel($model);
        $modelNamespace = $this->rootNamespace() . "Models\\" . $module . $model;

        $stub = str_replace('DummyModelVariable', $modelVariable, $stub);
        $stub = str_replace('DummyModelNamespace', $modelNamespace, $stub);
        $stub = str_replace('DummyModel', $model, $stub);

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
            [
                'module',
                'module',
                InputOption::VALUE_REQUIRED,
                'Put the generated files inside of a module folder.',
            ],
            [
                'model',
                'model',
                InputOption::VALUE_REQUIRED,
                'The model used by this controller.',
            ],
            [
                'request',
                'request',
                InputOption::VALUE_NONE,
                'Whether or not to add requests into the controller method signatures.',
            ],
            [
                'web',
                'web',
                InputOption::VALUE_NONE,
                'Create a web controller instead of an API focussed controller.',
            ],
        ];
    }
}
