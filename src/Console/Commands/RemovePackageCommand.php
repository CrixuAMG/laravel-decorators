<?php

namespace CrixuAMG\Decorators\Console\Commands;

use Throwable;
use FilesystemIterator;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RemovePackageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'decorators:remove {model} --from';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove an entire package';

    public function handle()
    {
        $model = $this->argument('model');
        $fromModule = $this->option('from');

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
                'path'     => 'Policies',
                'name_ext' => 'Policy',
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
            [
                'path'     => 'factories',
                'name_ext' => 'Factory',
                'prefix'   => 'database',
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
            $from = $this->getFilePath($file, $file['prefix'] ?? 'app')['from'];

            if (file_exists($from)) {
                $message = '';
                try {
                    unlink($from);

                    $pathParts = Str::of($from)->explode('/');
                    $pathParts->pop();
                    $fromParentPath = $pathParts->join('/');

                    if (!(new FilesystemIterator($fromParentPath))->valid()) {
                        $this->info('Removed empty directory: ' . $this->pathFromProjectRoot($fromParentPath));

                        // If there are no more files in the parent directory, remove the parent directory as well
                        rmdir($fromParentPath);
                    }
                } catch (Throwable $e) {
                    $message = $e->getMessage();
                }

                $fileRows[] = [
                    $this->pathFromProjectRoot($from),
                    $message,
                ];
            } else {
                $fileRows[] = [
                    $this->pathFromProjectRoot($from),
                    'skipped',
                ];
            }
        }

        $this->table(['from', 'errors'], $fileRows);

        $this->info('Done!');
    }

    private function getFilePath(array $file, string $prefix = 'app')
    {
        $model = $this->argument('model');
        $fromModule = $this->option('from');
        $ext = '.php';

        $fileName = $model . ($file['name_ext'] ?? '');

        if (isset($file['file_name_formatter']) && is_callable($file['file_name_formatter'])) {
            $fileName = $file['file_name_formatter']();
        }

        $moduledPath = Arr::get($file, 'moduled', true)
            ? $fromModule . '/' . $fileName
            : $fileName;

        return [
            'from' => base_path($prefix . '/' . $file['path'] . '/' . $moduledPath . $ext),
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
        ];
    }
}
