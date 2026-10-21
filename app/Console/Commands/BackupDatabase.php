<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--disk=s3 : Storage disk to use} {--path=backups/database : Path to store backup}';

    protected $description = 'Create a database backup';

    public function handle(): int
    {
        $disk = $this->option('disk');
        $path = $this->option('path');

        $filename = sprintf(
            '%s/%s-%s.sql',
            $path,
            config('app.name'),
            now()->format('Y-m-d-H-i-s')
        );

        $this->info("Creating database backup: {$filename}");

        try {
            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_'.config('database.connections.'.config('database.default').'.database');
            $tables = array_column((array) $tables, $tableKey);

            $dump = '';
            $dump .= '-- Database backup for '.config('app.name')."\n";
            $dump .= '-- Generated at: '.now()->toDateTimeString()."\n\n";

            foreach ($tables as $table) {
                $dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
                $dump .= $createTable[0]->{'Create Table'}.";\n\n";

                $rows = DB::table($table)->get();
                if ($rows->isNotEmpty()) {
                    $columns = array_keys((array) $rows->first());
                    $dump .= "INSERT INTO `{$table}` (`".implode('`, `', $columns)."`) VALUES\n";

                    $values = [];
                    foreach ($rows as $row) {
                        $rowValues = [];
                        foreach ($columns as $col) {
                            $value = $row->$col;
                            if ($value === null) {
                                $rowValues[] = 'NULL';
                            } elseif (is_bool($value)) {
                                $rowValues[] = $value ? '1' : '0';
                            } elseif (is_numeric($value)) {
                                $rowValues[] = $value;
                            } else {
                                $rowValues[] = "'".addslashes($value)."'";
                            }
                        }
                        $values[] = '('.implode(', ', $rowValues).')';
                    }
                    $dump .= implode(",\n", $values).";\n\n";
                }
            }

            Storage::disk($disk)->put($filename, $dump);

            $this->info("Backup saved to {$disk}:{$filename}");

            // Clean old backups (keep last 7 days)
            $this->cleanOldBackups($disk, $path);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Backup failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    private function cleanOldBackups(string $disk, string $path): void
    {
        $files = Storage::disk($disk)->files($path);
        $cutoff = now()->subDays(7);

        foreach ($files as $file) {
            $lastModified = Storage::disk($disk)->lastModified($file);
            if ($lastModified < $cutoff->timestamp) {
                Storage::disk($disk)->delete($file);
                $this->line("Deleted old backup: {$file}");
            }
        }
    }
}
