<?php

namespace Tests\Feature;

use App\Models\Email;
use App\Models\Group;
use App\Models\Inbox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use ZipArchive;
use Tests\TestCase;

class GroupAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_admin_is_redirected_to_profile_until_password_is_changed(): void
    {
        $groupAdmin = User::factory()->groupAdmin()->create([
            'password' => 'TempPass123!',
            'must_change_password' => true,
        ]);

        $this->post('/login', [
            'email' => $groupAdmin->email,
            'password' => 'TempPass123!',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->actingAs($groupAdmin)
            ->get(route('dashboard'))
            ->assertRedirect(route('profile.edit', absolute: false));
    }

    public function test_group_admin_only_sees_inboxes_and_emails_for_own_group(): void
    {
        $firstGroup = Group::factory()->create(['name' => 'Alpha Group']);
        $secondGroup = Group::factory()->create(['name' => 'Beta Group']);
        $firstInbox = Inbox::factory()->create([
            'group_id' => $firstGroup->id,
            'inbox_name' => 'alpha-inbox',
            'slug' => 'alpha-inbox',
        ]);
        $secondInbox = Inbox::factory()->create([
            'group_id' => $secondGroup->id,
            'inbox_name' => 'beta-inbox',
            'slug' => 'beta-inbox',
        ]);

        Email::query()->create([
            'inbox_id' => $firstInbox->id,
            'sender_email' => 'sender-alpha@example.com',
            'sender_name' => 'Sender Alpha',
            'recipient_email' => 'alpha-inbox@email.apli.my.id',
            'subject' => 'Email Alpha',
            'body_text' => 'Halo Alpha',
            'received_at' => now(),
        ]);

        Email::query()->create([
            'inbox_id' => $secondInbox->id,
            'sender_email' => 'sender-beta@example.com',
            'sender_name' => 'Sender Beta',
            'recipient_email' => 'beta-inbox@email.apli.my.id',
            'subject' => 'Email Beta',
            'body_text' => 'Halo Beta',
            'received_at' => now(),
        ]);

        $groupAdmin = User::factory()->groupAdmin($firstGroup)->create([
            'must_change_password' => false,
        ]);

        $this->actingAs($groupAdmin)
            ->get(route('admin.inboxes.index'))
            ->assertOk()
            ->assertSee('alpha-inbox')
            ->assertDontSee('beta-inbox');

        $this->actingAs($groupAdmin)
            ->get(route('admin.emails.index'))
            ->assertOk()
            ->assertSee('Email Alpha')
            ->assertDontSee('Email Beta');
    }

    public function test_group_admin_cannot_access_group_manager_or_other_group_records(): void
    {
        $firstGroup = Group::factory()->create();
        $secondGroup = Group::factory()->create();
        $firstInbox = Inbox::factory()->create([
            'group_id' => $firstGroup->id,
        ]);
        $secondInbox = Inbox::factory()->create([
            'group_id' => $secondGroup->id,
        ]);
        $groupAdmin = User::factory()->groupAdmin($firstGroup)->create([
            'must_change_password' => false,
        ]);

        $this->actingAs($groupAdmin)
            ->get(route('admin.groups.index'))
            ->assertForbidden();

        $this->actingAs($groupAdmin)
            ->patch(route('admin.inboxes.update', $secondInbox), [
                'group_id' => $secondGroup->id,
                'inbox_name' => 'updated-beta',
            ])
            ->assertNotFound();

        $this->actingAs($groupAdmin)
            ->post(route('admin.inboxes.store'), [
                'group_id' => $secondGroup->id,
                'inbox_name' => 'new-alpha',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('inboxes', [
            'group_id' => $firstGroup->id,
            'inbox_name' => 'new-alpha',
        ]);

        $this->assertDatabaseMissing('inboxes', [
            'group_id' => $secondGroup->id,
            'inbox_name' => 'new-alpha',
        ]);
    }

    public function test_group_admin_can_import_inboxes_from_group_console_only_into_own_group(): void
    {
        $firstGroup = Group::factory()->create(['name' => 'Alpha Group']);
        $secondGroup = Group::factory()->create(['name' => 'Beta Group']);
        $groupAdmin = User::factory()->groupAdmin($firstGroup)->create([
            'must_change_password' => false,
        ]);

        $file = UploadedFile::fake()->createWithContent('group-inboxes.csv', implode("\n", [
            'inbox_name',
            'sales-alpha',
            'ops-alpha@email.apli.my.id',
        ]));

        $this->actingAs($groupAdmin)
            ->post(route('admin.inboxes.import'), [
                'group_id' => $secondGroup->id,
                'import_file' => $file,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('inboxes', [
            'group_id' => $firstGroup->id,
            'inbox_name' => 'sales-alpha',
        ]);

        $this->assertDatabaseHas('inboxes', [
            'group_id' => $firstGroup->id,
            'inbox_name' => 'ops-alpha',
        ]);

        $this->assertDatabaseMissing('inboxes', [
            'group_id' => $secondGroup->id,
            'inbox_name' => 'sales-alpha',
        ]);
    }

    public function test_group_admin_can_import_xlsx_from_group_console(): void
    {
        $group = Group::factory()->create(['name' => 'Gamma Group']);
        $groupAdmin = User::factory()->groupAdmin($group)->create([
            'must_change_password' => false,
        ]);

        $file = $this->makeXlsxUpload('group-inboxes.xlsx', [
            ['inbox_name'],
            ['billing-gamma'],
            ['support-gamma@email.apli.my.id'],
        ]);

        $this->actingAs($groupAdmin)
            ->post(route('admin.inboxes.import'), [
                'group_id' => $group->id,
                'import_file' => $file,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('inboxes', [
            'group_id' => $group->id,
            'inbox_name' => 'billing-gamma',
        ]);

        $this->assertDatabaseHas('inboxes', [
            'group_id' => $group->id,
            'inbox_name' => 'support-gamma',
        ]);
    }

    protected function makeXlsxUpload(string $name, array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'apli-group-import-');
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
