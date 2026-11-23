<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * البحث الشامل في عناوين الصفحات ومحتوى البلوكات بالإضافة للأوامر السريعة (Command Palette)
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->input('q', ''));
        $user = $request->user();

        // 1. الأوامر السريعة (Quick Commands)
        $commands = collect([
            [
                'type' => 'action',
                'id' => 'create_page',
                'title' => 'إنشاء صفحة جديدة',
                'icon' => '➕',
                'category' => 'إجراءات سريعة',
                'action' => 'create_page',
                'match' => 'command',
            ],
            [
                'type' => 'action',
                'id' => 'toggle_dark',
                'title' => 'تبديل المظهر الداكن / الفاتح',
                'icon' => '🌓',
                'category' => 'إجراءات سريعة',
                'action' => 'toggle_theme',
                'match' => 'command',
            ],
            [
                'type' => 'action',
                'id' => 'browse_templates',
                'title' => 'استعراض مكتبة القوالب',
                'icon' => '📑',
                'category' => 'إجراءات سريعة',
                'action' => 'open_templates',
                'match' => 'command',
            ],
            [
                'type' => 'action',
                'id' => 'export_markdown',
                'title' => 'تصدير الصفحة الحالية كـ Markdown',
                'icon' => '📥',
                'category' => 'إجراءات سريعة',
                'action' => 'export_markdown',
                'match' => 'command',
            ],
        ]);

        $filteredCommands = $commands->filter(function ($cmd) use ($query) {
            if (empty($query)) {
                return true;
            }

            return mb_stripos($cmd['title'], $query) !== false || mb_stripos($cmd['id'], $query) !== false;
        })->values();

        if (strlen($query) < 2) {
            return response()->json([
                'results' => $filteredCommands,
                'commands' => $filteredCommands,
            ]);
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
                'category' => 'صفحات',
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
                'category' => 'محتوى البلوكات',
                'excerpt' => mb_substr($block->content['text'] ?? '', 0, 80),
            ]);

        $results = $filteredCommands->merge($pageResults)->merge($blockResults)->values();

        return response()->json([
            'results' => $results,
            'commands' => $filteredCommands,
        ]);
    }
}
