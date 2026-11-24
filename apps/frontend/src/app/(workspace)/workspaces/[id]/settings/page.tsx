'use client';

import React, { useState, useEffect } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { useAuth } from '@/lib/auth-context';
import { useWorkspace } from '@/lib/workspace-context';
import { workspaceService } from '@/services/api';
import { Workspace, WorkspaceMember } from '@/types';
import { useToast } from '@/lib/toast-context';
import { Settings, Shield, User, Trash2, Loader2 } from 'lucide-react';

export default function WorkspaceSettingsPage() {
    const params = useParams();
    const router = useRouter();
    const workspaceId = Number(params?.id);
    const { user } = useAuth();
    const { refreshWorkspaces } = useWorkspace();
    const { showToast } = useToast();

    const [workspace, setWorkspace] = useState<Workspace | null>(null);
    const [isLoading, setIsLoading] = useState(true);

    // Update name form state
    const [name, setName] = useState('');
    const [isUpdatingName, setIsUpdatingName] = useState(false);

    // Invite member form state
    const [inviteEmail, setInviteEmail] = useState('');
    const [inviteRole, setInviteRole] = useState<'member' | 'admin'>('member');
    const [isInviting, setIsInviting] = useState(false);

    const loadWorkspace = async () => {
        try {
            const data = await workspaceService.getById(workspaceId);
            setWorkspace(data);
            setName(data.name);
        } catch {
            showToast('تعذر تحميل إعدادات مساحة العمل', 'error');
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        loadWorkspace();
    }, [workspaceId]);

    const isOwner = user && workspace && workspace.user_id === user.id;
    const canEditWorkspace =
        isOwner ||
        workspace?.members?.some((m) => m.id === user?.id && m.pivot?.role === 'admin');

    const handleUpdateName = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!name.trim() || !workspace) return;

        setIsUpdatingName(true);
        try {
            const updated = await workspaceService.update(workspace.id, { name: name.trim() });
            setWorkspace((prev) => (prev ? { ...prev, name: updated.name } : updated));
            await refreshWorkspaces();
            showToast('تم تحديث اسم مساحة العمل بنجاح', 'success');
        } catch (err: any) {
            showToast(err.response?.data?.message || 'فشل تحديث اسم مساحة العمل', 'error');
        } finally {
            setIsUpdatingName(false);
        }
    };

    const handleInviteMember = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!inviteEmail.trim() || !workspace) return;

        setIsInviting(true);
        try {
            const res = await workspaceService.inviteMember(workspace.id, {
                email: inviteEmail.trim(),
                role: inviteRole,
            });
            showToast(res.message || 'تمت دعوة العضو بنجاح', 'success');
            setInviteEmail('');
            setInviteRole('member');
            await loadWorkspace();
        } catch (err: any) {
            showToast(err.response?.data?.message || 'فشل إرسال الدعوة', 'error');
        } finally {
            setIsInviting(false);
        }
    };

    const handleRemoveMember = async (member: WorkspaceMember) => {
        if (!workspace) return;
        if (!window.confirm(`إزالة ${member.name} من مساحة العمل؟`)) return;

        try {
            await workspaceService.removeMember(workspace.id, member.id);
            showToast('تمت إزالة العضو من مساحة العمل', 'success');
            await loadWorkspace();
        } catch (err: any) {
            showToast(err.response?.data?.message || 'فشل إزالة العضو', 'error');
        }
    };

    if (isLoading) {
        return (
            <div className="py-24 flex items-center justify-center">
                <Loader2 className="w-8 h-8 animate-spin text-notion-muted" />
            </div>
        );
    }

    if (!workspace) {
        return (
            <div className="py-24 text-center">
                <p className="text-notion-muted">مساحة العمل غير موجودة.</p>
            </div>
        );
    }

    return (
        <div className="max-w-3xl mx-auto space-y-8">
            {/* رأس الصفحة */}
            <div className="pb-4 border-b border-notion-border dark:border-[#2E2E2E]">
                <div className="flex items-center gap-3 mb-2">
                    <span className="text-3xl">⚙️</span>
                    <div>
                        <h1 className="text-xl font-bold text-notion-text dark:text-[#E3E3E0]">
                            إعدادات مساحة العمل
                        </h1>
                        <p className="text-sm text-notion-muted dark:text-[#9B9A97]">
                            {workspace.name}
                        </p>
                    </div>
                </div>
            </div>

            {/* المعلومات الأساسية */}
            <section>
                <h2 className="text-xs font-semibold text-notion-muted dark:text-[#9B9A97] uppercase tracking-wider mb-3">
                    المعلومات الأساسية
                </h2>
                <div className="bg-notion-sidebar dark:bg-[#202020] border border-notion-border dark:border-[#2E2E2E] rounded-xl p-4 space-y-4">
                    <div className="flex items-center gap-3">
                        <span className="w-10 h-10 rounded-lg bg-notion-text dark:bg-[#3A3A3A] text-white flex items-center justify-center font-bold text-lg shrink-0">
                            {workspace.name.charAt(0)}
                        </span>
                        <div>
                            <div className="font-semibold text-notion-text dark:text-[#E3E3E0]">
                                {workspace.name}
                            </div>
                            <div className="text-xs text-notion-muted dark:text-[#9B9A97]">
                                المالك: {workspace.owner?.name || 'غير معروف'}
                            </div>
                        </div>
                    </div>

                    {canEditWorkspace && (
                        <form
                            onSubmit={handleUpdateName}
                            className="flex flex-col sm:flex-row gap-3 pt-3 border-t border-notion-border dark:border-[#2E2E2E]"
                        >
                            <input
                                type="text"
                                value={name}
                                onChange={(e) => setName(e.target.value)}
                                placeholder="اسم مساحة العمل"
                                required
                                className="flex-1 text-sm bg-white dark:bg-[#191919] border border-notion-border dark:border-[#2E2E2E] rounded-lg px-3 py-2 focus:outline-none focus:border-notion-accent text-notion-text dark:text-[#E3E3E0] placeholder-notion-muted"
                            />
                            <button
                                type="submit"
                                disabled={isUpdatingName}
                                className="px-4 py-2 bg-notion-text dark:bg-[#2EAADC] text-white rounded-lg text-sm hover:bg-black dark:hover:bg-[#2596C4] transition-colors whitespace-nowrap disabled:opacity-50"
                            >
                                {isUpdatingName ? 'جاري الحفظ...' : 'حفظ التعديلات'}
                            </button>
                        </form>
                    )}
                </div>
            </section>

            {/* الأعضاء الحاليون */}
            <section>
                <h2 className="text-xs font-semibold text-notion-muted dark:text-[#9B9A97] uppercase tracking-wider mb-3">
                    الأعضاء ({(workspace.members?.length || 0) + 1})
                </h2>
                <div className="border border-notion-border dark:border-[#2E2E2E] rounded-xl overflow-hidden">
                    {/* المالك */}
                    <div className="flex items-center justify-between px-4 py-3 bg-white dark:bg-[#202020] border-b border-notion-border dark:border-[#2E2E2E]">
                        <div className="flex items-center gap-3">
                            <div className="w-8 h-8 rounded-full bg-notion-text dark:bg-[#3A3A3A] text-white flex items-center justify-center text-xs font-bold">
                                {workspace.owner?.name?.charAt(0) || 'م'}
                            </div>
                            <div>
                                <div className="text-sm font-medium text-notion-text dark:text-[#E3E3E0]">
                                    {workspace.owner?.name}
                                </div>
                                <div className="text-xs text-notion-muted dark:text-[#9B9A97]">
                                    {workspace.owner?.email}
                                </div>
                            </div>
                        </div>
                        <span className="text-xs bg-notion-text dark:bg-[#3A3A3A] text-white px-2.5 py-0.5 rounded-full">
                            مالك
                        </span>
                    </div>

                    {/* بقية الأعضاء */}
                    {workspace.members && workspace.members.length > 0 ? (
                        workspace.members.map((member) => (
                            <div
                                key={member.id}
                                className="flex items-center justify-between px-4 py-3 bg-white dark:bg-[#202020] border-b border-notion-border dark:border-[#2E2E2E] last:border-0"
                            >
                                <div className="flex items-center gap-3">
                                    <div className="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-xs font-bold">
                                        {member.name.charAt(0)}
                                    </div>
                                    <div>
                                        <div className="text-sm font-medium text-notion-text dark:text-[#E3E3E0]">
                                            {member.name}
                                        </div>
                                        <div className="text-xs text-notion-muted dark:text-[#9B9A97]">
                                            {member.email}
                                        </div>
                                    </div>
                                </div>
                                <div className="flex items-center gap-2">
                                    <span
                                        className={`text-xs px-2 py-0.5 rounded-full ${
                                            member.pivot?.role === 'admin'
                                                ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300'
                                                : 'bg-gray-100 text-gray-600 dark:bg-[#2C2C2C] dark:text-[#9B9A97]'
                                        }`}
                                    >
                                        {member.pivot?.role === 'admin' ? 'مشرف' : 'عضو'}
                                    </span>

                                    {isOwner && (
                                        <button
                                            type="button"
                                            onClick={() => handleRemoveMember(member)}
                                            className="text-xs text-red-500 hover:text-red-700 p-1"
                                            title="إزالة العضو"
                                        >
                                            <Trash2 className="w-4 h-4" />
                                        </button>
                                    )}
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="px-4 py-6 text-center text-sm text-notion-muted dark:text-[#9B9A97] bg-white dark:bg-[#202020]">
                            لا يوجد أعضاء آخرون حتى الآن.
                        </div>
                    )}
                </div>
            </section>

            {/* دعوة عضو جديد */}
            {isOwner && (
                <section>
                    <h2 className="text-xs font-semibold text-notion-muted dark:text-[#9B9A97] uppercase tracking-wider mb-3">
                        دعوة عضو جديد
                    </h2>
                    <form
                        onSubmit={handleInviteMember}
                        className="bg-notion-sidebar dark:bg-[#202020] border border-notion-border dark:border-[#2E2E2E] rounded-xl p-4 space-y-3"
                    >
                        <div className="flex flex-col sm:flex-row gap-3">
                            <input
                                type="email"
                                value={inviteEmail}
                                onChange={(e) => setInviteEmail(e.target.value)}
                                placeholder="البريد الإلكتروني للمستخدم"
                                required
                                className="flex-1 text-sm bg-white dark:bg-[#191919] border border-notion-border dark:border-[#2E2E2E] rounded-lg px-3 py-2 focus:outline-none focus:border-notion-accent text-notion-text dark:text-[#E3E3E0] placeholder-notion-muted"
                            />
                            <select
                                value={inviteRole}
                                onChange={(e) => setInviteRole(e.target.value as 'member' | 'admin')}
                                className="text-sm bg-white dark:bg-[#191919] border border-notion-border dark:border-[#2E2E2E] rounded-lg px-3 py-2 focus:outline-none text-notion-text dark:text-[#E3E3E0]"
                            >
                                <option value="member">عضو</option>
                                <option value="admin">مشرف</option>
                            </select>
                            <button
                                type="submit"
                                disabled={isInviting}
                                className="px-4 py-2 bg-notion-text dark:bg-[#2EAADC] text-white rounded-lg text-sm hover:bg-black dark:hover:bg-[#2596C4] transition-colors whitespace-nowrap disabled:opacity-50"
                            >
                                {isInviting ? 'جاري الإرسال...' : 'دعوة'}
                            </button>
                        </div>
                        <p className="text-xs text-notion-muted dark:text-[#9B9A97]">
                            يجب أن يكون المستخدم مسجلاً في التطبيق مسبقاً.
                        </p>
                    </form>
                </section>
            )}
        </div>
    );
}

