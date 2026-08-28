<?php

namespace App\Domains\Integrations\Services\Import;

use App\Domains\Integrations\Exceptions\ImportFileUnreadableException;

final class ImportRowReader
{
    public function __construct(private string $filePath) {}

    public function read(): \Generator
    {
        $file = fopen($this->filePath, 'r');
        if ($file === false) {
            throw new ImportFileUnreadableException('Cannot read import file');
        }

        // Strip UTF-8 BOM if present
        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($file);
        }

        $headers = fgetcsv($file);
        if ($headers === false) {
            fclose($file);
            throw new ImportFileUnreadableException('CSV file has no header row');
        }

        $headers = array_map('strtolower', $headers ?? []);
        $rowNum = 2;
        $maxRows = config('integrations.import.max_rows', 50000);

        while (($row = fgetcsv($file)) !== false && $rowNum <= $maxRows) {
            if (empty(array_filter($row))) {
                $rowNum++;

                continue;
            }

            yield $rowNum => array_combine($headers, $row);
            $rowNum++;
        }

        fclose($file);

        if ($rowNum > $maxRows) {
            throw new ImportFileUnreadableException("File exceeds maximum of $maxRows rows");
        }
    }
}
