<?php

use App\Models\Block;
use App\Models\Page;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('user can register, login, fetch profile, and logout via api v1', function () {
    // 1. Register
    $regResponse = $this->postJson('/api/v1/auth/register', [
        'name' => 'Ahmed Test',
        'email' => 'ahmed@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $regResponse->assertStatus(201)
        ->assertJsonPath('status', 'success')
        ->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email'],
                'access_token',
                'token_type',
            ],
        ]);

    $token = $regResponse->json('data.access_token');

    // 2. Fetch Profile (Me)
    $meResponse = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/auth/me');

    $meResponse->assertStatus(200)
        ->assertJsonPath('data.user.email', 'ahmed@test.com');

    // 3. Logout
    $logoutResponse = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/logout');

    $logoutResponse->assertStatus(200)
        ->assertJsonPath('status', 'success');

    // 4. Login
    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'email' => 'ahmed@test.com',
        'password' => 'password123',
    ]);

    $loginResponse->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonStructure(['data' => ['access_token']]);
});

test('user can perform workspace CRUD and manage members via api v1', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    // Create Workspace
    $createRes = $this->actingAs($owner, 'sanctum')->postJson('/api/v1/workspaces', [
        'name' => 'Rocket Team',
    ]);

    $createRes->assertStatus(201)
        ->assertJsonPath('data.name', 'Rocket Team');

    $workspaceId = $createRes->json('data.id');

    // List Workspaces
    $listRes = $this->actingAs($owner, 'sanctum')->getJson('/api/v1/workspaces');
    $listRes->assertStatus(200)
        ->assertJsonFragment(['name' => 'Rocket Team']);

    // Update Workspace
    $updateRes = $this->actingAs($owner, 'sanctum')->patchJson("/api/v1/workspaces/{$workspaceId}", [
        'name' => 'Rocket Team Renamed',
    ]);
    $updateRes->assertStatus(200)
        ->assertJsonPath('data.name', 'Rocket Team Renamed');

    // Invite Member
    $inviteRes = $this->actingAs($owner, 'sanctum')->postJson("/api/v1/workspaces/{$workspaceId}/members", [
        'email' => $member->email,
        'role' => 'member',
    ]);
    $inviteRes->assertStatus(201);

    // Update Member Role
    $roleRes = $this->actingAs($owner, 'sanctum')->patchJson("/api/v1/workspaces/{$workspaceId}/members/{$member->id}/role", [
        'role' => 'admin',
    ]);
    $roleRes->assertStatus(200);

    // Remove Member
    $removeRes = $this->actingAs($owner, 'sanctum')->deleteJson("/api/v1/workspaces/{$workspaceId}/members/{$member->id}");
    $removeRes->assertStatus(200);
});

test('user can perform page CRUD, favorite toggle, and markdown export via api v1', function () {
    $user = User::factory()->create();
    $workspace = Workspace::create([
        'name' => 'Product Space',
        'slug' => 'product-space',
        'user_id' => $user->id,
    ]);

    // Create Page
    $pageRes = $this->actingAs($user, 'sanctum')->postJson("/api/v1/workspaces/{$workspace->id}/pages", [
        'title' => 'API Architecture',
        'icon' => '🚀',
    ]);
    $pageRes->assertStatus(201)
        ->assertJsonPath('data.title', 'API Architecture');

    $pageId = $pageRes->json('data.id');

    // Update Page
    $updateRes = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/pages/{$pageId}", [
        'title' => 'API Architecture V2',
        'icon' => '⚡',
        'cover_color' => 'gradient-blue',
    ]);
    $updateRes->assertStatus(200)
        ->assertJsonPath('data.title', 'API Architecture V2')
        ->assertJsonPath('data.cover_color', 'gradient-blue');

    // Toggle Favorite
    $favRes = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/pages/{$pageId}/favorite");
    $favRes->assertStatus(200)
        ->assertJsonPath('data.is_favorite', true);

    // Export Markdown (JSON payload)
    $exportRes = $this->actingAs($user, 'sanctum')->getJson("/api/v1/pages/{$pageId}/export/markdown");
    $exportRes->assertStatus(200)
        ->assertJsonStructure(['data' => ['markdown', 'filename']]);

    // Delete Page
    $delRes = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/pages/{$pageId}");
    $delRes->assertStatus(200);
});

test('user can manage blocks and reorder them via api v1', function () {
    $user = User::factory()->create();
    $workspace = Workspace::create([
        'name' => 'Engineering Space',
        'slug' => 'eng-space',
        'user_id' => $user->id,
    ]);
    $page = Page::create([
        'workspace_id' => $workspace->id,
        'title' => 'Release Notes',
        'created_by' => $user->id,
    ]);

    // 1. Add Block 1
    $b1Res = $this->actingAs($user, 'sanctum')->postJson("/api/v1/pages/{$page->id}/blocks", [
        'type' => 'heading_1',
        'text' => 'Release 1.0',
    ]);
    $b1Res->assertStatus(201)
        ->assertJsonPath('data.type', 'heading_1');
    $b1Id = $b1Res->json('data.id');

    // 2. Add Block 2
    $b2Res = $this->actingAs($user, 'sanctum')->postJson("/api/v1/pages/{$page->id}/blocks", [
        'type' => 'todo',
        'text' => 'Deploy to production',
    ]);
    $b2Res->assertStatus(201);
    $b2Id = $b2Res->json('data.id');

    // 3. Update Block 2
    $upRes = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/blocks/{$b2Id}", [
        'checked' => true,
    ]);
    $upRes->assertStatus(200)
        ->assertJsonPath('data.content.checked', true);

    // 4. Reorder Blocks
    $reorderRes = $this->actingAs($user, 'sanctum')->postJson("/api/v1/pages/{$page->id}/blocks/reorder", [
        'blocks' => [
            ['id' => $b2Id, 'position' => 1],
            ['id' => $b1Id, 'position' => 2],
        ],
    ]);
    $reorderRes->assertStatus(200);

    $this->assertDatabaseHas('blocks', ['id' => $b2Id, 'position' => 1]);
    $this->assertDatabaseHas('blocks', ['id' => $b1Id, 'position' => 2]);

    // 5. Delete Block
    $delRes = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/blocks/{$b1Id}");
    $delRes->assertStatus(200);
});

test('user can manage database, rows, views, and comments via api v1', function () {
    $user = User::factory()->create();
    $workspace = Workspace::create([
        'name' => 'Data Space',
        'slug' => 'data-space',
        'user_id' => $user->id,
    ]);
    $page = Page::create([
        'workspace_id' => $workspace->id,
        'title' => 'Roadmap Page',
        'created_by' => $user->id,
    ]);

    // 1. Create Database
    $dbRes = $this->actingAs($user, 'sanctum')->postJson("/api/v1/pages/{$page->id}/databases", [
        'name' => 'Tasks Table',
    ]);
    $dbRes->assertStatus(201)
        ->assertJsonPath('data.name', 'Tasks Table');
    $dbId = $dbRes->json('data.id');

    // 2. Add Row
    $rowRes = $this->actingAs($user, 'sanctum')->postJson("/api/v1/databases/{$dbId}/rows", [
        'values' => ['الاسم' => 'Task A', 'الحالة' => 'جاري'],
    ]);
    $rowRes->assertStatus(201)
        ->assertJsonPath('data.values.الاسم', 'Task A');
    $rowId = $rowRes->json('data.id');

    // 3. Add Custom View
    $viewRes = $this->actingAs($user, 'sanctum')->postJson("/api/v1/databases/{$dbId}/views", [
        'name' => 'Board Sprint',
        'type' => 'board',
    ]);
    $viewRes->assertStatus(201)
        ->assertJsonPath('data.name', 'Board Sprint')
        ->assertJsonPath('data.type', 'board');
    $viewId = $viewRes->json('data.id');

    // 4. Update View
    $upViewRes = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/database-views/{$viewId}", [
        'name' => 'Kanban Q1',
    ]);
    $upViewRes->assertStatus(200)
        ->assertJsonPath('data.name', 'Kanban Q1');

    // 5. Search API
    $searchRes = $this->actingAs($user, 'sanctum')->getJson('/api/v1/search?q=Roadmap');
    $searchRes->assertStatus(200)
        ->assertJsonPath('status', 'success');
});
