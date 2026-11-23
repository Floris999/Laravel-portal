<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $this->actingAs($user = User::factory()->create());

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Instellingen')
            ->assertSee('Beheer je profiel- en accountinstellingen')
            ->assertSee('Naam')
            ->assertSee('E-mailadres')
            ->assertDontSee('Account verwijderen');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.profile')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->call('updateProfileInformation');

        $response->assertHasNoErrors()
            ->assertSet('showSavedDialog', true)
            ->assertSet('savedMessage', 'Je profiel is opgeslagen. Je naam is nu Test User.')
            ->assertSee('Wijzigingen opgeslagen')
            ->assertSee('Je profiel is opgeslagen. Je naam is nu Test User.');

        $user->refresh();

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.profile')
            ->set('name', 'Test User')
            ->set('email', $user->email)
            ->call('updateProfileInformation');

        $response->assertHasNoErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_updating_profile_does_not_change_two_factor_or_remember_token_data(): void
    {
        $user = User::factory()->withTwoFactor()->create([
            'remember_token' => 'existing-remember-token',
        ]);
        $twoFactorSecret = $user->two_factor_secret;
        $recoveryCodes = $user->two_factor_recovery_codes;
        $confirmedAt = $user->two_factor_confirmed_at;

        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->set('name', 'Updated User')
            ->set('email', $user->email)
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame($twoFactorSecret, $user->two_factor_secret);
        $this->assertSame($recoveryCodes, $user->two_factor_recovery_codes);
        $this->assertEquals($confirmedAt, $user->two_factor_confirmed_at);
        $this->assertSame('existing-remember-token', $user->remember_token);
    }
}
