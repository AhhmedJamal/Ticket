<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Workspace\InviteMemberRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkspaceMemberController extends ApiController
{
    /**
     * جلب أعضاء مساحة العمل وإعداداتها
     */
    public function index(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorizeOwnerOrAdmin($workspace, $request);

        $workspace->load(['members', 'owner']);

        return $this->successResponse([
            'owner' => new UserResource($workspace->owner),
            'members' => UserResource::collection($workspace->members),
        ], 'تم جلب أعضاء مساحة العمل بنجاح.');
    }

    /**
     * دعوة عضو جديد عبر البريد الإلكتروني
     */
    public function invite(InviteMemberRequest $request, Workspace $workspace): JsonResponse
    {
        $this->authorizeOwner($workspace, $request);

        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();

        if ($workspace->members()->where('users.id', $user->id)->exists() || $workspace->user_id === $user->id) {
            return $this->errorResponse('هذا المستخدم عضو بالفعل في مساحة العمل.', 422, [
                'email' => ['هذا المستخدم عضو بالفعل.'],
            ]);
        }

        $workspace->members()->attach($user->id, ['role' => $data['role']]);

        return $this->successResponse([
            'member' => new UserResource($workspace->members()->where('users.id', $user->id)->first()),
        ], "تمت دعوة {$user->name} بنجاح.", 201);
    }

    /**
     * تحديث دور عضو في مساحة العمل
     */
    public function updateRole(Request $request, Workspace $workspace, User $user): JsonResponse
    {
        $this->authorizeOwner($workspace, $request);

        $request->validate([
            'role' => ['required', 'in:admin,member'],
        ]);

        $workspace->members()->updateExistingPivot($user->id, ['role' => $request->input('role')]);

        return $this->successResponse(null, 'تم تحديث دور العضو بنجاح.');
    }

    /**
     * إزالة عضو من مساحة العمل
     */
    public function remove(Request $request, Workspace $workspace, User $user): JsonResponse
    {
        $this->authorizeOwner($workspace, $request);

        if ($workspace->user_id === $user->id) {
            return $this->errorResponse('لا يمكن إزالة مالك مساحة العمل.', 422);
        }

        $workspace->members()->detach($user->id);

        return $this->successResponse(null, 'تمت إزالة العضو بنجاح.');
    }

    private function authorizeOwner(Workspace $workspace, Request $request): void
    {
        if ($workspace->user_id !== $request->user()->id) {
            abort(403, 'غير مخول بإدارة أعضاء هذه المساحة.');
        }
    }

    private function authorizeOwnerOrAdmin(Workspace $workspace, Request $request): void
    {
        $user = $request->user();
        $isAdmin = $workspace->user_id === $user->id
            || $workspace->members()->where('users.id', $user->id)->wherePivot('role', 'admin')->exists();

        if (! $isAdmin) {
            abort(403, 'غير مخول بعرض إعدادات هذه المساحة.');
        }
    }
}
