<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class SyncLocalDatabaseToProduction extends Command
{
    protected $signature = 'db:sync-local-to-production
        {--source= : Nombre de conexión origen (default: DB_CONNECTION)}
        {--destination=mysql_production : Nombre de conexión destino}
        {--truncate : Vaciar tablas destino antes de copiar}
        {--include=* : Tablas a incluir (si no se usa, toma todas menos excluidas)}
        {--exclude=* : Tablas a excluir}
        {--chunk=500 : Tamaño de lote para inserts}
        {--dry-run : No escribe, solo muestra lo que haría}';

    protected $description = 'Sincroniza datos desde la BD local hacia la BD de producción (misma estructura).';

    public function handle(): int
    {
        $sourceConnection = (string) ($this->option('source') ?: config('database.default'));
        $destinationConnection = (string) $this->option('destination');

        if ($sourceConnection === $destinationConnection) {
            $this->error('La conexión origen y destino no pueden ser la misma.');

            return self::FAILURE;
        }

        $source = DB::connection($sourceConnection);
        $destination = DB::connection($destinationConnection);

        $chunkSize = (int) $this->option('chunk');
        if ($chunkSize < 1) {
            $this->error('El parámetro --chunk debe ser un entero mayor a 0.');

            return self::FAILURE;
        }

        $defaultExcluded = [
            'migrations',
            'cache',
            'cache_locks',
            'failed_jobs',
            'jobs',
            'job_batches',
            'sessions',
            'password_reset_tokens',
            'personal_access_tokens',
        ];

        $excluded = array_values(array_unique(array_filter(array_merge(
            $defaultExcluded,
            (array) $this->option('exclude')
        ))));

        $included = array_values(array_unique(array_filter((array) $this->option('include'))));

        $tables = $included ?: $this->listTables($source);
        $tables = array_values(array_filter($tables, function (string $table) use ($excluded) {
            return ! in_array($table, $excluded, true);
        }));

        if (empty($tables)) {
            $this->warn('No hay tablas para sincronizar.');

            return self::SUCCESS;
        }

        $this->info('Origen: '.$sourceConnection);
        $this->info('Destino: '.$destinationConnection);
        $this->info('Tablas: '.implode(', ', $tables));

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN: no se escribirán datos.');

            return self::SUCCESS;
        }

        $this->disableForeignKeyChecks($destination);

        try {
            foreach ($tables as $table) {
                $this->syncTable($source, $destination, $table, $chunkSize, (bool) $this->option('truncate'));
            }
        } finally {
            $this->enableForeignKeyChecks($destination);
        }

        $this->info('Sincronización finalizada.');

        return self::SUCCESS;
    }

    private function listTables(ConnectionInterface $connection): array
    {
        $databaseName = $connection->getDatabaseName();
        $driver = $connection->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $rows = $connection->select('SHOW TABLES');
            $key = 'Tables_in_'.$databaseName;

            return array_values(array_map(function ($row) use ($key) {
                $data = (array) $row;

                return (string) ($data[$key] ?? reset($data));
            }, $rows));
        }

        $this->error('Driver no soportado para listar tablas: '.$driver);

        return [];
    }

    private function syncTable(
        ConnectionInterface $source,
        ConnectionInterface $destination,
        string $table,
        int $chunkSize,
        bool $truncate
    ): void {
        $this->line('Sincronizando: '.$table);

        if ($truncate) {
            $destination->table($table)->truncate();
        }

        $primaryKey = $this->getPrimaryKeyColumn($source, $table);
        $query = $source->table($table);

        if ($primaryKey) {
            $query->orderBy($primaryKey)->chunk($chunkSize, function ($rows) use ($destination, $table) {
                $payload = array_map(fn ($row) => (array) $row, $rows->all());
                if (! empty($payload)) {
                    $destination->table($table)->insert($payload);
                }
            });

            return;
        }

        $rows = $query->get();
        $payload = array_map(fn ($row) => (array) $row, $rows->all());

        foreach (array_chunk($payload, $chunkSize) as $chunk) {
            if (! empty($chunk)) {
                $destination->table($table)->insert($chunk);
            }
        }
    }

    private function getPrimaryKeyColumn(ConnectionInterface $connection, string $table): ?string
    {
        $driver = $connection->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return null;
        }

        $rows = $connection->select("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'");
        if (empty($rows)) {
            return null;
        }

        $first = (array) $rows[0];

        return isset($first['Column_name']) ? (string) $first['Column_name'] : null;
    }

    private function disableForeignKeyChecks(ConnectionInterface $connection): void
    {
        $driver = $connection->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $connection->statement('SET FOREIGN_KEY_CHECKS=0');
        }
    }

    private function enableForeignKeyChecks(ConnectionInterface $connection): void
    {
        $driver = $connection->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $connection->statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
