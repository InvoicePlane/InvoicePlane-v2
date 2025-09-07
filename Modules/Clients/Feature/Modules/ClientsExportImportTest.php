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
    public function export_contacts_downloads_csv_with_correct_data(): void
    {
        /* Arrange */
        $contacts = Contact::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('export')
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
    public function export_contacts_downloads_excel_with_correct_data(): void
    {
        /* Arrange */
        $contacts = Contact::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('export', ['format' => 'xlsx'])
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
    public function export_contacts_with_no_records(): void
    {
        /* Arrange */
        // No contacts created

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('export')
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
    public function import_contacts_creates_records_from_csv(): void
    {
        /* Arrange */
        $csv  = "name,email\nTest Contact,test@example.com\nAnother Contact,another@example.com\n";
        $file = UploadedFile::fake()->createWithContent('contacts.csv', $csv);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListContacts::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('contacts', 2);
        $this->assertDatabaseHas('contacts', ['name' => 'Test Contact', 'email' => 'test@example.com']);
        $this->assertDatabaseHas('contacts', ['name' => 'Another Contact', 'email' => 'another@example.com']);
    }
}
