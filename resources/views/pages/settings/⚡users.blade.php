<?php

use App\Enums\UserRole;
use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Gebruikersbeheer')] class extends Component {
    use PasswordValidationRules;

    public array $roles = [];
    public bool $showSavedDialog = false;
    public string $savedMessage = '';
    public bool $showDeleteDialog = false;
    public string $deletePassword = '';
    public bool $showCreateDialog = false;
    public string $newUserEmail = '';
    public string $newUserPassword = '';
    public string $newUserPasswordConfirmation = '';

    #[Locked]
    public ?int $deletingUserId = null;

    #[Locked]
    public string $deletingUserName = '';

    public function mount(): void
    {
        $this->authorize('manage-users');

        $this->roles = User::query()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (User $user): array => [$user->id => $user->role->value])
            ->all();
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function users(): Collection
    {
        return User::query()->orderBy('name')->get();
    }

    public function updateRole(int $userId): void
    {
        $this->authorize('manage-users');

        $validated = validator(
            ['role' => $this->roles[$userId] ?? null],
            ['role' => ['required', Rule::enum(UserRole::class)]],
        )->validate();

        if ($userId === auth()->id()) {
            throw ValidationException::withMessages([
                'roles.'.$userId => __('users.self_role'),
            ]);
        }

        $updatedUser = DB::transaction(function () use ($userId, $validated): User {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $newRole = UserRole::from($validated['role']);

            if ($user->role === UserRole::Admin && $newRole !== UserRole::Admin) {
                $adminCount = User::query()
                    ->where('role', UserRole::Admin->value)
                    ->lockForUpdate()
                    ->get()
                    ->count();

                if ($adminCount <= 1) {
                    throw ValidationException::withMessages([
                        'roles.'.$userId => __('users.last_admin'),
                    ]);
                }
            }

            $user->role = $newRole;
            $user->save();

            return $user;
        });

        $this->roles[$userId] = $validated['role'];
        $this->savedMessage = __('users.updated', [
            'name' => $updatedUser->name,
            'role' => $validated['role'],
        ]);
        $this->showSavedDialog = true;
    }

    public function confirmDelete(int $userId): void
    {
        $this->authorize('manage-users');

        if ($userId === auth()->id()) {
            throw ValidationException::withMessages([
                'deletePassword' => __('users.self_delete'),
            ]);
        }

        $user = User::query()->findOrFail($userId);

        $this->deletingUserId = $user->id;
        $this->deletingUserName = $user->name;
        $this->deletePassword = '';
        $this->resetValidation('deletePassword');
        $this->showDeleteDialog = true;
    }

    public function deleteUser(): void
    {
        $this->authorize('manage-users');

        $this->validate([
            'deletePassword' => $this->currentPasswordRules(),
        ]);

        if (! $this->deletingUserId) {
            throw ValidationException::withMessages([
                'deletePassword' => __('users.select_user_to_delete'),
            ]);
        }

        if ($this->deletingUserId === auth()->id()) {
            throw ValidationException::withMessages([
                'deletePassword' => __('users.self_delete'),
            ]);
        }

        $user = DB::transaction(function (): User {
            $user = User::query()->lockForUpdate()->findOrFail($this->deletingUserId);

            if ($user->role === UserRole::Admin) {
                $adminCount = User::query()
                    ->where('role', UserRole::Admin->value)
                    ->lockForUpdate()
                    ->get()
                    ->count();

                if ($adminCount <= 1) {
                    throw ValidationException::withMessages([
                        'deletePassword' => __('users.last_admin_delete'),
                    ]);
                }
            }

            $user->delete();

            return $user;
        });

        unset($this->roles[$user->id], $this->users);

        $this->deletePassword = '';
        $this->deletingUserId = null;
        $this->deletingUserName = '';
        $this->showDeleteDialog = false;
        $this->savedMessage = __('users.deleted', ['name' => $user->name]);
        $this->showSavedDialog = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteDialog = false;
        $this->deletePassword = '';
        $this->deletingUserId = null;
        $this->deletingUserName = '';
        $this->resetValidation('deletePassword');
    }

    public function openCreateDialog(): void
    {
        $this->authorize('manage-users');
        $this->newUserEmail = '';
        $this->newUserPassword = '';
        $this->newUserPasswordConfirmation = '';
        $this->resetValidation();
        $this->showCreateDialog = true;
    }

    public function createUser(): void
    {
        $this->authorize('manage-users');

        $validated = $this->validate([
            'newUserEmail' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'newUserPassword' => ['required', 'string', Password::default()],
            'newUserPasswordConfirmation' => ['required', 'same:newUserPassword'],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            return User::query()->create([
                'name' => explode('@', $validated['newUserEmail'])[0],
                'email' => $validated['newUserEmail'],
                'password' => bcrypt($validated['newUserPassword']),
                'role' => UserRole::Medewerker,
            ]);
        });

        $this->roles[$user->id] = $user->role->value;
        unset($this->users);

        $this->newUserEmail = '';
        $this->newUserPassword = '';
        $this->newUserPasswordConfirmation = '';
        $this->showCreateDialog = false;
        $this->savedMessage = __('users.created', ['name' => $user->name]);
        $this->showSavedDialog = true;
    }

    public function cancelCreate(): void
    {
        $this->showCreateDialog = false;
        $this->newUserEmail = '';
        $this->newUserPassword = '';
        $this->newUserPasswordConfirmation = '';
        $this->resetValidation();
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-pages::settings.layout :heading="__('users.heading')" :subheading="__('users.subheading')">
        <div class="my-6 w-full flex justify-end">
            <flux:button variant="primary" wire:click="openCreateDialog">
                {{ __('users.create_user_button') }}
            </flux:button>
        </div>

        <div class="my-6 w-full space-y-4">
            @foreach ($this->users as $user)
                <div class="flex min-w-0 flex-col gap-4 rounded-lg border border-gray-200 p-4">
                    <div class="min-w-0">
                        <flux:heading>{{ $user->name }}</flux:heading>
                        <flux:text class="break-all">{{ $user->email }}</flux:text>
                    </div>

                    <div class="flex min-w-0 flex-col gap-3">
                        <form wire:submit="updateRole({{ $user->id }})"
                            class="flex min-w-0 flex-col gap-2 sm:flex-row sm:items-end">
                            <flux:select wire:model="roles.{{ $user->id }}" :label="__('users.role')"
                                class="min-w-0 flex-1"
                                :disabled="$user->is(auth()->user())">
                                @foreach (UserRole::cases() as $role)
                                    <flux:select.option :value="$role->value">{{ $role->value }}</flux:select.option>
                                @endforeach
                            </flux:select>

                            @if ($user->is(auth()->user()))
                                <flux:button type="button" disabled>{{ __('users.current_user') }}</flux:button>
                            @else
                                <flux:button variant="primary" type="submit" class="w-full sm:w-auto">
                                    {{ __('settings.common.save') }}
                                </flux:button>
                            @endif
                        </form>

                        @unless ($user->is(auth()->user()))
                            <flux:button variant="danger" type="button" class="w-full sm:w-auto sm:self-end"
                                wire:click="confirmDelete({{ $user->id }})">
                                {{ __('users.delete') }}
                            </flux:button>
                        @endunless
                    </div>

                    <flux:error name="roles.{{ $user->id }}" />
                </div>
            @endforeach
        </div>
    </x-pages::settings.layout>

    <flux:modal name="user-role-saved" wire:model="showSavedDialog" class="max-w-sm">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('settings.common.saved_heading') }}</flux:heading>
                <flux:text>{{ $savedMessage }}</flux:text>
            </div>

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="primary">{{ __('settings.common.close') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="delete-user" wire:model="showDeleteDialog" class="max-w-md">
        <form wire:submit="deleteUser" class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('users.delete_confirmation_heading') }}</flux:heading>
                <flux:text>{{ __('users.delete_confirmation_description', ['name' => $deletingUserName]) }}</flux:text>
            </div>

            <flux:input wire:model="deletePassword" :label="__('settings.security.current_password')" type="password"
                autocomplete="current-password" required viewable />
            <flux:error name="deletePassword" />

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" type="button" wire:click="cancelDelete">
                    {{ __('settings.common.cancel') }}
                </flux:button>
                <flux:button variant="danger" type="submit">{{ __('users.confirm_delete') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="create-user" wire:model="showCreateDialog" class="max-w-md">
        <form wire:submit="createUser" class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('users.create_user_heading') }}</flux:heading>
            </div>

            <flux:input wire:model="newUserEmail" :label="__('users.create_user_email')" type="email"
                required placeholder="email@example.com" />
            <flux:error name="newUserEmail" />

            <flux:input wire:model="newUserPassword" :label="__('users.create_user_password')" type="password"
                required viewable
                :placeholder="__('users.create_user_password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}" />
            <flux:error name="newUserPassword" />

            <flux:input wire:model="newUserPasswordConfirmation" :label="__('users.create_user_password_confirm')" type="password"
                required viewable
                :placeholder="__('users.create_user_password_confirm')" />
            <flux:error name="newUserPasswordConfirmation" />

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" type="button" wire:click="cancelCreate">
                    {{ __('settings.common.cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">{{ __('users.create_user_button') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
