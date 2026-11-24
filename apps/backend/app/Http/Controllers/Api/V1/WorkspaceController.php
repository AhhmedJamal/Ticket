<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Workspace\StoreWorkspaceRequest;
use App\Http\Requests\Api\V1\Workspace\UpdateWorkspaceRequest;
use App\Http\Resources\Api\V1\WorkspaceResource;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkspaceController extends ApiController
{
    /**
     * جلب مساحات العمل الخاصة بالمستخدم
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $workspaces = $user->workspaces()
            ->with(['owner', 'members'])
            ->get()
            ->merge($user->ownedWorkspaces()->with(['owner', 'members'])->get())
            ->unique('id')
            ->values();

        return $this->successResponse(
            WorkspaceResource::collection($workspaces),
            'تم جلب مساحات العمل بنجاح.'
        );
    }

    /**
     * إنشاء مساحة عمل جديدة
     */
    public function store(StoreWorkspaceRequest $request): JsonResponse
    {
        $user = $request->user();
        $name = $request->validated('name');

        $workspace = Workspace::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(5),
            'user_id' => $user->id,
        ]);

        $workspace->members()->attach($user->id, ['role' => 'admin']);

        return $this->successResponse(
            new WorkspaceResource($workspace->load(['owner', 'members'])),
            'تم إنشاء مساحة العمل بنجاح.',
            201
        );
    }

    /**
     * عرض مساحة عمل معينة مع صفحاتها الجذرية وأعضائها
     */
    public function show(Request $request, Workspace $workspace): JsonResponse
    {
        $user = $request->user();

        $isMember = $workspace->user_id === $user->id || $workspace->members()->where('users.id', $user->id)->exists();

        if (! $isMember) {
            return $this->errorResponse('غير مخول بالوصول لمساحة العمل هذه.', 403);
        }

        $workspace->load([
            'pages' => function ($query) {
                $query->whereNull('parent_id')
                    ->with(['children' => fn ($q) => $q->with('children')])
                    ->orderBy('id', 'asc');
            },
            'members',
            'owner',
            'tags',
        ]);

        return $this->successResponse(
            new WorkspaceResource($workspace),
            'تم جلب بيانات مساحة العمل بنجاح.'
        );
    }

    /**
     * تحديث بيانات مساحة العمل (مشرف أو مالك)
     */
    public function update(UpdateWorkspaceRequest $request, Workspace $workspace): JsonResponse
    {
        $user = $request->user();

        $isAdmin = $workspace->user_id === $user->id
            || $workspace->members()->where('users.id', $user->id)->wherePivot('role', 'admin')->exists();

        if (! $isAdmin) {
            return $this->errorResponse('غير مخول بتعديل مساحة العمل هذه.', 403);
        }

        $workspace->update([
            'name' => $request->validated('name'),
        ]);

        return $this->successResponse(
            new WorkspaceResource($workspace->fresh(['owner', 'members'])),
            'تم تحديث مساحة العمل بنجاح.'
        );
    }
}
