<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Tag;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * إنشاء وسمة جديدة في مساحة العمل
     */
    public function store(Request $request, Workspace $workspace): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'required|string|in:gray,red,orange,yellow,green,blue,purple,pink',
        ]);

        $tag = $workspace->tags()->create($data);

        return response()->json(['tag' => $tag], 201);
    }

    /**
     * إضافة وسمة إلى صفحة (أو إنشاؤها إذا لم تكن موجودة)
     */
    public function attachToPage(Request $request, Page $page): JsonResponse
    {
        $data = $request->validate([
            'tag_id' => 'nullable|exists:tags,id',
            'tag_name' => 'nullable|string|max:50',
            'tag_color' => 'nullable|string|in:gray,red,orange,yellow,green,blue,purple,pink',
        ]);

        if (! empty($data['tag_id'])) {
            $tag = Tag::findOrFail($data['tag_id']);
        } else {
            $tag = $page->workspace->tags()->firstOrCreate(
                ['name' => $data['tag_name']],
                ['color' => $data['tag_color'] ?? 'gray']
            );
        }

        $page->tags()->syncWithoutDetaching([$tag->id]);

        return response()->json(['tag' => $tag, 'message' => 'تمت إضافة الوسمة']);
    }

    /**
     * إزالة وسمة من صفحة
     */
    public function detachFromPage(Request $request, Page $page, Tag $tag): JsonResponse
    {
        $page->tags()->detach($tag->id);

        return response()->json(['message' => 'تمت إزالة الوسمة']);
    }

    /**
     * حذف وسمة من مساحة العمل بالكامل
     */
    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json(['message' => 'تم حذف الوسمة']);
    }
}
