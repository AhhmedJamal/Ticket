'use client';

import React, { useState, useEffect } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { templateService } from '@/services/api';
import { Template } from '@/types';
import { useWorkspace } from '@/lib/workspace-context';
import { useToast } from '@/lib/toast-context';
import { Loader2 } from 'lucide-react';

const CATEGORY_MAP: Record<string, { label: string; icon: string }> = {
    general: { label: 'عام', icon: '📄' },
    project: { label: 'مشروع', icon: '🗂️' },
    meeting: { label: 'اجتماع', icon: '🤝' },
    notes: { label: 'ملاحظات', icon: '📝' },
    goals: { label: 'أهداف', icon: '🎯' },
};

export default function TemplatesPage() {
    const params = useParams();
    const router = useRouter();
    const workspaceId = Number(params?.id);
    const { refreshCurrentWorkspace } = useWorkspace();
    const { showToast } = useToast();

    const [templates, setTemplates] = useState<Record<string, Template[]>>({});
    const [isLoading, setIsLoading] = useState(true);
    const [applyingId, setApplyingId] = useState<number | null>(null);

    useEffect(() => {
        const loadTemplates = async () => {
            try {
                const data = await templateService.getAll();
                setTemplates(data);
            } catch {
                showToast('تعذر تحميل القوالب', 'error');
            } finally {
                setIsLoading(false);
            }
        };
        loadTemplates();
    }, []);

    const handleApplyTemplate = async (templateId: number) => {
        setApplyingId(templateId);
        try {
            const page = await templateService.apply(workspaceId, templateId);
            await refreshCurrentWorkspace();
            showToast('تم تطبيق القالب بنجاح', 'success');
            router.push(`/pages/${page.id}`);
        } catch {
            showToast('فشل تطبيق القالب، حاول مجدداً', 'error');
            setApplyingId(null);
        }
    };

    if (isLoading) {
        return (
            <div className="py-24 flex items-center justify-center">
                <Loader2 className="w-8 h-8 animate-spin text-notion-muted" />
            </div>
        );
    }

    return (
        <div className="max-w-5xl mx-auto">
            {/* رأس الصفحة */}
            <div className="mb-6 sm:mb-8">
                <h1 className="text-xl sm:text-2xl font-bold text-notion-text dark:text-white mb-1">
                    📋 قوالب الصفحات
                </h1>
                <p className="text-xs sm:text-sm text-notion-muted dark:text-gray-400">
                    ابدأ بقالب جاهز لتوفير وقتك
                </p>
            </div>

            {/* شبكة القوالب */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {Object.entries(templates).flatMap(([category, categoryTemplates]) => {
                    const catInfo = CATEGORY_MAP[category] || { label: category, icon: '📄' };

                    return categoryTemplates.map((template) => (
                        <div
                            key={template.id}
                            className="border border-notion-border dark:border-[#2E2E2E] rounded-xl overflow-hidden hover:border-notion-muted/60 dark:hover:border-[#4A4A4A] transition-all group bg-white dark:bg-[#202020] flex flex-col justify-between"
                        >
                            <div>
                                <div className="h-24 bg-notion-sidebar dark:bg-[#252525] border-b border-notion-border dark:border-[#2E2E2E] flex items-center justify-center">
                                    <span className="text-4xl group-hover:scale-110 transition-transform">
                                        {template.icon}
                                    </span>
                                </div>
                                <div className="p-4">
                                    <span className="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-md bg-notion-sidebar dark:bg-[#2A2A2A] text-notion-muted dark:text-[#9B9A97] border border-notion-border dark:border-[#333333] mb-2">
                                        <span>{catInfo.icon}</span>
                                        <span>{catInfo.label}</span>
                                    </span>
                                    <h3 className="font-semibold text-notion-text dark:text-[#E3E3E0] text-sm truncate">
                                        {template.name}
                                    </h3>
                                    <p className="text-xs text-notion-muted dark:text-[#9B9A97] mt-1 line-clamp-2 leading-relaxed">
                                        {template.description}
                                    </p>
                                </div>
                            </div>
                            <div className="px-4 pb-4">
                                <button
                                    type="button"
                                    disabled={applyingId === template.id}
                                    onClick={() => handleApplyTemplate(template.id)}
                                    className="w-full text-xs py-2 px-3 bg-notion-text dark:bg-[#2C2C2C] text-white dark:text-[#E3E3E0] border border-transparent dark:border-[#3A3A3A] rounded-lg hover:bg-black dark:hover:bg-[#3A3A3A] transition-colors font-medium disabled:opacity-50"
                                >
                                    {applyingId === template.id ? 'جاري التطبيق...' : 'استخدام هذا القالب'}
                                </button>
                            </div>
                        </div>
                    ));
                })}
            </div>
        </div>
    );
}

