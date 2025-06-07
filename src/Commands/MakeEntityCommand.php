<?php

namespace Appeto\EntityGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


class MakeEntityCommand extends Command
{
    protected $signature = 'make:entity {name?}';
    protected $description = 'Generate model, controller, repository, request, resource files for an entity';

    public function handle()
    {
        $name = $this->argument('name');

        if (empty($name)) {
            $name = $this->ask('Please provide a name for the entity');
            if (empty($name)) {
                $this->error('The name is required. Exiting...');
                return;
            }
        }

        $nameLower = Str::lower($name);
        $namePluralLower = Str::plural($nameLower);
        $namePlural = Str::plural($name);

        $this->generateMigration($name);
        $this->addBindingToRepositoryServiceProvider($name);
        $files = [
            "app/Models/{$name}.php" => 'model.stub',
            "app/Http/Controllers/{$name}Controller.php" => 'controller.stub',
            "app/Http/Resources/{$name}Resource.php" => 'resource.stub',

            "app/Repositories/{$name}/{$name}RepositoryInterface.php" => 'repository/repositoryInterface.stub',
            "app/Repositories/{$name}/{$name}Repository.php" => 'repository/repository.stub',
            "app/Repositories/{$name}/{$name}CacheRepository.php" => 'repository/cacheRepository.stub',

            "app/Http/Requests/{$namePlural}/Add{$name}Request.php" => 'Request/AddRequest.stub',
            "app/Http/Requests/{$namePlural}/All{$namePlural}Request.php" => 'Request/AllRequest.stub',
            "app/Http/Requests/{$namePlural}/Update{$name}Request.php" => 'Request/UpdateRequest.stub',

            "app/Repositories/{$name}/{$name}RepositoryInterface.php" => 'repository/repositoryInterface.stub',
            "app/Repositories/{$name}/{$name}Repository.php" => 'repository/repository.stub',
            "app/Repositories/{$name}/{$name}CacheRepository.php" => 'repository/cacheRepository.stub',
        ];

        foreach ($files as $path => $stub) {
            $content = File::get(__DIR__ . "/../stubs/{$stub}");
            $content = str_replace('{{ name }}', $name, $content);
            $content = str_replace('{{ nameLower }}', $nameLower, $content);
            $content = str_replace('{{ namePluralLower }}', $namePluralLower, $content);
            $content = str_replace('{{ namePlural }}', $namePlural, $content);
            File::ensureDirectoryExists(dirname(base_path($path)));
            File::put(base_path($path), $content);
            $this->info("Created: {$path}");
        }
    }

    private function generateMigration($name)
    {
        $table = Str::snake(Str::plural($name));
        $migrationName = 'create_' . $table . '_table';

        // Check if migration already exists
        $existing = collect(File::allFiles(database_path('migrations')))
            ->contains(fn($file) => str_contains($file->getFilename(), $migrationName));

        if ($existing) {
            $this->warn("⚠️ Migration {$migrationName} already exists. Skipping...");
            return;
        }

        $this->call('make:migration', [
            'name' => $migrationName,
            '--create' => $table,
        ]);

        $this->info("✅ Migration for {$table} created.");
    }

    private function addBindingToRepositoryServiceProvider($name)
    {
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');

        if (!File::exists($providerPath)) {
            $this->warn("⚠️ RepositoryServiceProvider.php not found. Skipping binding registration.");
            return;
        }

        $content = File::get($providerPath);

        // Prepare use statements
        $interfaceUse = "use App\\Repositories\\{$name}\\{$name}RepositoryInterface;";
        $classUse = "use App\\Repositories\\{$name}\\{$name}CacheRepository;";

        // Insert use statements if not already present
        if (!str_contains($content, $interfaceUse)) {
            $content = preg_replace(
                '/<\?php\s+namespace[^\n]+;\s+/s',
                "$0\n{$interfaceUse}\n{$classUse}\n",
                $content,
                1
            );
        }

        // Prepare binding line
        $interface = "{$name}RepositoryInterface::class";
        $implementation = "{$name}CacheRepository::class";
        $bindingLine = "        {$interface} => {$implementation},";

        // Check if already bound
        if (str_contains($content, $interface)) {
            $this->warn("⚠️ Binding for {$name} already exists. Skipping...");
            File::put($providerPath, $content); // still update with use statements
            return;
        }

        // If $bindings doesn't exist, insert it before register()
        if (!str_contains($content, 'public $bindings')) {
            $insertionPoint = strpos($content, 'public function register()');
            if ($insertionPoint !== false) {
                $bindingsBlock = "\n    public \$bindings = [\n{$bindingLine}\n    ];\n";
                $content = substr_replace($content, $bindingsBlock, $insertionPoint, 0);
            } else {
                $this->error("❌ Could not find where to insert \$bindings.");
                return;
            }
        } else {
            // Insert binding inside existing $bindings array
            if (preg_match('/public\s+\$bindings\s*=\s*\[\s*/s', $content, $matches, PREG_OFFSET_CAPTURE)) {
                $endPos = strpos($content, '];', $matches[0][1]);
                $content = substr_replace($content, "{$bindingLine}\n", $endPos, 0);
            } else {
                $this->error("❌ Could not find end of \$bindings array.");
                return;
            }
        }

        File::put($providerPath, $content);
        $this->info("✅ Binding and use statements for {$name} added to RepositoryServiceProvider.");
    }

}
