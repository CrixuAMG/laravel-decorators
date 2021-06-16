<?php

namespace CrixuAMG\Decorators\Console\Commands;

use Artisan;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DecoratorsMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'decorators:make';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Contract, Cache, Repository for the given name';

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
            'decorators:contract'   => 'Contract',
            'decorators:cache'      => 'Cache',
            'decorators:repository' => 'Repository',
        ];

        $className = $this->getNameInput();

        foreach ($commandsToExecute as $commandToExecute => $extension) {
            $this->info('php artisan '.$commandToExecute.' '.$className.$extension);

            Artisan::call($commandToExecute, [
                'name' => $className.$extension,
            ]);
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
