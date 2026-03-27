<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateSqliteToPgsql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-sqlite-to-pgsql';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sqlite = DB::connection('sqlite');
        $pgsql = DB::connection('pgsql');

        $tables = collect($sqlite->select("
                SELECT name FROM sqlite_master 
                WHERE type='table'
            "))->pluck('name')->filter(function ($table) {
            return !in_array($table, [
                'sqlite_sequence',
                'sqlite_stat1',
                'sqlite_stat4',
            ]);
        });

        $priorityTables = [
            'users',
            'categories',
            'products',
            'carts',
            'orders',
            'cart_items',
            'order_items',
        ];

        $tables = collect($tables);

        $tables = collect($priorityTables)
            ->filter(fn ($t) => $tables->contains($t))
            ->merge($tables->diff($priorityTables));

        foreach ($tables as $tableName) {
            if ($tableName === 'migrations') {
                continue;
            }

            $rows = $sqlite->table($tableName)->get();

            foreach ($rows as $row) {
                $pgsql->table($tableName)->insert((array) $row);
            }

            $this->info("Migrated: $tableName");
        }

        foreach ($tables as $table) {
            if ($table === 'password_reset_tokens' || $table === 'sessions' || $table === 'cache' || $table === 'cache_locks' || $table === 'job_batches') {
                continue;
            }
            DB::statement("
        SELECT setval(
            pg_get_serial_sequence('{$table}', 'id'),
            COALESCE(MAX(id), 1),
            true
        ) FROM {$table}
    ");
        }

        $this->info('Migration completed successfully.');
    }
}
