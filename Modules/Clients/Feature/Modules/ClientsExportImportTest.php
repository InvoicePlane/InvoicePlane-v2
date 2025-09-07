<?php

namespace Modules\Clients\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Modules\Clients\Filament\Company\Resources\Contacts\Pages\ListContacts;
use Modules\Clients\Models\Contact;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ClientsExportImportTest extends AbstractAdminPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    public function export_contacts_downloads_csv_with_correct_data(): void
    {
        /* Arrange */
        $contacts = Contact::factory()->count(3)->create();

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
