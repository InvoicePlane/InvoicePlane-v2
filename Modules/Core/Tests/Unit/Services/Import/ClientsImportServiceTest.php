<?php

namespace Modules\Core\Tests\Unit\Services\Import;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Clients\Models\Address;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Services\Import\ClientsImportService;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class ClientsImportServiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    private ClientsImportService $service;

    private $company;

    private array $idMappings = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->service    = new ClientsImportService();
        $this->company    = Company::factory()->create();
        $this->idMappings = ['clients' => []];

        DB::purge('import_v1');

        $this->setupImportDatabase();
    }

    protected function tearDown(): void
    {
        DB::connection('import_v1')->statement('DROP TABLE IF EXISTS ip_clients');
        DB::connection('import_v1')->statement('DROP TABLE IF EXISTS ip_contacts');
        parent::tearDown();
    }

    #[Test]
    public function it_imports_clients_as_relations_successfully(): void
    {
        /* Arrange */
        DB::connection('import_v1')->table('ip_clients')->insert([
            [
                'client_id'        => 1,
                'client_name'      => 'Test Client 1',
                'client_vat_id'    => 'VAT123',
                'client_active'    => 1,
                'client_address_1' => null,
                'client_address_2' => null,
                'client_city'      => null,
                'client_state'     => null,
                'client_zip'       => null,
                'client_country'   => null,
            ],
        ]);

        /* Act */
        $stats = $this->service->import($this->company->id, $this->idMappings);

        /* Assert */
        $this->assertEquals(1, $stats['clients']);
        $this->assertEquals(1, Relation::where('company_id', $this->company->id)->count());

        $relation = Relation::where('company_id', $this->company->id)->first();
        $this->assertEquals('Test Client 1', $relation->company_name);
        $this->assertEquals('VAT123', $relation->vat_number);
        $this->assertEquals('active', $relation->relation_status->value);
        $this->assertEquals('customer', $relation->relation_type->value);
    }

    #[Test]
    public function it_creates_addresses_for_clients_with_address_data(): void
    {
        /* Arrange */
        DB::connection('import_v1')->table('ip_clients')->insert([
            [
                'client_id'        => 1,
                'client_name'      => 'Test Client',
                'client_vat_id'    => null,
                'client_active'    => 1,
                'client_address_1' => '123 Main St',
                'client_address_2' => 'Suite 100',
                'client_city'      => 'New York',
                'client_state'     => 'NY',
                'client_zip'       => '10001',
                'client_country'   => 'US',
            ],
        ]);

        /* Act */
        $stats = $this->service->import($this->company->id, $this->idMappings);

        /* Assert */
        $this->assertEquals(1, $stats['addresses']);

        $address = Address::where('company_id', $this->company->id)->first();
        $this->assertNotNull($address);
        $this->assertEquals('123 Main St', $address->address_1);
        $this->assertEquals('New York', $address->city);
        $this->assertEquals('10001', $address->zip);
    }

    #[Test]
    public function it_does_not_create_address_when_no_address_data(): void
    {
        /* Arrange */
        DB::connection('import_v1')->table('ip_clients')->insert([
            [
                'client_id'        => 1,
                'client_name'      => 'Test Client',
                'client_vat_id'    => null,
                'client_active'    => 1,
                'client_address_1' => null,
                'client_address_2' => null,
                'client_city'      => null,
                'client_state'     => null,
                'client_zip'       => null,
                'client_country'   => null,
            ],
        ]);

        /* Act */
        $stats = $this->service->import($this->company->id, $this->idMappings);

        /* Assert */
        $this->assertEquals(0, $stats['addresses']);
        $this->assertEquals(0, Address::where('company_id', $this->company->id)->count());
    }

    #[Test]
    public function it_imports_contacts_successfully(): void
    {
        /* Arrange */
        DB::connection('import_v1')->table('ip_clients')->insert([
            [
                'client_id'        => 1,
                'client_name'      => 'Test Client',
                'client_vat_id'    => null,
                'client_active'    => 1,
                'client_address_1' => null,
                'client_address_2' => null,
                'client_city'      => null,
                'client_state'     => null,
                'client_zip'       => null,
                'client_country'   => null,
            ],
        ]);

        DB::connection('import_v1')->table('ip_contacts')->insert([
            [
                'contact_id'    => 1,
                'client_id'     => 1,
                'contact_name'  => 'John Doe',
                'contact_email' => 'john@example.com',
                'contact_phone' => '555-1234',
            ],
        ]);

        /* Act */
        $stats = $this->service->import($this->company->id, $this->idMappings);

        /* Assert */
        $this->assertEquals(1, $stats['contacts']);
        $this->assertEquals(2, $stats['communications']); // email + phone

        $contact = Contact::where('company_id', $this->company->id)->first();
        $this->assertNotNull($contact);
        $this->assertEquals('John', $contact->first_name);
        $this->assertEquals('Doe', $contact->last_name);
        $this->assertEquals('John Doe', $contact->full_name);
    }

    #[Test]
    public function it_skips_contacts_for_non_existent_clients(): void
    {
        /* Arrange */
        DB::connection('import_v1')->table('ip_contacts')->insert([
            [
                'contact_id'    => 1,
                'client_id'     => 999, // Non-existent
                'contact_name'  => 'John Doe',
                'contact_email' => 'john@example.com',
                'contact_phone' => '555-1234',
            ],
        ]);

        /* Act */
        $stats = $this->service->import($this->company->id, $this->idMappings);

        /* Assert */
        $this->assertEquals(0, $stats['contacts']);
    }

    #[Test]
    public function it_handles_inactive_clients(): void
    {
        /* Arrange */
        DB::connection('import_v1')->table('ip_clients')->insert([
            [
                'client_id'        => 1,
                'client_name'      => 'Inactive Client',
                'client_vat_id'    => null,
                'client_active'    => 0,
                'client_address_1' => null,
                'client_address_2' => null,
                'client_city'      => null,
                'client_state'     => null,
                'client_zip'       => null,
                'client_country'   => null,
            ],
        ]);

        /* Act */
        $stats = $this->service->import($this->company->id, $this->idMappings);

        /* Assert */
        $relation = Relation::where('company_id', $this->company->id)->first();
        $this->assertEquals('inactive', $relation->relation_status->value);
    }

    private function setupImportDatabase(): void
    {
        DB::connection('import_v1')->statement('DROP TABLE IF EXISTS ip_clients');
        DB::connection('import_v1')->statement('DROP TABLE IF EXISTS ip_contacts');

        DB::connection('import_v1')->statement('
            CREATE TABLE ip_clients (
                client_id INT PRIMARY KEY,
                client_name VARCHAR(255),
                client_vat_id VARCHAR(255),
                client_active TINYINT,
                client_address_1 VARCHAR(255),
                client_address_2 VARCHAR(255),
                client_city VARCHAR(255),
                client_state VARCHAR(255),
                client_zip VARCHAR(255),
                client_country VARCHAR(255)
            )
        ');

        DB::connection('import_v1')->statement('
            CREATE TABLE ip_contacts (
                contact_id INT PRIMARY KEY,
                client_id INT,
                contact_name VARCHAR(255),
                contact_email VARCHAR(255),
                contact_phone VARCHAR(255)
            )
        ');
    }
}
