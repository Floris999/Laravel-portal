<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'pages::settings.profile')->name('profile.edit');
    Route::livewire('settings/users', 'pages::settings.users')
        ->middleware('can:manage-users')
        ->name('users.manage');
    Route::livewire('settings/products', 'pages::settings.products')
        ->middleware('can:manage-users')
        ->name('products.manage');
    Route::livewire('settings/moneybird', 'pages::settings.moneybird')
        ->middleware('can:manage-users')
        ->name('moneybird.edit');
    Route::livewire('settings/categories', 'pages::settings.categories')
        ->middleware('can:manage-users')
        ->name('categories.manage');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('settings/appearance', 'pages::settings.appearance')->name('appearance.edit');

    Route::livewire('settings/security', 'pages::settings.security')
        ->middleware([
            'password.confirm',
        ])
        ->name('security.edit');
});

Route::get('.well-known/passkey-endpoints', function () {
    return response()->json([
        'enroll' => route('security.edit'),
        'manage' => route('security.edit'),
    ]);
})->name('well-known.passkeys');
