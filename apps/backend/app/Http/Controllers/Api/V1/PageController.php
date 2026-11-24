<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Page\StorePageRequest;
use App\Http\Requests\Api\V1\Page\UpdatePageRequest;
use App\Http\Resources\Api\V1\PageResource;
use App\Models\Block;
use App\Models\Page;
use App\Models\PageActivity;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class PageController extends ApiController
{
    /**
     * إنشاء صفحة جديدة (رئيسية أو فرعية)
     */
    public function store(StorePageRequest $request, Workspace $workspace): JsonResponse
    {
        $user = $request->user();

        // التأكد من عضوية المستخدم في مساحة العمل
        $isMember = $workspace->user_id === $user->id || $workspace->members()->where('users.id', $user->id)->exists();
        if (! $isMember) {
            return $this->errorResponse('غير مخول بإنشاء صفحات في هذه المساحة.', 403);
        }

        $page = Page::create([
            'workspace_id' => $workspace->id,
            'parent_id' => $request->input('parent_id'),
            'title' => $request->input('title') ?: 'بدون عنوان',
            'icon' => $request->input('icon') ?: '📄',
            'created_by' => $user->id,
        ]);

        PageActivity::create([
            'page_id' => $page->id,
            'user_id' => $user->id,
            'action' => 'created',
            'description' => 'تم إنشاء الصفحة',
        ]);

        return $this->successResponse(
            new PageResource($page->load(['creator', 'parent'])),
            'تم إنشاء الصفحة بنجاح.',
            201
        );
    }

    /**
     * عرض الصفحة وبلوكاتها وقواعد بياناتها وكافة تفاصيلها
     */
    public function show(Request $request, Page $page): JsonResponse
    {
        Gate::authorize('view', $page);

        $workspace = $page->workspace;

        $page->load([
            'blocks' => fn ($q) => $q->orderBy('position', 'asc')->with(['comments.user', 'comments.replies.user']),
            'children',
            'creator',
            'parent',
            'tags',
            'databases.rows',
            'databases.views',
            'permissions.user',
            'activities' => fn ($q) => $q->with('user')->latest()->limit(30),
        ]);

        // Backlinks: الصفحات التي ترتبط بهذه الصفحة
        $backlinks = Block::whereHas('page', fn ($q) => $q->where('workspace_id', $workspace->id))
            ->where('content->link_page_id', $page->id)
            ->with('page')
            ->get()
            ->pluck('page')
            ->filter()
            ->unique('id')
            ->values();

        $meta = [
            'backlinks' => PageResource::collection($backlinks),
            'user_role' => $page->getUserRole($request->user()),
        ];

        return $this->successResponse(
            new PageResource($page),
            'تم جلب بيانات الصفحة بنجاح.',
            200,
            $meta
        );
    }

    /**
     * تحديث عنوان أو أيقونة أو غلاف الصفحة
     */
    public function update(UpdatePageRequest $request, Page $page): JsonResponse
    {
        Gate::authorize('update', $page);

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
            'title' => $request->has('title') ? ($request->input('title') ?: 'بدون عنوان') : $page->title,
            'icon' => $request->has('icon') ? ($request->input('icon') ?: '📄') : $page->icon,
            'cover_image' => $request->has('cover_image') ? $request->input('cover_image') : $page->cover_image,
            'cover_color' => $request->has('cover_color') ? $request->input('cover_color') : $page->cover_color,
        ]);

        foreach ($changes as $action) {
            PageActivity::create([
                'page_id' => $page->id,
                'user_id' => $request->user()->id,
                'action' => $action,
            ]);
        }

        return $this->successResponse(
            new PageResource($page->fresh(['creator', 'parent', 'tags'])),
            'تم تحديث الصفحة بنجاح.'
        );
    }

    /**
     * تبديل حالة المفضلة
     */
    public function toggleFavorite(Request $request, Page $page): JsonResponse
    {
        Gate::authorize('view', $page);

        $page->update(['is_favorite' => ! $page->is_favorite]);

        PageActivity::create([
            'page_id' => $page->id,
            'user_id' => $request->user()->id,
            'action' => $page->is_favorite ? 'favorited' : 'unfavorited',
        ]);

        return $this->successResponse([
            'is_favorite' => (bool) $page->is_favorite,
        ], $page->is_favorite ? 'تمت الإضافة للمفضلة' : 'تمت الإزالة من المفضلة');
    }

    /**
     * حذف الصفحة
     */
    public function destroy(Request $request, Page $page): JsonResponse
    {
        Gate::authorize('delete', $page);

        $page->delete();

        return $this->successResponse(null, 'تم حذف الصفحة بنجاح.');
    }

    /**
     * تصدير الصفحة بصيغة Markdown
     */
    public function exportMarkdown(Request $request, Page $page): JsonResponse|Response
    {
        Gate::authorize('view', $page);

        $page->load(['blocks' => fn ($q) => $q->orderBy('position')]);

        $md = "# {$page->icon} {$page->title}\n\n";

        foreach ($page->blocks as $block) {
            $text = $block->content['text'] ?? '';

            $md .= match ($block->type) {
                'heading_1', 'heading' => "# {$text}\n\n",
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

        if ($request->wantsJson() && ! $request->has('download')) {
            return $this->successResponse([
                'markdown' => $md,
                'filename' => $filename,
            ], 'تم تصدير الصفحة بنجاح.');
        }

        return response($md, 200, [
            'Content-Type' => 'text/markdown; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
