<?php

namespace App\Domains\Integrations\Jobs;

use App\Domains\Integrations\Models\ImportRun;
use App\Domains\Integrations\Services\Import\RunImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class RunImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    public $tries = 1;

    public function __construct(private ImportRun $run) {}

    public function handle(RunImport $service): void
    {
        $validator = match ($this->run->kind) {
            'customers' => fn ($row, $num) => $this->validateCustomerRow($row, $num),
            'historical_tickets' => fn ($row, $num) => $this->validateTicketRow($row, $num),
            default => fn ($row, $num) => ['Unknown import kind'],
        };

        $service->execute($this->run, $validator);
    }

    private function validateCustomerRow(array $row, int $rowNum): array
    {
        $errors = [];
        if (empty($row['name'] ?? null)) {
            $errors[] = 'name is required';
        }

        return $errors;
    }

    private function validateTicketRow(array $row, int $rowNum): array
    {
        $errors = [];
        if (empty($row['reference'] ?? null)) {
            $errors[] = 'reference is required';
        }
        if (empty($row['created_at'] ?? null)) {
            $errors[] = 'created_at is required for historical tickets';
        }

        return $errors;
    }

    public function failed(\Throwable $exception): void
    {
        $this->run->update(['state' => 'failed', 'finished_at' => now()]);
    }
}
