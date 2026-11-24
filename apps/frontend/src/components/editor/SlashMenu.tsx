'use client';

import React, { useState, useRef, useEffect } from 'react';
import {
  Search,
  X,
  Type,
  Heading1,
  Heading2,
  Heading3,
  CheckSquare,
  List,
  ListOrdered,
  ChevronRightSquare,
  Quote,
  AlertCircle,
  Code,
  Minus,
  Database,
  FileText,
} from 'lucide-react';

interface SlashMenuProps {
  isOpen: boolean;
  onClose: () => void;
  onSelectBlockType: (type: string, label: string) => void;
  onCreateDatabase: () => void;
  onCreateSubpage: () => void;
}

export const SlashMenu: React.FC<SlashMenuProps> = ({
  isOpen,
  onClose,
  onSelectBlockType,
  onCreateDatabase,
  onCreateSubpage,
}) => {
  const [search, setSearch] = useState('');
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (isOpen) {
      setSearch('');
      setTimeout(() => inputRef.current?.focus(), 40);
    }
  }, [isOpen]);

  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape' && isOpen) {
        onClose();
      }
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [isOpen, onClose]);

  if (!isOpen) return null;

  const matches = (keywords: string) => {
    if (!search.trim()) return true;
    return keywords.toLowerCase().includes(search.toLowerCase().trim());
  };

  const handleSelect = (type: string, label: string) => {
    onSelectBlockType(type, label);
    onClose();
  };

  return (
    <div className="mt-2 mb-6 w-full bg-white dark:bg-[#1E1E1E] border border-notion-border dark:border-[#303030] rounded-2xl shadow-2xl overflow-hidden z-30 select-none animate-in fade-in zoom-in-95 duration-100">
      {/* 1. Header with search bar */}
      <div className="flex items-center gap-2 px-4 py-3 border-b border-notion-border dark:border-[#2A2A2A] bg-notion-sidebar/40 dark:bg-[#252525]/60">
        <Search className="w-4 h-4 text-notion-muted shrink-0" />
        <input
          ref={inputRef}
          type="text"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          placeholder="ابحث عن نوع البلوك أو الأمر..."
          className="flex-1 text-xs bg-transparent border-0 p-0 focus:ring-0 focus:outline-none text-notion-text dark:text-[#E3E3E0] placeholder:text-notion-muted/50"
        />
        <button
          type="button"
          onClick={onClose}
          className="text-[11px] px-2 py-0.5 rounded bg-notion-hover dark:bg-[#2E2E2E] text-notion-muted hover:text-notion-text dark:hover:text-[#E3E3E0] transition-colors"
        >
          Esc
        </button>
      </div>

      {/* 2. Grid Body */}
      <div className="p-4 sm:p-5">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
          {/* Column 1 */}
          <div className="space-y-4">
            {/* Section: نصوص وعناوين */}
            <div>
              <div className="px-1.5 pb-1.5 text-[11px] font-semibold text-notion-muted dark:text-[#9B9A97] uppercase tracking-wider">
                نصوص وعناوين
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                {matches('نص عادي فقرة paragraph text') && (
                  <button
                    type="button"
                    onClick={() => handleSelect('paragraph', '¶ نص عادي')}
                    className="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0] group"
                  >
                    <span className="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-105 transition-transform">
                      ¶
                    </span>
                    <div className="min-w-0">
                      <div className="text-xs font-semibold truncate">نص عادي</div>
                      <div className="text-[10px] text-notion-muted truncate">فقرة بسيطة</div>
                    </div>
                  </button>
                )}

                {matches('عنوان رئيسي كبير h1 heading 1') && (
                  <button
                    type="button"
                    onClick={() => handleSelect('heading_1', 'H1 عنوان كبير')}
                    className="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0] group"
                  >
                    <span className="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-105 transition-transform">
                      H1
                    </span>
                    <div className="min-w-0">
                      <div className="text-xs font-semibold truncate">عنوان رئيسي</div>
                      <div className="text-[10px] text-notion-muted truncate">قسم كبير بارز</div>
                    </div>
                  </button>
                )}

                {matches('عنوان فرعي متوسط h2 heading 2') && (
                  <button
                    type="button"
                    onClick={() => handleSelect('heading_2', 'H2 عنوان متوسط')}
                    className="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0] group"
                  >
                    <span className="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-105 transition-transform">
                      H2
                    </span>
                    <div className="min-w-0">
                      <div className="text-xs font-semibold truncate">عنوان متوسط</div>
                      <div className="text-[10px] text-notion-muted truncate">قسم فرعي</div>
                    </div>
                  </button>
                )}

                {matches('عنوان قسم صغير h3 heading 3') && (
                  <button
                    type="button"
                    onClick={() => handleSelect('heading_3', 'H3 عنوان صغير')}
                    className="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0] group"
                  >
                    <span className="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-105 transition-transform">
                      H3
                    </span>
                    <div className="min-w-0">
                      <div className="text-xs font-semibold truncate">عنوان صغير</div>
                      <div className="text-[10px] text-notion-muted truncate">تفريعة أو فقرة</div>
                    </div>
                  </button>
                )}
              </div>
            </div>

            {/* Section: قوائم وتنظيم */}
            <div className="border-t border-notion-border/60 dark:border-[#2A2A2A] pt-3">
              <div className="px-1.5 pb-1.5 text-[11px] font-semibold text-notion-muted dark:text-[#9B9A97] uppercase tracking-wider">
                قوائم وتنظيم
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                {matches('قائمة مهام todo to-do مربعات') && (
                  <button
                    type="button"
                    onClick={() => handleSelect('todo', 'قائمة مهام')}
                    className="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0] group"
                  >
                    <span className="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-105 transition-transform">
                      ☑
                    </span>
                    <div className="min-w-0">
                      <div className="text-xs font-semibold truncate">قائمة مهام</div>
                      <div className="text-[10px] text-notion-muted truncate">مربعات إنجاز</div>
                    </div>
                  </button>
                )}

                {matches('قائمة نقطية bullet list') && (
                  <button
                    type="button"
                    onClick={() => handleSelect('bulleted_list', 'قائمة نقطية')}
                    className="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0] group"
                  >
                    <span className="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-105 transition-transform">
                      •
                    </span>
                    <div className="min-w-0">
                      <div className="text-xs font-semibold truncate">قائمة نقطية</div>
                      <div className="text-[10px] text-notion-muted truncate">تعداد نقطي</div>
                    </div>
                  </button>
                )}

                {matches('قائمة مرقمة numbered list') && (
                  <button
                    type="button"
                    onClick={() => handleSelect('numbered_list', 'قائمة مرقمة')}
                    className="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0] group"
                  >
                    <span className="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-105 transition-transform">
                      1.
                    </span>
                    <div className="min-w-0">
                      <div className="text-xs font-semibold truncate">قائمة مرقمة</div>
                      <div className="text-[10px] text-notion-muted truncate">تعداد تسلسلي</div>
                    </div>
                  </button>
                )}

                {matches('قائمة قابلة للطي toggle list') && (
                  <button
                    type="button"
                    onClick={() => handleSelect('toggle', 'قائمة قابلة للطي')}
                    className="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0] group"
                  >
                    <span className="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-105 transition-transform">
                      &gt;
                    </span>
                    <div className="min-w-0">
                      <div className="text-xs font-semibold truncate">قائمة قابلة للطي</div>
                      <div className="text-[10px] text-notion-muted truncate">إخفاء وإظهار</div>
                    </div>
                  </button>
                )}
              </div>
            </div>
          </div>

          {/* Column 2 */}
          <div className="space-y-4">
            {/* Section: تنسيقات وملاحظات */}
            <div>
              <div className="px-1.5 pb-1.5 text-[11px] font-semibold text-notion-muted dark:text-[#9B9A97] uppercase tracking-wider">
                تنسيقات وملاحظات
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                {matches('ملاحظة صندوق تنبيه callout') && (
                  <button
                    type="button"
                    onClick={() => handleSelect('callout', 'صندوق ملاحظة')}
                    className="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0] group"
                  >
                    <span className="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center text-sm shrink-0 group-hover:scale-105 transition-transform">
                      💡
                    </span>
                    <div className="min-w-0">
                      <div className="text-xs font-semibold truncate">صندوق ملاحظة</div>
                      <div className="text-[10px] text-notion-muted truncate">مربع ملون مميز</div>
                    </div>
                  </button>
                )}

                {matches('اقتباس quote') && (
                  <button
                    type="button"
                    onClick={() => handleSelect('quote', 'اقتباس')}
                    className="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0] group"
                  >
                    <span className="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center font-serif font-bold text-xs shrink-0 group-hover:scale-105 transition-transform">
                      &quot;
                    </span>
                    <div className="min-w-0">
                      <div className="text-xs font-semibold truncate">اقتباس</div>
                      <div className="text-[10px] text-notion-muted truncate">اقتباس نصي</div>
                    </div>
                  </button>
                )}

                {matches('كود برمجي code prism') && (
                  <button
                    type="button"
                    onClick={() => handleSelect('code', 'كود برمجي')}
                    className="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0] group"
                  >
                    <span className="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center font-mono font-bold text-xs shrink-0 group-hover:scale-105 transition-transform">
                      &lt;/&gt;
                    </span>
                    <div className="min-w-0">
                      <div className="text-xs font-semibold truncate">كود برمجي</div>
                      <div className="text-[10px] text-notion-muted truncate">تلوين برمجيات</div>
                    </div>
                  </button>
                )}

                {matches('فاصل أفقي divider line') && (
                  <button
                    type="button"
                    onClick={() => handleSelect('divider', 'فاصل أفقي')}
                    className="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0] group"
                  >
                    <span className="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-105 transition-transform">
                      —
                    </span>
                    <div className="min-w-0">
                      <div className="text-xs font-semibold truncate">فاصل أفقي</div>
                      <div className="text-[10px] text-notion-muted truncate">خط فاصل</div>
                    </div>
                  </button>
                )}
              </div>
            </div>

            {/* Section: صفحات وبيانات */}
            <div className="border-t border-notion-border/60 dark:border-[#2A2A2A] pt-3">
              <div className="px-1.5 pb-1.5 text-[11px] font-semibold text-notion-muted dark:text-[#9B9A97] uppercase tracking-wider">
                صفحات وبيانات
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                {matches('قاعدة بيانات جدول كانبان database table board') && (
                  <button
                    type="button"
                    onClick={() => {
                      onCreateDatabase();
                      onClose();
                    }}
                    className="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0] group"
                  >
                    <span className="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                      <Database className="w-4 h-4 text-notion-accent" />
                    </span>
                    <div className="min-w-0">
                      <div className="text-xs font-semibold truncate">قاعدة بيانات</div>
                      <div className="text-[10px] text-notion-muted truncate">جدول أو لوحة مدمجة</div>
                    </div>
                  </button>
                )}

                {matches('صفحة فرعية subpage page') && (
                  <button
                    type="button"
                    onClick={() => {
                      onCreateSubpage();
                      onClose();
                    }}
                    className="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0] group"
                  >
                    <span className="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                      <FileText className="w-4 h-4 text-emerald-500" />
                    </span>
                    <div className="min-w-0">
                      <div className="text-xs font-semibold truncate">صفحة فرعية</div>
                      <div className="text-[10px] text-notion-muted truncate">صفحة تابعة بالداخل</div>
                    </div>
                  </button>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
