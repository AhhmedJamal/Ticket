<?php

namespace App\Http\Controllers;

use App\Models\DatabaseRow;
use App\Models\NotionDatabase;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DatabaseController extends Controller
{
    /**
     * إنشاء قاعدة بيانات جديدة مرتبطة بصفحة
     */
    public function store(Request $request, Page $page): JsonResponse
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'properties' => 'nullable|array',
        ]);

        $database = NotionDatabase::create([
            'page_id' => $page->id,
            'name' => $data['name'] ?? 'قاعدة بيانات جديدة',
            'properties' => $data['properties'] ?? [
                ['name' => 'الاسم', 'type' => 'text'],
                ['name' => 'الحالة', 'type' => 'select', 'options' => ['لم يبدأ', 'جاري', 'مكتمل']],
                ['name' => 'التاريخ', 'type' => 'date'],
            ],
        ]);

        return response()->json(['database' => $database->load('rows')], 201);
    }

    /**
     * تحديث تعريف الأعمدة أو اسم القاعدة
     */
    public function update(Request $request, NotionDatabase $database): JsonResponse
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'properties' => 'nullable|array',
        ]);

        $database->update(array_filter($data, fn ($v) => $v !== null));

        return response()->json(['database' => $database]);
    }

    /**
     * إضافة صف جديد
     */
    public function addRow(Request $request, NotionDatabase $database): JsonResponse
    {
        $nextPosition = ($database->rows()->max('position') ?? 0) + 1;

        $row = $database->rows()->create([
            'values' => $request->input('values', []),
            'position' => $nextPosition,
        ]);

        return response()->json(['row' => $row], 201);
    }

    /**
     * تحديث قيم صف
     */
    public function updateRow(Request $request, DatabaseRow $row): JsonResponse
    {
        $row->update(['values' => $request->input('values', [])]);

        return response()->json(['row' => $row]);
    }

    /**
     * حذف صف
     */
    public function deleteRow(DatabaseRow $row): JsonResponse
    {
        $row->delete();

        return response()->json(['message' => 'تم حذف السجل']);
    }

    /**
     * حذف قاعدة بيانات كاملة
     */
    public function destroy(NotionDatabase $database): JsonResponse
    {
        $database->delete();

        return response()->json(['message' => 'تم حذف القاعدة']);
    }
}
