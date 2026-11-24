'use client';

import React from 'react';
import { Page } from '@/types';
import { X, Clock, User, Calendar, Layers, Database } from 'lucide-react';

const ACTION_ICONS: Record<string, string> = {
    created: '✨',
    updated: '✏️',
    block_added: '➕',
    block_updated: '📝',
    block_deleted: '🗑️',
    blocks_reordered: '↕️',
    permission_added: '👥',
    permission_removed: '🚫',
};

interface PageOverviewPanelProps {
    isOpen: boolean;
    page: Page;
    databasesCount: number;
    onClose: () => void;
}

export const PageOverviewPanel: React.FC<PageOverviewPanelProps> = ({
    isOpen,
    page,
    databasesCount,
    onClose,
}) => {
    if (!isOpen) return null;

    const formatDate = (dateStr: string) => {
        try {
            const d = new Date(dateStr);
            return `${d.getFullYear()}/${String(d.getMonth() + 1).padStart(2, '0')}/${String(d.getDate()).padStart(2, '0')}`;
        } catch {
            return dateStr;
        }
    };

    const formatRelativeTime = (dateStr: string) => {
        try {
            const diff = Date.now() - new Date(dateStr).getTime();
            const minutes = Math.floor(diff / 60000);
            if (minutes < 1) return 'الآن';
            if (minutes < 60) return `منذ ${minutes} دقيقة`;
            const hours = Math.floor(minutes / 60);
            if (hours < 24) return `منذ ${hours} ساعة`;
            const days = Math.floor(hours / 24);
            return `منذ ${days} يوم`;
        } catch {
            return dateStr;
        }
    };

    return (
        <aside className="fixed inset-y-0 left-0 z-40 lg:static lg:z-auto w-72 shrink-0 border-r border-notion-border dark:border-[#2E2E2E] bg-notion-sidebar lg:bg-notion-sidebar/60 dark:bg-[#202020] p-5 space-y-6 overflow-y-auto shadow-2xl lg:shadow-none">
            {/* نظرة عامة */}
            <div>
                <div className="flex items-center justify-between mb-3">
                    <h3 className="text-xs font-semibold text-notion-muted dark:text-[#9B9A97]">
                        نظرة عامة على الصفحة
                    </h3>
                    <button
                        type="button"
                        onClick={onClose}
                        className="text-xs text-notion-muted hover:text-notion-text dark:hover:text-[#E3E3E0]"
                    >
                        <X className="w-4 h-4" />
                    </button>
                </div>
                <div className="space-y-2.5 text-xs">
                    <div className="flex items-center justify-between">
                        <span className="text-notion-muted dark:text-[#9B9A97] flex items-center gap-1.5">
                            <User className="w-3.5 h-3.5" />
                            أنشئت بواسطة
                        </span>
                        <span className="font-medium text-notion-text dark:text-[#E3E3E0]">
                            {page.creator?.name || '—'}
                        </span>
                    </div>
                    <div className="flex items-center justify-between">
                        <span className="text-notion-muted dark:text-[#9B9A97] flex items-center gap-1.5">
                            <Calendar className="w-3.5 h-3.5" />
                            تاريخ الإنشاء
                        </span>
                        <span className="font-medium text-notion-text dark:text-[#E3E3E0]">
                            {formatDate(page.created_at)}
                        </span>
                    </div>
                    <div className="flex items-center justify-between">
                        <span className="text-notion-muted dark:text-[#9B9A97] flex items-center gap-1.5">
                            <Clock className="w-3.5 h-3.5" />
                            آخر تعديل
                        </span>
                        <span className="font-medium text-notion-text dark:text-[#E3E3E0]">
                            {formatRelativeTime(page.updated_at)}
                        </span>
                    </div>
                    <div className="flex items-center justify-between">
                        <span className="text-notion-muted dark:text-[#9B9A97] flex items-center gap-1.5">
                            <Layers className="w-3.5 h-3.5" />
                            البلوكات
                        </span>
                        <span className="font-medium text-notion-text dark:text-[#E3E3E0]">
                            {page.blocks?.length || 0}
                        </span>
                    </div>
                    <div className="flex items-center justify-between">
                        <span className="text-notion-muted dark:text-[#9B9A97] flex items-center gap-1.5">
                            <Database className="w-3.5 h-3.5" />
                            قواعد البيانات
                        </span>
                        <span className="font-medium text-notion-text dark:text-[#E3E3E0]">
                            {databasesCount}
                        </span>
                    </div>
                </div>
            </div>

            <div className="border-t border-notion-border dark:border-[#2E2E2E]" />

            {/* سجل النشاط والتعديلات */}
            <div>
                <h3 className="text-xs font-semibold text-notion-muted dark:text-[#9B9A97] mb-3">
                    سجل التعديلات ({page.activities?.length || 0})
                </h3>
                <div className="space-y-3">
                    {page.activities && page.activities.length > 0 ? (
                        page.activities.map((activity) => (
                            <div key={activity.id} className="flex items-start gap-2.5 text-xs">
                                <span className="w-6 h-6 rounded-full bg-notion-hover dark:bg-[#2C2C2C] flex items-center justify-center text-[11px] font-bold text-notion-text dark:text-[#E3E3E0] shrink-0">
                                    {activity.user?.name ? activity.user.name.charAt(0) : 'م'}
                                </span>
                                <div className="flex-1 min-w-0">
                                    <div className="flex items-center justify-between gap-1">
                                        <span className="font-medium text-notion-text dark:text-[#E3E3E0] truncate">
                                            {activity.user?.name || 'مستخدم'}
                                        </span>
                                        <span className="text-[10px] text-notion-muted dark:text-[#9B9A97] shrink-0">
                                            {formatRelativeTime(activity.created_at)}
                                        </span>
                                    </div>
                                    <p className="text-notion-muted dark:text-[#9B9A97] mt-0.5 leading-relaxed">
                                        {ACTION_ICONS[activity.action] || '•'}{' '}
                                        {activity.description || activity.action}
                                    </p>
                                </div>
                            </div>
                        ))
                    ) : (
                        <p className="text-xs text-notion-muted dark:text-[#9B9A97]">
                            لا يوجد نشاط مسجل بعد.
                        </p>
                    )}
                </div>
            </div>
        </aside>
    );
};

