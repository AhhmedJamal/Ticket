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
  Check,
  ChevronRight,
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
    <header className="h-11 flex items-center justify-between px-3 shrink-0 bg-white/95 dark:bg-[#191919]/95 backdrop-blur-sm sticky top-0 z-20 border-b border-notion-border/40 dark:border-[#2A2A2A] select-none text-xs">
      {/* 1. Breadcrumbs Trail */}
      <div className="flex items-center gap-1.5 text-notion-muted min-w-0">
        <button
          type="button"
          onClick={onToggleSidebar}
          className="p-1 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] text-notion-muted hover:text-notion-text dark:hover:text-[#E3E3E0] transition-colors shrink-0"
          title="إخفاء/إظهار القائمة الجانبية"
        >
          <Menu className="w-4 h-4" />
        </button>

        {currentWorkspace && (
          <Link
            href={`/workspaces/${currentWorkspace.id}`}
            className="px-1.5 py-0.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] hover:text-notion-text dark:hover:text-[#E3E3E0] text-notion-muted truncate max-w-[120px] transition-colors"
          >
            {currentWorkspace.name}
          </Link>
        )}

        {currentPage && (
          <>
            <span className="text-notion-muted/40 shrink-0">/</span>
            {currentPage.parent && (
              <>
                <Link
                  href={`/pages/${currentPage.parent.id}`}
                  className="hidden sm:inline-block px-1.5 py-0.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] hover:text-notion-text text-notion-muted truncate max-w-[120px] transition-colors"
                >
                  {currentPage.parent.icon || '📄'} {currentPage.parent.title}
                </Link>
                <span className="text-notion-muted/40 shrink-0 hidden sm:inline">/</span>
              </>
            )}
            <span className="px-1.5 py-0.5 text-notion-text dark:text-[#E3E3E0] font-medium flex items-center gap-1.5 truncate max-w-[160px] sm:max-w-[240px]">
              <span className="shrink-0">{currentPage.icon || '📄'}</span>
              <span className="truncate">{currentPage.title || 'بدون عنوان'}</span>
            </span>
          </>
        )}
      </div>

      {/* 2. Actions & User Profile */}
      <div className="flex items-center gap-1 shrink-0">
        {/* Saved status indicator */}
        <div className="hidden md:flex items-center gap-1 text-[11px] text-notion-muted/70 px-2 select-none">
          <Check className="w-3 h-3 text-green-500 stroke-[2.5]" />
          <span>تم الحفظ</span>
        </div>

        {currentPage && (
          <>
            {/* Favorite Star */}
            <button
              type="button"
              onClick={handleFavoriteClick}
              className="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] text-notion-muted hover:text-notion-text transition-colors"
              title={isFavorite ? 'إزالة من المفضلة' : 'إضافة للمفضلة'}
            >
              <Star
                className={`w-3.5 h-3.5 ${
                  isFavorite ? 'text-yellow-500 fill-yellow-500' : ''
                }`}
              />
            </button>

            {/* Page Overview Drawer trigger */}
            {onToggleOverview && (
              <button
                type="button"
                onClick={onToggleOverview}
                className={`p-1.5 rounded transition-colors ${
                  overviewOpen
                    ? 'bg-notion-hover dark:bg-[#2C2C2C] text-notion-text dark:text-[#E3E3E0]'
                    : 'text-notion-muted hover:bg-notion-hover dark:hover:bg-[#2C2C2C] hover:text-notion-text'
                }`}
                title="سجل التعديلات والنشاط"
              >
                <Info className="w-3.5 h-3.5" />
              </button>
            )}
          </>
        )}

        {/* Quick Search */}
        <button
          type="button"
          onClick={onOpenSearch}
          className="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] text-notion-muted hover:text-notion-text transition-colors"
          title="بحث (Ctrl+K)"
        >
          <Search className="w-3.5 h-3.5" />
        </button>

        {/* Dark Mode toggle */}
        <button
          type="button"
          onClick={toggleTheme}
          className="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] text-notion-muted hover:text-notion-text transition-colors"
          title="تبديل المظهر (Ctrl+Shift+L)"
        >
          {darkMode ? <Sun className="w-3.5 h-3.5" /> : <Moon className="w-3.5 h-3.5" />}
        </button>

        <div className="w-[1px] h-3.5 bg-notion-border dark:bg-[#333333] mx-1" />

        {/* User Account Popover */}
        <div className="relative" ref={menuRef}>
          <button
            type="button"
            onClick={() => setUserMenuOpen(!userMenuOpen)}
            className="flex items-center gap-1.5 p-1 rounded-md hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors"
          >
            <span className="w-5 h-5 rounded-full bg-notion-accent text-white flex items-center justify-center font-bold text-[10px]">
              {user?.name ? user.name.charAt(0).toUpperCase() : 'U'}
            </span>
          </button>

          {userMenuOpen && (
            <div className="absolute left-0 mt-1 w-52 rounded-lg bg-white dark:bg-[#252525] border border-notion-border dark:border-[#383838] shadow-notion py-1 z-50 text-xs animate-in fade-in zoom-in-95 duration-100">
              <div className="px-3 py-2 border-b border-notion-border/60 dark:border-[#333333]">
                <div className="font-semibold text-notion-text dark:text-[#E3E3E0] truncate">
                  {user?.name}
                </div>
                <div className="text-[11px] text-notion-muted truncate">{user?.email}</div>
              </div>

              {currentWorkspace && (
                <Link
                  href={`/workspaces/${currentWorkspace.id}/settings`}
                  onClick={() => setUserMenuOpen(false)}
                  className="flex items-center gap-2 px-3 py-1.5 text-notion-text dark:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2F2F2F] transition-colors"
                >
                  <Settings className="w-3.5 h-3.5 text-notion-muted" />
                  <span>إعدادات مساحة العمل</span>
                </Link>
              )}

              <div className="my-1 border-t border-notion-border/60 dark:border-[#333333]" />

              <button
                type="button"
                onClick={() => {
                  setUserMenuOpen(false);
                  logout();
                }}
                className="w-full flex items-center gap-2 px-3 py-1.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors text-right"
              >
                <LogOut className="w-3.5 h-3.5" />
                <span>تسجيل الخروج</span>
              </button>
            </div>
          )}
        </div>
      </div>
    </header>
  );
};
