<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Inbox;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class InboxImportService
{
    public function import(Group $group, UploadedFile $file): array
    {
        $entries = $this->extractEntries($file);
        $prepared = [];
        $skipped = [];
        $seenNames = [];

        foreach ($entries as $entry) {
            $normalized = $this->normalizeInboxName($entry['value']);

            if ($normalized === null) {
                continue;
            }

            if (isset($seenNames[$normalized])) {
                $skipped[] = "Baris {$entry['row']}: inbox {$normalized} duplikat di file import.";
                continue;
            }

            $seenNames[$normalized] = true;
            $prepared[] = [
                'row' => $entry['row'],
                'inbox_name' => $normalized,
            ];
        }

        $existingNames = Inbox::query()
            ->whereIn('inbox_name', array_column($prepared, 'inbox_name'))
            ->pluck('inbox_name')
            ->all();

        $existingLookup = array_fill_keys($existingNames, true);
        $created = 0;

        foreach ($prepared as $item) {
            if (isset($existingLookup[$item['inbox_name']])) {
                $skipped[] = "Baris {$item['row']}: inbox {$item['inbox_name']} sudah terdaftar.";
                continue;
            }

            Inbox::query()->create([
                'group_id' => $group->id,
                'inbox_name' => $item['inbox_name'],
                'slug' => $this->generateUniqueSlug($item['inbox_name']),
            ]);

            $existingLookup[$item['inbox_name']] = true;
            $created++;
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'processed' => count($prepared),
        ];
    }

    protected function extractEntries(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        $rows = match ($extension) {
            'csv', 'txt' => $this->readCsvRows($file),
            'xlsx' => $this->readXlsxRows($file),
            default => throw new RuntimeException('Format file tidak didukung. Gunakan CSV atau XLSX.'),
        };

        if ($rows === []) {
            throw new RuntimeException('File import kosong atau tidak berisi data inbox.');
        }

        $headerColumnIndex = $this->detectInboxColumnIndex($rows[0]['cells'] ?? []);
        $entries = [];

        foreach ($rows as $index => $row) {
            if ($index === 0 && $headerColumnIndex !== null) {
                continue;
            }

            $value = $headerColumnIndex !== null
                ? ($row['cells'][$headerColumnIndex] ?? null)
                : $this->firstNonEmptyCell($row['cells']);

            if ($value === null) {
                continue;
            }

            $entries[] = [
                'row' => $row['number'],
                'value' => $value,
            ];
        }

        return $entries;
    }

    protected function readCsvRows(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'rb');

        if (! $handle) {
            throw new RuntimeException('File CSV tidak bisa dibuka.');
        }

        $rows = [];
        $lineNumber = 0;

        while (($cells = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if ($cells === [null] || $cells === false) {
                continue;
            }

            $rows[] = [
                'number' => $lineNumber,
                'cells' => array_map(fn ($cell) => trim((string) $cell), $cells),
            ];
        }

        fclose($handle);

        return $rows;
    }

    protected function readXlsxRows(UploadedFile $file): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi ZIP pada PHP belum tersedia untuk membaca file XLSX.');
        }

        $zip = new ZipArchive();

        if ($zip->open($file->getRealPath()) !== true) {
            throw new RuntimeException('File XLSX tidak bisa dibuka.');
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $worksheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');

        if (! is_string($worksheetXml) || $worksheetXml === '') {
            $zip->close();
            throw new RuntimeException('Sheet pertama pada file XLSX tidak ditemukan.');
        }

        $sheet = simplexml_load_string($worksheetXml);

        if (! $sheet) {
            $zip->close();
            throw new RuntimeException('Isi sheet XLSX tidak valid.');
        }

        $sheet->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $namespace = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $sheetData = $sheet->children($namespace)->sheetData ?? null;

        $rows = [];

        if (! $sheetData) {
            $zip->close();

            return $rows;
        }

        foreach ($sheetData->children($namespace) as $rowNode) {
            $cells = [];

            foreach ($rowNode->children($namespace) as $cellNode) {
                $attributes = $cellNode->attributes();
                $reference = (string) ($attributes->r ?? '');
                $columnIndex = $this->columnReferenceToIndex($reference);
                $type = (string) ($attributes->t ?? '');
                $cellChildren = $cellNode->children($namespace);
                $valueNode = $cellChildren->v;
                $value = '';

                if ($type === 'inlineStr') {
                    $value = trim((string) (($cellChildren->is?->children($namespace)->t) ?? ''));
                } elseif ($type === 's') {
                    $sharedIndex = (int) ($valueNode ?? 0);
                    $value = $sharedStrings[$sharedIndex] ?? '';
                } else {
                    $value = trim((string) ($valueNode ?? ''));
                }

                $cells[$columnIndex] = $value;
            }

            if ($cells === []) {
                continue;
            }

            ksort($cells);

            $rows[] = [
                'number' => (int) ($rowNode['r'] ?? (count($rows) + 1)),
                'cells' => array_values($cells),
            ];
        }

        $zip->close();

        return $rows;
    }

    protected function readSharedStrings(ZipArchive $zip): array
    {
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');

        if (! is_string($sharedStringsXml) || $sharedStringsXml === '') {
            return [];
        }

        $xml = simplexml_load_string($sharedStringsXml);

        if (! $xml) {
            return [];
        }

        $namespace = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $items = [];

        foreach ($xml->children($namespace) as $stringItem) {
            $text = '';

            foreach ($stringItem->children($namespace) as $child) {
                if ($child->getName() === 't') {
                    $text .= (string) $child;
                }

                if ($child->getName() === 'r') {
                    $text .= (string) ($child->children($namespace)->t ?? '');
                }
            }

            $items[] = trim($text);
        }

        return $items;
    }

    protected function detectInboxColumnIndex(array $cells): ?int
    {
        foreach ($cells as $index => $cell) {
            $normalized = Str::of((string) $cell)
                ->lower()
                ->replace(['_', '-'], ' ')
                ->squish()
                ->value();

            if (in_array($normalized, [
                'inbox',
                'inbox name',
                'inboxname',
                'inbox email',
                'email',
                'email address',
                'alamat email',
            ], true)) {
                return (int) $index;
            }
        }

        return null;
    }

    protected function firstNonEmptyCell(array $cells): ?string
    {
        foreach ($cells as $cell) {
            $value = trim((string) $cell);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function normalizeInboxName(string $value): ?string
    {
        $value = strtolower(trim($value));

        if ($value === '') {
            return null;
        }

        if (str_contains($value, '@')) {
            $expectedDomain = '@'.strtolower(config('apli_mail.domain'));

            if (! str_ends_with($value, $expectedDomain)) {
                return null;
            }

            $value = Str::before($value, '@');
        }

        return $value !== '' ? $value : null;
    }

    protected function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name, '-');

        if ($slug === '') {
            $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-') ?: strtolower(Str::random(8));
        }

        $candidate = $slug;
        $suffix = 1;

        while (Inbox::query()->where('slug', $candidate)->exists()) {
            $candidate = "{$slug}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }

    protected function columnReferenceToIndex(string $reference): int
    {
        preg_match('/^[A-Z]+/i', $reference, $matches);
        $letters = strtoupper($matches[0] ?? 'A');
        $index = 0;

        for ($i = 0; $i < strlen($letters); $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return max($index - 1, 0);
    }
}
