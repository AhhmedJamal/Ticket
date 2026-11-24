'use client';

import React, { useState, useRef, useEffect } from 'react';
import Link from 'next/link';
import { useAuth } from '@/lib/auth-context';
import { useWorkspace } from '@/lib/workspace-context';
import { useTheme } from '@/lib/theme-context';
import { Page } from '@/types';
import {
    Menu,
    Search,
    Sun,
    Moon,
    Star,
    Info,
    Download,
    LogOut,
    Settings,
} from 'lucide-react';

interface TopbarProps {
    onToggleSidebar: () => void;
    onOpenSearch: () => void;
    onToggleOverview?: () => void;
    overviewOpen?: boolean;
    currentPage?: Page | null;
}

export const Topbar: React.FC<TopbarProps> = ({
    onToggleSidebar,
    onOpenSearch,
    onToggleOverview,
    overviewOpen,
    currentPage,
}) => {
    const { user, logout } = useAuth();
    const { currentWorkspace, togglePageFavorite } = useWorkspace();
    const { darkMode, toggleTheme } = useTheme();
    const [userMenuOpen, setUserMenuOpen] = useState(false);
    const [isFavorite, setIsFavorite] = useState(currentPage?.is_favorite ?? false);
    const menuRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        setIsFavorite(currentPage?.is_favorite ?? false);
    }, [currentPage?.is_favorite]);

    useEffect(() => {
        const handleClickOutside = (e: MouseEvent) => {
            if (menuRef.current && !menuRef.current.contains(e.target as Node)) {
                setUserMenuOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const handleFavoriteClick = async () => {
        if (!currentPage) return;
        const newFav = await togglePageFavorite(currentPage.id);
        setIsFavorite(newFav);
    };

    return (
        <header className="h-11 flex items-center justify-between px-2 sm:px-3 shrink-0 bg-white/95 dark:bg-[#191919]/95 backdrop-blur-sm sticky top-0 z-20 border-b border-notion-border/50 dark:border-[#2A2A2A]">
            {/* الجزء الأيمن: زر القائمة الجانبية ومسار التنقل (Breadcrumbs) */}
            <div className="flex items-center gap-1 sm:gap-1.5 text-[13px] text-notion-muted min-w-0">
                <button
                    type="button"
                    onClick={onToggleSidebar}
                    className="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] text-notion-muted hover:text-notion-text dark:hover:text-[#E3E3E0] transition-colors shrink-0"
                    title="إخفاء/إظهار القائمة الجانبية"
                >
                    <Menu className="w-4 h-4" />
                </button>

                {currentWorkspace && (
                    <Link
                        href={`/workspaces/${currentWorkspace.id}`}
                        className="px-1.5 py-0.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] hover:text-notion-text dark:hover:text-[#E3E3E0] text-notion-muted truncate max-w-[90px] sm:max-w-[140px] transition-colors"
                    >
                        {currentWorkspace.name}
                    </Link>
                )}

                {currentPage && (
                    <>
                        <span className="text-notion-muted/40 hidden sm:inline">/</span>
                        {currentPage.parent && (
                            <>
                                <Link
                                    href={`/pages/${currentPage.parent.id}`}
                                    className="hidden sm:inline-block px-1.5 py-0.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] hover:text-notion-text dark:hover:text-[#E3E3E0] text-notion-muted truncate max-w-[140px] transition-colors"
                                >
                                    {currentPage.parent.icon || '📄'} {currentPage.parent.title}
                                </Link>
                                <span className="text-notion-muted/40 hidden sm:inline">/</span>
                            </>
                        )}
                        <span className="px-1.5 py-0.5 text-notion-text dark:text-[#E3E3E0] flex items-center gap-1.5 truncate max-w-[110px] sm:max-w-[220px]">
                            <span>{currentPage.icon || '📄'}</span>
                            <span className="truncate">{currentPage.title || 'بدون عنوان'}</span>
                        </span>
                    </>
                )}
            </div>

            {/* الجزء الأيسر: الإجراءات السريعة والمستخدم */}
            <div className="flex items-center gap-0.5 shrink-0">
                {currentPage && (
                    <>
                        {/* تصدير Markdown */}
                        <a
                            href={`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1'}/pages/${currentPage.id}/export/markdown`}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="px-2 py-1 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] text-xs text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] transition-colors"
                            title="تصدير بصيغة Markdown"
                        >
                            <Download className="w-3.5 h-3.5 inline ml-1" />
                            تصدير
                        </a>

                        {/* المفضلة */}
                        <button
                            type="button"
                            onClick={handleFavoriteClick}
                            className="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] transition-colors"
                            title={isFavorite ? 'إزالة من المفضلة' : 'إضافة للمفضلة'}
                        >
                            <Star
                                className={`w-4 h-4 ${
                                    isFavorite ? 'text-yellow-400 fill-yellow-400' : ''
                                }`}
                            />
                        </button>

                        {/* لوحة التفاصيل والنشاط */}
                        {onToggleOverview && (
                            <button
                                type="button"
                                onClick={onToggleOverview}
                                className={`p-1.5 rounded transition-colors ${
                                    overviewOpen
                                        ? 'bg-notion-hover dark:bg-[#2C2C2C] text-notion-text dark:text-[#E3E3E0]'
                                        : 'text-notion-muted dark:text-[#9B9A97] hover:bg-notion-hover dark:hover:bg-[#2C2C2C]'
                                }`}
                                title="تفاصيل الصفحة وسجل التعديلات"
                            >
                                <Info className="w-4 h-4" />
                            </button>
                        )}
                    </>
                )}

                {/* زر البحث */}
                <button
                    type="button"
                    onClick={onOpenSearch}
                    className="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] transition-colors"
                    title="بحث (Ctrl+K)"
                >
                    <Search className="w-4 h-4" />
                </button>

                {/* زر الوضع الليلي */}
                <button
                    type="button"
                    onClick={toggleTheme}
                    className="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] text-notion-muted hover:text-notion-text dark:hover:text-[#E3E3E0] transition-colors"
                    title="تبديل المظهر (Ctrl+Shift+L)"
                >
                    {darkMode ? <Sun className="w-4 h-4" /> : <Moon className="w-4 h-4" />}
                </button>

                {/* قائمة المستخدم */}
                {user && (
                    <div className="relative" ref={menuRef}>
                        <button
                            type="button"
                            onClick={() => setUserMenuOpen(!userMenuOpen)}
                            className="p-1 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors"
                            title={user.name}
                        >
                            <span className="w-5 h-5 rounded-full bg-[#3A3A3A] text-[#E3E3E0] flex items-center justify-center text-[11px] font-medium">
                                {user.name.charAt(0)}
                            </span>
                        </button>

                        {userMenuOpen && (
                            <div className="absolute left-0 mt-1.5 w-48 bg-white dark:bg-[#252525] border border-notion-border dark:border-[#333333] rounded-lg shadow-2xl py-1 z-50">
                                <div className="px-3 py-1.5 border-b border-notion-border dark:border-[#333333]">
                                    <div className="text-xs font-medium text-notion-text dark:text-[#E3E3E0] truncate">
                                        {user.name}
                                    </div>
                                    <div className="text-[11px] text-notion-muted truncate">
                                        {user.email}
                                    </div>
                                </div>

                                {currentWorkspace && (
                                    <Link
                                        href={`/workspaces/${currentWorkspace.id}/settings`}
                                        onClick={() => setUserMenuOpen(false)}
                                        className="flex items-center gap-2 px-3 py-1.5 text-xs text-notion-text dark:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors"
                                    >
                                        <Settings className="w-3.5 h-3.5" />
                                        <span>إعدادات مساحة العمل</span>
                                    </Link>
                                )}

                                <button
                                    type="button"
                                    onClick={() => {
                                        setUserMenuOpen(false);
                                        logout();
                                    }}
                                    className="w-full text-right flex items-center gap-2 px-3 py-1.5 text-xs text-red-500 dark:text-red-400 hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors"
                                >
                                    <LogOut className="w-3.5 h-3.5" />
                                    <span>تسجيل الخروج</span>
                                </button>
                            </div>
                        )}
                    </div>
                )}
            </div>
        </header>
    );
};

