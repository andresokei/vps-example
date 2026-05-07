<?php

use App\Models\User;
use App\Models\UserFeedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('authenticated users can send feedback', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('feedback.store'), [
            'category' => 'idea',
            'rating' => 5,
            'subject' => 'Add a faster group overview',
            'message' => 'It would help me review class activity much faster.',
        ])
        ->assertRedirect(route('feedback.create'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('user_feedback', [
        'user_id' => $user->id,
        'category' => 'idea',
        'rating' => 5,
        'subject' => 'Add a faster group overview',
        'status' => UserFeedback::STATUS_NEW,
    ]);
});

test('only admins can view and update feedback', function () {
    Role::findOrCreate('admin', 'web');

    $user = User::factory()->create();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $feedback = UserFeedback::create([
        'user_id' => $user->id,
        'category' => 'bug',
        'subject' => 'Export does not load',
        'message' => 'The export button keeps loading when I click it.',
    ]);

    $this->actingAs($user)
        ->get(route('admin.feedback.index'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('admin.feedback.index'))
        ->assertOk()
        ->assertSee('Export does not load');

    $this->actingAs($admin)
        ->patch(route('admin.feedback.update', $feedback), [
            'status' => UserFeedback::STATUS_RESOLVED,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('user_feedback', [
        'id' => $feedback->id,
        'status' => UserFeedback::STATUS_RESOLVED,
        'reviewed_by' => $admin->id,
    ]);
});
