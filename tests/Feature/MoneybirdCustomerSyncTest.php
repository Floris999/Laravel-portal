<?php

namespace Tests\Feature;

use App\Jobs\SyncCustomerToMoneybird;
use App\Models\Customer;
use App\Services\MoneybirdService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MoneybirdCustomerSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_contact_and_updates_its_local_moneybird_id(): void
    {
        config([
            'services.moneybird.api_token' => 'test-token',
            'services.moneybird.administration_id' => '123456',
            'services.moneybird.base_url' => 'https://moneybird.test/api/v2',
        ]);

        Http::fake([
            'moneybird.test/*' => Http::sequence()
                ->push(['id' => '987654321'], 201)
                ->push([], 200),
        ]);

        $customer = Customer::factory()->create([
            'country' => 'Nederland',
            'administration_email' => 'administratie@example.com',
            'email' => 'facturen@example.com',
        ]);

        $job = new SyncCustomerToMoneybird($customer->id);
        $service = app(MoneybirdService::class);

        $job->handle($service);

        $customer->refresh();
        $this->assertSame('987654321', $customer->moneybird_contact_id);

        $job->handle($service);

        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === 'https://moneybird.test/api/v2/123456/contacts.json'
            && $request->hasHeader('Authorization', 'Bearer test-token')
            && $request['contact']['customer_id'] === (string) $customer->id
            && $request['contact']['email'] === 'administratie@example.com'
            && $request['contact']['send_invoices_to_email'] === 'facturen@example.com'
            && $request['contact']['country'] === 'NL');

        Http::assertSent(fn ($request) => $request->method() === 'PUT'
            && $request->url() === 'https://moneybird.test/api/v2/123456/contacts/987654321.json'
            && $request['contact']['customer_id'] === (string) $customer->id);
    }
}
