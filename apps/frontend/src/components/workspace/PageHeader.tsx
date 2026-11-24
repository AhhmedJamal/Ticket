'use client';

import React, { useState } from 'react';
import { Page, Tag, Block } from '@/types';
import { CoverPicker } from './CoverPicker';
import { EmojiPicker } from './EmojiPicker';
import { TagsManager } from '@/components/editor/TagsManager';
import {
  Users,
  Star,
  Lock,
  Unlock,
  Maximize2,
  Minimize2,
  Download,
  Clock,
  User as UserIcon,
  Printer,
  Copy,
  Check,
  MoreHorizontal,
  Tag as TagIcon,
  Calendar,
  MessageSquare,
  Image as ImageIcon,
  ChevronDown,
  ChevronLeft,
  SlidersHorizontal,
} from 'lucide-react';
import { formatDate, timeAgo } from '@/lib/utils';
import { pageService } from '@/services/api';
import { useToast } from '@/lib/toast-context';

interface PageHeaderProps {
  page: Page;
  title: string;
  icon: string;
  coverColor: string | null;
  coverImage: string | null;
  showCoverPicker: boolean;
  userRole?: string;
  workspaceTags?: Tag[];
  blocks?: Block[];
  isLocked?: boolean;
  isFullWidth?: boolean;
  setShowCoverPicker: (show: boolean) => void;
  onUpdateTitle: (title: string) => void;
  onSelectEmoji: (emoji: string) => void;
  onSelectCoverColor: (color: string) => void;
  onRemoveCover: () => void;
  onOpenShareModal: () => void;
  onAttachTag: (tagId: number) => void;
  onCreateAndAttachTag: (name: string, color: string) => void;
  onDetachTag: (tagId: number) => void;
  onToggleFavorite?: () => void;
  onToggleLock?: () => void;
  onToggleFullWidth?: () => void;
  onOpenComments?: () => void;
}

export const PageHeader: React.FC<PageHeaderProps> = ({
  page,
  title,
  icon,
  coverColor,
  coverImage,
  showCoverPicker,
  userRole = 'editor',
  workspaceTags = [],
  blocks = [],
  isLocked = false,
  isFullWidth = false,
  setShowCoverPicker,
  onUpdateTitle,
  onSelectEmoji,
  onSelectCoverColor,
  onRemoveCover,
  onOpenShareModal,
  onAttachTag,
  onCreateAndAttachTag,
  onDetachTag,
  onToggleFavorite,
  onToggleLock,
  onToggleFullWidth,
  onOpenComments,
}) => {
  const { showToast } = useToast();
  const [showExportMenu, setShowExportMenu] = useState(false);
  const [copiedLink, setCopiedLink] = useState(false);
  const [propertiesCollapsed, setPropertiesCollapsed] = useState(true);
  const hasCover = Boolean(coverColor || coverImage);

  // Calculate word count & reading time
  const totalWords = [
    title,
    ...blocks.map((b) => b.content?.text || ''),
  ].reduce((acc, str) => acc + (str.trim() ? str.trim().split(/\s+/).length : 0), 0);
  const readingTimeMinutes = Math.max(1, Math.ceil(totalWords / 180));

  const handleExportMarkdown = async () => {
    try {
      const { markdown, filename } = await pageService.exportMarkdown(page.id);
      const blob = new Blob([markdown], { type: 'text/markdown;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = filename || `${title || 'page'}.md`;
      link.click();
      URL.revokeObjectURL(url);
      showToast('تم تصدير ملف Markdown بنجاح', 'success');
      setShowExportMenu(false);
    } catch {
      showToast('تعذر تصدير الملف', 'error');
    }
  };

  const handleCopyLink = () => {
    navigator.clipboard.writeText(window.location.href);
    setCopiedLink(true);
    showToast('تم نسخ رابط الصفحة إلى الحافظة', 'success');
    setTimeout(() => setCopiedLink(false), 2000);
    setShowExportMenu(false);
  };

  const handlePrint = () => {
    window.print();
    setShowExportMenu(false);
  };

  return (
    <div className="w-full select-none">
      {/* 1. Cover Canvas */}
      <CoverPicker
        coverColor={coverColor}
        coverImage={coverImage}
        showPicker={showCoverPicker}
        setShowPicker={setShowCoverPicker}
        onSelectColor={onSelectCoverColor}
        onRemoveCover={onRemoveCover}
      />

      {/* 2. Page Meta Controls Bar */}
      <div
        className={`w-full ${
          isFullWidth ? 'max-w-7xl' : 'max-w-[880px]'
        } mx-auto px-4 sm:px-10 transition-all duration-200 ${
          hasCover ? 'pt-0' : 'pt-6'
        } pb-2 group/header`}
      >
        {/* Top Floating Glass Toolbar & Icon */}
        <div
          className={`flex items-end justify-between gap-4 mb-4 ${
            hasCover ? '-mt-10 relative z-10' : ''
          }`}
        >
          {/* Page Icon Squircle */}
          <EmojiPicker icon={icon} hasCover={hasCover} onSelectEmoji={onSelectEmoji} />

          {/* Sleek Action Toolbar */}
          <div className="flex items-center gap-1.5 p-1 rounded-2xl bg-neutral-100/80 dark:bg-[#202020]/90 backdrop-blur-md border border-neutral-200/60 dark:border-white/[0.08] shadow-xs no-print">
            {/* Role Badge */}
            {userRole && (
              <span className="text-[11px] px-2.5 py-0.5 rounded-xl font-medium bg-blue-500/10 text-blue-500 dark:text-blue-400 border border-blue-500/20 flex items-center gap-1">
                <span className="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse" />
                <span>
                  {userRole === 'editor'
                    ? 'محرر'
                    : userRole === 'commenter'
                    ? 'معلق'
                    : 'مشاهد'}
                </span>
              </span>
            )}

            {/* Favorite Star */}
            {onToggleFavorite && (
              <button
                type="button"
                onClick={onToggleFavorite}
                title={page.is_favorite ? 'إزالة من المفضلة' : 'إضافة إلى المفضلة'}
                className={`p-1.5 rounded-xl transition-all ${
                  page.is_favorite
                    ? 'bg-amber-500/15 text-amber-500 shadow-xs'
                    : 'text-neutral-400 hover:text-amber-500 hover:bg-neutral-200/60 dark:hover:bg-white/10'
                }`}
              >
                <Star className={`w-3.5 h-3.5 ${page.is_favorite ? 'fill-current' : ''}`} />
              </button>
            )}

            {/* Lock Page toggle */}
            {onToggleLock && (
              <button
                type="button"
                onClick={onToggleLock}
                title={isLocked ? 'الصفحة مقفلة (اضغط لإلغاء القفل)' : 'قفل الصفحة لمنع التعديل'}
                className={`p-1.5 rounded-xl transition-all ${
                  isLocked
                    ? 'bg-amber-500/15 text-amber-500 border border-amber-500/30'
                    : 'text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 hover:bg-neutral-200/60 dark:hover:bg-white/10'
                }`}
              >
                {isLocked ? <Lock className="w-3.5 h-3.5" /> : <Unlock className="w-3.5 h-3.5" />}
              </button>
            )}

            {/* Full-width toggle */}
            {onToggleFullWidth && (
              <button
                type="button"
                onClick={onToggleFullWidth}
                title={isFullWidth ? 'عرض قياسي (مركزي)' : 'عرض كامل بعرض الشاشة'}
                className={`p-1.5 rounded-xl transition-all ${
                  isFullWidth
                    ? 'bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 shadow-xs'
                    : 'text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 hover:bg-neutral-200/60 dark:hover:bg-white/10'
                }`}
              >
                {isFullWidth ? (
                  <Minimize2 className="w-3.5 h-3.5" />
                ) : (
                  <Maximize2 className="w-3.5 h-3.5" />
                )}
              </button>
            )}

            <div className="h-4 w-px bg-neutral-200 dark:bg-white/10 mx-0.5" />

            {/* Share Button */}
            <button
              type="button"
              onClick={onOpenShareModal}
              className="text-xs px-3 py-1 rounded-xl bg-neutral-900 text-white dark:bg-white/10 dark:text-neutral-100 dark:hover:bg-white/15 hover:opacity-90 transition-all flex items-center gap-1.5 font-medium shadow-xs"
            >
              <Users className="w-3.5 h-3.5 text-neutral-300 dark:text-neutral-300" />
              <span>المشاركة</span>
            </button>

            {/* Export & Actions Menu */}
            <div className="relative">
              <button
                type="button"
                onClick={() => setShowExportMenu(!showExportMenu)}
                className="p-1.5 rounded-xl text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 hover:bg-neutral-200/60 dark:hover:bg-white/10 transition-colors"
                title="خيارات إضافية"
              >
                <MoreHorizontal className="w-3.5 h-3.5" />
              </button>

              {showExportMenu && (
                <div className="absolute left-0 top-full mt-2 w-56 rounded-2xl bg-white dark:bg-[#222222] border border-neutral-200 dark:border-[#333333] shadow-2xl p-1.5 z-50 text-xs animate-in fade-in zoom-in-95 duration-100">
                  <button
                    type="button"
                    onClick={handleExportMarkdown}
                    className="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-right hover:bg-neutral-100 dark:hover:bg-[#2C2C2C] text-neutral-700 dark:text-neutral-200 transition-colors"
                  >
                    <Download className="w-3.5 h-3.5 text-neutral-400" />
                    <span>تصدير ملف Markdown (.md)</span>
                  </button>

                  <button
                    type="button"
                    onClick={handleCopyLink}
                    className="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-right hover:bg-neutral-100 dark:hover:bg-[#2C2C2C] text-neutral-700 dark:text-neutral-200 transition-colors"
                  >
                    {copiedLink ? (
                      <Check className="w-3.5 h-3.5 text-emerald-500" />
                    ) : (
                      <Copy className="w-3.5 h-3.5 text-neutral-400" />
                    )}
                    <span>نسخ رابط الصفحة</span>
                  </button>

                  <button
                    type="button"
                    onClick={handlePrint}
                    className="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-right hover:bg-neutral-100 dark:hover:bg-[#2C2C2C] text-neutral-700 dark:text-neutral-200 transition-colors"
                  >
                    <Printer className="w-3.5 h-3.5 text-neutral-400" />
                    <span>طباعة / حفظ كـ PDF</span>
                  </button>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Quick Notion Prompts (Add Cover / Add Icon / Add Comment) on Header Hover */}
        <div className="flex items-center gap-2 mb-2 text-xs opacity-0 group-hover/header:opacity-100 transition-opacity duration-200 no-print">
          {!hasCover && (
            <button
              type="button"
              onClick={() => setShowCoverPicker(true)}
              className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-[#252525] transition-colors"
            >
              <ImageIcon className="w-3.5 h-3.5" />
              <span>إضافة غلاف</span>
            </button>
          )}

          {onOpenComments && (
            <button
              type="button"
              onClick={onOpenComments}
              className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-[#252525] transition-colors"
            >
              <MessageSquare className="w-3.5 h-3.5" />
              <span>مناقشة الصفحة</span>
            </button>
          )}
        </div>

        {/* 3. Page Title */}
        <input
          type="text"
          dir="auto"
          disabled={isLocked}
          value={title}
          onChange={(e) => onUpdateTitle(e.target.value)}
          className={`w-full text-3xl sm:text-4xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 bg-transparent border-0 focus:ring-0 focus:outline-none p-0 placeholder:text-neutral-400/40 leading-snug ${
            isLocked ? 'cursor-not-allowed opacity-90' : ''
          }`}
          placeholder="بدون عنوان"
        />

        {/* 4. Sleek Notion-Style Properties Card */}
        <div className="mt-4 rounded-2xl bg-neutral-50/70 dark:bg-[#1E1E1E]/80 border border-neutral-200/60 dark:border-[#2C2C2C] shadow-xs overflow-hidden transition-all">
          {/* Properties Card Header */}
          <div
            onClick={() => setPropertiesCollapsed(!propertiesCollapsed)}
            className={`flex items-center justify-between px-3.5 py-2.5 cursor-pointer hover:bg-neutral-100/70 dark:hover:bg-[#252525] transition-colors select-none group/propheader ${
              !propertiesCollapsed ? 'border-b border-neutral-200/40 dark:border-[#2A2A2A]/60' : ''
            }`}
          >
            <div className="flex items-center gap-2 text-xs font-semibold text-neutral-700 dark:text-neutral-300">
              <SlidersHorizontal className="w-3.5 h-3.5 text-neutral-400 group-hover/propheader:text-neutral-600 dark:group-hover/propheader:text-neutral-200 transition-colors" />
              <span>خصائص الصفحة</span>
              <span className="text-[10px] px-2 py-0.5 rounded-full bg-neutral-200/60 dark:bg-[#2A2A2A] text-neutral-500 dark:text-neutral-400 font-normal">
                3 خصائص
              </span>

              {/* Tag preview pills when collapsed */}
              {propertiesCollapsed && page.tags && page.tags.length > 0 && (
                <div className="flex items-center gap-1 mr-2">
                  {page.tags.slice(0, 3).map((tag) => (
                    <span
                      key={tag.id}
                      className="text-[10px] px-2 py-0.5 rounded-full bg-neutral-200/50 dark:bg-white/[0.06] text-neutral-600 dark:text-neutral-300 font-medium"
                    >
                      {tag.name}
                    </span>
                  ))}
                  {page.tags.length > 3 && (
                    <span className="text-[10px] text-neutral-400">+{page.tags.length - 3}</span>
                  )}
                </div>
              )}
            </div>

            <div className="flex items-center gap-1 text-xs text-neutral-400 group-hover/propheader:text-neutral-700 dark:group-hover/propheader:text-neutral-200 transition-colors">
              <span className="text-[11px] font-normal">{propertiesCollapsed ? 'عرض الخصائص' : 'طي'}</span>
              <ChevronDown
                className={`w-3.5 h-3.5 transition-transform duration-200 ${
                  propertiesCollapsed ? '-rotate-90 rtl:rotate-90' : 'rotate-0'
                }`}
              />
            </div>
          </div>

          {/* Properties Content Grid */}
          {!propertiesCollapsed && (
            <div className="p-3.5 space-y-3 text-xs">
              {/* Property: Tags */}
              <div className="flex items-start gap-4">
                <span className="w-28 flex items-center gap-2 text-neutral-400 shrink-0 pt-0.5 select-none font-medium">
                  <span className="p-1 rounded-lg bg-purple-500/10 text-purple-400">
                    <TagIcon className="w-3 h-3" />
                  </span>
                  <span>الوسوم</span>
                </span>
                <div className="flex-1 min-w-0">
                  <TagsManager
                    tags={page.tags || []}
                    workspaceTags={workspaceTags}
                    onAttachTag={onAttachTag}
                    onCreateAndAttachTag={onCreateAndAttachTag}
                    onDetachTag={onDetachTag}
                  />
                </div>
              </div>

              {/* Property: Creator */}
              <div className="flex items-center gap-4">
                <span className="w-28 flex items-center gap-2 text-neutral-400 shrink-0 select-none font-medium">
                  <span className="p-1 rounded-lg bg-emerald-500/10 text-emerald-400">
                    <UserIcon className="w-3 h-3" />
                  </span>
                  <span>المنشئ</span>
                </span>
                <div className="flex items-center gap-2 text-neutral-700 dark:text-neutral-300">
                  <span className="w-5 h-5 rounded-full bg-gradient-to-tr from-emerald-500 to-teal-400 text-white flex items-center justify-center text-[10px] font-bold shadow-xs">
                    {(page.creator?.name || 'م')[0]}
                  </span>
                  <span className="font-medium">{page.creator?.name || 'مستخدم'}</span>
                  <span className="text-[11px] text-neutral-400">• {timeAgo(page.created_at)}</span>
                </div>
              </div>

              {/* Property: Stats & Reading Time */}
              <div className="flex items-center gap-4">
                <span className="w-28 flex items-center gap-2 text-neutral-400 shrink-0 select-none font-medium">
                  <span className="p-1 rounded-lg bg-blue-500/10 text-blue-400">
                    <Clock className="w-3 h-3" />
                  </span>
                  <span>الإحصائيات</span>
                </span>
                <div className="flex items-center gap-2 text-neutral-600 dark:text-neutral-400 text-[11px]">
                  <span className="px-2 py-0.5 rounded-full bg-neutral-200/50 dark:bg-[#252525] border border-neutral-300/40 dark:border-white/5 font-medium">
                    {totalWords} كلمة
                  </span>
                  <span>•</span>
                  <span className="px-2 py-0.5 rounded-full bg-neutral-200/50 dark:bg-[#252525] border border-neutral-300/40 dark:border-white/5 font-medium">
                    تقريباً {readingTimeMinutes} دقيقة قراءة
                  </span>
                </div>
              </div>

              {/* Property: Last Updated */}
              <div className="flex items-center gap-4">
                <span className="w-28 flex items-center gap-2 text-neutral-400 shrink-0 select-none font-medium">
                  <span className="p-1 rounded-lg bg-amber-500/10 text-amber-400">
                    <Calendar className="w-3 h-3" />
                  </span>
                  <span>آخر تحديث</span>
                </span>
                <div className="text-neutral-600 dark:text-neutral-400 text-[11px]">
                  {formatDate(page.updated_at || page.created_at)}
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};
