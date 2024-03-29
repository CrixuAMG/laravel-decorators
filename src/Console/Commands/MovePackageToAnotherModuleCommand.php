<?php

namespace CrixuAMG\Decorators\Console\Commands;

use FilesystemIterator;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
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
            [
                'path'                => 'module',
                'file_name_formatter' => fn($module) => Str::snake($module),
                'prefix'              => 'routes',
                'moduled'             => false,
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
            $to = $this->getFilePath($file, $file['prefix'] ?? 'app')['to'];

            if (file_exists($from)) {
                $message = '';

                try {
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

                    $replacedSlashesPath = str_replace('/', "\\", $file['path']);

                    // Move file and update namespace
                    file_put_contents(
                        $to,
                        Str::of(file_get_contents($from))
                            ->replace(
                                "App\\{$replacedSlashesPath}\\{$fromModule}",
                                "App\\{$replacedSlashesPath}\\{$toModule}",
                            )
                            ->replaceMatches(
                                "/\\\\" . $fromModule . "\\\\/",
                                "\\" . $toModule . "\\",
                            ),
                    );

                    // Remove the original file
                    @unlink($from);
                    if (!(new FilesystemIterator($fromPath))->valid()) {
                        // If there are no more files in the directory, remove the directory
                        rmdir($fromPath);

                        if (!(new FilesystemIterator($fromParentPath))->valid()) {
                            $this->info('Removed empty directory: ' . $this->pathFromProjectRoot($fromParentPath));

                            // If there are no more files in the parent directory, remove the parent directory as well
                            rmdir($fromParentPath);
                        } else {
                            $this->info('Removed empty directory: ' . $this->pathFromProjectRoot($fromPath));
                        }
                    }
                } catch (\Throwable $e) {
                    $message = $e->getMessage();
                }

                $fileRows[] = [
                    $this->pathFromProjectRoot($from),
                    $this->pathFromProjectRoot($to),
                    $message,
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

        $this->info('Done!');
        $this->info('To finalize moving the package to another module, test your code, import any missing or update incorrect imports and enjoy!');
    }

    private function getFilePath(array $file, string $prefix = 'app')
    {
        $model = $this->argument('model');
        $fromModule = $this->option('from');
        $toModule = $this->option('to');
        $ext = '.php';

        $fileNameFrom = $model . ($file['name_ext'] ?? '');
        $fileNameTo = $model . ($file['name_ext'] ?? '');

        if (isset($file['file_name_formatter']) && is_callable($file['file_name_formatter'])) {
            $fileNameFrom = $file['file_name_formatter']($fromModule);
            $fileNameTo = $file['file_name_formatter']($toModule);
        }

        $moduledFromPath = Arr::get($file, 'moduled', true)
            ? $fromModule . '/' . $fileNameFrom
            : $fileNameFrom;
        $moduledToPath = Arr::get($file, 'moduled', true)
            ? $toModule . '/' . $fileNameTo
            : $fileNameTo;

        return [
            'from' => base_path($prefix . '/' . $file['path'] . '/' . $moduledFromPath . $ext),
            'to'   => base_path($prefix . '/' . $file['path'] . '/' . $moduledToPath . $ext),
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
