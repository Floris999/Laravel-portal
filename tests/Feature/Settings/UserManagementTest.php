<?php

namespace Tests\Feature\Settings;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_and_update_a_users_role(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $user = User::factory()->create(['role' => UserRole::Medewerker]);

        $this->actingAs($admin);

        $this->get(route('users.manage'))->assertOk();

        Livewire::test('pages::settings.users')
            ->set('roles.'.$user->id, UserRole::Manager->value)
            ->call('updateRole', $user->id)
            ->assertHasNoErrors()
            ->assertSet('showSavedDialog', true)
            ->assertSet('savedMessage', "De rol van {$user->name} is gewijzigd naar Manager.")
            ->assertSee('Wijzigingen opgeslagen')
            ->assertSee("De rol van {$user->name} is gewijzigd naar Manager.");

        $this->assertSame(UserRole::Manager, $user->refresh()->role);
    }

    public function test_non_admin_cannot_view_user_management(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Manager]));

        $this->get(route('users.manage'))->assertForbidden();
    }

    public function test_admin_cannot_change_their_own_role(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin);

        Livewire::test('pages::settings.users')
            ->set('roles.'.$admin->id, UserRole::Medewerker->value)
            ->call('updateRole', $admin->id)
            ->assertHasErrors(['roles.'.$admin->id]);

        $this->assertSame(UserRole::Admin, $admin->refresh()->role);
    }

    public function test_admin_can_delete_another_users_account_with_their_password(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $user = User::factory()->create();

        $this->actingAs($admin);

        Livewire::test('pages::settings.users')
            ->call('confirmDelete', $user->id)
            ->assertSet('showDeleteDialog', true)
            ->assertSet('deletingUserName', $user->name)
            ->set('deletePassword', 'password')
            ->call('deleteUser')
            ->assertHasNoErrors()
            ->assertSet('showDeleteDialog', false)
            ->assertSet('showSavedDialog', true)
            ->assertSet('savedMessage', "Het account van {$user->name} is verwijderd.");

        $this->assertNull($user->fresh());
    }

    public function test_admin_must_confirm_account_deletion_with_their_own_password(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $user = User::factory()->create();

        $this->actingAs($admin);

        Livewire::test('pages::settings.users')
            ->call('confirmDelete', $user->id)
            ->set('deletePassword', 'wrong-password')
            ->call('deleteUser')
            ->assertHasErrors(['deletePassword']);

        $this->assertNotNull($user->fresh());
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin);

        Livewire::test('pages::settings.users')
            ->call('confirmDelete', $admin->id)
            ->assertHasErrors('deletePassword');

        $this->assertNotNull($admin->fresh());
    }
}
