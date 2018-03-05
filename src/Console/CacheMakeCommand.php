<?php

namespace CrixuAMG\Decorators\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CacheMakeCommand extends GeneratorCommand
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $name = 'decorator:cache';
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
	protected $type = 'Caches';

	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub()
	{
		return __DIR__ . '/stubs/cache.stub';
	}

	/**
	 * Get the default namespace for the class.
	 *
	 * @param  string $rootNamespace
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
	 * @param  string $stub
	 * @param  string $name
	 *
	 * @return string
	 */
	protected function replaceClass($stub, $name)
	{
		$stub = parent::replaceClass($stub, $name);
		$stub = str_replace('RootNamespace\\', $this->rootNamespace(), $stub);

		return str_replace('dummy:command', $this->option('command'), $stub);
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['name', InputArgument::REQUIRED, 'The name of the command.'],
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
				'command', null, InputOption::VALUE_OPTIONAL, 'The terminal command that should be assigned.',
				'command:name'
			],
		];
	}
}
