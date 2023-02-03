<?php

namespace CrixuAMG\Decorators\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PublishStubsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'decorators:stubs';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish stubs';

    public function handle()
    {
        if (!is_dir($stubsPath = $this->laravel->basePath('stubs'))) {
            (new Filesystem)->makeDirectory($stubsPath);
        }

        $files = [
            __DIR__ . '/../../stubs/model.stub' => $stubsPath . '/model.stub',
        ];

        foreach ($files as $from => $to) {
            if (!file_exists($to)) {
                file_put_contents($to, file_get_contents($from));
            }
        }
    }
}