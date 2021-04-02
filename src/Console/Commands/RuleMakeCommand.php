<?php

namespace CrixuAMG\Decorators\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\InputOption;

class RuleMakeCommand extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'decorators:rule {--module=}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new rule';
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Rule';

    public function handle()
    {
        Artisan::call("make:rule ".$this->option('module').'/'.$this->getNameInput());
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

    protected function getStub()
    {
        // TODO: Implement getStub() method.
    }
}
