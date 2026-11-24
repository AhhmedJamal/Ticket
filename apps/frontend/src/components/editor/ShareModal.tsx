'use client';

import React, { useState, useEffect } from 'react';
import { pagePermissionService } from '@/services/api';
import { PagePermission } from '@/types';
import { useToast } from '@/lib/toast-context';
import { Users, X, Loader2 } from 'lucide-react';

interface ShareModalProps {
    isOpen: boolean;
    pageId: number;
    ownerEmail?: string;
    onClose: () => void;
}

export const ShareModal: React.FC<ShareModalProps> = ({
    isOpen,
    pageId,
    ownerEmail,
    onClose,
}) => {
    const { showToast } = useToast();
    const [permissions, setPermissions] = useState<PagePermission[]>([]);
    const [isLoading, setIsLoading] = useState(false);
    const [email, setEmail] = useState('');
    const [role, setRole] = useState<'editor' | 'commenter' | 'viewer'>('editor');
    const [isSaving, setIsSaving] = useState(false);

    useEffect(() => {
        if (isOpen) {
            loadPermissions();
        }
    }, [isOpen, pageId]);

    const loadPermissions = async () => {
        setIsLoading(true);
        try {
            const list = await pagePermissionService.getByPage(pageId);
            setPermissions(list);
        } catch {
            showToast('فشل تحميل الصلاحيات', 'error');
        } finally {
            setIsLoading(false);
        }
    };

    const handleAddPermission = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!email.trim()) return;

        setIsSaving(true);
        try {
            const perm = await pagePermissionService.setPermission(pageId, {
                email: email.trim(),
                role,
            });
            setPermissions((prev) => {
                const idx = prev.findIndex((p) => p.id === perm.id);
                if (idx >= 0) {
                    const clone = [...prev];
                    clone[idx] = perm;
                    return clone;
                }
                return [...prev, perm];
            });
            setEmail('');
            showToast('تم تحديث الصلاحية بنجاح', 'success');
        } catch (err: any) {
            showToast(err.response?.data?.message || 'فشل تحديث الصلاحية', 'error');
        } finally {
            setIsSaving(false);
        }
    };

    const handleRemovePermission = async (userId: number) => {
        try {
            await pagePermissionService.removePermission(pageId, userId);
            setPermissions((prev) => prev.filter((p) => p.user_id !== userId));
            showToast('تمت إزالة الصلاحية', 'success');
        } catch {
            showToast('فشل إزالة الصلاحية', 'error');
        }
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div className="fixed inset-0 bg-black/50 backdrop-blur-sm" onClick={onClose} />
            <div className="relative w-full max-w-md bg-white dark:bg-[#222222] border border-notion-border dark:border-[#333333] rounded-2xl shadow-2xl p-5 space-y-4 z-10">
                <div className="flex items-center justify-between border-b border-notion-border dark:border-[#303030] pb-3">
                    <div className="flex items-center gap-2">
                        <Users className="w-5 h-5 text-blue-500" />
                        <h3 className="text-sm font-semibold text-notion-text dark:text-[#E3E3E0]">
                            مشاركة الصفحة وإدارة الصلاحيات
                        </h3>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="text-xs text-notion-muted hover:text-notion-text dark:hover:text-[#E3E3E0]"
                    >
                        <X className="w-4 h-4" />
                    </button>
                </div>

                {/* نموذج إضافة مستخدم */}
                <form onSubmit={handleAddPermission} className="space-y-2">
                    <div className="text-xs font-medium text-notion-muted dark:text-[#9B9A97]">
                        إضافة عضو أو تعديل صلاحيته:
                    </div>
                    <div className="flex items-center gap-2">
                        <input
                            type="email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            placeholder="بريد المستخدم الإلكتروني..."
                            required
                            className="flex-1 text-xs px-3 py-2 rounded-xl border border-notion-border dark:border-[#353535] bg-transparent text-notion-text dark:text-[#E3E3E0] focus:outline-none focus:ring-1 focus:ring-blue-500"
                        />
                        <select
                            value={role}
                            onChange={(e) =>
                                setRole(e.target.value as 'editor' | 'commenter' | 'viewer')
                            }
                            className="text-xs px-2.5 py-2 rounded-xl border border-notion-border dark:border-[#353535] bg-white dark:bg-[#1E1E1E] text-notion-text dark:text-[#E3E3E0] focus:outline-none"
                        >
                            <option value="editor">محرر (Editor)</option>
                            <option value="commenter">معلق (Commenter)</option>
                            <option value="viewer">مشاهد (Viewer)</option>
                        </select>
                        <button
                            type="submit"
                            disabled={isSaving}
                            className="text-xs px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium transition-colors shrink-0 disabled:opacity-50"
                        >
                            {isSaving ? 'حفظ...' : 'حفظ'}
                        </button>
                    </div>
                </form>

                {/* قائمة الصلاحيات الحالية */}
                <div className="space-y-2 pt-2">
                    <div className="text-xs font-semibold text-notion-muted dark:text-[#9B9A97]">
                        المخولون بالوصول:
                    </div>
                    <div className="max-h-52 overflow-y-auto divide-y divide-notion-border/60 dark:divide-[#2E2E2E]">
                        {/* المالك */}
                        <div className="flex items-center justify-between py-2 text-xs">
                            <div className="flex items-center gap-2">
                                <span className="w-6 h-6 rounded-full bg-amber-500/20 text-amber-400 font-bold text-[10px] flex items-center justify-center">
                                    👑
                                </span>
                                <div>
                                    <div className="font-medium text-notion-text dark:text-[#E3E3E0]">
                                        مالك مساحة العمل
                                    </div>
                                    <div className="text-[10px] text-notion-muted">{ownerEmail}</div>
                                </div>
                            </div>
                            <span className="text-[10px] px-2 py-0.5 rounded-full bg-notion-sidebar dark:bg-[#2C2C2C] text-notion-muted">
                                محرر كامل
                            </span>
                        </div>

                        {/* بقية الصلاحيات */}
                        {isLoading ? (
                            <div className="py-4 text-center">
                                <Loader2 className="w-4 h-4 animate-spin mx-auto text-notion-muted" />
                            </div>
                        ) : (
                            permissions.map((perm) => (
                                <div
                                    key={perm.id}
                                    className="flex items-center justify-between py-2 text-xs"
                                >
                                    <div className="flex items-center gap-2">
                                        <span className="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 font-bold text-[10px] flex items-center justify-center">
                                            {perm.user?.name ? perm.user.name.charAt(0) : 'م'}
                                        </span>
                                        <div>
                                            <div className="font-medium text-notion-text dark:text-[#E3E3E0]">
                                                {perm.user?.name || 'مستخدم'}
                                            </div>
                                            <div className="text-[10px] text-notion-muted">
                                                {perm.user?.email}
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <span className="text-[10px] px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 font-medium">
                                            {perm.role === 'editor'
                                                ? 'محرر'
                                                : perm.role === 'commenter'
                                                ? 'معلق'
                                                : 'مشاهد'}
                                        </span>
                                        <button
                                            type="button"
                                            onClick={() => handleRemovePermission(perm.user_id)}
                                            className="text-notion-muted hover:text-red-500 p-1"
                                        >
                                            <X className="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

