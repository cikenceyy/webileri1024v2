<?php

namespace App\Core\Exports\Support;

use RuntimeException;
use ZipArchive;

/**
 * Basit veri setleri için hafif XLSX oluşturucu.
 * Amaç: CSV alternatifi olarak minimal bir Excel dosyası döndürmek.
 */
class SimpleXlsxWriter
{
    /** @var resource */
    private $sheetHandle;

    private string $sheetPath;

    private bool $finalized = false;

    /**
     * @param  array<int, string>  $headers
     */
    public function __construct(private readonly array $headers)
    {
        $this->sheetHandle = tmpfile();
        if ($this->sheetHandle === false) {
            throw new RuntimeException('Geçici dosya oluşturulamadı.');
        }

        $meta = stream_get_meta_data($this->sheetHandle);
        $this->sheetPath = $meta['uri'];

        $this->write("<?xml version=\"1.0\" encoding=\"UTF-8\"?>");
        $this->write('<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">');
        $this->write('<sheetData>');
        $this->writeRow($this->headers);
    }

    /**
     * @param  array<int, mixed>  $row
     */
    public function addRow(array $row): void
    {
        if ($this->finalized) {
            throw new RuntimeException('Finalize edilen XLSX dosyasına satır eklenemez.');
        }

        $this->writeRow($row);
    }

    public function saveTo(string $path): void
    {
        $this->finalizeSheet();

        $zip = new ZipArchive();
        $status = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($status !== true) {
            throw new RuntimeException('XLSX dosyası oluşturulamadı: ' . $status);
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('docProps/app.xml', $this->appXml());
        $zip->addFromString('docProps/core.xml', $this->coreXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels());
        $zip->addFile($this->sheetPath, 'xl/worksheets/sheet1.xml');
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->close();
    }

    private function writeRow(array $row): void
    {
        $cells = [];
        foreach ($row as $value) {
            $escaped = htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $escaped = str_replace(["\r\n", "\r", "\n"], '&#10;', $escaped);
            $cells[] = '<c t="inlineStr"><is><t>' . $escaped . '</t></is></c>';
        }

        $this->write('<row>' . implode('', $cells) . '</row>');
    }

    private function write(string $content): void
    {
        fwrite($this->sheetHandle, $content);
    }

    private function finalizeSheet(): void
    {
        if ($this->finalized) {
            return;
        }

        $this->write('</sheetData></worksheet>');
        fflush($this->sheetHandle);
        $this->finalized = true;
    }

    public function __destruct()
    {
        if (is_resource($this->sheetHandle)) {
            fclose($this->sheetHandle);
        }
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function appXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" '
            . 'xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>Webileri Export</Application>'
            . '</Properties>';
    }

    private function coreXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
            . 'xmlns:dc="http://purl.org/dc/elements/1.1/" '
            . 'xmlns:dcterms="http://purl.org/dc/terms/" '
            . 'xmlns:dcmitype="http://purl.org/dc/dcmitype/" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>Export</dc:title>'
            . '<dc:creator>Webileri 1024</dc:creator>'
            . '</cp:coreProperties>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
            . '</styleSheet>';
    }
}
