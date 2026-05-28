<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service {name : Nama service (contoh: Product atau ProductService)}';

    protected $description = 'Buat service class baru di app/Services';

    public function __construct(private Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $className = $this->normalizedName();
        $modelName = $this->modelName($className);
        $path      = app_path("Services/{$className}.php");

        if ($this->files->exists($path)) {
            $this->components->error("Service [{$className}] sudah ada.");
            return self::FAILURE;
        }

        $this->files->ensureDirectoryExists(app_path('Services'));
        $this->files->put($path, $this->buildClass($className, $modelName));

        $this->components->info("Service [{$className}] berhasil dibuat.");

        return self::SUCCESS;
    }

    private function normalizedName(): string
    {
        $name = $this->argument('name');

        return str_ends_with($name, 'Service') ? $name : $name . 'Service';
    }

    private function modelName(string $className): string
    {
        return str_ends_with($className, 'Service')
            ? substr($className, 0, -7)
            : $className;
    }

    private function buildClass(string $className, string $modelName): string
    {
        $stub = $this->files->get(base_path('stubs/service.stub'));

        return str_replace(
            ['{{ class }}', '{{ model }}'],
            [$className, $modelName],
            $stub
        );
    }
}
