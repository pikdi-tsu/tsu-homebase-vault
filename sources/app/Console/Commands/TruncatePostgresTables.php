<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class TruncatePostgresTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * {--except= : Tabel yang tidak akan dikosongkan, dipisahkan koma. Contoh: --except=users,roles}
     */
    protected $signature = 'db:truncate-all-postgres-tables {--except= : Tabel yang tidak akan dikosongkan, dipisahkan koma.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengosongkan tabel yang ada di migrasi, dengan opsi pengecualian.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // === LANGKAH 1: AMBIL DAFTAR PENGECUALIAN DARI OPSI ===
        $exceptOption = $this->option('except');
        $exceptions = $exceptOption ? explode(',', $exceptOption) : [];

        // === LANGKAH 2: BUAT DAFTAR PUTIH (WHITELIST) DARI MIGRATIONS ===
        $migrationFiles = File::files(database_path('migrations'));
        $migrationTables = [];

        foreach ($migrationFiles as $file) {
            if (preg_match('/create_(\w+)_table/', $file->getFilename(), $matches)) {
                $migrationTables[] = $matches[1];
            }
        }

        if (empty($migrationTables)) {
            $this->error('Tidak ditemukan file migrasi dengan format "create_..._table".');
            return;
        }

        // Filter whitelist dengan daftar pengecualian
        $tablesToTruncate = array_diff($migrationTables, $exceptions);

        if (empty($tablesToTruncate)) {
            $this->warn('Tidak ada tabel yang akan dikosongkan setelah menerapkan pengecualian.');
            return;
        }

        $this->info('Tabel yang akan dikosongkan: ' . implode(', ', $tablesToTruncate));
        if (!empty($exceptions)) {
            $this->warn('Tabel yang dilewati (dilindungi): ' . implode(', ', $exceptions));
        }

        if (!$this->confirm('Lanjutkan proses truncate?')) {
            $this->info('Operasi dibatalkan.');
            return;
        }

        // === LANGKAH 3: PROSES TRUNCATE SELEKTIF ===
        Schema::disableForeignKeyConstraints();
        $this->info('Memulai proses truncate...');

        foreach ($tablesToTruncate as $table) {
            DB::statement('TRUNCATE TABLE "' . $table . '" CASCADE');
            $this->line("Tabel `{$table}` berhasil dikosongkan.");
        }

        Schema::enableForeignKeyConstraints();
        $this->info('✨ Proses selesai! Semua tabel berhasil dikosongkan! ✨');
    }
}
