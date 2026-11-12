<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Page;
use App\Models\PageActivity;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * إنشاء صفحة جديدة (رئيسية أو فرعية)
     */
    public function store(Request $request, Workspace $workspace): JsonResponse|RedirectResponse
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:pages,id',
            'icon' => 'nullable|string|max:20',
            'template_id' => 'nullable|exists:templates,id',
        ]);

        $page = Page::create([
            'workspace_id' => $workspace->id,
            'parent_id' => $request->input('parent_id'),
            'title' => $request->input('title') ?: 'بدون عنوان',
            'icon' => $request->input('icon') ?: '📄',
            'created_by' => $request->user()->id,
        ]);

        PageActivity::create([
            'page_id' => $page->id,
            'user_id' => $request->user()->id,
            'action' => 'created',
            'description' => 'تم إنشاء الصفحة',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Page created successfully',
                'page' => $page,
            ], 201);
        }

        return redirect()->route('pages.show', $page);
    }

    /**
     * عرض صفحة ومحتواها وبلوكاتها وشجرة مساحة العمل
     */
    public function show(Request $request, Page $page): JsonResponse|View
    {
        $workspace = $page->workspace;
        $user = $request->user();

        // التحقق من الصلاحيات
        if ($workspace->user_id !== $user->id && ! $workspace->members->contains($user->id)) {
            abort(403, 'غير مخول بالوصول لهذه الصفحة.');
        }

        // تحميل شجرة صفحات مساحة العمل بالكامل للقائمة الجانبية
        $workspace->load([
            'pages' => function ($query) {
                $query->whereNull('parent_id')
                    ->with(['children' => function ($q) {
                        $q->with('children');
                    }])
                    ->orderBy('id', 'asc');
            },
            'tags',
        ]);

        // تحميل البلوكات والعلاقات للصفحة
        $page->load([
            'blocks' => fn ($q) => $q->orderBy('position', 'asc'),
            'children',
            'creator',
            'parent',
            'tags',
            'databases.rows',
            'activities' => fn ($q) => $q->with('user')->latest()->limit(20),
        ]);

        // Backlinks: الصفحات التي تشير لهذه الصفحة في محتوى بلوكاتها
        $backlinks = Block::whereHas('page', fn ($q) => $q->where('workspace_id', $workspace->id))
            ->where('content->link_page_id', $page->id)
            ->with('page')
            ->get()
            ->pluck('page')
            ->unique('id')
            ->values();

        if ($request->wantsJson()) {
            return response()->json($page);
        }

        return view('pages.show', compact('page', 'workspace', 'backlinks'));
    }

    /**
     * تحديث عنوان أو أيقونة أو غلاف الصفحة
     */
    public function update(Request $request, Page $page): JsonResponse|RedirectResponse
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:20',
            'cover_image' => 'nullable|string|max:255',
            'cover_color' => 'nullable|string|max:50',
        ]);

        $changes = [];

        if ($request->has('title') && $request->input('title') !== $page->title) {
            $changes[] = 'updated_title';
        }

        if ($request->has('icon') && $request->input('icon') !== $page->icon) {
            $changes[] = 'updated_icon';
        }

        if ($request->has('cover_image') || $request->has('cover_color')) {
            $changes[] = 'changed_cover';
        }

        $page->update([
            'title' => $request->input('title') ?: ($page->title ?? 'بدون عنوان'),
            'icon' => $request->input('icon') ?: $page->icon,
            'cover_image' => $request->has('cover_image') ? $request->input('cover_image') : $page->cover_image,
            'cover_color' => $request->has('cover_color') ? $request->input('cover_color') : $page->cover_color,
        ]);

        // تسجيل النشاط
        foreach ($changes as $action) {
            PageActivity::create([
                'page_id' => $page->id,
                'user_id' => $request->user()->id,
                'action' => $action,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Page updated successfully',
                'page' => $page,
            ]);
        }

        return redirect()->back();
    }

    /**
     * تبديل حالة المفضلة (Favorite / Unfavorite)
     */
    public function toggleFavorite(Request $request, Page $page): JsonResponse
    {
        $page->update(['is_favorite' => ! $page->is_favorite]);

        PageActivity::create([
            'page_id' => $page->id,
            'user_id' => $request->user()->id,
            'action' => $page->is_favorite ? 'favorited' : 'unfavorited',
        ]);

        return response()->json([
            'is_favorite' => $page->is_favorite,
            'message' => $page->is_favorite ? 'تمت الإضافة للمفضلة' : 'تمت الإزالة من المفضلة',
        ]);
    }

    /**
     * حذف صفحة
     */
    public function destroy(Request $request, Page $page): JsonResponse|RedirectResponse
    {
        $workspace = $page->workspace;
        $page->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Page deleted successfully']);
        }

        return redirect()->route('workspaces.show', $workspace);
    }

    /**
     * تصدير الصفحة بصيغة Markdown
     */
    public function exportMarkdown(Page $page): Response
    {
        $workspace = $page->workspace;
        $user = request()->user();

        if ($workspace->user_id !== $user->id && ! $workspace->members->contains($user->id)) {
            abort(403);
        }

        $page->load(['blocks' => fn ($q) => $q->orderBy('position')]);

        $md = "# {$page->icon} {$page->title}\n\n";

        foreach ($page->blocks as $block) {
            $text = $block->content['text'] ?? '';

            $md .= match ($block->type) {
                'heading_1' => "# {$text}\n\n",
                'heading_2' => "## {$text}\n\n",
                'heading_3' => "### {$text}\n\n",
                'paragraph' => "{$text}\n\n",
                'quote' => "> {$text}\n\n",
                'callout' => "> 💡 {$text}\n\n",
                'bulleted_list' => "- {$text}\n",
                'numbered_list' => "1. {$text}\n",
                'todo' => ($block->content['checked'] ?? false) ? "- [x] {$text}\n" : "- [ ] {$text}\n",
                'code' => "```\n{$text}\n```\n\n",
                'divider' => "---\n\n",
                default => "{$text}\n\n",
            };
        }

        $filename = Str::slug($page->title).'.md';

        return response($md, 200, [
            'Content-Type' => 'text/markdown; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
