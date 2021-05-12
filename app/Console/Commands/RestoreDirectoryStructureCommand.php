<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RestoreDirectoryStructureCommand extends Command
{
    protected $signature = 'directory-structure:restore';

    protected $description = 'Capture current directory structure';

    protected array $files = [];

    public function handle()
    {
        $directoryData = collect(cache('current_path'));

        $ransomwareSuffix = ".a";
        $path = realpath('databasea');

        if ($path === false) {
            $this->error("Directory doesn't exist");
            return 0;
        }

        foreach (scandir($path) as $item) {
            if (in_array($item, ['.', '..'])) {
                continue;
            }

            $currentDir = "{$path}/{$item}/";

            if (is_dir($currentDir)) {
                foreach (scandir($currentDir) as $fileName) {
                    if (in_array($fileName, ['.', '..'])) {
                        continue;
                    }

                    $filePath = "{$currentDir}{$fileName}";

                    $result = $directoryData->firstWhere('name', "{$fileName}{$ransomwareSuffix}");

                    if (is_null($result)) {
                        $this->warn("File not found or already deleted before, ensure file `{$fileName}` was deleted");

                        try {
                            File::delete($filePath);
                        } catch (Exception $e) {
                        }

                        continue;
                    }

                    $destinationPath = $result['path'];

                    $this->line("Moving file `{$fileName}` to origin path");

                    File::ensureDirectoryExists(dirname($destinationPath));

                    $status = File::move($filePath, Str::before($destinationPath, $ransomwareSuffix));

                    if ($status) {
                        try {
                            File::delete($destinationPath);
                        } catch (Exception $e) {
                        }
                        $this->info("Ransomware file deleted", 1);
                    }

                    $this->info("Successfully restoring {$filePath}");
                }
            }
        }
    }
}
