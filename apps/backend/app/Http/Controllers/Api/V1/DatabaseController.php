<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\DatabaseResource;
use App\Http\Resources\Api\V1\DatabaseRowResource;
use App\Models\DatabaseRow;
use App\Models\NotionDatabase;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DatabaseController extends ApiController
{
    /**
     * إنشاء قاعدة بيانات جديدة مدمجة بصفحة
     */
    public function store(Request $request, Page $page): JsonResponse
    {
        Gate::authorize('update', $page);

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'properties' => ['nullable', 'array'],
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

        return $this->successResponse(
            new DatabaseResource($database->load(['rows', 'views'])),
            'تم إنشاء قاعدة البيانات بنجاح.',
            201
        );
    }

    /**
     * تحديث اسم أو خصائص قاعدة البيانات
     */
    public function update(Request $request, NotionDatabase $database): JsonResponse
    {
        Gate::authorize('update', $database->page);

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'properties' => ['nullable', 'array'],
        ]);

        $database->update(array_filter($data, fn ($v) => $v !== null));

        return $this->successResponse(
            new DatabaseResource($database->fresh(['rows', 'views'])),
            'تم تحديث قاعدة البيانات بنجاح.'
        );
    }

    /**
     * حذف قاعدة البيانات بالكامل
     */
    public function destroy(NotionDatabase $database): JsonResponse
    {
        Gate::authorize('update', $database->page);

        $database->delete();

        return $this->successResponse(null, 'تم حذف قاعدة البيانات بنجاح.');
    }

    /**
     * إضافة سجل جديد في قاعدة البيانات
     */
    public function addRow(Request $request, NotionDatabase $database): JsonResponse
    {
        Gate::authorize('update', $database->page);

        $nextPosition = ($database->rows()->max('position') ?? 0) + 1;

        $row = $database->rows()->create([
            'values' => $request->input('values', []),
            'position' => $nextPosition,
        ]);

        return $this->successResponse(
            new DatabaseRowResource($row),
            'تمت إضافة السجل بنجاح.',
            201
        );
    }

    /**
     * تحديث قيم سجل
     */
    public function updateRow(Request $request, DatabaseRow $row): JsonResponse
    {
        Gate::authorize('update', $row->notionDatabase->page);

        $row->update([
            'values' => $request->input('values', []),
        ]);

        return $this->successResponse(
            new DatabaseRowResource($row),
            'تم تحديث السجل بنجاح.'
        );
    }

    /**
     * حذف سجل
     */
    public function deleteRow(DatabaseRow $row): JsonResponse
    {
        Gate::authorize('update', $row->notionDatabase->page);

        $row->delete();

        return $this->successResponse(null, 'تم حذف السجل بنجاح.');
    }
}
