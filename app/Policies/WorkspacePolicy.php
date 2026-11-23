<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{
    /**
     * هل يمكن للمستخدم عرض مساحة العمل وصفحاتها
     */
    public function view(User $user, Workspace $workspace): bool
    {
        return $workspace->user_id === $user->id || $workspace->members()->where('users.id', $user->id)->exists();
    }

    /**
     * هل يمكن للمستخدم تعديل إعدادات مساحة العمل أو اسمها
     */
    public function update(User $user, Workspace $workspace): bool
    {
        if ($workspace->user_id === $user->id) {
            return true;
        }

        $member = $workspace->members()->where('users.id', $user->id)->first();

        return $member && $member->pivot->role === 'admin';
    }

    /**
     * هل يمكن حذف مساحة العمل بالكامل (فقط المالك الأساسي)
     */
    public function delete(User $user, Workspace $workspace): bool
    {
        return $workspace->user_id === $user->id;
    }

    /**
     * هل يمكن دعوة وتعديل أعضاء مساحة العمل
     */
    public function manageMembers(User $user, Workspace $workspace): bool
    {
        return $this->update($user, $workspace);
    }
}
