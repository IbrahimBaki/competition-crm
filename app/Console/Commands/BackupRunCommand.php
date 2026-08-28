<?php

namespace App\Console\Commands;

use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use App\Support\Attachments\Attachment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupRunCommand extends Command
{
    protected $signature = 'backup:run';

    protected $description = 'Run a backup of the database and attachments';

    public function __construct(private AuditLogger $auditLogger)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $backupDisk = config('backup.disk', 'backups');
        $backupPath = Storage::disk($backupDisk)->path('/');

        if ($this->isDiskInsideAppStorage($backupPath)) {
            $this->error('Backup disk must not be inside application storage directory');
            $this->logAuditFailure();

            return 1;
        }

        try {
            $timestamp = now()->format('Y-m-d-H-i-s');
            $backupName = "backup-{$timestamp}";
            $tempDir = storage_path("backups-temp/{$backupName}");

            if (! is_dir(dirname($tempDir))) {
                mkdir(dirname($tempDir), 0755, true);
            }
            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $this->line('Dumping database...');
            $dbDump = $this->dumpDatabase($tempDir);

            if (config('backup.include_attachments', true)) {
                $this->line('Archiving attachments...');
                $this->archiveAttachments($tempDir);
            }

            $this->line('Creating backup archive...');
            $archivePath = "{$tempDir}.zip";
            $this->createArchive($tempDir, $archivePath);

            $this->line('Computing checksum...');
            $checksum = hash_file('sha256', $archivePath);

            $this->line('Uploading to backup disk...');
            $uploadPath = "{$backupName}.zip";
            Storage::disk($backupDisk)->putFileAs('/', $archivePath, $uploadPath);

            $manifest = [
                'name' => $backupName,
                'size_bytes' => filesize($archivePath),
                'checksum_sha256' => $checksum,
                'created_at' => now()->toIso8601String(),
                'schema_head' => DB::selectOne('SELECT MAX(batch) as batch FROM migrations')?->batch,
            ];

            Storage::disk($backupDisk)->put("{$backupName}.json", json_encode($manifest, JSON_PRETTY_PRINT));

            $this->pruneOldBackups($backupDisk);

            @unlink($archivePath);
            @rmdir($tempDir);
            @rmdir(dirname($tempDir));

            $this->line("<fg=green>✓</> Backup completed: {$backupName}");
            $this->logAuditSuccess($manifest);

            return 0;
        } catch (\Exception $e) {
            $this->error("Backup failed: {$e->getMessage()}");
            $this->logAuditFailure();

            return 1;
        }
    }

    private function isDiskInsideAppStorage(string $diskPath): bool
    {
        $appStoragePath = storage_path('app');
        $realDiskPath = realpath($diskPath) ?: $diskPath;
        $realAppPath = realpath($appStoragePath) ?: $appStoragePath;

        return str_starts_with($realDiskPath, $realAppPath);
    }

    private function dumpDatabase(string $tempDir): string
    {
        $driver = config('database.default');
        $dumpFile = "{$tempDir}/database.sql";

        if ($driver === 'sqlite') {
            $dbPath = config("database.connections.{$driver}.database");
            copy($dbPath, $dumpFile);
        } elseif ($driver === 'mysql') {
            $host = config("database.connections.{$driver}.host");
            $database = config("database.connections.{$driver}.database");
            $user = config("database.connections.{$driver}.username");
            $password = config("database.connections.{$driver}.password");

            $cmd = "mysqldump --host={$host} --user={$user} --password={$password} --ssl=FALSE {$database} > {$dumpFile}";
            exec($cmd, $output, $exitCode);
            if ($exitCode !== 0) {
                throw new \Exception("mysqldump failed with code {$exitCode}");
            }
        } elseif ($driver === 'pgsql') {
            $host = config("database.connections.{$driver}.host");
            $database = config("database.connections.{$driver}.database");
            $user = config("database.connections.{$driver}.username");

            $cmd = "pg_dump --host={$host} --username={$user} {$database} > {$dumpFile}";
            exec($cmd, $output, $exitCode);
            if ($exitCode !== 0) {
                throw new \Exception("pg_dump failed with code {$exitCode}");
            }
        } else {
            throw new \Exception("Unsupported database driver: {$driver}");
        }

        return $dumpFile;
    }

    private function archiveAttachments(string $tempDir): void
    {
        $attachmentsDir = "{$tempDir}/attachments";
        @mkdir($attachmentsDir, 0755, true);

        Attachment::chunk(100, function ($attachments) use ($attachmentsDir) {
            foreach ($attachments as $attachment) {
                try {
                    $disk = Storage::disk($attachment->disk);
                    $content = $disk->get($attachment->storage_key);
                    $path = "{$attachmentsDir}/{$attachment->uuid}-{$attachment->original_name}";
                    file_put_contents($path, $content);
                } catch (\Exception $e) {
                    \Log::warning("Failed to backup attachment: {$attachment->uuid}", [
                        'exception' => $e->getMessage(),
                    ]);
                }
            }
        });
    }

    private function createArchive(string $sourceDir, string $archivePath): void
    {
        $zip = new ZipArchive;
        if ($zip->open($archivePath, ZipArchive::CREATE) !== true) {
            throw new \Exception('Failed to create zip archive');
        }

        $this->addDirToZip($zip, $sourceDir, basename($sourceDir));
        $zip->close();
    }

    private function addDirToZip(ZipArchive $zip, string $dir, string $baseName): void
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));

        foreach ($files as $file) {
            if ($file->isFile()) {
                $filePath = $file->getRealPath();
                $zipPath = $baseName.'/'.substr($filePath, strlen($dir) + 1);
                $zip->addFile($filePath, $zipPath);
            }
        }
    }

    private function pruneOldBackups(string $diskName): void
    {
        $retentionDays = config('backup.retention_days', 30);
        $cutoff = now()->subDays($retentionDays);

        $files = Storage::disk($diskName)->files('/');
        foreach ($files as $file) {
            if (! str_ends_with($file, '.zip')) {
                continue;
            }

            $lastModified = Storage::disk($diskName)->lastModified($file);
            if ($lastModified < $cutoff->timestamp) {
                Storage::disk($diskName)->delete($file);
                $manifestFile = str_replace('.zip', '.json', $file);
                Storage::disk($diskName)->delete($manifestFile);
            }
        }
    }

    private function logAuditSuccess(array $manifest): void
    {
        $systemUser = User::query()
            ->where('email', 'system@internal')
            ->first();

        if ($systemUser) {
            $this->auditLogger->record(
                $systemUser,
                'backup.completed',
                $systemUser,
                null,
                $manifest,
            );
        }
    }

    private function logAuditFailure(): void
    {
        $systemUser = User::query()
            ->where('email', 'system@internal')
            ->first();

        if ($systemUser) {
            $this->auditLogger->record(
                $systemUser,
                'backup.failed',
                $systemUser,
                null,
                [],
            );
        }
    }
}
