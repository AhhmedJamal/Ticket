'use client';

import React, { useState, useRef, useEffect } from 'react';
import { Tag as TagType } from '@/types';
import { Plus, X, Tag as TagIcon, Check } from 'lucide-react';

export const TAG_COLORS: Record<string, { badge: string; dot: string; name: string }> = {
  gray: {
    badge: 'bg-neutral-500/15 text-neutral-300 border-neutral-500/30 hover:bg-neutral-500/25',
    dot: 'bg-neutral-400',
    name: 'رمادي',
  },
  blue: {
    badge: 'bg-blue-500/15 text-blue-300 border-blue-500/30 hover:bg-blue-500/25',
    dot: 'bg-blue-400',
    name: 'أزرق',
  },
  green: {
    badge: 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30 hover:bg-emerald-500/25',
    dot: 'bg-emerald-400',
    name: 'أخضر',
  },
  purple: {
    badge: 'bg-purple-500/15 text-purple-300 border-purple-500/30 hover:bg-purple-500/25',
    dot: 'bg-purple-400',
    name: 'بنفسجي',
  },
  orange: {
    badge: 'bg-amber-500/15 text-amber-300 border-amber-500/30 hover:bg-amber-500/25',
    dot: 'bg-amber-400',
    name: 'برتقالي',
  },
  red: {
    badge: 'bg-rose-500/15 text-rose-300 border-rose-500/30 hover:bg-rose-500/25',
    dot: 'bg-rose-400',
    name: 'أحمر',
  },
  pink: {
    badge: 'bg-pink-500/15 text-pink-300 border-pink-500/30 hover:bg-pink-500/25',
    dot: 'bg-pink-400',
    name: 'وردي',
  },
  yellow: {
    badge: 'bg-yellow-500/15 text-yellow-300 border-yellow-500/30 hover:bg-yellow-500/25',
    dot: 'bg-yellow-400',
    name: 'أصفر',
  },
};

const COLOR_OPTIONS = [
  { key: 'blue', bg: 'bg-blue-400' },
  { key: 'purple', bg: 'bg-purple-400' },
  { key: 'green', bg: 'bg-emerald-400' },
  { key: 'orange', bg: 'bg-amber-400' },
  { key: 'red', bg: 'bg-rose-400' },
  { key: 'pink', bg: 'bg-pink-400' },
  { key: 'gray', bg: 'bg-neutral-400' },
  { key: 'yellow', bg: 'bg-yellow-400' },
];

interface TagsManagerProps {
  tags: TagType[];
  workspaceTags: TagType[];
  onAttachTag: (tagId: number) => void;
  onCreateAndAttachTag: (name: string, color: string) => void;
  onDetachTag: (tagId: number) => void;
}

export const TagsManager: React.FC<TagsManagerProps> = ({
  tags,
  workspaceTags,
  onAttachTag,
  onCreateAndAttachTag,
  onDetachTag,
}) => {
  const [pickerOpen, setPickerOpen] = useState(false);
  const [newTagName, setNewTagName] = useState('');
  const [newTagColor, setNewTagColor] = useState('blue');
  const popoverRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (popoverRef.current && !popoverRef.current.contains(e.target as Node)) {
        setPickerOpen(false);
      }
    };
    if (pickerOpen) {
      document.addEventListener('mousedown', handleClickOutside);
    }
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [pickerOpen]);

  const handleCreate = () => {
    if (!newTagName.trim()) return;
    onCreateAndAttachTag(newTagName.trim(), newTagColor);
    setNewTagName('');
    setPickerOpen(false);
  };

  const attachedTagIds = new Set(tags.map((t) => t.id));
  const availableWorkspaceTags = workspaceTags.filter((t) => !attachedTagIds.has(t.id));

  return (
    <div className="flex items-center flex-wrap gap-1.5 select-none" ref={popoverRef}>
      {tags.map((tag) => {
        const colorStyle = TAG_COLORS[tag.color] || TAG_COLORS.blue;
        return (
          <span
            key={tag.id}
            className={`inline-flex items-center gap-1.5 text-xs px-2.5 py-0.5 rounded-full border transition-all ${colorStyle.badge}`}
          >
            <span className={`w-1.5 h-1.5 rounded-full ${colorStyle.dot}`} />
            <span className="font-medium">{tag.name}</span>
            <button
              type="button"
              onClick={() => onDetachTag(tag.id)}
              className="text-neutral-400 hover:text-rose-400 p-0.5 rounded-full hover:bg-black/20 transition-colors"
              title="إزالة الوسم"
            >
              <X className="w-3 h-3" />
            </button>
          </span>
        );
      })}

      {/* Add Tag Trigger */}
      <div className="relative">
        <button
          type="button"
          onClick={() => setPickerOpen(!pickerOpen)}
          className="inline-flex items-center gap-1 text-[11px] font-medium text-neutral-400 hover:text-neutral-200 bg-neutral-100/60 dark:bg-white/[0.04] hover:bg-neutral-200/60 dark:hover:bg-white/[0.08] border border-dashed border-neutral-300 dark:border-white/10 px-2 py-0.5 rounded-full transition-all"
        >
          <Plus className="w-3 h-3" />
          <span>إضافة وسم</span>
        </button>

        {/* Modern Tag Popover */}
        {pickerOpen && (
          <div className="absolute top-full mt-1.5 right-0 z-40 bg-white dark:bg-[#222222] border border-neutral-200 dark:border-[#333333] rounded-2xl shadow-2xl p-3.5 w-72 space-y-3 animate-in fade-in zoom-in-95 duration-100">
            <div className="text-xs font-semibold text-neutral-700 dark:text-neutral-300 flex items-center gap-1.5">
              <TagIcon className="w-3.5 h-3.5 text-neutral-400" />
              <span>إدارة الوسوم والتصنيفات</span>
            </div>

            {/* Existing Workspace Tags */}
            {availableWorkspaceTags.length > 0 && (
              <div className="space-y-1.5">
                <div className="text-[10px] uppercase font-semibold text-neutral-400 tracking-wider">
                  وسوم متاحة في مساحة العمل
                </div>
                <div className="flex flex-wrap gap-1 max-h-28 overflow-y-auto p-0.5">
                  {availableWorkspaceTags.map((wsTag) => {
                    const style = TAG_COLORS[wsTag.color] || TAG_COLORS.blue;
                    return (
                      <button
                        key={wsTag.id}
                        type="button"
                        onClick={() => {
                          onAttachTag(wsTag.id);
                          setPickerOpen(false);
                        }}
                        className={`inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full border transition-transform hover:scale-105 ${style.badge}`}
                      >
                        <span className={`w-1.5 h-1.5 rounded-full ${style.dot}`} />
                        <span>{wsTag.name}</span>
                      </button>
                    );
                  })}
                </div>
                <div className="border-t border-neutral-100 dark:border-[#2C2C2C] my-1.5" />
              </div>
            )}

            {/* Create New Tag */}
            <div className="space-y-2">
              <div className="text-[10px] uppercase font-semibold text-neutral-400 tracking-wider">
                إنشاء وسم جديد
              </div>
              <input
                type="text"
                value={newTagName}
                onChange={(e) => setNewTagName(e.target.value)}
                onKeyDown={(e) => {
                  if (e.key === 'Enter') handleCreate();
                }}
                placeholder="اسم الوسم (مثال: أولوية عاجلة)..."
                className="w-full text-xs border border-neutral-200 dark:border-[#333333] bg-neutral-50 dark:bg-[#1A1A1A] rounded-xl px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-blue-500 text-neutral-800 dark:text-neutral-100"
              />

              {/* Color dots */}
              <div className="flex items-center justify-between px-1">
                <span className="text-[11px] text-neutral-400">اللون:</span>
                <div className="flex items-center gap-1.5">
                  {COLOR_OPTIONS.map((c) => (
                    <button
                      key={c.key}
                      type="button"
                      onClick={() => setNewTagColor(c.key)}
                      className={`w-4 h-4 rounded-full ${c.bg} hover:scale-125 transition-transform flex items-center justify-center ${
                        newTagColor === c.key
                          ? 'ring-2 ring-offset-1 ring-neutral-700 dark:ring-white scale-110'
                          : ''
                      }`}
                    >
                      {newTagColor === c.key && <Check className="w-2.5 h-2.5 text-white" />}
                    </button>
                  ))}
                </div>
              </div>

              <button
                type="button"
                onClick={handleCreate}
                disabled={!newTagName.trim()}
                className="w-full text-xs py-1.5 font-medium bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 rounded-xl hover:opacity-90 transition-opacity disabled:opacity-40"
              >
                إضافة الوسم للصفحة
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};
