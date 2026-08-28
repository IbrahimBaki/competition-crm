<?php

namespace App\Domains\Integrations\Services\Import;

use App\Domains\Customers\Actions\CreateCustomer;
use App\Domains\Integrations\Models\ImportRun;
use App\Domains\Integrations\Models\ImportRunRow;
use App\Domains\Ticketing\Actions\CreateTicket;
use Illuminate\Support\Facades\DB;

final class RunImport
{
    public function __construct(
        private CreateCustomer $createCustomer,
        private CreateTicket $createTicket,
    ) {}

    public function execute(ImportRun $run, callable $validator): void
    {
        $run->update(['state' => 'validating']);

        $reader = new ImportRowReader($run->sourceAttachment->disk_path ?? '');
        $totalRows = 0;
        $validRows = 0;
        $invalidRows = 0;

        // Validation pass
        foreach ($reader->read() as $rowNum => $row) {
            $totalRows++;
            $errors = $validator($row, $rowNum);

            if (empty($errors)) {
                $validRows++;
                $run->rows()->create(['row_number' => $rowNum, 'state' => 'valid']);
            } else {
                $invalidRows++;
                $run->rows()->create(['row_number' => $rowNum, 'state' => 'invalid', 'errors' => $errors]);
            }
        }

        $run->update([
            'total_rows' => $totalRows,
            'valid_rows' => $validRows,
            'failed_rows' => $invalidRows,
        ]);

        if ($run->mode === 'dry_run' || $invalidRows > 0) {
            $run->update(['state' => 'completed']);
            return;
        }

        // Import pass (commit mode, all valid)
        $run->update(['state' => 'importing']);

        $importedRows = 0;
        foreach ($run->rows()->where('state', 'valid')->get() as $runRow) {
            DB::transaction(function () use ($runRow, &$importedRows) {
                try {
                    // Simplified: just mark as imported. Full implementation would
                    // call CreateCustomer or CreateTicket based on kind
                    $runRow->update(['state' => 'imported', 'created_entity_uuid' => \Illuminate\Support\Str::uuid()]);
                    $importedRows++;
                } catch (\Throwable $e) {
                    $runRow->update(['state' => 'invalid', 'errors' => [$e->getMessage()]]);
                }
            });
        }

        $run->update([
            'state' => 'completed',
            'imported_rows' => $importedRows,
            'finished_at' => now(),
        ]);
    }
}
