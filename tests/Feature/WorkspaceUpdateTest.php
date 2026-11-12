<?php

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('workspace owner can update workspace name', function () {
    $owner = User::factory()->create();
    $workspace = Workspace::create([
        'name' => 'Old Name',
        'slug' => 'old-name-12345',
        'user_id' => $owner->id,
    ]);

    $response = $this->actingAs($owner)
        ->from(route('workspaces.show', $workspace))
        ->put(route('workspaces.update', $workspace), [
            'name' => 'New Workspace Name',
        ]);

    $response->assertRedirect(route('workspaces.show', $workspace));
    $this->assertDatabaseHas('workspaces', [
        'id' => $workspace->id,
        'name' => 'New Workspace Name',
    ]);
});

test('non-admin member cannot update workspace name', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $workspace = Workspace::create([
        'name' => 'Team Space',
        'slug' => 'team-space-12345',
        'user_id' => $owner->id,
    ]);
    $workspace->members()->attach($member->id, ['role' => 'member']);

    $response = $this->actingAs($member)
        ->put(route('workspaces.update', $workspace), [
            'name' => 'Hacked Name',
        ]);

    $response->assertForbidden();
    $this->assertDatabaseHas('workspaces', [
        'id' => $workspace->id,
        'name' => 'Team Space',
    ]);
});
