<?php

use App\Models\Grupo;
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

test('only admins can view the admin overview dashboard', function () {
    Role::findOrCreate('admin', 'web');

    $user = User::factory()->create();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(__('Admin overview'))
        ->assertSee(__('Users'))
        ->assertSee(__('Assignments'));
});

test('admin root no longer redirects to users list', function () {
    Role::findOrCreate('admin', 'web');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSee(__('Recent users'));
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

test('admin can delete a user without activity', function () {
    Role::findOrCreate('admin', 'web');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $bot = User::factory()->create(['email' => 'bot@example.test']);

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $bot))
        ->assertRedirect();

    $this->assertDatabaseMissing('users', ['email' => 'bot@example.test']);
});

test('admin cannot delete self, admins or users with activity', function () {
    Role::findOrCreate('admin', 'web');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $otherAdmin = User::factory()->create();
    $otherAdmin->assignRole('admin');

    $activeUser = User::factory()->create();
    Grupo::create([
        'nombre_grupo' => 'Grupo activo',
        'id_profesor' => $activeUser->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $admin))
        ->assertForbidden();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $otherAdmin))
        ->assertForbidden();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $activeUser))
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->assertDatabaseHas('users', ['id' => $activeUser->id]);
});
