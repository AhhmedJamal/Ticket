'use client';

import React, { useState, useRef, useEffect } from 'react';
import {
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
  Copy,
  Trash2,
  Link,
  MessageSquare,
  Palette,
  Check,
} from 'lucide-react';

interface BlockActionMenuProps {
  currentType: string;
  currentColor: string;
  isOpen: boolean;
  onClose: () => void;
  onTurnInto: (newType: string) => void;
  onColorChange: (newColor: string) => void;
  onDuplicate: () => void;
  onDelete: () => void;
  onOpenComments: () => void;
  onCopyLink: () => void;
}

const BLOCK_TYPES = [
  { id: 'paragraph', label: 'نص عادي', icon: Type, shortcut: 'Text' },
  { id: 'h1', label: 'عنوان رئيسي 1', icon: Heading1, shortcut: '# ' },
  { id: 'h2', label: 'عنوان فرعي 2', icon: Heading2, shortcut: '## ' },
  { id: 'h3', label: 'عنوان قسم 3', icon: Heading3, shortcut: '### ' },
  { id: 'todo', label: 'قائمة مهام', icon: CheckSquare, shortcut: '[] ' },
  { id: 'bulleted_list', label: 'قائمة نقطية', icon: List, shortcut: '- ' },
  { id: 'numbered_list', label: 'قائمة مرقمة', icon: ListOrdered, shortcut: '1. ' },
  { id: 'toggle', label: 'قائمة قابلة للطي', icon: ChevronRightSquare, shortcut: '> ' },
  { id: 'quote', label: 'اقتباس', icon: Quote, shortcut: '| ' },
  { id: 'callout', label: 'صندوق ملاحظة', icon: AlertCircle, shortcut: 'Callout' },
  { id: 'code', label: 'كود برمجي', icon: Code, shortcut: '```' },
];

const COLORS = [
  { id: 'default', label: 'افتراضي', color: '#888888', bg: 'transparent' },
  { id: 'gray', label: 'رمادي', color: '#787774', bg: '#F1F1EF', darkBg: '#202020' },
  { id: 'brown', label: 'بني', color: '#976D57', bg: '#F3EEEE', darkBg: '#292321' },
  { id: 'orange', label: 'برتقالي', color: '#D9730D', bg: '#FAEBDD', darkBg: '#2E2218' },
  { id: 'yellow', label: 'أصفر', color: '#DFAB01', bg: '#FBF3DB', darkBg: '#2E281B' },
  { id: 'green', label: 'أخضر', color: '#0F7B6C', bg: '#EDF3EC', darkBg: '#1E2B22' },
  { id: 'blue', label: 'أزرق', color: '#0B6E99', bg: '#E7F3F8', darkBg: '#1D282E' },
  { id: 'purple', label: 'بنفسجي', color: '#6940A5', bg: '#F4F0F7', darkBg: '#27212E' },
  { id: 'pink', label: 'وردي', color: '#AD1A72', bg: '#F9EFF4', darkBg: '#2E1E26' },
  { id: 'red', label: 'أحمر', color: '#E03E3E', bg: '#FDEBEC', darkBg: '#2F1E1E' },
];

export const BlockActionMenu: React.FC<BlockActionMenuProps> = ({
  currentType,
  currentColor,
  isOpen,
  onClose,
  onTurnInto,
  onColorChange,
  onDuplicate,
  onDelete,
  onOpenComments,
  onCopyLink,
}) => {
  const [activeSubmenu, setActiveSubmenu] = useState<'none' | 'turnInto' | 'color'>('none');
  const menuRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (menuRef.current && !menuRef.current.contains(e.target as Node)) {
        onClose();
      }
    };
    if (isOpen) {
      document.addEventListener('mousedown', handleClickOutside);
    }
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [isOpen, onClose]);

  if (!isOpen) return null;

  return (
    <div
      ref={menuRef}
      className="absolute right-0 top-7 z-50 w-56 rounded-lg bg-white dark:bg-[#252525] border border-notion-border dark:border-[#333333] shadow-notion-menu dark:shadow-notion-dark py-1 text-xs select-none animate-in fade-in zoom-in-95 duration-100"
    >
      {/* 1. Quick Actions */}
      <button
        type="button"
        onClick={() => {
          onDelete();
          onClose();
        }}
        className="w-full flex items-center justify-between px-3 py-1.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors"
      >
        <div className="flex items-center gap-2">
          <Trash2 className="w-3.5 h-3.5" />
          <span>حذف البلوك</span>
        </div>
        <span className="kbd text-[9px]">Del</span>
      </button>

      <button
        type="button"
        onClick={() => {
          onDuplicate();
          onClose();
        }}
        className="w-full flex items-center justify-between px-3 py-1.5 text-notion-text dark:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2F2F2F] transition-colors"
      >
        <div className="flex items-center gap-2">
          <Copy className="w-3.5 h-3.5 text-notion-muted" />
          <span>تكرار البلوك</span>
        </div>
        <span className="kbd text-[9px]">Ctrl+D</span>
      </button>

      <button
        type="button"
        onClick={() => {
          onCopyLink();
          onClose();
        }}
        className="w-full flex items-center justify-between px-3 py-1.5 text-notion-text dark:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2F2F2F] transition-colors"
      >
        <div className="flex items-center gap-2">
          <Link className="w-3.5 h-3.5 text-notion-muted" />
          <span>نسخ رابط البلوك</span>
        </div>
      </button>

      <button
        type="button"
        onClick={() => {
          onOpenComments();
          onClose();
        }}
        className="w-full flex items-center justify-between px-3 py-1.5 text-notion-text dark:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2F2F2F] transition-colors"
      >
        <div className="flex items-center gap-2">
          <MessageSquare className="w-3.5 h-3.5 text-notion-muted" />
          <span>تعليقات ومناقشة</span>
        </div>
      </button>

      <div className="my-1 border-t border-notion-border dark:border-[#333333]" />

      {/* 2. Turn into (تحويل إلى نوع آخر) */}
      <div className="px-2 py-1 text-[10px] font-semibold text-notion-muted uppercase tracking-wider">
        تحويل النوع والمظهر
      </div>

      <div className="relative">
        <button
          type="button"
          onClick={() => setActiveSubmenu(activeSubmenu === 'turnInto' ? 'none' : 'turnInto')}
          className="w-full flex items-center justify-between px-3 py-1.5 text-notion-text dark:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2F2F2F] transition-colors"
        >
          <div className="flex items-center gap-2">
            <Type className="w-3.5 h-3.5 text-notion-muted" />
            <span>تحويل إلى...</span>
          </div>
          <span className="text-[10px] text-notion-muted">◀</span>
        </button>

        {activeSubmenu === 'turnInto' && (
          <div className="absolute right-full top-0 mr-1 w-52 max-h-64 overflow-y-auto rounded-lg bg-white dark:bg-[#252525] border border-notion-border dark:border-[#333333] shadow-notion py-1 z-50">
            {BLOCK_TYPES.map((t) => {
              const Icon = t.icon;
              const isSelected = currentType === t.id;
              return (
                <button
                  key={t.id}
                  type="button"
                  onClick={() => {
                    onTurnInto(t.id);
                    onClose();
                  }}
                  className={`w-full flex items-center justify-between px-3 py-1.5 transition-colors ${
                    isSelected
                      ? 'bg-notion-hover dark:bg-[#333333] font-medium'
                      : 'hover:bg-notion-hover dark:hover:bg-[#2F2F2F]'
                  }`}
                >
                  <div className="flex items-center gap-2 truncate">
                    <Icon className="w-3.5 h-3.5 text-notion-muted shrink-0" />
                    <span className="truncate">{t.label}</span>
                  </div>
                  {isSelected && <Check className="w-3.5 h-3.5 text-notion-accent shrink-0" />}
                </button>
              );
            })}
          </div>
        )}
      </div>

      {/* 3. Color swatch menu */}
      <div className="relative">
        <button
          type="button"
          onClick={() => setActiveSubmenu(activeSubmenu === 'color' ? 'none' : 'color')}
          className="w-full flex items-center justify-between px-3 py-1.5 text-notion-text dark:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2F2F2F] transition-colors"
        >
          <div className="flex items-center gap-2">
            <Palette className="w-3.5 h-3.5 text-notion-muted" />
            <span>تلوين البلوك...</span>
          </div>
          <span className="text-[10px] text-notion-muted">◀</span>
        </button>

        {activeSubmenu === 'color' && (
          <div className="absolute right-full top-0 mr-1 w-48 max-h-64 overflow-y-auto rounded-lg bg-white dark:bg-[#252525] border border-notion-border dark:border-[#333333] shadow-notion py-1 z-50">
            <div className="px-3 py-1 text-[10px] font-semibold text-notion-muted uppercase">
              ألوان الخلفية والملاحظات
            </div>
            {COLORS.map((c) => {
              const isSelected = currentColor === c.id;
              return (
                <button
                  key={c.id}
                  type="button"
                  onClick={() => {
                    onColorChange(c.id);
                    onClose();
                  }}
                  className={`w-full flex items-center justify-between px-3 py-1.5 transition-colors ${
                    isSelected
                      ? 'bg-notion-hover dark:bg-[#333333] font-medium'
                      : 'hover:bg-notion-hover dark:hover:bg-[#2F2F2F]'
                  }`}
                >
                  <div className="flex items-center gap-2">
                    <span
                      className="w-3.5 h-3.5 rounded-full border border-black/10 dark:border-white/10 shrink-0"
                      style={{ backgroundColor: c.color }}
                    />
                    <span>{c.label}</span>
                  </div>
                  {isSelected && <Check className="w-3 h-3 text-notion-accent" />}
                </button>
              );
            })}
          </div>
        )}
      </div>
    </div>
  );
};

