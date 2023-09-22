<?php

namespace CrixuAMG\Decorators\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MovePackageToAnotherModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'decorators:move {model} --from --to';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish stubs';

    public function handle()
    {
        $model = $this->argument('model');
        $fromModule = $this->option('from');
        $toModule = $this->option('to');

        $files = [
            [
                'path'     => 'Models',
                'name_ext' => '',
            ],
            [
                'path'     => 'Caches',
                'name_ext' => 'Cache',
            ],
            [
                'path'     => 'Contracts',
                'name_ext' => 'Contract',
            ],
            [
                'path'     => 'Repositories',
                'name_ext' => 'Repository',
            ],
            [
                'path'     => 'Decorators',
                'name_ext' => 'Decorator',
            ],
            [
                'path'     => 'Definitions',
                'name_ext' => 'Definition',
            ],
            [
                'path'     => 'Http/Controllers/Api',
                'name_ext' => 'Controller',
            ],
            [
                'path'     => 'Http/Controllers/Web',
                'name_ext' => 'Controller',
            ],
            [
                'path'     => 'Http/Resources',
                'name_ext' => 'Resource',
            ],
        ];

        $requests = ['Show', 'Store', 'Update', 'Delete'];
        foreach ($requests as $request) {
            $files[] = [
                'path'                => 'Http/Requests',
                'file_name_formatter' => fn() => $model . '/' . $request . 'Request',
            ];
        }

        $fileRows = [];

        foreach ($files as $file) {
            $from = $this->getFilePath($file)['from'];
            $to = $this->getFilePath($file)['to'];

            if (file_exists($from)) {
                $fileRows[] = [
                    $this->pathFromProjectRoot($from),
                    $this->pathFromProjectRoot($to),
                    '',
                ];
            } else {
                $fileRows[] = [
                    $this->pathFromProjectRoot($from),
                    '',
                    'skipped',
                ];
            }
        }

        $this->table(['from', 'to', 'errors'], $fileRows);

        foreach ($files as $file) {
            $from = $this->getFilePath($file)['from'];
            $to = $this->getFilePath($file)['to'];

            if (file_exists($from)) {
                $pathParts = Str::of($from)->explode('/');
                $pathParts->pop();
                $fromPath = $pathParts->join('/');
                $pathParts->pop();
                $fromParentPath = $pathParts->join('/');
                @mkdir($fromPath, 0777, true);

                $pathParts = Str::of($to)->explode('/');
                $pathParts->pop();
                $toPath = $pathParts->join('/');
                @mkdir($toPath, 0777, true);

                // Move file and update namespace
                file_put_contents($to,
                    Str::of(file_get_contents($from))->replaceFirst(
                        "App\\{$file['path']}\\{$fromModule}",
                        "App\\{$file['path']}\\{$toModule}",
                    ),
                );

                // Remove the original file
                @unlink($from);
                if (!(new \FilesystemIterator($fromPath))->valid()) {
                    // If there are no more files in the directory, remove the directory
                    @unlink($fromPath);

                    if (!(new \FilesystemIterator($fromParentPath))->valid()) {
                        $this->info('Removed empty directory: ' . $this->pathFromProjectRoot($fromParentPath));

                        // If there are no more files in the parent directory, remove the parent directory as well
                        @unlink($fromParentPath);
                    } else {
                        $this->info('Removed empty directory: ' . $this->pathFromProjectRoot($fromPath));
                    }
                }
            }
        }

        $this->info('Done!');
        $this->info('Make sure to edit your decorators config file by moving the package to the new module and update the path in the setup function call in the controller');
        $this->info('To finalize moving the package to another module, test your code, import any missing or update incorrect imports and enjoy!');
    }

    private function getFilePath(array $file)
    {
        $model = $this->argument('model');
        $fromModule = $this->option('from');
        $toModule = $this->option('to');
        $ext = '.php';

        $fileName = $model . ($file['name_ext'] ?? '');

        if (isset($file['file_name_formatter']) && is_callable($file['file_name_formatter'])) {
            $fileName = $file['file_name_formatter']();
        }

        return [
            'from' => app_path($file['path'] . '/' . $fromModule . '/' . $fileName . $ext),
            'to'   => app_path($file['path'] . '/' . $toModule . '/' . $fileName . $ext),
        ];
    }

    private function pathFromProjectRoot(string $path)
    {
        return (string)Str::of($path)->remove(base_path() . '/');
    }

    protected function getArguments()
    {
        return [
            [
                'model',
                InputArgument::REQUIRED,
                'Model name.',
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
            ],
            [
                'from',
                'from',
                InputOption::VALUE_REQUIRED,
                'Move from module.',
            ],
            [
                'to',
                'to',
                InputOption::VALUE_REQUIRED,
                'Move to module.',
            ],
        ];
    }
}