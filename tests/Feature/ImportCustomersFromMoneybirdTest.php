<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ImportCustomersFromMoneybirdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.moneybird.api_token' => 'test-token',
            'services.moneybird.administration_id' => '123456',
            'services.moneybird.base_url' => 'https://api.example.com/api/v2',
        ]);

        Queue::fake();
    }

    /**
     * @param  array<int, array<string, mixed>>  $contacts
     */
    private function fakeContacts(array $contacts): void
    {
        Http::fake([
            'api.example.com/api/v2/123456/contacts.json*' => Http::response($contacts, 200),
            'nominatim.openstreetmap.org/*' => Http::response([], 200),
        ]);
    }

    public function test_it_maps_contacts_and_keeps_numeric_customer_numbers_as_ids(): void
    {
        $this->fakeContacts([
            [
                'id' => '9001',
                'customer_id' => '42',
                'company_name' => 'Acme Corp',
                'address1' => 'Hoofdstraat 1',
                'zipcode' => '1234 AB',
                'city' => 'Amsterdam',
                'country' => 'NL',
                'phone' => '0612345678',
                'email' => 'admin@acme.test',
                'send_invoices_to_email' => 'facturen@acme.test',
                'chamber_of_commerce' => '12345678',
                'tax_number' => 'NL123456789B01',
                'created_at' => '2024-03-15T10:00:00.000Z',
            ],
            [
                'id' => '9002',
                'customer_id' => '',
                'company_name' => 'Belgische BV',
                'country' => 'BE',
                'email' => 'info@belgie.test',
            ],
            [
                'id' => '9003',
                'customer_id' => null,
                'company_name' => '',
                'firstname' => 'Jan',
                'lastname' => 'Jansen',
                'country' => 'XX',
            ],
        ]);

        $this->artisan('moneybird:import-customers', ['--force' => true])->assertExitCode(0);

        $acme = Customer::findOrFail(42);
        $this->assertSame('Acme Corp', $acme->name);
        $this->assertSame('9001', $acme->moneybird_contact_id);
        $this->assertSame('admin@acme.test', $acme->administration_email);
        $this->assertSame('facturen@acme.test', $acme->email);
        $this->assertSame('Nederland', $acme->country);
        $this->assertSame('2024-03-15', $acme->customer_since->toDateString());

        $belgian = Customer::where('moneybird_contact_id', '9002')->firstOrFail();
        $this->assertGreaterThan(42, $belgian->id);
        $this->assertSame('België', $belgian->country);
        $this->assertSame('', $belgian->address);

        $person = Customer::where('moneybird_contact_id', '9003')->firstOrFail();
        $this->assertSame('Jan Jansen', $person->name);
        $this->assertSame('Overig', $person->country);
    }

    public function test_it_does_not_geocode_or_sync_back_to_moneybird(): void
    {
        $this->fakeContacts([
            ['id' => '9001', 'customer_id' => '1', 'company_name' => 'Acme', 'address1' => 'Straat 1', 'city' => 'Utrecht', 'country' => 'NL', 'email' => 'a@acme.test'],
            ['id' => '9002', 'customer_id' => 'L00001', 'company_name' => 'Lev', 'address1' => 'Straat 2', 'city' => 'Utrecht', 'country' => 'NL', 'email' => 'l@lev.test'],
        ]);

        $this->artisan('moneybird:import-customers', ['--force' => true])->assertExitCode(0);

        Queue::assertNothingPushed();
        Http::assertSentCount(1);
        $this->assertNull(Customer::firstOrFail()->latitude);
        $this->assertNull(Supplier::firstOrFail()->latitude);
    }

    public function test_it_imports_l_numbered_contacts_as_suppliers_and_keeps_their_number(): void
    {
        $this->fakeContacts([
            ['id' => '9001', 'customer_id' => 'L12', 'company_name' => 'Leverancier BV', 'address1' => 'Industrieweg 5', 'city' => 'Zwolle', 'country' => 'NL', 'email' => 'l@l.test', 'created_at' => '2024-01-10T08:00:00.000Z'],
            ['id' => '9002', 'customer_id' => '7', 'company_name' => null],
            ['id' => '9003', 'customer_id' => '8', 'company_name' => 'Echte Klant', 'email' => 'k@k.test'],
            ['id' => '9004', 'customer_id' => 'L00001', 'company_name' => 'Zonder mail', 'country' => 'DE'],
        ]);

        $this->artisan('moneybird:import-customers', ['--force' => true])->assertExitCode(0);

        $this->assertSame(['Echte Klant'], Customer::pluck('name')->all());

        $supplier = Supplier::findOrFail(12);
        $this->assertSame('L12', $supplier->supplier_number);
        $this->assertSame('Leverancier BV', $supplier->name);
        $this->assertSame('9001', $supplier->moneybird_contact_id);
        $this->assertSame('2024-01-10', $supplier->supplier_since->toDateString());
        $this->assertSame('Nederland', $supplier->country);

        $withoutMail = Supplier::findOrFail(1);
        $this->assertSame('', $withoutMail->administration_email);
        $this->assertSame('Duitsland', $withoutMail->country);
        $this->assertSame(2, Supplier::count());
    }

    public function test_supplier_and_customer_numbers_do_not_collide_and_reruns_do_not_duplicate(): void
    {
        $this->fakeContacts([
            ['id' => '9001', 'customer_id' => '5', 'company_name' => 'Klant vijf', 'email' => 'k@k.test'],
            ['id' => '9002', 'customer_id' => 'L00005', 'company_name' => 'Leverancier vijf', 'email' => 'l@l.test'],
            ['id' => '9003', 'customer_id' => 'L00005', 'company_name' => 'Dubbel nummer', 'email' => 'd@d.test'],
        ]);

        $this->artisan('moneybird:import-customers', ['--force' => true])
            ->expectsOutputToContain('was al in gebruik')
            ->assertExitCode(0);
        $this->artisan('moneybird:import-customers', ['--force' => true])->assertExitCode(0);

        $this->assertSame(5, Customer::where('moneybird_contact_id', '9001')->value('id'));
        $this->assertSame(5, Supplier::where('moneybird_contact_id', '9002')->value('id'));
        $this->assertSame(2, Supplier::count());
        $this->assertSame(1, Customer::count());
    }

    public function test_it_skips_contacts_without_any_name(): void
    {
        $this->fakeContacts([
            ['id' => '9002', 'customer_id' => '7', 'company_name' => null],
            ['id' => '9003', 'customer_id' => 'L00003', 'company_name' => ''],
        ]);

        $this->artisan('moneybird:import-customers', ['--force' => true])->assertExitCode(0);

        $this->assertSame(0, Customer::count() + Supplier::count());
    }

    public function test_running_it_twice_does_not_duplicate_customers(): void
    {
        $this->fakeContacts([
            ['id' => '9001', 'customer_id' => '1', 'company_name' => 'Acme', 'email' => 'a@acme.test'],
            ['id' => '9002', 'customer_id' => '1', 'company_name' => 'Zelfde nummer', 'email' => 'z@z.test'],
        ]);

        $this->artisan('moneybird:import-customers', ['--force' => true])->assertExitCode(0);
        $this->artisan('moneybird:import-customers', ['--force' => true])->assertExitCode(0);

        $this->assertSame(2, Customer::count());
        $this->assertSame(1, Customer::where('moneybird_contact_id', '9001')->value('id'));
    }

    public function test_dry_run_does_not_write_anything(): void
    {
        $this->fakeContacts([
            ['id' => '9001', 'customer_id' => '1', 'company_name' => 'Test Corp', 'email' => 'a@test.test'],
        ]);

        $this->artisan('moneybird:import-customers', ['--dry-run' => true])
            ->expectsOutputToContain('Test Corp (Moneybird 9001)')
            ->assertExitCode(0);

        $this->assertSame(0, Customer::withTrashed()->count());
    }

    public function test_a_failing_contact_does_not_stop_the_rest_and_fails_the_command(): void
    {
        $this->fakeContacts([
            ['id' => '9001', 'customer_id' => '1', 'company_name' => 'Kapot', 'email' => 'a@a.test', 'created_at' => 'geen datum'],
            ['id' => '9002', 'customer_id' => '2', 'company_name' => 'Gewoon', 'email' => 'b@b.test'],
        ]);

        $this->artisan('moneybird:import-customers', ['--force' => true])->assertExitCode(1);

        $this->assertSame(['Gewoon'], Customer::pluck('name')->all());
    }

    public function test_it_drops_values_that_do_not_fit_and_warns(): void
    {
        $this->fakeContacts([
            ['id' => '9001', 'customer_id' => '1', 'company_name' => 'Acme', 'email' => 'a@a.test', 'chamber_of_commerce' => str_repeat('1', 21)],
        ]);

        $this->artisan('moneybird:import-customers', ['--force' => true])
            ->expectsOutputToContain('KvK-nummer')
            ->assertExitCode(0);

        $this->assertNull(Customer::firstOrFail()->kvk);
    }

    public function test_it_follows_pagination(): void
    {
        $firstPage = collect(range(1, 100))->map(fn (int $number) => [
            'id' => (string) (9000 + $number),
            'customer_id' => (string) $number,
            'company_name' => "Klant {$number}",
            'email' => "k{$number}@test.test",
        ])->all();

        Http::fake([
            'api.example.com/api/v2/123456/contacts.json*' => Http::sequence()
                ->push($firstPage, 200)
                ->push([['id' => '9999', 'customer_id' => '101', 'company_name' => 'Laatste', 'email' => 'l@test.test']], 200),
        ]);

        $this->artisan('moneybird:import-customers', ['--force' => true])->assertExitCode(0);

        $this->assertSame(101, Customer::count());
        Http::assertSentCount(2);
    }

    public function test_it_fails_without_credentials(): void
    {
        config(['services.moneybird.api_token' => null]);

        $this->artisan('moneybird:import-customers', ['--force' => true])->assertExitCode(1);
    }
}
