'use client';

import React, { useState } from 'react';
import { Page } from '@/types';
import { Trash2, Search, RotateCcw, X, FileText } from 'lucide-react';
import Link from 'next/link';

interface TrashModalProps {
  isOpen: boolean;
  onClose: () => void;
  deletedPages?: Page[];
}

export const TrashModal: React.FC<TrashModalProps> = ({
  isOpen,
  onClose,
  deletedPages = [],
}) => {
  const [search, setSearch] = useState('');

  if (!isOpen) return null;

  const filtered = deletedPages.filter((p) =>
    (p.title || '').toLowerCase().includes(search.toLowerCase().trim())
  );

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs animate-in fade-in select-none">
      <div className="w-full max-w-md rounded-xl bg-white dark:bg-[#202020] border border-notion-border dark:border-[#333333] shadow-notion dark:shadow-notion-dark overflow-hidden">
        {/* Header */}
        <div className="flex items-center justify-between px-4 py-3 border-b border-notion-border dark:border-[#2C2C2C]">
          <div className="flex items-center gap-2 text-sm font-semibold text-notion-text dark:text-[#E3E3E0]">
            <Trash2 className="w-4 h-4 text-notion-muted" />
            <span>سلة المحذوفات</span>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="p-1 rounded text-notion-muted hover:text-notion-text transition-colors"
          >
            <X className="w-4 h-4" />
          </button>
        </div>

        {/* Search */}
        <div className="p-3 border-b border-notion-border dark:border-[#2C2C2C] bg-notion-sidebar/40 dark:bg-[#1A1A1A]">
          <div className="flex items-center gap-2 px-2.5 py-1.5 rounded-lg border border-notion-border dark:border-[#333333] bg-white dark:bg-[#252525]">
            <Search className="w-3.5 h-3.5 text-notion-muted shrink-0" />
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="ابحث في الصفحات المحذوفة..."
              className="w-full text-xs bg-transparent border-0 p-0 text-notion-text dark:text-[#E3E3E0] placeholder:text-notion-muted/50 focus:outline-none focus:ring-0"
            />
          </div>
        </div>

        {/* List of deleted items */}
        <div className="max-h-72 overflow-y-auto p-2 divide-y divide-notion-border/40 dark:divide-[#282828] text-xs">
          {filtered.length > 0 ? (
            filtered.map((item) => (
              <div
                key={item.id}
                className="flex items-center justify-between py-2 px-2 hover:bg-notion-hover dark:hover:bg-[#2A2A2A] rounded-md transition-colors"
              >
                <div className="flex items-center gap-2 truncate">
                  <span className="text-base">{item.icon || '📄'}</span>
                  <span className="font-medium text-notion-text dark:text-[#E3E3E0] truncate">
                    {item.title || 'بدون عنوان'}
                  </span>
                </div>
                <button
                  type="button"
                  className="flex items-center gap-1 text-[11px] text-notion-accent hover:underline shrink-0"
                >
                  <RotateCcw className="w-3 h-3" />
                  <span>استرجاع</span>
                </button>
              </div>
            ))
          ) : (
            <div className="py-12 text-center text-notion-muted space-y-1">
              <FileText className="w-6 h-6 mx-auto opacity-40 mb-1" />
              <div className="text-xs font-medium">سلة المحذوفات فارغة</div>
              <div className="text-[11px] text-notion-muted/70">
                الصفحات التي تحذفها ستظهر هنا
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

