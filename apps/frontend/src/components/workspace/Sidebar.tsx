'use client';

import React, { useState, useRef, useEffect } from 'react';
import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { useWorkspace } from '@/lib/workspace-context';
import { SidebarPageItem } from './SidebarPageItem';
import { TrashModal } from './TrashModal';
import { Page } from '@/types';
import {
  Home,
  LayoutTemplate,
  Search,
  Settings,
  Plus,
  Star,
  ChevronDown,
  Trash2,
  Check,
  FolderOpen,
} from 'lucide-react';

interface SidebarProps {
  onOpenSearch: () => void;
}

export const Sidebar: React.FC<SidebarProps> = ({ onOpenSearch }) => {
  const pathname = usePathname();
  const router = useRouter();
  const {
    workspaces,
    currentWorkspace,
    setCurrentWorkspace,
    createPage,
    deletePage,
  } = useWorkspace();

  const [favoritesOpen, setFavoritesOpen] = useState(true);
  const [workspaceMenuOpen, setWorkspaceMenuOpen] = useState(false);
  const [trashOpen, setTrashOpen] = useState(false);
  const [deletedPagesList, setDeletedPagesList] = useState<Page[]>([]);

  const wsDropdownRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (wsDropdownRef.current && !wsDropdownRef.current.contains(e.target as Node)) {
        setWorkspaceMenuOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

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
    if (window.confirm(`حذف الصفحة "${page.title}"؟`)) {
      setDeletedPagesList((prev) => [page, ...prev]);
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

  const rootPages = currentWorkspace?.pages?.filter((p) => !p.parent_id) || [];
  const favoritePages = currentWorkspace?.pages?.filter((p) => p.is_favorite) || [];

  return (
    <div className="flex flex-col h-full select-none bg-notion-sidebar dark:bg-[#202020] text-notion-text dark:text-[#E3E3E0] text-xs">
      {/* 1. Workspace Header with Dropdown Switcher */}
      <div className="p-2 border-b border-notion-border/40 dark:border-[#2A2A2A] relative" ref={wsDropdownRef}>
        <button
          type="button"
          onClick={() => setWorkspaceMenuOpen(!workspaceMenuOpen)}
          className="w-full flex items-center justify-between p-1.5 rounded-md hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors"
        >
          <div className="flex items-center gap-2 min-w-0">
            <span className="w-5 h-5 rounded-md bg-[#37352F] dark:bg-[#3A3A3A] text-white flex items-center justify-center text-[11px] font-bold shrink-0">
              {currentWorkspace ? currentWorkspace.name.charAt(0) : 'T'}
            </span>
            <span className="font-semibold text-xs truncate">
              {currentWorkspace ? currentWorkspace.name : 'مساحة العمل'}
            </span>
          </div>
          <ChevronDown className="w-3.5 h-3.5 text-notion-muted shrink-0" />
        </button>

        {/* Workspace Switcher Menu */}
        {workspaceMenuOpen && (
          <div className="absolute top-full left-2 right-2 mt-1 z-50 rounded-lg bg-white dark:bg-[#252525] border border-notion-border dark:border-[#383838] shadow-notion py-1 animate-in fade-in zoom-in-95 duration-100">
            <div className="px-3 py-1 text-[10px] font-semibold text-notion-muted uppercase">
              مساحات العمل
            </div>
            {workspaces.map((ws) => {
              const isSelected = ws.id === currentWorkspace?.id;
              return (
                <button
                  key={ws.id}
                  type="button"
                  onClick={() => {
                    setCurrentWorkspace(ws);
                    setWorkspaceMenuOpen(false);
                    router.push(`/workspaces/${ws.id}`);
                  }}
                  className={`w-full flex items-center justify-between px-3 py-1.5 text-xs text-right transition-colors ${
                    isSelected
                      ? 'bg-notion-hover dark:bg-[#323232] font-semibold'
                      : 'hover:bg-notion-hover dark:hover:bg-[#2F2F2F]'
                  }`}
                >
                  <div className="flex items-center gap-2 truncate">
                    <span className="w-4 h-4 rounded bg-[#37352F] text-white flex items-center justify-center text-[10px] font-bold shrink-0">
                      {ws.name.charAt(0)}
                    </span>
                    <span className="truncate">{ws.name}</span>
                  </div>
                  {isSelected && <Check className="w-3.5 h-3.5 text-notion-accent" />}
                </button>
              );
            })}

            <div className="my-1 border-t border-notion-border dark:border-[#333333]" />
            <Link
              href="/dashboard"
              onClick={() => setWorkspaceMenuOpen(false)}
              className="flex items-center gap-2 px-3 py-1.5 text-xs text-notion-accent hover:bg-notion-hover dark:hover:bg-[#2F2F2F] transition-colors"
            >
              <Plus className="w-3.5 h-3.5" />
              <span>إنشاء مساحة عمل جديدة</span>
            </Link>
          </div>
        )}
      </div>

      {/* 2. Quick Action Items (Search, Home, Templates, New Page) */}
      <div className="px-2 py-2 space-y-0.5 border-b border-notion-border/40 dark:border-[#2A2A2A]">
        {/* Quick Search with shortcut */}
        <button
          type="button"
          onClick={onOpenSearch}
          className="w-full flex items-center justify-between px-2.5 py-1.5 rounded-md text-notion-muted hover:text-notion-text hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors"
        >
          <div className="flex items-center gap-2">
            <Search className="w-3.5 h-3.5 shrink-0" />
            <span className="font-medium text-xs">بحث سريع</span>
          </div>
          <span className="kbd text-[9px]">Ctrl+K</span>
        </button>

        {currentWorkspace && (
          <Link
            href={`/workspaces/${currentWorkspace.id}`}
            className={`w-full flex items-center gap-2 px-2.5 py-1.5 rounded-md font-medium transition-colors ${
              pathname === `/workspaces/${currentWorkspace.id}`
                ? 'bg-notion-hover dark:bg-[#2C2C2C] text-notion-text dark:text-[#E3E3E0]'
                : 'text-notion-muted hover:text-notion-text hover:bg-notion-hover dark:hover:bg-[#2C2C2C]'
            }`}
          >
            <Home className="w-3.5 h-3.5 shrink-0" />
            <span>الرئيسية</span>
          </Link>
        )}

        {currentWorkspace && (
          <Link
            href={`/workspaces/${currentWorkspace.id}/templates`}
            className={`w-full flex items-center gap-2 px-2.5 py-1.5 rounded-md font-medium transition-colors ${
              pathname.includes('/templates')
                ? 'bg-notion-hover dark:bg-[#2C2C2C] text-notion-text dark:text-[#E3E3E0]'
                : 'text-notion-muted hover:text-notion-text hover:bg-notion-hover dark:hover:bg-[#2C2C2C]'
            }`}
          >
            <LayoutTemplate className="w-3.5 h-3.5 shrink-0" />
            <span>معرض القوالب</span>
          </Link>
        )}
      </div>

      {/* 3. Pages & Favorites Tree */}
      <div className="flex-1 overflow-y-auto px-2 py-2 space-y-3">
        {/* Favorites section */}
        {favoritePages.length > 0 && (
          <div>
            <div className="flex items-center justify-between px-2 py-1 text-[11px] font-medium text-notion-muted">
              <button
                type="button"
                onClick={() => setFavoritesOpen(!favoritesOpen)}
                className="flex items-center gap-1 hover:text-notion-text"
              >
                <Star className="w-3 h-3 text-yellow-500 fill-yellow-500" />
                <span>المفضلة</span>
                <span className="text-[10px] text-notion-muted/70 mr-0.5">({favoritePages.length})</span>
                <ChevronDown
                  className={`w-3 h-3 transition-transform ${!favoritesOpen ? 'rotate-90' : ''}`}
                />
              </button>
            </div>

            {favoritesOpen && (
              <div className="space-y-0.5 mt-0.5">
                {favoritePages.map((favPage) => (
                  <Link
                    key={favPage.id}
                    href={`/pages/${favPage.id}`}
                    className={`flex items-center gap-2 px-2 py-1 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors ${
                      pathname === `/pages/${favPage.id}`
                        ? 'bg-notion-hover dark:bg-[#2C2C2C] font-semibold'
                        : ''
                    }`}
                  >
                    <span className="text-sm shrink-0">{favPage.icon || '📄'}</span>
                    <span className="truncate text-xs">{favPage.title || 'بدون عنوان'}</span>
                  </Link>
                ))}
              </div>
            )}
          </div>
        )}

        {/* All Pages section */}
        <div>
          <div className="flex items-center justify-between px-2 py-1 text-[11px] font-medium text-notion-muted">
            <div className="flex items-center gap-1.5">
              <span>الصفحات</span>
              <span className="text-[10px] text-notion-muted/70">({rootPages.length})</span>
            </div>
            {currentWorkspace && (
              <button
                type="button"
                onClick={handleCreateRootPage}
                title="إضافة صفحة جديدة"
                className="p-0.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] hover:text-notion-text transition-colors"
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
            <div className="px-2 py-4 text-center text-xs text-notion-muted/70">
              لا توجد صفحات بعد
            </div>
          )}
        </div>
      </div>

      {/* 4. Bottom Footer: Trash Bin & Settings */}
      <div className="p-2 border-t border-notion-border/40 dark:border-[#2A2A2A] space-y-0.5">
        {/* Trash Bin */}
        <button
          type="button"
          onClick={() => setTrashOpen(true)}
          className="w-full flex items-center justify-between px-2.5 py-1.5 text-xs text-notion-muted hover:text-notion-text hover:bg-notion-hover dark:hover:bg-[#2C2C2C] rounded transition-colors"
        >
          <div className="flex items-center gap-2">
            <Trash2 className="w-3.5 h-3.5 shrink-0" />
            <span>سلة المحذوفات</span>
          </div>
          {deletedPagesList.length > 0 && (
            <span className="text-[10px] px-1.5 py-0.2 rounded-full bg-red-500/10 text-red-500 font-semibold">
              {deletedPagesList.length}
            </span>
          )}
        </button>

        {/* Settings & Members */}
        {currentWorkspace && (
          <Link
            href={`/workspaces/${currentWorkspace.id}/settings`}
            className={`w-full flex items-center gap-2 px-2.5 py-1.5 text-xs rounded transition-colors ${
              pathname.includes('/settings')
                ? 'bg-notion-hover dark:bg-[#2C2C2C] text-notion-text dark:text-[#E3E3E0] font-semibold'
                : 'text-notion-muted hover:text-notion-text hover:bg-notion-hover dark:hover:bg-[#2C2C2C]'
            }`}
          >
            <Settings className="w-3.5 h-3.5 shrink-0" />
            <span>الإعدادات والأعضاء</span>
          </Link>
        )}
      </div>

      {/* Trash Bin Modal */}
      <TrashModal
        isOpen={trashOpen}
        onClose={() => setTrashOpen(false)}
        deletedPages={deletedPagesList}
      />
    </div>
  );
};
