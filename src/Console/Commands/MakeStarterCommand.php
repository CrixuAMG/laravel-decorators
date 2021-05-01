<?php

namespace CrixuAMG\Decorators\Console\Commands;

use Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class FullMakeCommand
 *
 * @package CrixuAMG\Decorators\Console\Commands
 */
class MakeStarterCommand extends Command
{
    private $generatedClasses = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'decorators:starter {--module=}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all required classes for the given name';

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
        $commandsToExecute = [
            'make:model'            => '',
            'make:resource'         => 'Resource',
            'make:factory'          => 'Factory',
            'decorators:controller' => 'Controller',
            'decorators:contract'   => 'Contract',
            'decorators:repository' => 'Repository',
            'decorators:cache'      => 'Cache',
        ];
        $module = $this->option('module');

        $className = $this->getNameInput();
        $classNameTemp = null;

        foreach ($commandsToExecute as $commandToExecute => $type) {
            $classNameTemp = $className;
            $append = '';

            if ($module) {
                $className = $module.'\\'.$className;
            }

            if ($commandToExecute === 'make:model') {
                $className = config('nextlevel.model_namespace').$className;
            }
            if ($commandToExecute === 'decorators:controller') {
                $className = 'Api\\'.$className;
                $append = ' --module='.$module.' --model='.$classNameTemp;

                if ($this->option('request')) {
                    $append .= ' --request';
                }
            }

            $this->addToGenerated($commandToExecute, $className);

            $command = $commandToExecute.' '.$className.$type.$append;

            $this->info('php artisan '.$command);

            $command = str_replace("\\", '/', $command);

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

    /**
     * Create two request files for the model.
     *
     * @param  string|null  $module
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
            $name = $module.'\\'.$name;
        }

        foreach ($nameExtensions as $nameExtension) {
            $this->info('php artisan make:request '.$name.'\\'.$nameExtension.'Request');

            Artisan::call('make:request', [
                'name' => $name.'\\'.$nameExtension.'Request',
            ]);
        }

        // Create a policy
        $this->info('php artisan make:policy '.$name.'Policy');

        $this->addToGenerated('make:policy', $name);

        Artisan::call('make:policy', [
            'name' => $name.'Policy',
        ]);
    }

    /**
     * Create a new decorator class for the model.
     *
     * @param  string|null  $module
     * @return void
     */
    private function createDecorator(string $module = null)
    {
        $name = $this->getNameInput();

        if ($module) {
            $name = $module.'\\'.$name;
        }

        $this->info('php artisan decorators:decorator '.$name.'Decorator');

        $this->addToGenerated('decorators:decorator', $name.'Decorator');

        Artisan::call('decorators:decorator', [
            'name' => $name.'Decorator',
        ]);
    }

    /**
     * Create a new seeder class for the model.
     *
     * @param  string|null  $module
     * @return void
     */
    private function createSeeder(string $module = null)
    {
        $name = $this->getNameInput();

        if ($module) {
            $name = $module.'/'.$name;
        }

        $this->info('php artisan make:seeder '.$name.'Seeder');

        Artisan::call('make:seeder', [
            'name' => $name.'Seeder',
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

    private function addToGenerated(string $command, string $className)
    {
        $classToGenerate = Str::after($command, 'make:');
        if ($classToGenerate === $command) {
            // Was decorator command
            $classToGenerate = str_replace(['decorators:', 'make:'], '', $command);
        }

        $classesToRegister = [
            'repository',
            'cache',
            'contract',
            'decorator',
            'model',
        ];

        if (!in_array($classToGenerate, $classesToRegister)) {
            return;
        }

        $folder = Str::ucfirst(
            Str::plural($classToGenerate)
        );

        $fullNamespace = 'App\\'.$folder.'\\'.$className;
        $snakedClassname = Str::snake($this->getNameInput());

        $key = '__arguments';
        if ($folder === 'Contracts') {
            $key = '__contract';
        }
        if ($folder === 'Models') {
            $key = '__model';
        }

        $fullyQualifiedClassName = $folder === 'Models'
            ? $fullNamespace
            : $fullNamespace.Str::ucfirst($classToGenerate);

        $this->addToGeneratedList($snakedClassname, $key, $fullyQualifiedClassName);
    }

    private function addToGeneratedList(string $className, string $key, $value)
    {
        $snakedModule = Str::snake($this->option('module'));
        if ($snakedModule) {
            if ($key === '__arguments') {
                $this->generatedClasses[$snakedModule][$className][$key][] = $value;
            } else {
                $this->generatedClasses[$snakedModule][$className][$key] = $value;
            }

            return;
        }

        if ($key === '__arguments') {
            $this->generatedClasses[$className][$key][] = $value;
        } else {
            $this->generatedClasses[$className][$key] = $value;
        }
    }

    private function convertGeneratedClassesToCode(array $array, int $depth = 0)
    {
        $output = '';
        $indent = '';
        $addClosingBracket = true;

        if ($depth > 0) {
            $indent = str_repeat("\t", $depth);
        }

        foreach ($array as $key => $value) {
            if (is_string($key) && !$value) {
                // Add start of array
                $output .= "$indent'$key' => [".PHP_EOL;
            } elseif (is_string($key) && is_array($value)) {
                // Add a key and the corresponding array value
                $output .= "$indent'$key' => [".PHP_EOL.$this->convertGeneratedClassesToCode($value, $depth + 1);
            } elseif (is_string($key) && is_string($value)) {
                // Add string to string values
                $output .= "$indent'$key' => $value::class,".PHP_EOL;
            } elseif (is_int($key) && is_string($value)) {
                // Add only string values
                $output .= "$indent $value::class,".PHP_EOL;
                $addClosingBracket = false;
            }
        }

        if ($addClosingBracket) {
            $output .= "$indent],".PHP_EOL;
        }

        return $output;
    }

    private function showConfigInfo()
    {
        $output = $this->convertGeneratedClassesToCode($this->generatedClasses);

        $snakedModule = Str::snake($this->option('module'));
        $moduleText = !empty($snakedModule) && config('decorators.tree.'.$snakedModule)
            ? PHP_EOL."Note: Add the inner array to the decorators.tree.$snakedModule array"
            : '';

        echo <<< CONFIG

To enable the classes generated, simply add the array listed below to the tree array in your decorators.php $moduleText

$output
CONFIG;
    }
}
