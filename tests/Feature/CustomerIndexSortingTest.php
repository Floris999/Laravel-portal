<?php

namespace Tests\Feature;

use App\Livewire\Customers\CustomerTable;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerIndexSortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_list_is_paginated_and_keeps_the_sort_order(): void
    {
        $this->actingAs(User::factory()->create());

        $customers = Customer::factory()->count(11)->create([
            'customer_since' => '2025-01-01',
        ]);

        Livewire::test(CustomerTable::class, ['sort' => 'asc'])
            ->assertSee($customers[0]->name)
            ->assertDontSee($customers[10]->name)
            ->call('nextPage')
            ->assertSee($customers[10]->name)
            ->assertSet('sort', 'asc');
    }

    public function test_customer_list_is_sorted_by_id_newest_first_by_default_and_can_reverse(): void
    {
        $customers = Customer::factory()->count(3)->create();

        Livewire::test(CustomerTable::class)
            ->assertSeeInOrder([
                (string) $customers[2]->id,
                (string) $customers[1]->id,
                (string) $customers[0]->id,
            ])
            ->assertSet('sort', 'desc')
            ->call('toggleSort')
            ->assertSeeInOrder([
                (string) $customers[0]->id,
                (string) $customers[1]->id,
                (string) $customers[2]->id,
            ])
            ->assertSet('sort', 'asc');
    }

    public function test_customer_list_can_be_searched_by_name_or_email(): void
    {
        $this->actingAs(User::factory()->create());
        Customer::factory()->create([
            'name' => 'Fresh Foods',
            'email' => 'billing@fresh.test',
            'administration_email' => 'admin@fresh.test',
        ]);
        Customer::factory()->create([
            'name' => 'Other Company',
            'email' => 'billing@other.test',
            'administration_email' => 'admin@other.test',
        ]);

        Livewire::test(CustomerTable::class)
            ->set('search', 'Fresh')
            ->assertSee('Fresh Foods')
            ->assertSee('Kopiëren')
            ->assertSee('aria-label="Actie"', false)
            ->assertDontSee('Other Company')
            ->assertSet('search', 'Fresh');

        Livewire::test(CustomerTable::class)
            ->set('search', 'admin@other.test')
            ->assertSee('Other Company')
            ->assertDontSee('Fresh Foods');
    }

    public function test_customer_list_can_be_searched_by_city(): void
    {
        $this->actingAs(User::factory()->create());
        Customer::factory()->create([
            'name' => 'Amsterdam Company',
            'city' => 'Amsterdam',
        ]);
        Customer::factory()->create([
            'name' => 'Utrecht Company',
            'city' => 'Utrecht',
        ]);

        Livewire::test(CustomerTable::class)
            ->set('search', 'Amsterdam')
            ->assertSee('Amsterdam Company')
            ->assertDontSee('Utrecht Company');
    }
}
