<?php

namespace Tests\Feature\Settings;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class MoneybirdStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.moneybird.api_token' => 'token',
            'services.moneybird.administration_id' => '123',
            'services.moneybird.base_url' => 'https://moneybird.test/api/v2',
        ]);
    }

    public function test_admin_sees_connected_status(): void
    {
        Http::fake(['moneybird.test/*' => Http::response([])]);

        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));

        $this->get(route('moneybird.edit'))->assertOk();

        Livewire::test('pages::settings.moneybird')
            ->assertSet('connected', true)
            ->assertSee('Verbonden');
    }

    public function test_invalid_token_is_reported(): void
    {
        Http::fake(['moneybird.test/*' => Http::response([], 401)]);

        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));

        Livewire::test('pages::settings.moneybird')
            ->assertSet('connected', false)
            ->assertSee('Het API-token is ongeldig of verlopen.');
    }

    public function test_missing_configuration_is_reported_without_calling_moneybird(): void
    {
        Http::fake();
        config(['services.moneybird.api_token' => null]);

        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));

        Livewire::test('pages::settings.moneybird')
            ->assertSet('connected', false)
            ->assertSee('API-token of administratienummer is niet ingesteld.');

        Http::assertNothingSent();
    }

    public function test_non_admin_cannot_view_moneybird_settings(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Medewerker]));

        $this->get(route('moneybird.edit'))->assertForbidden();
    }
}
