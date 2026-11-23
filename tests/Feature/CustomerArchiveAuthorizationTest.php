<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerArchiveAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_medewerker_cannot_see_or_use_customer_archive_actions(): void
    {
        $user = User::factory()->create(['role' => UserRole::Medewerker]);
        $customer = Customer::factory()->create();
        $archivedCustomer = Customer::factory()->create();
        $archivedCustomer->delete();

        $this->actingAs($user)
            ->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee($customer->name)
            ->assertDontSee('Klant wijzigen');

        $this->get(route('customers.edit', $customer))->assertForbidden();
        $this->put(route('customers.update', $customer), [])->assertForbidden();

        $this->get(route('customers.archived'))
            ->assertOk()
            ->assertDontSee('Dearchiveren');

        $this->delete(route('customers.destroy', $customer))->assertForbidden();
        $this->patch(route('customers.restore', $archivedCustomer))->assertForbidden();

        $this->assertFalse($customer->fresh()->trashed());
        $this->assertTrue($archivedCustomer->fresh()->trashed());
    }

    public function test_manager_can_archive_and_restore_customers(): void
    {
        $user = User::factory()->create(['role' => UserRole::Manager]);
        $customer = Customer::factory()->create();

        $this->actingAs($user)
            ->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee('Klant wijzigen');

        $this->get(route('customers.edit', $customer))->assertOk();

        $this->delete(route('customers.destroy', $customer))->assertRedirect(route('customers.index'));
        $this->assertTrue($customer->fresh()->trashed());

        $this->patch(route('customers.restore', $customer))->assertRedirect(route('customers.archived'));
        $this->assertFalse($customer->fresh()->trashed());
    }

    public function test_admin_can_open_customer_edit_form(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $customer = Customer::factory()->create();

        $this->actingAs($user)
            ->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee('Klant wijzigen');

        $this->get(route('customers.edit', $customer))->assertOk();
    }
}
