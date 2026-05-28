<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeFeatureCommand extends Command
{
    protected $signature = 'make:feature {name : Nama fitur (contoh: Product)}';

    protected $description = 'Generate lengkap satu fitur: model, migration, controller, request, dan service';

    public function handle(): int
    {
        $name = $this->argument('name');

        $this->components->info("Membuat fitur [{$name}]...");

        $this->call('make:model', [
            'name' => $name,
            '-m'   => true,
            '-c'   => true,
            '-r'   => true,
            '-R'   => true,
        ]);

        $this->call('make:service', ['name' => $name]);

        $this->newLine();
        $this->components->info("Fitur [{$name}] selesai dibuat. Langkah selanjutnya:");
        $this->components->bulletList([
            "Tambah 'config/starterkit.php' → permissions baru untuk {$name}",
            "Tambah 'routes/web.php'        → Route::resource('...', {$name}Controller::class)",
            "Isi migration                  → kolom tabel",
            "Isi Request rules              → Store{$name}Request & Update{$name}Request",
            "Isi Service                    → app/Services/{$name}Service.php",
            "Isi Controller                 → app/Http/Controllers/{$name}Controller.php",
            "Buat TypeScript interface      → resources/js/types/",
            "Buat halaman React             → resources/js/pages/",
        ]);

        return self::SUCCESS;
    }
}
