<?php

namespace App\Http\Controllers;

use App\Models\DatabaseView;
use App\Models\NotionDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DatabaseViewController extends Controller
{
    /**
     * إنشاء عرض جديد لقاعدة البيانات (جدول، لوحة، قائمة، معرض)
     */
    public function store(Request $request, NotionDatabase $database): JsonResponse
    {
        Gate::authorize('update', $database->page);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:table,board,list,gallery',
            'filter_config' => 'nullable|array',
            'sort_config' => 'nullable|array',
            'visible_properties' => 'nullable|array',
        ]);

        $nextPosition = ($database->views()->max('position') ?? 0) + 1;

        $view = $database->views()->create([
            'name' => $data['name'],
            'type' => $data['type'],
            'filter_config' => $data['filter_config'] ?? null,
            'sort_config' => $data['sort_config'] ?? null,
            'visible_properties' => $data['visible_properties'] ?? null,
            'position' => $nextPosition,
        ]);

        return response()->json([
            'message' => 'تم إنشاء العرض بنجاح',
            'view' => $view,
        ], 201);
    }

    /**
     * تحديث إعدادات العرض أو الفلترة أو الترتيب (PATCH / PUT)
     */
    public function update(Request $request, DatabaseView $view): JsonResponse
    {
        Gate::authorize('update', $view->database->page);

        $data = $request->validate([
            'name' => 'sometimes|string|max:100',
            'type' => 'sometimes|in:table,board,list,gallery',
            'filter_config' => 'nullable|array',
            'sort_config' => 'nullable|array',
            'visible_properties' => 'nullable|array',
            'position' => 'sometimes|integer',
        ]);

        $view->update($data);

        return response()->json([
            'message' => 'تم تحديث العرض بنجاح',
            'view' => $view,
        ]);
    }

    /**
     * حذف عرض محفوظ
     */
    public function destroy(DatabaseView $view): JsonResponse
    {
        Gate::authorize('update', $view->database->page);

        $view->delete();

        return response()->json([
            'message' => 'تم حذف العرض بنجاح',
        ]);
    }
}
