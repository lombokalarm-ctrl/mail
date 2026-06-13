<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Inbox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use ZipArchive;

class AdminGroupManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_management_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create([
            'name' => 'Acme Travel',
            'viewer_token' => 'acme2026',
        ]);

        Inbox::factory()->create([
            'group_id' => $group->id,
            'inbox_name' => 'support-acme',
            'slug' => 'support-acme',
        ]);

        $this->actingAs($user)
            ->get(route('admin.groups.index'))
            ->assertOk()
            ->assertSee('Kelola Group SaaS Dan Inbox')
            ->assertSee('Acme Travel')
            ->assertSee('support-acme');
    }

    public function test_admin_can_create_group_and_inbox_from_web_ui(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.groups.store'), [
                'name' => 'Acme Travel',
                'viewer_token' => 'acme2026',
                'status' => 'active',
            ])
            ->assertRedirect();

        $group = Group::query()->where('viewer_token', 'acme2026')->firstOrFail();

        $this->actingAs($user)
            ->post(route('admin.inboxes.store'), [
                'group_id' => $group->id,
                'inbox_name' => 'support-acme',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('groups', [
            'name' => 'Acme Travel',
            'viewer_token' => 'acme2026',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('inboxes', [
            'group_id' => $group->id,
            'inbox_name' => 'support-acme',
            'slug' => 'support-acme',
            'access_token' => 'acme2026',
        ]);
    }

    public function test_admin_can_update_and_delete_group_and_inbox_from_web_ui(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create([
            'name' => 'Acme Travel',
            'viewer_token' => 'acme2026',
            'status' => 'trial',
        ]);
        $otherGroup = Group::factory()->create([
            'name' => 'Beta Travel',
            'viewer_token' => 'beta2026',
        ]);
        $inbox = Inbox::factory()->create([
            'group_id' => $group->id,
            'inbox_name' => 'support-acme',
            'slug' => 'support-acme',
        ]);

        $this->actingAs($user)
            ->patch(route('admin.groups.update', $group), [
                'name' => 'Acme Premium',
                'viewer_token' => 'acmepremium',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->patch(route('admin.inboxes.update', $inbox), [
                'group_id' => $otherGroup->id,
                'inbox_name' => 'sales-beta',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'name' => 'Acme Premium',
            'viewer_token' => 'acmepremium',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('inboxes', [
            'id' => $inbox->id,
            'group_id' => $otherGroup->id,
            'inbox_name' => 'sales-beta',
            'slug' => 'sales-beta',
            'access_token' => 'beta2026',
        ]);

        $this->actingAs($user)
            ->delete(route('admin.inboxes.destroy', $inbox))
            ->assertRedirect();

        $this->actingAs($user)
            ->delete(route('admin.groups.destroy', $group))
            ->assertRedirect();

        $this->assertDatabaseMissing('inboxes', [
            'id' => $inbox->id,
        ]);

        $this->assertDatabaseMissing('groups', [
            'id' => $group->id,
        ]);
    }

    public function test_admin_can_import_inboxes_from_csv_into_group(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create([
            'name' => 'Acme Travel',
            'viewer_token' => 'acme2026',
        ]);

        Inbox::factory()->create([
            'group_id' => $group->id,
            'inbox_name' => 'existing-acme',
            'slug' => 'existing-acme',
        ]);

        $file = $this->makeUploadedFile(
            'inboxes.csv',
            implode("\n", [
                'inbox_name',
                'support-acme',
                'billing-acme@email.apli.my.id',
                'existing-acme',
                'support-acme',
            ]),
        );

        $this->actingAs($user)
            ->post(route('admin.groups.import-inboxes'), [
                'group_id' => $group->id,
                'import_file' => $file,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('inboxes', [
            'group_id' => $group->id,
            'inbox_name' => 'support-acme',
            'slug' => 'support-acme',
            'access_token' => 'acme2026',
        ]);

        $this->assertDatabaseHas('inboxes', [
            'group_id' => $group->id,
            'inbox_name' => 'billing-acme',
            'slug' => 'billing-acme',
            'access_token' => 'acme2026',
        ]);
    }

    public function test_admin_can_import_inboxes_from_xlsx_into_group(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create([
            'name' => 'Beta Travel',
            'viewer_token' => 'beta2026',
        ]);

        $file = $this->makeXlsxUpload('inboxes.xlsx', [
            ['inbox_name'],
            ['sales-beta'],
            ['ops-beta@email.apli.my.id'],
        ]);

        $this->actingAs($user)
            ->post(route('admin.groups.import-inboxes'), [
                'group_id' => $group->id,
                'import_file' => $file,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('inboxes', [
            'group_id' => $group->id,
            'inbox_name' => 'sales-beta',
            'slug' => 'sales-beta',
            'access_token' => 'beta2026',
        ]);

        $this->assertDatabaseHas('inboxes', [
            'group_id' => $group->id,
            'inbox_name' => 'ops-beta',
            'slug' => 'ops-beta',
            'access_token' => 'beta2026',
        ]);
    }

    protected function makeUploadedFile(string $name, string $contents): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, $contents);
    }

    protected function makeXlsxUpload(string $name, array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'apli-import-');
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $sharedStrings = [];
        $sharedStringIndexes = [];
        $sheetRowsXml = '';

        foreach ($rows as $rowIndex => $row) {
            $cellsXml = '';

            foreach ($row as $columnIndex => $value) {
                $stringValue = (string) $value;

                if (! array_key_exists($stringValue, $sharedStringIndexes)) {
                    $sharedStringIndexes[$stringValue] = count($sharedStrings);
                    $sharedStrings[] = $stringValue;
                }

                $cellReference = $this->columnIndexToLetters($columnIndex).($rowIndex + 1);
                $sharedIndex = $sharedStringIndexes[$stringValue];
                $cellsXml .= '<c r="'.$cellReference.'" t="s"><v>'.$sharedIndex.'</v></c>';
            }

            $sheetRowsXml .= '<row r="'.($rowIndex + 1).'">'.$cellsXml.'</row>';
        }

        $sharedStringsXml = '';

        foreach ($sharedStrings as $stringValue) {
            $sharedStringsXml .= '<si><t>'.htmlspecialchars($stringValue, ENT_XML1).'</t></si>';
        }

        $zip->addFromString('[Content_Types].xml', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
</Types>
XML);

        $zip->addFromString('_rels/.rels', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML);

        $zip->addFromString('xl/workbook.xml', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
    xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Sheet1" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>
XML);

        $zip->addFromString('xl/_rels/workbook.xml.rels', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>
XML);

        $zip->addFromString('xl/sharedStrings.xml', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="{$this->countCells($rows)}" uniqueCount="{$this->countUniqueCells($sharedStrings)}">
    {$sharedStringsXml}
</sst>
XML);

        $zip->addFromString('xl/worksheets/sheet1.xml', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheetData>
        {$sheetRowsXml}
    </sheetData>
</worksheet>
XML);

        $zip->close();

        $contents = file_get_contents($path);
        unlink($path);

        return UploadedFile::fake()->createWithContent($name, $contents ?: '');
    }

    protected function columnIndexToLetters(int $index): string
    {
        $letters = '';
        $index++;

        while ($index > 0) {
            $remainder = ($index - 1) % 26;
            $letters = chr(65 + $remainder).$letters;
            $index = intdiv($index - 1, 26);
        }

        return $letters;
    }

    protected function countCells(array $rows): int
    {
        return array_sum(array_map(fn ($row) => count($row), $rows));
    }

    protected function countUniqueCells(array $sharedStrings): int
    {
        return count($sharedStrings);
    }
}
