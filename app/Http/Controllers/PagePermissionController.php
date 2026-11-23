<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PagePermission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PagePermissionController extends Controller
{
    /**
     * جلب قائمة صلاحيات الصفحة المخصصة
     */
    public function index(Page $page): JsonResponse
    {
        Gate::authorize('view', $page);

        $permissions = $page->permissions()->with('user:id,name,email')->get();

        return response()->json([
            'permissions' => $permissions,
            'current_user_role' => $page->getUserRole(request()->user()),
        ]);
    }

    /**
     * تعيين أو تحديث دور مستخدم في الصفحة
     */
    public function store(Request $request, Page $page): JsonResponse
    {
        Gate::authorize('managePermissions', $page);

        $data = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:editor,commenter,viewer',
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        // لا يمكن تغيير دور مالك مساحة العمل
        if ($page->workspace && $page->workspace->user_id === $user->id) {
            return response()->json(['message' => 'مالك مساحة العمل يمتلك صلاحية تحرير كاملة دائماً'], 422);
        }

        $permission = PagePermission::updateOrCreate(
            ['page_id' => $page->id, 'user_id' => $user->id],
            ['role' => $data['role']]
        );

        return response()->json([
            'message' => "تم تعيين صلاحية {$data['role']} للمستخدم {$user->name}",
            'permission' => $permission->load('user:id,name,email'),
        ]);
    }

    /**
     * إزالة صلاحية مخصصة لمستخدم (يعود للدور الافتراضي)
     */
    public function destroy(Page $page, User $user): JsonResponse
    {
        Gate::authorize('managePermissions', $page);

        $page->permissions()->where('user_id', $user->id)->delete();

        return response()->json([
            'message' => 'تم حذف الصلاحية المخصصة للمستخدم',
        ]);
    }
}
