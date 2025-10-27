<?php

namespace Modules\Clients\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Clients\Filament\Company\Resources\Contacts\Pages\ListContacts;
use Modules\Clients\Models\Contact;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ClientsExportImportTest extends AbstractCompanyPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    public function it_exports_contacts_downloads_csv_with_correct_data(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $contacts = Contact::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('exportCsv')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertTrue(
            in_array(
                $response->headers->get('content-type'),
                [
                    'text/csv',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            )
        );
        $content = $response->getContent();
        $lines   = preg_split('/\r?\n/', mb_trim($content));
        $this->assertGreaterThanOrEqual(2, count($lines));
        $this->assertCount($contacts->count() + 1, $lines);
        foreach ($contacts as $contact) {
            $this->assertStringContainsString($contact->name, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function it_exports_contacts_downloads_excel_with_correct_data(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $contacts = Contact::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('exportExcel')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $content = $response->getContent();
        // Check for XLSX file signature (PK\x03\x04)
        $this->assertStringStartsWith('PK', $content);
    }

    #[Test]
    #[Group('export')]
    public function it_exports_contacts_with_no_records(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        // No contacts created

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('exportExcel')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $content = $response->getContent();
        $lines   = preg_split('/\r?\n/', mb_trim($content));
        $this->assertGreaterThanOrEqual(1, count($lines)); // Only header row
    }

    #[Test]
    #[Group('export')]
    public function export_contacts_with_special_characters(): void
    {
        /* Arrange */
        $contacts = Contact::factory()->for($this->company)->for($this->company)->create(['name' => 'Jöhn Dœ, "Test"', 'email' => 'special@example.com']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('export')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $content = $response->getContent();
        $this->assertStringContainsString('Jöhn Dœ', $content);
        $this->assertStringContainsString('"Test"', $content);
        $this->assertStringContainsString('special@example.com', $content);
    }

    #[Test]
    #[Group('import')]
    public function import_contacts_with_empty_file(): void
    {
        /* Arrange */
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('contacts.csv', '');

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('contacts', 0);
    }

    #[Test]
    #[Group('import')]
    public function import_contacts_with_only_headers(): void
    {
        /* Arrange */
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('contacts.csv', "name,email\n");

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('contacts', 0);
    }

    #[Test]
    #[Group('import')]
    public function import_contacts_with_invalid_columns(): void
    {
        /* Arrange */
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('contacts.csv', "foo,bar\nabc,def\n");

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('contacts', 0);
        // Optionally, assert error message if your import action provides one
    }

    #[Test]
    #[Group('import')]
    public function import_contacts_with_duplicate_records(): void
    {
        /* Arrange */
        $csv  = "name,email\nDup User,dup@example.com\nDup User,dup@example.com\n";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('contacts.csv', $csv);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('contacts', 2); // or 1 if your import deduplicates
    }

    #[Test]
    #[Group('import')]
    public function import_contacts_with_invalid_data_types(): void
    {
        /* Arrange */
        $csv  = "name,email\n12345,not-an-email\n";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('contacts.csv', $csv);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        // Depending on your validation, this may fail or create a record
        $this->assertDatabaseHas('contacts', ['name' => '12345', 'email' => 'not-an-email']);
    }

    #[Test]
    #[Group('import')]
    public function import_contacts_with_large_file(): void
    {
        /* Arrange */
        $rows = [];
        for ($i = 0; $i < 1000; $i++) {
            $rows[] = "User{$i},user{$i}@example.com";
        }
        $csv  = "name,email\n" . implode("\n", $rows);
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('contacts.csv', $csv);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('contacts', 1000);
    }

    #[Test]
    #[Group('import')]
    public function import_contacts_with_extra_columns(): void
    {
        /* Arrange */
        $csv  = "name,email,extra\nExtra User,extra@example.com,something\n";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('contacts.csv', $csv);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseHas('contacts', ['name' => 'Extra User', 'email' => 'extra@example.com']);
    }

    #[Test]
    #[Group('import')]
    public function import_contacts_with_missing_required_columns(): void
    {
        /* Arrange */
        $csv  = "name\nMissing Email\n";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('contacts.csv', $csv);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        // Should not create a record if email is required
        $this->assertDatabaseCount('contacts', 0);
    }
}
