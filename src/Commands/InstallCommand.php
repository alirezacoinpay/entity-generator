<?php

namespace Alirezappeto\EntityGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'entity-generator:install';
    protected $description = 'Install Entity Generator (BaseModel, Repositories, etc.)';

    public function handle()
    {
        $this->info('🚀 Installing Entity Generator...');

        $this->addMethodToMainController();
        $this->addRepositoryServiceProivder();

        $files = [
            // fa.Lang
            [
                'source' => __DIR__ . '/../stubs/persianLanguage.stub',
                'target' => base_path('lang/fa/api.php'),
                'name'   => 'api',
            ],
            // BaseModel
            [
                'source' => __DIR__ . '/../stubs/baseModel.stub',
                'target' => app_path('Models/BaseModel.php'),
                'name'   => 'BaseModel',
            ],
            // Repositories
            [
                'source' => __DIR__ . '/../stubs/repository/baseRepository.stub',
                'target' => app_path('Repositories/BaseRepository.php'),
                'name'   => 'BaseRepository',
            ],
            [
                'source' => __DIR__ . '/../stubs/repository/baseCacheRepository.stub',
                'target' => app_path('Repositories/BaseCacheRepository.php'),
                'name'   => 'BaseCacheRepository',
            ],
            [
                'source' => __DIR__ . '/../stubs/repository/baseRepositoryInterface.stub',
                'target' => app_path('Repositories/BaseRepositoryInterface.php'),
                'name'   => 'BaseRepositoryInterface',
            ],
        ];

        foreach ($files as $file) {
            $this->installFile($file['source'], $file['target'], $file['name']);
        }

        $this->info('✅ Entity Generator installation complete!');
    }
    private function installFile($source, $target, $name)
    {
        if (!File::exists($source)) {
            $this->error("❌ Stub not found: {$source}");
            return;
        }

        if (File::exists($target)) {
            $this->warn("⚠️ {$name} already exists. Skipping...");
            return;
        }

        File::ensureDirectoryExists(dirname($target));
        File::copy($source, $target);
        $this->info("✅ {$name} created at: {$target}");

    }
    private function addMethodToMainController()
    {
        $controllerPath = app_path('Http/Controllers/Controller.php');

        if (!File::exists($controllerPath)) {
            $this->error("❌ Main Controller.php not found at {$controllerPath}");
            return;
        }

        $methodCode = <<<EOD

    public function success(\$data = [], string \$message = '', int \$status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => \$data,
            'message' => __(\$message),
        ], \$status);
    }

    public function error(string \$message = '', \$data = [], int \$status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => \$data,
            'message' => __(\$message),
        ], \$status);
    }

EOD;

        $controllerContent = File::get($controllerPath);

        if (str_contains($controllerContent, 'function success(')) {
            $this->warn('⚠️ success() method already exists in Controller.php. Skipping...');
            return;
        }

        // Add 'use Illuminate\Http\JsonResponse;' if missing
        if (!str_contains($controllerContent, 'use Illuminate\Http\JsonResponse;')) {
            $controllerContent = preg_replace(
                '/namespace App\\\\Http\\\\Controllers;(\r\n|\n)/',
                "namespace App\Http\Controllers;\n\nuse Illuminate\Http\JsonResponse;\n",
                $controllerContent
            );
        }

        // Insert method(s) before final closing brace
        $newContent = preg_replace('/}\s*$/', $methodCode . "\n}", $controllerContent);

        File::put($controllerPath, $newContent);

        $this->info("✅ success() and error() methods added to Controller.php");
    }
    private function addRepositoryServiceProivder()
    {
        $repositoryServiceProviderFileName = 'RepositoryServiceProvider';

        // Check if migration already exists
        $existing = collect(File::allFiles(app_path('Providers')))
            ->contains(fn($file) => str_contains($file->getFilename(), $repositoryServiceProviderFileName));

        if ($existing) {
            $this->warn("⚠️ Provider {$repositoryServiceProviderFileName} already exists. Skipping...");
            return;
        }

        $this->call('make:provider', [
            'name' => $repositoryServiceProviderFileName,
        ]);

        $this->info("✅ provider for repositories created.");
    }

}
