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
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'decorators:starter';
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
            'make:controller'       => 'Controller',
            'make:resource'         => 'Resource',
            'decorators:contract'   => 'Contract',
            'decorators:cache'      => 'Cache',
            'decorators:repository' => 'Repository',
        ];

        $className = $this->getNameInput();
        $classNameTemp = null;

        foreach ($commandsToExecute as $commandToExecute => $type) {
            if ($commandToExecute === 'make:model') {
                $classNameTemp = $className;
                $className = config('nextlevel.model_namespace') . $className;
            }
            if ($commandToExecute === 'make:controller') {
                $classNameTemp = $className;
                $className = 'Api\\' . $className;
            }

            $this->info('php artisan ' . $commandToExecute . ' ' . $className . $type);

            Artisan::call($commandToExecute, [
                'name' => $className . $type,
            ]);

            if ($classNameTemp !== null) {
                $className = $classNameTemp;
            }
        }

        if ($this->option('request')) {
            $this->info('Creating the requests');

            $this->createRequests();
        }

        if ($this->option('decorator')) {
            $this->info('Creating the decorator');

            $this->createDecorator();
        }

        if ($this->option('seeder')) {
            $this->info('Creating the seeder');

            $this->createSeeder();
        }

        if ($this->option('migration')) {
            $this->info('Creating the migration');

            $this->createMigration();
        }
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
     * @return void
     */
    protected function createRequests()
    {
        $name = $this->getNameInput();

        $nameExtensions = [
            'Show',
            'Store',
            'Update',
            'Delete',
        ];

        foreach ($nameExtensions as $nameExtension) {
            $this->info('php artisan make:request ' . $name . '\\' . $nameExtension . 'Request');

            Artisan::call('make:request', [
                'name' => $name . '\\' . $nameExtension . 'Request',
            ]);
        }

        // Create a policy
        $this->info('php artisan make:policy ' . $name . 'Policy');

        Artisan::call('make:policy', [
            'name' => $name . 'Policy',
        ]);
    }

    /**
     * Create a new decorator class for the model.
     *
     * @return void
     */
    private function createDecorator()
    {
        $name = $this->getNameInput();

        $this->info('php artisan decorators:decorator ' . $name . 'Decorator');

        Artisan::call('make:decorator', [
            'name' => $name . 'Decorator',
        ]);
    }

    /**
     * Create a new seeder class for the model.
     *
     * @return void
     */
    private function createSeeder()
    {
        $name = $this->getNameInput();

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
