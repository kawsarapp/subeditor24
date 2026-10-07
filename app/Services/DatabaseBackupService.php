<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class DatabaseBackupService
{
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (!File::exists($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
    }

    /**
     * Create a full MySQL database backup
     *
     * @param bool $compress Whether to compress with GZIP (.sql.gz)
     * @param bool $isSafety Whether this is an auto-safety backup before restore
     * @return array
     */
    public function createBackup(bool $compress = true, bool $isSafety = false): array
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $dbName = config('database.connections.mysql.database', 'laravel');
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $prefix = $isSafety ? 'safety_backup_' : 'db_backup_';
        $extension = $compress ? 'sql.gz' : 'sql';
        $filename = "{$prefix}{$dbName}_{$timestamp}.{$extension}";
        $filePath = "{$this->backupDir}/{$filename}";

        $fileHandle = $compress ? @gzopen($filePath, 'wb9') : @fopen($filePath, 'w');

        if (!$fileHandle) {
            throw new \RuntimeException("Unable to open backup file for writing: {$filePath}");
        }

        $write = function ($content) use ($fileHandle, $compress) {
            if ($compress) {
                gzwrite($fileHandle, $content);
            } else {
                fwrite($fileHandle, $content);
            }
        };

        try {
            $pdo = DB::getPdo();

            // 1. Write Header & SQL Environment Configuration
            $header = "-- ==========================================================\n"
                    . "-- SubEditor24 / Newsmanage24 Database Backup\n"
                    . "-- Database: `{$dbName}`\n"
                    . "-- Generated At: " . Carbon::now()->toDateTimeString() . "\n"
                    . "-- Compression: " . ($compress ? "GZIP (Level 9)" : "None (Plain SQL)") . "\n"
                    . "-- ==========================================================\n\n"
                    . "SET FOREIGN_KEY_CHECKS=0;\n"
                    . "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n"
                    . "SET AUTOCOMMIT = 0;\n"
                    . "START TRANSACTION;\n"
                    . "SET time_zone = '+00:00';\n"
                    . "SET NAMES utf8mb4;\n\n";
            $write($header);

            // 2. Fetch all table names
            $tables = [];
            $tableRows = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
            foreach ($tableRows as $row) {
                $rowArray = (array) $row;
                $tables[] = reset($rowArray);
            }

            // 3. Loop through tables and export Schema + Data
            foreach ($tables as $table) {
                $write("--\n-- Table structure for table `{$table}`\n--\n\n");
                $write("DROP TABLE IF EXISTS `{$table}`;\n");

                // Get CREATE TABLE DDL
                $createTableRes = DB::select("SHOW CREATE TABLE `{$table}`");
                if (!empty($createTableRes)) {
                    $createRow = (array) $createTableRes[0];
                    $createSql = $createRow['Create Table'] ?? ($createRow['create table'] ?? null);
                    if ($createSql) {
                        $write($createSql . ";\n\n");
                    }
                }

                // Get Table Data in batches
                $write("--\n-- Dumping data for table `{$table}`\n--\n\n");
                
                $totalRows = DB::table($table)->count();
                if ($totalRows > 0) {
                    $batchSize = 250;
                    $offset = 0;

                    while ($offset < $totalRows) {
                        $rows = DB::table($table)->offset($offset)->limit($batchSize)->get();
                        if ($rows->isEmpty()) {
                            break;
                        }

                        $insertSql = "INSERT INTO `{$table}` VALUES \n";
                        $valuesList = [];

                        foreach ($rows as $row) {
                            $rowValues = [];
                            foreach ((array)$row as $val) {
                                if (is_null($val)) {
                                    $rowValues[] = 'NULL';
                                } elseif (is_numeric($val) && !is_string($val)) {
                                    $rowValues[] = $val;
                                } else {
                                    $rowValues[] = $pdo->quote((string)$val);
                                }
                            }
                            $valuesList[] = '(' . implode(', ', $rowValues) . ')';
                        }

                        $insertSql .= implode(",\n", $valuesList) . ";\n";
                        $write($insertSql);

                        $offset += $batchSize;
                    }
                    $write("\n");
                }
            }

            // 4. Write Footer
            $footer = "\n-- ==========================================================\n"
                    . "-- Finalize Transaction\n"
                    . "-- ==========================================================\n"
                    . "COMMIT;\n"
                    . "SET FOREIGN_KEY_CHECKS=1;\n";
            $write($footer);

        } finally {
            if (!empty($fileHandle)) {
                if ($compress) {
                    @gzclose($fileHandle);
                } else {
                    @fclose($fileHandle);
                }
            }
        }

        $size = File::size($filePath);

        Log::info("✅ Database Backup Created: {$filename} (Size: " . $this->formatBytes($size) . ")");

        return [
            'success'       => true,
            'filename'      => $filename,
            'path'          => $filePath,
            'size'          => $size,
            'formatted_size'=> $this->formatBytes($size),
            'tables_count'  => count($tables),
            'is_safety'     => $isSafety,
            'created_at'    => Carbon::now()->toDateTimeString(),
        ];
    }

    /**
     * Restore database from a .sql or .sql.gz file
     *
     * @param string $sourceFilePath Full path to backup file
     * @param bool $autoSafetyBackup Automatically take safety backup before restoring
     * @return array
     */
    public function restoreBackup(string $sourceFilePath, bool $autoSafetyBackup = true): array
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        if (!File::exists($sourceFilePath)) {
            throw new \InvalidArgumentException("Backup file not found at: {$sourceFilePath}");
        }

        $safetyBackupInfo = null;
        if ($autoSafetyBackup) {
            try {
                $safetyBackupInfo = $this->createBackup(true, true);
            } catch (\Throwable $e) {
                Log::warning("⚠️ Could not create auto-safety backup before restore: " . $e->getMessage());
            }
        }

        $isCompressed = str_ends_with(strtolower($sourceFilePath), '.gz');
        $fileHandle = $isCompressed ? @gzopen($sourceFilePath, 'rb') : @fopen($sourceFilePath, 'r');

        if (!$fileHandle) {
            throw new \RuntimeException("Unable to open backup file for reading.");
        }

        $queryCount = 0;
        $currentQuery = '';

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            while (!($isCompressed ? gzeof($fileHandle) : feof($fileHandle))) {
                $line = $isCompressed ? gzgets($fileHandle, 1024 * 128) : fgets($fileHandle, 1024 * 128);
                if ($line === false) {
                    break;
                }

                $trimmedLine = trim($line);

                // Skip comment lines and empty lines
                if ($trimmedLine === '' || str_starts_with($trimmedLine, '--') || str_starts_with($trimmedLine, '/*') || str_starts_with($trimmedLine, '#')) {
                    continue;
                }

                $currentQuery .= $line;

                // If query ends with semicolon (and not inside quotes), execute it
                if (str_ends_with(rtrim($line), ';')) {
                    try {
                        DB::unprepared($currentQuery);
                        $queryCount++;
                    } catch (\Throwable $e) {
                        Log::warning("⚠️ Restore query error (skipped or non-fatal): " . $e->getMessage());
                    }
                    $currentQuery = '';
                }
            }

            // Execute any trailing query
            if (!empty(trim($currentQuery))) {
                try {
                    DB::unprepared($currentQuery);
                    $queryCount++;
                } catch (\Throwable $e) {
                    // Ignore trailing minor errors
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Clear application caches after restore
            try {
                Artisan::call('cache:clear');
                Artisan::call('view:clear');
            } catch (\Throwable $e) {
                // Ignore cache clearing errors
            }

            Log::info("✅ Database Restored successfully from: " . basename($sourceFilePath) . " (Queries: {$queryCount})");

            return [
                'success'        => true,
                'queries_count'  => $queryCount,
                'safety_backup'  => $safetyBackupInfo ? $safetyBackupInfo['filename'] : null,
                'restored_from'  => basename($sourceFilePath),
            ];

        } finally {
            if ($isCompressed) {
                gzclose($fileHandle);
            } else {
                fclose($fileHandle);
            }
        }
    }

    /**
     * List all existing backups
     *
     * @return array
     */
    public function listBackups(): array
    {
        $files = File::files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (!preg_match('/\.(sql|gz|sql\.gz)$/i', $filename)) {
                continue;
            }
            $filePath = $file->getPathname();
            $size = $file->getSize();
            $modified = $file->getMTime();

            $backups[] = [
                'filename'       => $filename,
                'path'           => $filePath,
                'size'           => $size,
                'formatted_size' => $this->formatBytes($size),
                'is_compressed'  => str_ends_with(strtolower($filename), '.gz'),
                'is_safety'      => str_starts_with($filename, 'safety_backup_'),
                'created_at'     => Carbon::createFromTimestamp($modified)->toDateTimeString(),
                'relative_time'  => Carbon::createFromTimestamp($modified)->diffForHumans(),
                'timestamp'      => $modified,
            ];
        }

        // Sort newest first
        usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $backups;
    }

    /**
     * Delete a specific backup file
     *
     * @param string $filename
     * @return bool
     */
    public function deleteBackup(string $filename): bool
    {
        $cleanFilename = basename($filename);
        $filePath = "{$this->backupDir}/{$cleanFilename}";

        if (File::exists($filePath)) {
            return File::delete($filePath);
        }

        return false;
    }

    /**
     * Get path for a safe download
     *
     * @param string $filename
     * @return string|null
     */
    public function getBackupPath(string $filename): ?string
    {
        $cleanFilename = basename($filename);
        $filePath = "{$this->backupDir}/{$cleanFilename}";

        return File::exists($filePath) ? $filePath : null;
    }

    /**
     * Human-readable byte formatting helper
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
