<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class SimpleExcelExporter
{
    /**
     * Download Excel file (.xlsx or .xls)
     *
     * @param string $filename Base filename without extension
     * @param array $headers Column headers
     * @param array $rows 2D array of row data
     * @param string $format 'xlsx' or 'xls'
     * @return StreamedResponse|\Illuminate\Http\Response
     */
    public static function download(string $filename, array $headers, array $rows, string $format = 'xlsx')
    {
        if (strtolower($format) === 'xls') {
            return self::downloadXls($filename . '.xls', $headers, $rows);
        }

        return self::downloadXlsx($filename . '.xlsx', $headers, $rows);
    }

    /**
     * Generate and download native OpenXML (.xlsx) file
     */
    public static function downloadXlsx(string $filename, array $headers, array $rows)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');

        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            // Fallback to XML spreadsheet if zip cannot be created
            return self::downloadXls(str_replace('.xlsx', '.xls', $filename), $headers, $rows);
        }

        // [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
            '</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // _rels/.rels
        $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
            '</Relationships>';
        $zip->addFromString('_rels/.rels', $rootRels);

        // xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
            '</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        // xl/workbook.xml
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<sheets><sheet name="Master Items" sheetId="1" r:id="rId1"/></sheets>' .
            '</workbook>';
        $zip->addFromString('xl/workbook.xml', $workbook);

        // xl/styles.xml
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<fonts count="2">' .
            '<font><name val="Calibri"/><sz val="11"/></font>' .
            '<font><b/><name val="Calibri"/><sz val="11"/><color rgb="FFFFFFFF"/></font>' .
            '</fonts>' .
            '<fills count="3">' .
            '<fill><patternFill patternType="none"/></fill>' .
            '<fill><patternFill patternType="gray125"/></fill>' .
            '<fill><patternFill patternType="solid"><fgColor rgb="FF0D6EFD"/></patternFill></fill>' .
            '</fills>' .
            '<borders count="2">' .
            '<border><left/><right/><top/><bottom/></border>' .
            '<border>' .
            '<left style="thin"><color rgb="FFD3D3D3"/></left>' .
            '<right style="thin"><color rgb="FFD3D3D3"/></right>' .
            '<top style="thin"><color rgb="FFD3D3D3"/></top>' .
            '<bottom style="thin"><color rgb="FFD3D3D3"/></bottom>' .
            '</border>' .
            '</borders>' .
            '<cellStyleXfs count="1">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>' .
            '</cellStyleXfs>' .
            '<cellXfs count="3">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' .
            '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>' .
            '</cellXfs>' .
            '</styleSheet>';
        $zip->addFromString('xl/styles.xml', $styles);

        // xl/worksheets/sheet1.xml
        $sheetData = '<sheetData>';

        // Header Row
        $sheetData .= '<row r="1" ht="25" customHeight="1">';
        foreach ($headers as $colIndex => $headerText) {
            $colLetter = self::getColLetter($colIndex + 1);
            $sheetData .= '<c r="' . $colLetter . '1" s="1" t="inlineStr"><is><t xml:space="preserve">' .
                htmlspecialchars((string)$headerText, ENT_XML1, 'UTF-8') .
                '</t></is></c>';
        }
        $sheetData .= '</row>';

        // Data Rows
        $rowNum = 2;
        foreach ($rows as $row) {
            $sheetData .= '<row r="' . $rowNum . '">';
            $colIndex = 1;
            foreach ($row as $val) {
                $colLetter = self::getColLetter($colIndex);
                if (is_numeric($val) && !preg_match('/^0[0-9]+/', (string)$val)) {
                    $sheetData .= '<c r="' . $colLetter . $rowNum . '" s="2" t="n"><v>' . $val . '</v></c>';
                } else {
                    $sheetData .= '<c r="' . $colLetter . $rowNum . '" s="2" t="inlineStr"><is><t xml:space="preserve">' .
                        htmlspecialchars((string)$val, ENT_XML1, 'UTF-8') .
                        '</t></is></c>';
                }
                $colIndex++;
            }
            $sheetData .= '</row>';
            $rowNum++;
        }
        $sheetData .= '</sheetData>';

        // Set column widths
        $cols = '<cols>' .
            '<col min="1" max="1" width="8" customWidth="1"/>' .
            '<col min="2" max="2" width="28" customWidth="1"/>' .
            '<col min="3" max="3" width="30" customWidth="1"/>' .
            '<col min="4" max="4" width="22" customWidth="1"/>' .
            '<col min="5" max="5" width="16" customWidth="1"/>' .
            '<col min="6" max="6" width="12" customWidth="1"/>' .
            '<col min="7" max="7" width="16" customWidth="1"/>' .
            '</cols>';

        $worksheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            $cols .
            $sheetData .
            '</worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $worksheet);

        $zip->close();

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Generate and download Microsoft XML Spreadsheet 2003 (.xls) file
     */
    public static function downloadXls(string $filename, array $headers, array $rows)
    {
        $xml = '<?xml version="1.0"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        $xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        $xml .= ' <Styles>' . "\n";
        $xml .= '  <Style ss:ID="Header">' . "\n";
        $xml .= '   <Font ss:Bold="1" ss:Color="#FFFFFF"/>' . "\n";
        $xml .= '   <Interior ss:Color="#0D6EFD" ss:Pattern="Solid"/>' . "\n";
        $xml .= '   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n";
        $xml .= '  </Style>' . "\n";
        $xml .= '  <Style ss:ID="DataCell">' . "\n";
        $xml .= '   <Borders>' . "\n";
        $xml .= '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>' . "\n";
        $xml .= '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>' . "\n";
        $xml .= '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>' . "\n";
        $xml .= '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D3D3D3"/>' . "\n";
        $xml .= '   </Borders>' . "\n";
        $xml .= '  </Style>' . "\n";
        $xml .= ' </Styles>' . "\n";
        $xml .= ' <Worksheet ss:Name="Master Items">' . "\n";
        $xml .= '  <Table>' . "\n";

        // Headers
        $xml .= '   <Row ss:StyleID="Header">' . "\n";
        foreach ($headers as $h) {
            $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">' . htmlspecialchars((string)$h, ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
        }
        $xml .= '   </Row>' . "\n";

        // Rows
        foreach ($rows as $row) {
            $xml .= '   <Row>' . "\n";
            foreach ($row as $val) {
                if (is_numeric($val) && !preg_match('/^0[0-9]+/', (string)$val)) {
                    $xml .= '    <Cell ss:StyleID="DataCell"><Data ss:Type="Number">' . $val . '</Data></Cell>' . "\n";
                } else {
                    $xml .= '    <Cell ss:StyleID="DataCell"><Data ss:Type="String">' . htmlspecialchars((string)$val, ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
                }
            }
            $xml .= '   </Row>' . "\n";
        }

        $xml .= '  </Table>' . "\n";
        $xml .= ' </Worksheet>' . "\n";
        $xml .= '</Workbook>';

        return response($xml, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private static function getColLetter(int $colIndex): string
    {
        $letter = '';
        while ($colIndex > 0) {
            $mod = ($colIndex - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $colIndex = (int)(($colIndex - $mod) / 26);
        }
        return $letter;
    }
}
