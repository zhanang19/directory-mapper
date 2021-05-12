<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CaptureDirectoryStructureCommand extends Command
{
    protected $signature = 'directory-structure:capture';

    protected $description = 'Capture current directory structure';

    protected array $files = [];

    public function handle()
    {
        $path = $this->ask('');

        $scanResult = $this->scandir($path, $path);

        Cache::put('current_path', $scanResult);
    }

    protected function scandir($basePath, $parent)
    {
        $scanResult = scandir($basePath);

        foreach ($scanResult as $dir) {
            if (in_array($dir, ['.', '..'])) {
                continue;
            }

            $path = $parent . DIRECTORY_SEPARATOR . $dir;

            if (is_dir($path)) {
                $this->directories[] = $path;

                $this->scandir($path . DIRECTORY_SEPARATOR, $path);
            } else {
                $this->files[] = [
                    'path' => $path,
                    'name' => basename($path),
                ];
            }
        }

        return $this->files;
    }
}
