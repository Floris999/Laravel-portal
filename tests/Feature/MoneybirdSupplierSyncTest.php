<?php

namespace Tests\Feature;

use App\Jobs\SyncSupplierToMoneybird;
use App\Models\Customer;
use App\Models\Supplier;
use App\Services\MoneybirdService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MoneybirdSupplierSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_numbers_are_prefixed_and_independent_of_customers(): void
    {
        Customer::factory()->count(3)->create();

        $first = Supplier::factory()->create();
        $second = Supplier::factory()->create();

        $this->assertSame('L00001', $first->supplier_number);
        $this->assertSame('L00002', $second->supplier_number);
    }

    public function test_it_sends_the_supplier_number_to_moneybird_as_customer_id(): void
    {
        config([
            'services.moneybird.api_token' => 'test-token',
            'services.moneybird.administration_id' => '123456',
            'services.moneybird.base_url' => 'https://moneybird.test/api/v2',
        ]);

        Http::fake(['moneybird.test/*' => Http::response(['id' => '555'], 201)]);

        $supplier = Supplier::factory()->create(['kvk' => null, 'country' => 'Nederland']);

        (new SyncSupplierToMoneybird($supplier->id))->handle(app(MoneybirdService::class));

        $this->assertSame('555', $supplier->refresh()->moneybird_contact_id);

        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === 'https://moneybird.test/api/v2/123456/contacts.json'
            && $request['contact']['customer_id'] === $supplier->supplier_number
            && str_starts_with($request['contact']['customer_id'], 'L')
            && $request['contact']['chamber_of_commerce'] === null);
    }
}
