<?php

use App\Models\Block;
use App\Models\BlockComment;
use App\Models\DatabaseView;
use App\Models\NotionDatabase;
use App\Models\Page;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('user can create, update, and delete database views', function () {
    $user = User::factory()->create();
    $workspace = Workspace::create([
        'name' => 'Tech Space',
        'slug' => 'tech-space-1',
        'user_id' => $user->id,
    ]);

    $page = Page::create([
        'workspace_id' => $workspace->id,
        'title' => 'Sprint 1',
        'created_by' => $user->id,
    ]);

    $database = NotionDatabase::create([
        'page_id' => $page->id,
        'name' => 'Tasks DB',
    ]);

    // 1. Create View
    $response = $this->actingAs($user)->postJson(route('databases.views.store', $database), [
        'name' => 'Kanban Sprint',
        'type' => 'board',
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('view.name', 'Kanban Sprint');
    $response->assertJsonPath('view.type', 'board');

    $viewId = $response->json('view.id');
    $view = DatabaseView::find($viewId);

    // 2. Update View
    $updateRes = $this->actingAs($user)->patchJson(route('databases.views.update', $view), [
        'name' => 'Kanban Sprint Active',
    ]);

    $updateRes->assertStatus(200);
    $this->assertDatabaseHas('database_views', [
        'id' => $viewId,
        'name' => 'Kanban Sprint Active',
    ]);

    // 3. Delete View
    $deleteRes = $this->actingAs($user)->deleteJson(route('databases.views.destroy', $view));
    $deleteRes->assertStatus(200);
    $this->assertDatabaseMissing('database_views', [
        'id' => $viewId,
    ]);
});

test('user can add comments, reply, and resolve comments on blocks', function () {
    $user = User::factory()->create();
    $workspace = Workspace::create([
        'name' => 'Dev Workspace',
        'slug' => 'dev-ws-1',
        'user_id' => $user->id,
    ]);

    $page = Page::create([
        'workspace_id' => $workspace->id,
        'title' => 'Project Scope',
        'created_by' => $user->id,
    ]);

    $block = Block::create([
        'page_id' => $page->id,
        'type' => 'paragraph',
        'content' => ['text' => 'Architecture overview'],
        'position' => 1,
    ]);

    // 1. Add Comment
    $res = $this->actingAs($user)->postJson(route('blocks.comments.store', $block), [
        'content' => 'Please review this section.',
    ]);

    $res->assertStatus(201);
    $commentId = $res->json('comment.id');

    // 2. Reply to Comment
    $replyRes = $this->actingAs($user)->postJson(route('blocks.comments.store', $block), [
        'content' => 'Looks great!',
        'parent_id' => $commentId,
    ]);

    $replyRes->assertStatus(201);
    $this->assertDatabaseHas('block_comments', [
        'parent_id' => $commentId,
        'content' => 'Looks great!',
    ]);

    // 3. Resolve Comment
    $comment = BlockComment::find($commentId);
    $resolveRes = $this->actingAs($user)->patchJson(route('comments.resolve', $comment));
    $resolveRes->assertStatus(200);
    $this->assertTrue((bool) BlockComment::find($commentId)->is_resolved);
});

test('page permissions enforce rbac role restriction', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $workspace = Workspace::create([
        'name' => 'Team Workspace',
        'slug' => 'team-ws-2',
        'user_id' => $owner->id,
    ]);
    $workspace->members()->attach($member->id, ['role' => 'member']);

    $page = Page::create([
        'workspace_id' => $workspace->id,
        'title' => 'Confidential Roadmap',
        'created_by' => $owner->id,
    ]);

    // Give member explicit "viewer" permission
    $res = $this->actingAs($owner)->postJson(route('pages.permissions.store', $page), [
        'email' => $member->email,
        'role' => 'viewer',
    ]);

    $res->assertStatus(200);
    $this->assertDatabaseHas('page_permissions', [
        'page_id' => $page->id,
        'user_id' => $member->id,
        'role' => 'viewer',
    ]);

    // Viewer can view page
    $viewRes = $this->actingAs($member)->get(route('pages.show', $page));
    $viewRes->assertStatus(200);
});

test('search endpoint returns quick action commands for command palette', function () {
    $user = User::factory()->create();

    $res = $this->actingAs($user)->getJson(route('search', ['q' => '']));
    $res->assertStatus(200);
    $res->assertJsonFragment([
        'title' => 'إنشاء صفحة جديدة',
        'type' => 'action',
    ]);
});
