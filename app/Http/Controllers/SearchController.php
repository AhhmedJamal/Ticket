<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * البحث الشامل في عناوين الصفحات ومحتوى البلوكات
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->input('q', ''));
        $user = $request->user();

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        // جمع كل مساحات العمل التي يملكها أو ينتمي إليها المستخدم
        $workspaceIds = $user->ownedWorkspaces()->pluck('id')
            ->merge($user->workspaces()->pluck('workspaces.id'))
            ->unique();

        // البحث في عناوين الصفحات
        $pageResults = Page::whereIn('workspace_id', $workspaceIds)
            ->where('title', 'like', "%{$query}%")
            ->with('workspace')
            ->limit(8)
            ->get()
            ->map(fn (Page $page) => [
                'type' => 'page',
                'id' => $page->id,
                'title' => $page->title,
                'icon' => $page->icon ?? '📄',
                'workspace' => $page->workspace?->name,
                'url' => route('pages.show', $page),
                'match' => 'title',
            ]);

        // البحث في محتوى البلوكات
        $blockResults = Block::whereHas('page', function ($q) use ($workspaceIds) {
            $q->whereIn('workspace_id', $workspaceIds);
        })
            ->where('content->text', 'like', "%{$query}%")
            ->with('page.workspace')
            ->limit(5)
            ->get()
            ->map(fn (Block $block) => [
                'type' => 'block',
                'id' => $block->page_id,
                'title' => $block->page?->title ?? 'صفحة',
                'icon' => $block->page?->icon ?? '📄',
                'workspace' => $block->page?->workspace?->name,
                'url' => route('pages.show', $block->page_id),
                'match' => 'content',
                'excerpt' => mb_substr($block->content['text'] ?? '', 0, 80),
            ]);

        $results = $pageResults->merge($blockResults)->unique('url')->values();

        return response()->json(['results' => $results]);
    }
}
