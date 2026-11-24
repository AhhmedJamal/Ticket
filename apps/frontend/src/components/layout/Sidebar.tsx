'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { useWorkspace } from '@/lib/workspace-context';
import { SidebarPageItem } from './SidebarPageItem';
import { Page } from '@/types';
import { Home, LayoutTemplate, Search, Settings, Plus, Star, ChevronDown } from 'lucide-react';

interface SidebarProps {
    onOpenSearch: () => void;
}

export const Sidebar: React.FC<SidebarProps> = ({ onOpenSearch }) => {
    const pathname = usePathname();
    const router = useRouter();
    const { currentWorkspace, createPage, deletePage } = useWorkspace();
    const [favoritesOpen, setFavoritesOpen] = useState(true);

    const handleCreateRootPage = async () => {
        const page = await createPage();
        if (page) {
            router.push(`/pages/${page.id}`);
        }
    };

    const handleAddSubpage = async (parentId: number) => {
        const page = await createPage(parentId);
        if (page) {
            router.push(`/pages/${page.id}`);
        }
    };

    const handleDeletePage = async (page: Page) => {
        if (window.confirm(`حذف الصفحة "${page.title}" وجميع صفحاتها الفرعية؟`)) {
            await deletePage(page.id);
            if (pathname === `/pages/${page.id}`) {
                if (currentWorkspace) {
                    router.push(`/workspaces/${currentWorkspace.id}`);
                } else {
                    router.push('/dashboard');
                }
            }
        }
    };

    // Filter root pages (no parent_id)
    const rootPages = currentWorkspace?.pages?.filter((p) => !p.parent_id) || [];
    // Filter favorites
    const favoritePages = currentWorkspace?.pages?.filter((p) => p.is_favorite) || [];

    return (
        <div className="flex flex-col h-full select-none">
            {/* رأس مساحة العمل */}
            <div className="px-2.5 py-2 flex items-center justify-between gap-1 border-b border-notion-border/40 dark:border-[#2A2A2A]">
                {currentWorkspace ? (
                    <Link
                        href={`/workspaces/${currentWorkspace.id}`}
                        className="flex items-center gap-2 overflow-hidden hover:bg-notion-hover dark:hover:bg-[#2C2C2C] px-1.5 py-1 rounded flex-1 transition-colors"
                    >
                        <span className="w-5 h-5 rounded bg-[#37352F] dark:bg-[#3A3A3A] text-white flex items-center justify-center text-[11px] font-semibold shrink-0">
                            {currentWorkspace.name.charAt(0)}
                        </span>
                        <span className="font-medium text-[13px] truncate text-notion-text dark:text-[#E3E3E0]">
                            {currentWorkspace.name}
                        </span>
                    </Link>
                ) : (
                    <span className="font-medium text-[13px] text-notion-text dark:text-[#E3E3E0] px-1.5 py-1">
                        مساحة العمل
                    </span>
                )}

                {currentWorkspace && (
                    <button
                        type="button"
                        onClick={handleCreateRootPage}
                        title="صفحة جديدة"
                        className="p-1 rounded text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors"
                    >
                        <Plus className="w-4 h-4" />
                    </button>
                )}
            </div>

            {/* شريط الاختصارات السريع */}
            {currentWorkspace && (
                <div className="px-2 py-2 flex items-center gap-1 border-b border-notion-border/30 dark:border-[#252525]">
                    <Link
                        href={`/workspaces/${currentWorkspace.id}`}
                        className={`flex-1 flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors ${
                            pathname === `/workspaces/${currentWorkspace.id}`
                                ? 'bg-notion-hover dark:bg-[#2C2C2C] text-notion-text dark:text-[#E3E3E0] ring-1 ring-[#3A3A3A]'
                                : 'bg-notion-hover/60 dark:bg-[#2A2A2A] hover:bg-notion-hover dark:hover:bg-[#323232] text-notion-text dark:text-[#E3E3E0]'
                        }`}
                    >
                        <Home className="w-3.5 h-3.5 shrink-0" />
                        <span>الرئيسية</span>
                    </Link>

                    <Link
                        href={`/workspaces/${currentWorkspace.id}/templates`}
                        title="القوالب"
                        className={`p-1.5 rounded-md hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors ${
                            pathname.includes('/templates')
                                ? 'bg-notion-hover dark:bg-[#2C2C2C] text-notion-text dark:text-[#E3E3E0]'
                                : 'text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0]'
                        }`}
                    >
                        <LayoutTemplate className="w-4 h-4" />
                    </Link>

                    <button
                        type="button"
                        onClick={onOpenSearch}
                        title="بحث (Ctrl+K)"
                        className="p-1.5 rounded-md hover:bg-notion-hover dark:hover:bg-[#2C2C2C] text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] transition-colors"
                    >
                        <Search className="w-4 h-4" />
                    </button>
                </div>
            )}

            {/* قائمة الصفحات والمفضلة */}
            <div className="flex-1 overflow-y-auto px-2 py-2 space-y-3">
                {/* المفضلة */}
                {favoritePages.length > 0 && (
                    <div>
                        <div className="flex items-center justify-between px-2 py-1 text-[11px] font-medium text-notion-muted dark:text-[#7F7F7F]">
                            <button
                                type="button"
                                onClick={() => setFavoritesOpen(!favoritesOpen)}
                                className="flex items-center gap-1 hover:text-notion-text dark:hover:text-[#E3E3E0]"
                            >
                                <Star className="w-3 h-3 text-yellow-400 fill-yellow-400" />
                                <span>المفضلة</span>
                                <ChevronDown
                                    className={`w-3 h-3 transition-transform ${
                                        !favoritesOpen ? 'rotate-90' : ''
                                    }`}
                                />
                            </button>
                        </div>
                        {favoritesOpen && (
                            <div className="space-y-0.5 mt-0.5">
                                {favoritePages.map((favPage) => (
                                    <div
                                        key={favPage.id}
                                        className={`sidebar-item group flex items-center justify-between px-2 py-1 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] cursor-pointer transition-colors ${
                                            pathname === `/pages/${favPage.id}`
                                                ? 'bg-notion-hover dark:bg-[#2C2C2C] font-medium'
                                                : ''
                                        }`}
                                    >
                                        <Link
                                            href={`/pages/${favPage.id}`}
                                            className="flex items-center gap-1.5 flex-1 min-w-0"
                                        >
                                            <span className="text-sm shrink-0">{favPage.icon || '📄'}</span>
                                            <span className="truncate text-notion-text dark:text-[#E3E3E0] text-[13px]">
                                                {favPage.title || 'بدون عنوان'}
                                            </span>
                                        </Link>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                )}

                {/* الصفحات */}
                <div>
                    <div className="flex items-center justify-between px-2 py-1 text-[11px] font-medium text-notion-muted dark:text-[#7F7F7F]">
                        <span>الصفحات</span>
                        {currentWorkspace && (
                            <button
                                type="button"
                                onClick={handleCreateRootPage}
                                title="إضافة صفحة رئيسية جديدة"
                                className="hover:text-notion-text dark:hover:text-[#E3E3E0] p-0.5 hover:bg-notion-hover dark:hover:bg-[#2C2C2C] rounded transition-colors"
                            >
                                <Plus className="w-3.5 h-3.5" />
                            </button>
                        )}
                    </div>

                    {rootPages.length > 0 ? (
                        <div className="space-y-0.5 mt-0.5">
                            {rootPages.map((page) => (
                                <SidebarPageItem
                                    key={page.id}
                                    page={page}
                                    level={0}
                                    onAddSubpage={handleAddSubpage}
                                    onDeletePage={handleDeletePage}
                                />
                            ))}
                        </div>
                    ) : (
                        <div className="px-2 py-3 text-xs text-notion-muted dark:text-[#7F7F7F]">
                            لا توجد صفحات بعد
                        </div>
                    )}
                </div>
            </div>

            {/* أسفل السايدبار: الإعدادات والأعضاء */}
            {currentWorkspace && (
                <div className="p-2 border-t border-notion-border dark:border-[#2A2A2A] space-y-0.5">
                    <Link
                        href={`/workspaces/${currentWorkspace.id}/settings`}
                        className={`flex items-center gap-2 px-2 py-1.5 text-[13px] rounded transition-colors ${
                            pathname.includes('/settings')
                                ? 'bg-notion-hover dark:bg-[#2C2C2C] text-notion-text dark:text-[#E3E3E0]'
                                : 'text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2C2C2C]'
                        }`}
                    >
                        <Settings className="w-3.5 h-3.5 shrink-0" />
                        <span>الإعدادات والأعضاء</span>
                    </Link>
                </div>
            )}
        </div>
    );
};

