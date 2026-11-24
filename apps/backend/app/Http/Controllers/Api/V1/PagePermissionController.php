<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\PagePermissionResource;
use App\Models\Page;
use App\Models\PagePermission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PagePermissionController extends ApiController
{
    /**
     * جلب قائمة صلاحيات الصفحة المخصصة
     */
    public function index(Request $request, Page $page): JsonResponse
    {
        Gate::authorize('view', $page);

        $permissions = $page->permissions()->with('user')->get();

        return $this->successResponse([
            'permissions' => PagePermissionResource::collection($permissions),
            'current_user_role' => $page->getUserRole($request->user()),
        ], 'تم جلب صلاحيات الصفحة بنجاح.');
    }

    /**
     * تعيين أو تحديث دور مستخدم في الصفحة
     */
    public function store(Request $request, Page $page): JsonResponse
    {
        Gate::authorize('managePermissions', $page);

        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['required', 'in:editor,commenter,viewer'],
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        // لا يمكن تغيير دور مالك مساحة العمل
        if ($page->workspace && $page->workspace->user_id === $user->id) {
            return $this->errorResponse('مالك مساحة العمل يمتلك صلاحية تحرير كاملة دائماً.', 422);
        }

        $permission = PagePermission::updateOrCreate(
            ['page_id' => $page->id, 'user_id' => $user->id],
            ['role' => $data['role']]
        );

        return $this->successResponse(
            new PagePermissionResource($permission->load('user')),
            "تم تعيين صلاحية {$data['role']} للمستخدم {$user->name} بنجاح."
        );
    }

    /**
     * إزالة صلاحية مخصصة لمستخدم
     */
    public function destroy(Page $page, User $user): JsonResponse
    {
        Gate::authorize('managePermissions', $page);

        $page->permissions()->where('user_id', $user->id)->delete();

        return $this->successResponse(null, 'تم حذف الصلاحية المخصصة للمستخدم بنجاح.');
    }
}
