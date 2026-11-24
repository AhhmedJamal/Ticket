<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceMemberController extends Controller
{
    /**
     * عرض صفحة إعدادات مساحة العمل والأعضاء
     */
    public function index(Workspace $workspace): View
    {
        $this->authorizeOwner($workspace, request());

        $workspace->load('members', 'owner');

        return view('workspaces.settings', compact('workspace'));
    }

    /**
     * دعوة عضو جديد عبر البريد الإلكتروني
     */
    public function invite(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeOwner($workspace, $request);

        $data = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:admin,member',
        ]);

        $user = User::where('email', $data['email'])->first();

        if ($workspace->members->contains($user->id) || $workspace->user_id === $user->id) {
            return back()->withErrors(['email' => 'هذا المستخدم عضو بالفعل.']);
        }

        $workspace->members()->attach($user->id, ['role' => $data['role']]);

        return back()->with('success', "تمت دعوة {$user->name} بنجاح.");
    }

    /**
     * تغيير دور عضو
     */
    public function updateRole(Request $request, Workspace $workspace, User $user): JsonResponse
    {
        $this->authorizeOwner($workspace, $request);

        $data = $request->validate([
            'role' => 'required|in:admin,member',
        ]);

        $workspace->members()->updateExistingPivot($user->id, ['role' => $data['role']]);

        return response()->json(['message' => 'تم تحديث الدور.']);
    }

    /**
     * إزالة عضو من مساحة العمل
     */
    public function remove(Request $request, Workspace $workspace, User $user): RedirectResponse
    {
        $this->authorizeOwner($workspace, $request);

        if ($workspace->user_id === $user->id) {
            return back()->withErrors(['member' => 'لا يمكن إزالة المالك.']);
        }

        $workspace->members()->detach($user->id);

        return back()->with('success', 'تمت إزالة العضو.');
    }

    /** التحقق من أن الطالب هو مالك مساحة العمل */
    private function authorizeOwner(Workspace $workspace, Request $request): void
    {
        if ($workspace->user_id !== $request->user()->id) {
            abort(403, 'غير مخول بإدارة هذه المساحة.');
        }
    }
}
