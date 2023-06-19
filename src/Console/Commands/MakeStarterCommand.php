<?php

namespace CrixuAMG\Decorators\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use CrixuAMG\Decorators\Services\ConfigResolver;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class FullMakeCommand
 *
 * @package CrixuAMG\Decorators\Console\Commands
 */
class MakeStarterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'decorators:starter {--module=} {--definition} {--web}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all required classes for the given name';
    /**
     * @var string
     */
    private $generatedClasses = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $module = $this->option('module');
        $commandsToExecute = [
            'make:model'            => '',
            'decorators:controller' => 'Controller',
            'make:resource'         => 'Resource',
            'decorators:contract'   => 'Contract',
        ];

        if ($this->option('definition')) {
            $commandsToExecute['decorators:definition'] = 'Definition';
        }

        if ($module) {
            $commandsToExecute['decorators:route-file'] = $module;
        }

        $commandsToExecute = array_merge($commandsToExecute, [
            'decorators:repository' => 'Repository',
            'decorators:cache'      => 'Cache',
        ]);

        $className = $this->getNameInput();

        foreach ($commandsToExecute as $commandToExecute => $type) {
            $classNameTemp = $className;
            $append = '';

            if ($module) {
                $className = $module . '/' . $className;
            }

            if ($commandToExecute === 'make:model') {
                $className = config('decorators.model_namespace') . $className;
                $append = ' --factory';
            }
            if ($commandToExecute === 'decorators:controller') {
                $controllerNamespace = $this->option('web') ? 'Web' : 'Api';
                $className = $controllerNamespace.'/' . $className;
                $append = ' --module=' . $module . ' --model=' . $classNameTemp;

                if ($this->option('request')) {
                    $append .= ' --request';
                }
            }
            if ($commandToExecute === 'decorators:route-file') {
                $className = $module;
                $type = null;
                $append = null;
            }

            $this->addToGenerated($commandToExecute, $className);

            $command = $commandToExecute . ' ' . $className . $type . $append;

            $this->info('php artisan ' . $command);

            Artisan::call($command);

            if ($classNameTemp !== null) {
                $className = $classNameTemp;
            }
        }

        if ($this->option('request')) {
            $this->info('Creating the requests');

            $this->createRequests($module);
        }

        if ($this->option('decorator')) {
            $this->info('Creating the decorator');

            $this->createDecorator($module);
        }

        if ($this->option('seeder')) {
            $this->info('Creating the seeder');

            $this->createSeeder($module);
        }

        if ($this->option('migration')) {
            $this->info('Creating the migration');

            $this->createMigration();
        }

        $this->showConfigInfo();
    }

    /**
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name'));
    }

    private function addToGenerated(string $command, string $className)
    {
        $classToGenerate = Str::after($command, 'make:');
        if ($classToGenerate === $command) {
            // Was decorator command
            $classToGenerate = str_replace([
                'decorators:',
                'make:',
            ], '', $command);
        }

        $classesToRegister = [
            'repository',
            'cache',
            'contract',
            'decorator',
            'model',
            'definition',
        ];

        if (!in_array($classToGenerate, $classesToRegister)) {
            return;
        }

        $folder = Str::ucfirst(
            Str::plural($classToGenerate)
        );

        $fullNamespace = 'App/' . $folder . '/' . $className;
        $snakedClassname = Str::snake($this->getNameInput());

        $key = 'arguments';
        if ($folder === 'Contracts') {
            $key = 'contract';
        }
        if ($folder === 'Models') {
            $key = 'model';
        }
        if ($folder === 'Definitions') {
            $key = 'definition';
        }

        $fullyQualifiedClassName = $folder === 'Models'
            ? $fullNamespace
            : $fullNamespace . Str::ucfirst($classToGenerate);

        $this->addToGeneratedList($snakedClassname, $key, $fullyQualifiedClassName);
    }

    private function addToGeneratedList(string $className, string $key, $value)
    {
        $snakedModule = Str::snake($this->option('module'));
        if ($snakedModule) {
            if ($key === 'arguments') {
                $this->generatedClasses[$snakedModule][$className][$key][] = $value;
            } else {
                $this->generatedClasses[$snakedModule][$className][$key] = $value;
            }

            return;
        }

        if ($key === 'arguments') {
            $this->generatedClasses[$className][$key][] = $value;
        } else {
            $this->generatedClasses[$className][$key] = $value;
        }
    }

    /**
     * Create two request files for the model.
     *
     * @param string|null $module
     *
     * @return void
     */
    protected function createRequests(string $module = null)
    {
        $name = $this->getNameInput();

        $nameExtensions = [
            'Show',
            'Store',
            'Update',
            'Delete',
        ];

        if ($module) {
            $name = $module . '/' . $name;
        }

        foreach ($nameExtensions as $nameExtension) {
            $this->info('php artisan make:request ' . $name . '/' . $nameExtension . 'Request');

            Artisan::call('make:request', [
                'name' => $name . '/' . $nameExtension . 'Request',
            ]);
        }

        // Create a policy
        $this->info('php artisan make:policy ' . $name . 'Policy');

        $this->addToGenerated('make:policy', $name);

        Artisan::call('make:policy', [
            'name' => $name . 'Policy',
        ]);
    }

    /**
     * Create a new decorator class for the model.
     *
     * @param string|null $module
     *
     * @return void
     */
    private function createDecorator(string $module = null)
    {
        $name = $this->getNameInput();

        if ($module) {
            $name = $module . '/' . $name;
        }

        $this->info('php artisan decorators:decorator ' . $name . 'Decorator');

        $this->addToGenerated('decorators:decorator', $name);

        Artisan::call('decorators:decorator ' . $name . 'Decorator');
    }

    /**
     * Create a new seeder class for the model.
     *
     * @param string|null $module
     *
     * @return void
     */
    private function createSeeder(string $module = null)
    {
        $name = $this->getNameInput();

        if ($module) {
            $name = $module . '/' . $name;
        }

        $this->info('php artisan make:seeder ' . $name . 'Seeder');

        Artisan::call('make:seeder', [
            'name' => $name . 'Seeder',
        ]);
    }

    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createMigration()
    {
        $table = Str::plural(Str::snake(class_basename($this->getNameInput())));

        $this->info("php artisan make:migration create_{$table}_table");

        $this->call('make:migration', [
            'name' => "create_{$table}_table",
        ]);
    }

    private function showConfigInfo()
    {
        $output = ConfigResolver::generateConfiguration($this->generatedClasses);

        $module = $this->option('module');
        $snakedModule = Str::snake($module);
        $moduleText = !empty($snakedModule) && config('decorators.tree.' . $snakedModule)
            ? PHP_EOL . "Note: Add the inner array to the decorators.tree.$snakedModule array if it already exists"
            : '';
        
        $moduleRouteText = $module ? <<< MODULE
Register the route module by putting the following in your routes/api.php: 

Route::module('$snakedModule', [
    'namespace' => '$module',
]);
MODULE : '';

        echo <<< CONFIG

To enable the classes generated, simply add the array listed below to the tree array in your decorators.php $moduleText

$output

$moduleRouteText

CONFIG;
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
            [
                'module',
                'module',
                InputOption::VALUE_REQUIRED,
                'Put the generated files inside of a module folder.',
            ],
            [
                'definition',
                'definition',
                InputOption::VALUE_NONE,
                'Create a new definition file for the model.',
            ],
            [
                'web',
                'web',
                InputOption::VALUE_NONE,
                'Register controller in the Web namespace instead of Api.',
            ],
            [
                'migration',
                'm',
                InputOption::VALUE_NONE,
                'Create a new migration file for the model.',
            ],
            [
                'request',
                'r',
                InputOption::VALUE_NONE,
                'Create two new request files for the model.',
            ],
            [
                'decorator',
                'd',
                InputOption::VALUE_NONE,
                'Create a new decorator class for the model.',
            ],
            [
                'seeder',
                's',
                InputOption::VALUE_NONE,
                'Create a new seeder class for the model.',
            ],
        ];
    }
}
