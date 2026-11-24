'use client';

import React, { useState } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/lib/auth-context';
import { useWorkspace } from '@/lib/workspace-context';
import { workspaceService } from '@/services/api';
import { useToast } from '@/lib/toast-context';

export default function CreateWorkspacePage() {
    const router = useRouter();
    const { logout } = useAuth();
    const { refreshWorkspaces, setCurrentWorkspace } = useWorkspace();
    const { showToast } = useToast();

    const [name, setName] = useState('');
    const [isCreating, setIsCreating] = useState(false);
    const [error, setError] = useState('');

    const handleCreate = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!name.trim()) return;

        setIsCreating(true);
        setError('');

        try {
            const workspace = await workspaceService.create({ name: name.trim() });
            await refreshWorkspaces();
            setCurrentWorkspace(workspace);
            showToast('تم إنشاء مساحة العمل بنجاح', 'success');
            router.push(`/workspaces/${workspace.id}`);
        } catch (err: any) {
            setError(err.response?.data?.message || 'حدث خطأ أثناء إنشاء مساحة العمل');
            setIsCreating(false);
        }
    };

    return (
        <div className="min-h-[80vh] flex items-center justify-center p-4">
            <div className="bg-white dark:bg-[#202020] rounded-2xl shadow-sm border border-[#e9e9e7] dark:border-[#2E2E2E] w-full max-w-md p-10">
                <div className="text-center mb-8">
                    <div className="text-5xl mb-4">🏢</div>
                    <h1 className="text-2xl font-bold text-[#37352f] dark:text-[#E3E3E0] mb-2">
                        إنشاء مساحة عمل
                    </h1>
                    <p className="text-sm text-[#9b9a97]">
                        ابدأ بإنشاء مساحة عمل لتنظيم صفحاتك ومهامك
                    </p>
                </div>

                {error && (
                    <div className="mb-4 p-3 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 rounded-lg">
                        <p className="text-sm text-red-600 dark:text-red-400">{error}</p>
                    </div>
                )}

                <form onSubmit={handleCreate} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-[#37352f] dark:text-[#E3E3E0] mb-1.5">
                            اسم مساحة العمل
                        </label>
                        <input
                            type="text"
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                            placeholder="مشروعي الجديد"
                            required
                            autoFocus
                            className="w-full px-3 py-2.5 bg-white dark:bg-[#191919] border border-[#e9e9e7] dark:border-[#2E2E2E] rounded-lg text-sm text-[#37352f] dark:text-[#E3E3E0] placeholder-[#9b9a97] focus:outline-none focus:ring-2 focus:ring-[#2eaadc] focus:border-transparent"
                        />
                    </div>

                    <button
                        type="submit"
                        disabled={isCreating || !name.trim()}
                        className="w-full py-2.5 bg-[#37352f] dark:bg-[#2EAADC] text-white text-sm font-medium rounded-lg hover:bg-[#1a1915] dark:hover:bg-[#2596C4] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {isCreating ? 'جاري الإنشاء...' : 'إنشاء مساحة العمل'}
                    </button>
                </form>

                <div className="mt-6 text-center">
                    <button
                        type="button"
                        onClick={logout}
                        className="text-sm text-[#9b9a97] hover:text-[#37352f] dark:hover:text-[#E3E3E0] hover:underline"
                    >
                        تسجيل الخروج
                    </button>
                </div>
            </div>
        </div>
    );
}

