'use client';

import React, { useState, useEffect } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import { useAuth } from '@/lib/auth-context';
import { useWorkspace } from '@/lib/workspace-context';
import { workspaceService } from '@/services/api';
import { Workspace } from '@/types';
import { useToast } from '@/lib/toast-context';
import {
  Edit2,
  Plus,
  LayoutTemplate,
  Star,
  ArrowLeft,
  Loader2,
  Settings,
  FileText,
  Clock,
  Sparkles,
} from 'lucide-react';
import { timeAgo } from '@/lib/utils';

const COVER_GRADIENTS: Record<string, string> = {
  'gradient-blue': 'bg-gradient-to-r from-blue-400/80 to-indigo-500/80',
  'gradient-purple': 'bg-gradient-to-r from-purple-400/80 to-pink-500/80',
  'gradient-green': 'bg-gradient-to-r from-emerald-400/80 to-teal-500/80',
  'gradient-orange': 'bg-gradient-to-r from-amber-400/80 to-rose-400/80',
  'gradient-gray': 'bg-gradient-to-r from-zinc-300 to-zinc-500',
};

export default function WorkspaceShowPage() {
  const params = useParams();
  const router = useRouter();
  const workspaceId = Number(params?.id);
  const { user } = useAuth();
  const {
    currentWorkspace,
    setCurrentWorkspace,
    createPage,
    refreshCurrentWorkspace,
  } = useWorkspace();
  const { showToast } = useToast();

  const [workspace, setWorkspace] = useState<Workspace | null>(
    currentWorkspace?.id === workspaceId ? currentWorkspace : null
  );
  const [isLoading, setIsLoading] = useState(!workspace);
  const [isEditingName, setIsEditingName] = useState(false);
  const [nameInput, setNameInput] = useState('');
  const [isSavingName, setIsSavingName] = useState(false);

  useEffect(() => {
    let isMounted = true;
    const loadWorkspace = async () => {
      try {
        const data = await workspaceService.getById(workspaceId);
        if (isMounted) {
          setWorkspace(data);
          setNameInput(data.name);
          setCurrentWorkspace(data);
        }
      } catch {
        showToast('فشل تحميل مساحة العمل', 'error');
      } finally {
        if (isMounted) setIsLoading(false);
      }
    };

    loadWorkspace();
    return () => {
      isMounted = false;
    };
  }, [workspaceId]);

  const canEditWorkspace =
    user &&
    workspace &&
    (workspace.user_id === user.id ||
      workspace.members?.some(
        (m) => m.id === user.id && m.pivot?.role === 'admin'
      ));

  const handleUpdateName = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!nameInput.trim() || !workspace) return;

    setIsSavingName(true);
    try {
      const updated = await workspaceService.update(workspace.id, {
        name: nameInput.trim(),
      });
      setWorkspace((prev) => (prev ? { ...prev, name: updated.name } : updated));
      setCurrentWorkspace(updated);
      setIsEditingName(false);
      showToast('تم تحديث اسم مساحة العمل', 'success');
    } catch {
      showToast('فشل تحديث اسم مساحة العمل', 'error');
    } finally {
      setIsSavingName(false);
    }
  };

  const handleCreatePage = async () => {
    const page = await createPage();
    if (page) {
      router.push(`/pages/${page.id}`);
    }
  };

  if (isLoading) {
    return (
      <div className="py-32 flex items-center justify-center">
        <Loader2 className="w-7 h-7 animate-spin text-notion-muted" />
      </div>
    );
  }

  if (!workspace) {
    return (
      <div className="py-32 text-center">
        <p className="text-notion-muted text-sm">مساحة العمل غير موجودة.</p>
      </div>
    );
  }

  const allPages = workspace.pages || [];
  const rootPages = allPages.filter((p) => !p.parent_id);
  const favoritePages = allPages.filter((p) => p.is_favorite);

  return (
    <div className="max-w-4xl mx-auto py-8 px-4 sm:px-6 space-y-8 select-none">
      {/* 1. Workspace Hero Header */}
      <div className="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-notion-border/60 dark:border-[#2A2A2A]">
        <div className="min-w-0 flex-1">
          <div className="text-[11px] font-medium text-notion-muted uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
            <span className="w-2 h-2 rounded-full bg-notion-accent" />
            <span>مساحة العمل النشطة</span>
          </div>

          {!isEditingName ? (
            <div className="flex items-center gap-2.5 flex-wrap">
              <h1 className="text-2xl sm:text-3xl font-bold text-notion-text dark:text-[#E3E3E0] flex items-center gap-2.5 min-w-0">
                <span className="text-2xl select-none">🏠</span>
                <span className="truncate">{workspace.name}</span>
              </h1>
              {canEditWorkspace && (
                <button
                  type="button"
                  onClick={() => {
                    setIsEditingName(true);
                    setNameInput(workspace.name);
                  }}
                  title="تعديل اسم مساحة العمل"
                  className="p-1 rounded text-notion-muted hover:text-notion-text hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors"
                >
                  <Edit2 className="w-3.5 h-3.5" />
                </button>
              )}
            </div>
          ) : (
            <form onSubmit={handleUpdateName} className="flex flex-wrap items-center gap-2 mt-1">
              <input
                type="text"
                value={nameInput}
                onChange={(e) => setNameInput(e.target.value)}
                required
                autoFocus
                className="flex-1 min-w-[200px] max-w-xs px-3 py-1.5 text-sm font-semibold bg-white dark:bg-[#1E1E1E] border border-notion-border dark:border-[#3A3A3A] rounded-md text-notion-text dark:text-[#E3E3E0] focus:outline-none focus:ring-1 focus:ring-notion-accent"
              />
              <button
                type="submit"
                disabled={isSavingName}
                className="px-3 py-1.5 bg-notion-text dark:bg-[#E3E3E0] text-white dark:text-[#191919] rounded-md text-xs font-medium hover:opacity-90 transition-opacity disabled:opacity-50"
              >
                {isSavingName ? 'حفظ...' : 'حفظ'}
              </button>
              <button
                type="button"
                onClick={() => setIsEditingName(false)}
                className="px-3 py-1.5 bg-notion-sidebar dark:bg-[#252525] text-notion-muted border border-notion-border dark:border-[#333333] rounded-md text-xs hover:text-notion-text transition-colors"
              >
                إلغاء
              </button>
            </form>
          )}

          {/* Stat Pills */}
          <div className="flex items-center gap-3 text-xs text-notion-muted mt-2">
            <span>{allPages.length} صفحة</span>
            <span>•</span>
            <span>{favoritePages.length} في المفضلة</span>
            <span>•</span>
            <span>المالك: {workspace.owner?.name || 'أنت'}</span>
          </div>
        </div>

        {/* Header Action Buttons */}
        <div className="flex items-center gap-2 shrink-0">
          <button
            type="button"
            onClick={handleCreatePage}
            className="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-notion-text dark:bg-[#E3E3E0] text-white dark:text-[#191919] rounded-md text-xs font-medium hover:opacity-90 transition-opacity shadow-xs"
          >
            <Plus className="w-3.5 h-3.5" />
            <span>صفحة جديدة</span>
          </button>
          <Link
            href={`/workspaces/${workspace.id}/templates`}
            className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-[#252525] text-notion-text dark:text-[#E3E3E0] border border-notion-border dark:border-[#333333] rounded-md text-xs font-medium hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors"
          >
            <LayoutTemplate className="w-3.5 h-3.5 text-notion-muted" />
            <span>القوالب</span>
          </Link>
          <Link
            href={`/workspaces/${workspace.id}/settings`}
            className="p-1.5 bg-white dark:bg-[#252525] text-notion-muted hover:text-notion-text border border-notion-border dark:border-[#333333] rounded-md transition-colors"
            title="الإعدادات والأعضاء"
          >
            <Settings className="w-3.5 h-3.5" />
          </Link>
        </div>
      </div>

      {/* 2. Pinned Favorites Section */}
      {favoritePages.length > 0 && (
        <div className="space-y-3">
          <div className="flex items-center gap-1.5 text-xs font-semibold text-notion-muted">
            <Star className="w-3.5 h-3.5 text-yellow-500 fill-yellow-500" />
            <span>الصفحات المفضلة</span>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            {favoritePages.map((page) => (
              <Link
                key={page.id}
                href={`/pages/${page.id}`}
                className="group flex flex-col justify-between p-3.5 rounded-lg border border-notion-border dark:border-[#2E2E2E] bg-white dark:bg-[#202020] hover:bg-notion-hover/50 dark:hover:bg-[#282828] transition-all shadow-xs"
              >
                <div className="flex items-center gap-2 mb-2">
                  <span className="text-xl shrink-0">{page.icon || '📄'}</span>
                  <span className="font-semibold text-xs text-notion-text dark:text-[#E3E3E0] truncate group-hover:text-notion-accent transition-colors">
                    {page.title || 'بدون عنوان'}
                  </span>
                </div>
                <div className="flex items-center justify-between text-[11px] text-notion-muted">
                  <span>{timeAgo(page.created_at)}</span>
                  <ArrowLeft className="w-3 h-3 text-notion-muted group-hover:text-notion-text group-hover:-translate-x-1 transition-all" />
                </div>
              </Link>
            ))}
          </div>
        </div>
      )}

      {/* 3. All Workspace Root Pages */}
      <div className="space-y-3">
        <div className="flex items-center justify-between text-xs font-semibold text-notion-muted">
          <span>كل الصفحات ({rootPages.length})</span>
          <button
            type="button"
            onClick={handleCreatePage}
            className="text-notion-accent hover:underline flex items-center gap-1 font-normal"
          >
            <Plus className="w-3 h-3" />
            <span>إضافة صفحة</span>
          </button>
        </div>

        {rootPages.length > 0 ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5">
            {rootPages.map((page) => {
              const coverBg = page.cover_color
                ? COVER_GRADIENTS[page.cover_color] || 'bg-notion-sidebar dark:bg-[#252525]'
                : 'bg-notion-sidebar dark:bg-[#252525]';

              return (
                <Link
                  key={page.id}
                  href={`/pages/${page.id}`}
                  className="group flex flex-col rounded-lg border border-notion-border dark:border-[#2E2E2E] bg-white dark:bg-[#202020] hover:border-notion-border dark:hover:border-[#383838] hover:shadow-sm transition-all overflow-hidden"
                >
                  {/* Card Cover strip */}
                  <div className={`h-14 ${coverBg} flex items-center justify-center relative`}>
                    <span className="text-2xl select-none">{page.icon || '📄'}</span>
                  </div>

                  {/* Card Body */}
                  <div className="p-3 flex-1 flex flex-col justify-between">
                    <div>
                      <div className="font-semibold text-xs text-notion-text dark:text-[#E3E3E0] truncate group-hover:text-notion-accent transition-colors">
                        {page.title || 'بدون عنوان'}
                      </div>
                      {page.children && page.children.length > 0 && (
                        <div className="text-[11px] text-notion-muted mt-0.5">
                          {page.children.length} صفحات فرعية
                        </div>
                      )}
                    </div>

                    <div className="flex items-center justify-between text-[10px] text-notion-muted mt-3 pt-2 border-t border-notion-border/40 dark:border-[#2A2A2A]">
                      <span className="flex items-center gap-1">
                        <Clock className="w-2.5 h-2.5" />
                        <span>{timeAgo(page.created_at)}</span>
                      </span>
                      <ArrowLeft className="w-3 h-3 text-notion-muted group-hover:text-notion-text group-hover:-translate-x-1 transition-all" />
                    </div>
                  </div>
                </Link>
              );
            })}
          </div>
        ) : (
          <div className="py-16 text-center rounded-lg border border-dashed border-notion-border dark:border-[#333333] space-y-3">
            <FileText className="w-8 h-8 mx-auto text-notion-muted opacity-40" />
            <div className="text-xs font-medium text-notion-text dark:text-[#E3E3E0]">
              لا توجد صفحات في هذه المساحة بعد
            </div>
            <button
              type="button"
              onClick={handleCreatePage}
              className="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-notion-text dark:bg-[#E3E3E0] text-white dark:text-[#191919] text-xs font-medium hover:opacity-90 transition-opacity"
            >
              <Plus className="w-3 h-3" />
              <span>إنشاء أول صفحة</span>
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
