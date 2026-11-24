<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    /**
     * هل يمكن عرض الصفحة
     */
    public function view(User $user, Page $page): bool
    {
        return $page->getUserRole($user) !== null;
    }

    /**
     * هل يمكن تعديل الصفحة أو محتواها
     */
    public function update(User $user, Page $page): bool
    {
        return $page->getUserRole($user) === 'editor';
    }

    /**
     * هل يمكن إضافة تعليق على الصفحة/بلوكاتها
     */
    public function comment(User $user, Page $page): bool
    {
        $role = $page->getUserRole($user);

        return in_array($role, ['editor', 'commenter'], true);
    }

    /**
     * هل يمكن حذف الصفحة
     */
    public function delete(User $user, Page $page): bool
    {
        // المالك أو المنشئ أو مشرف مساحة العمل
        if ($page->workspace && $page->workspace->user_id === $user->id) {
            return true;
        }

        if ($page->created_by === $user->id) {
            return true;
        }

        return $page->getUserRole($user) === 'editor';
    }

    /**
     * هل يمكن إدارة صلاحيات الصفحة وتعيين الأدوار
     */
    public function managePermissions(User $user, Page $page): bool
    {
        if ($page->workspace && $page->workspace->user_id === $user->id) {
            return true;
        }

        return $page->created_by === $user->id;
    }
}
