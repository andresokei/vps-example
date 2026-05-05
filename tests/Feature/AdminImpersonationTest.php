<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('only admins can view the admin users panel', function () {
    Role::findOrCreate('admin', 'web');

    $user = User::factory()->create();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee($user->email);
});

test('an admin can view the application as a professor and return to admin', function () {
    Role::findOrCreate('admin', 'web');
    Role::findOrCreate('profesor', 'web');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $profesor = User::factory()->create();
    $profesor->assignRole('profesor');

    $this->actingAs($admin)
        ->post(route('admin.users.impersonate', $profesor))
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($profesor);
    expect(session('impersonator_id'))->toBe($admin->id);

    $this->post(route('admin.impersonation.stop'))
        ->assertRedirect(route('admin.users.index'));

    $this->assertAuthenticatedAs($admin);
    expect(session('impersonator_id'))->toBeNull();
});

test('an admin cannot impersonate another admin', function () {
    Role::findOrCreate('admin', 'web');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $otherAdmin = User::factory()->create();
    $otherAdmin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.users.impersonate', $otherAdmin))
        ->assertForbidden();
});

test('impersonation is read only except returning to admin', function () {
    Role::findOrCreate('admin', 'web');
    Role::findOrCreate('profesor', 'web');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $profesor = User::factory()->create();
    $profesor->assignRole('profesor');

    $this->actingAs($admin)
        ->post(route('admin.users.impersonate', $profesor));

    $this->get(route('dashboard'))->assertOk();

    $this->post(route('logout'))->assertForbidden();

    $this->post(route('admin.impersonation.stop'))
        ->assertRedirect(route('admin.users.index'));

    $this->assertAuthenticatedAs($admin);
});
