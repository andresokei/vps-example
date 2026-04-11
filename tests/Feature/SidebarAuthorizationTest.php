<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('dashboard sidebar hides teacher tools for users without profesor or admin role', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertDontSee('href="'.route('analisis').'"', false);
    $response->assertDontSee('href="'.route('grupos.index').'"', false);
});

test('dashboard sidebar shows teacher tools for profesores', function () {
    Role::findOrCreate('profesor', 'web');

    $user = User::factory()->create();
    $user->assignRole('profesor');

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('href="'.route('analisis').'"', false);
    $response->assertSee('href="'.route('grupos.index').'"', false);
});
