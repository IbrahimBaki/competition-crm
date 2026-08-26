<?php

namespace App\Console\Commands;

use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupVerifyCommand extends Command
{
    protected $signature = 'backup:verify {--latest : Verify the latest backup}';

    protected $description = 'Verify the integrity of a backup';

    public function __construct(private AuditLogger $auditLogger)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $backupDisk = config('backup.disk', 'backups');
        $disk = Storage::disk($backupDisk);

        $files = $disk->files('/');
        $backupFiles = array_filter($files, fn ($f) => str_ends_with($f, '.zip'));

        if (empty($backupFiles)) {
            $this->error('No backups found');

            return 1;
        }

        $backupFile = $this->option('latest')
            ? end($backupFiles)
            : $this->choice('Select backup to verify', $backupFiles);

        $manifestFile = str_replace('.zip', '.json', $backupFile);

        if (! $disk->exists($manifestFile)) {
            $this->error("Manifest not found for backup: {$backupFile}");
            $this->logAuditVerificationFailed($backupFile);

            return 1;
        }

        try {
            $manifest = json_decode($disk->get($manifestFile), true);

            $tempDir = storage_path('backups-verify');
            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $tempPath = "{$tempDir}/{$backupFile}";

            $content = $disk->get($backupFile);
            file_put_contents($tempPath, $content);

            $actualSize = filesize($tempPath);
            $computedChecksum = hash_file('sha256', $tempPath);

            @unlink($tempPath);

            if ($computedChecksum !== $manifest['checksum_sha256']) {
                $this->error("Checksum mismatch for {$backupFile}");
                $this->error("Expected: {$manifest['checksum_sha256']}");
                $this->error("Got: {$computedChecksum}");
                $this->logAuditVerificationFailed($backupFile);

                return 1;
            }

            if ($actualSize !== $manifest['size_bytes']) {
                $this->error("Size mismatch for {$backupFile}");
                $this->logAuditVerificationFailed($backupFile);

                return 1;
            }

            $this->line("<fg=green>✓</> Backup verified: {$backupFile}");
            $this->logAuditVerificationSuccess($backupFile, $manifest);

            return 0;
        } catch (\Exception $e) {
            $this->error("Verification failed: {$e->getMessage()}");
            $this->logAuditVerificationFailed($backupFile);

            return 1;
        }
    }

    private function logAuditVerificationSuccess(string $backupFile, array $manifest): void
    {
        $systemUser = User::query()
            ->where('email', 'system@internal')
            ->first();

        if ($systemUser) {
            $this->auditLogger->record(
                $systemUser,
                'backup.verified',
                $systemUser,
                null,
                array_merge(['backup_file' => $backupFile], $manifest),
            );
        }
    }

    private function logAuditVerificationFailed(string $backupFile): void
    {
        $systemUser = User::query()
            ->where('email', 'system@internal')
            ->first();

        if ($systemUser) {
            $this->auditLogger->record(
                $systemUser,
                'backup.verification_failed',
                $systemUser,
                null,
                ['backup_file' => $backupFile],
            );
        }
    }
}
