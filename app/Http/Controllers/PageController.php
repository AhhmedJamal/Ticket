<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Workspace;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * إنشاء صفحة جديدة (رئيسية أو فرعية)
     */
    public function store(Request $request, Workspace $workspace)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:pages,id',
            'icon' => 'nullable|string|max:20',
        ]);

        $page = Page::create([
            'workspace_id' => $workspace->id,
            'parent_id' => $request->parent_id,
            'title' => $request->title ?: 'بدون عنوان',
            'icon' => $request->icon ?: '📄',
            'created_by' => $request->user()->id,
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
    public function show(Request $request, Page $page)
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
                    ->with('children')
                    ->orderBy('id', 'asc');
            },
        ]);

        // تحميل البلوكات مرتبة حسب الموقع، والصفحات الفرعية
        $page->load([
            'blocks' => fn ($q) => $q->orderBy('position', 'asc'),
            'children',
            'creator',
            'parent',
        ]);

        if ($request->wantsJson()) {
            return response()->json($page);
        }

        return view('pages.show', compact('page', 'workspace'));
    }

    /**
     * تحديث عنوان أو أيقونة الصفحة
     */
    public function update(Request $request, Page $page)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:20',
        ]);

        $page->update([
            'title' => $request->title ?: 'بدون عنوان',
            'icon' => $request->icon ?: $page->icon,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Page updated successfully',
                'page' => $page,
            ]);
        }

        return redirect()->back();
    }

    /**
     * حذف صفحة
     */
    public function destroy(Request $request, Page $page)
    {
        $workspace = $page->workspace;
        $page->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Page deleted successfully',
            ]);
        }

        return redirect()->route('workspaces.show', $workspace);
    }
}
