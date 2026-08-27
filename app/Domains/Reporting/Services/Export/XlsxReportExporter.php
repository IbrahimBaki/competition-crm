<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Services\Export;

use App\Domains\Reporting\Models\ReportExportFormat;
use App\Domains\Reporting\Services\Definitions\ReportDefinition;
use App\Domains\Reporting\Services\Definitions\ReportResult;
use ZipArchive;

class XlsxReportExporter implements ReportExporter
{
    public function format(): ReportExportFormat
    {
        return ReportExportFormat::Xlsx;
    }

    public function render(ReportResult $result, ReportDefinition $definition): string
    {
        $zip = new ZipArchive;
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip->open($tempFile, ZipArchive::CREATE);

        // Add [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', $this->getContentTypes());

        // Add _rels/.rels
        $zip->addFromString('_rels/.rels', $this->getRelationships());

        // Add sheet XML
        $sheetXml = $this->generateSheetXml($result, $definition);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        // Add workbook
        $zip->addFromString('xl/workbook.xml', $this->getWorkbook());

        $zip->close();

        return file_get_contents($tempFile);
    }

    private function generateSheetXml(ReportResult $result, ReportDefinition $definition): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'."\n";
        $xml .= '<sheetData>'."\n";

        // Header row
        $xml .= '<row r="1">'."\n";
        foreach ($definition->columns() as $col => $name) {
            $cellRef = $this->getExcelColumn($col).'1';
            $xml .= "<c r=\"{$cellRef}\" t=\"inlineStr\"><is><t>{$this->escape($name)}</t></is></c>\n";
        }
        $xml .= '</row>'."\n";

        // Data rows
        foreach ($result->rows as $rowNum => $row) {
            $rowNum += 2; // Start after header
            $xml .= "<row r=\"{$rowNum}\">\n";
            foreach ($definition->columns() as $col => $columnKey) {
                $cellRef = $this->getExcelColumn($col).$rowNum;
                $value = $row[$columnKey] ?? '';
                $xml .= "<c r=\"{$cellRef}\" t=\"inlineStr\"><is><t>{$this->escape((string) $value)}</t></is></c>\n";
            }
            $xml .= "</row>\n";
        }

        $xml .= '</sheetData>'."\n";
        $xml .= '</worksheet>';

        return $xml;
    }

    private function getExcelColumn(int $index): string
    {
        $column = '';
        $index++;
        while ($index > 0) {
            $index--;
            $column = chr(65 + ($index % 26)).$column;
            $index = (int) ($index / 26);
        }

        return $column;
    }

    private function escape(string $str): string
    {
        return htmlspecialchars($str, ENT_XML1, 'UTF-8');
    }

    private function getContentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n".
               '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'."\n".
               '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'."\n".
               '<Default Extension="xml" ContentType="application/xml"/>'."\n".
               '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'."\n".
               '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'."\n".
               '</Types>';
    }

    private function getRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n".
               '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'."\n".
               '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'."\n".
               '</Relationships>';
    }

    private function getWorkbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n".
               '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'."\n".
               '<sheets>'."\n".
               '<sheet name="Sheet1" sheetId="1" r:id="rId1"/>'."\n".
               '</sheets>'."\n".
               '</workbook>';
    }
}
