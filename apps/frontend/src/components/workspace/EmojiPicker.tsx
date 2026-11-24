'use client';

import React, { useState, useRef, useEffect } from 'react';
import { Search, X, Trash2, Smile } from 'lucide-react';

const EMOJI_CATEGORIES: { name: string; icon: string; items: string[] }[] = [
  {
    name: 'شائعة',
    icon: '⭐',
    items: ['📄', '📝', '📋', '📌', '💡', '⭐', '🎯', '✅', '🔥', '💎', '🚀', '🌟', '⚡', '🎉', '🎨', '💻'],
  },
  {
    name: 'أوراق وعمل',
    icon: '📁',
    items: ['🗂️', '📁', '📂', '🗒️', '📓', '📔', '📒', '📕', '📗', '📘', '📙', '📚', '🔖', '🏷️', '📊', '📈', '📉', '💼', '📅', '📆', '🗓️', '📎'],
  },
  {
    name: 'تقنية وأدوات',
    icon: '🛠️',
    items: ['🔧', '🛠️', '⚙️', '🖥️', '⌨️', '🖱️', '📱', '🔒', '🔑', '🏷️', '📡', '🔋', '🔌', '🕹️', '🛡️', '🧬', '🔬', '🔭'],
  },
  {
    name: 'رموز وإشارات',
    icon: '✨',
    items: ['✨', '💫', '💥', '💢', '💯', '✔️', '❌', '⭕', '🔴', '🟢', '🟡', '🔵', '🟣', '🟠', '⚪', '⚫', '🔺', '🔹', '🏁', '🚩'],
  },
  {
    name: 'طبيعة ومشاهد',
    icon: '🌿',
    items: ['🏠', '🌍', '🌈', '🌙', '☀️', '🌤️', '⛅', '🌊', '🏔️', '🌴', '🌸', '🌺', '🍀', '🦋', '🌱', '🍁', '🍂', '🍄', '🪐'],
  },
];

interface EmojiPickerProps {
  icon: string;
  hasCover: boolean;
  onSelectEmoji: (emoji: string) => void;
}

export const EmojiPicker: React.FC<EmojiPickerProps> = ({
  icon,
  hasCover,
  onSelectEmoji,
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const [search, setSearch] = useState('');
  const [activeTab, setActiveTab] = useState(0);
  const [customInput, setCustomInput] = useState('');
  const menuRef = useRef<HTMLDivElement>(null);
  const searchInputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (menuRef.current && !menuRef.current.contains(e.target as Node)) {
        setIsOpen(false);
      }
    };
    if (isOpen) {
      document.addEventListener('mousedown', handleClickOutside);
      setTimeout(() => searchInputRef.current?.focus(), 40);
    }
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [isOpen]);

  const allEmojis = EMOJI_CATEGORIES.flatMap((c) => c.items);
  const filteredEmojis = search.trim()
    ? allEmojis.filter((e) => e.includes(search.trim()))
    : EMOJI_CATEGORIES[activeTab]?.items || [];

  return (
    <div className="relative inline-block" ref={menuRef}>
      {/* Icon Squircle Button */}
      <button
        type="button"
        onClick={() => setIsOpen(!isOpen)}
        className={`group relative transition-all duration-150 select-none flex items-center justify-center cursor-pointer ${
          hasCover
            ? 'w-20 h-20 -mt-10 rounded-2xl bg-white dark:bg-[#1E1E1E] shadow-xl ring-4 ring-white dark:ring-[#191919] text-4xl hover:scale-105'
            : 'w-14 h-14 rounded-2xl bg-neutral-100 dark:bg-[#252525] border border-neutral-200/80 dark:border-[#333333] shadow-xs text-3xl hover:scale-105 hover:bg-neutral-200/60 dark:hover:bg-[#2C2C2C]'
        }`}
        title="تغيير أيقونة الصفحة"
      >
        <span>{icon || '📄'}</span>

        {/* Edit Hint Pill */}
        <span className="absolute -bottom-1 -left-1 w-5 h-5 rounded-full bg-white dark:bg-[#2C2C2C] border border-neutral-200 dark:border-neutral-600 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-xs text-[10px] text-neutral-500 dark:text-neutral-300">
          ✏️
        </span>
      </button>

      {/* Modern Emoji & Icon Picker Popover */}
      {isOpen && (
        <div className="absolute top-full mt-2 right-0 z-50 bg-white dark:bg-[#222222] border border-neutral-200 dark:border-[#303030] rounded-2xl shadow-2xl p-3.5 w-80 animate-in fade-in zoom-in-95 duration-100 select-none">
          {/* Header & Search */}
          <div className="flex items-center gap-2 px-2.5 py-1.5 rounded-xl bg-neutral-100/80 dark:bg-[#1A1A1A] border border-neutral-200/60 dark:border-[#2C2C2C] mb-2.5">
            <Search className="w-3.5 h-3.5 text-neutral-400 shrink-0" />
            <input
              ref={searchInputRef}
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="ابحث عن أيقونة أو اكتب رمزاً..."
              className="flex-1 text-xs bg-transparent border-0 p-0 focus:ring-0 focus:outline-none text-neutral-800 dark:text-neutral-100 placeholder:text-neutral-400/60"
            />
            {search && (
              <button
                type="button"
                onClick={() => setSearch('')}
                className="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200"
              >
                <X className="w-3.5 h-3.5" />
              </button>
            )}
          </div>

          {/* Category Tabs */}
          {!search && (
            <div className="flex items-center gap-1 mb-2.5 overflow-x-auto pb-1 scrollbar-none">
              {EMOJI_CATEGORIES.map((cat, idx) => (
                <button
                  key={cat.name}
                  type="button"
                  onClick={() => setActiveTab(idx)}
                  className={`text-[11px] px-2 py-1 rounded-lg flex items-center gap-1 shrink-0 transition-colors ${
                    activeTab === idx
                      ? 'bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 font-medium'
                      : 'text-neutral-500 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-[#2A2A2A]'
                  }`}
                >
                  <span>{cat.icon}</span>
                  <span>{cat.name}</span>
                </button>
              ))}
            </div>
          )}

          {/* Emoji Grid */}
          <div className="grid grid-cols-7 gap-1.5 max-h-52 overflow-y-auto p-1 scrollbar-thin">
            {filteredEmojis.map((emoji) => (
              <button
                key={emoji}
                type="button"
                onClick={() => {
                  onSelectEmoji(emoji);
                  setIsOpen(false);
                }}
                className={`text-2xl hover:scale-125 hover:bg-neutral-100 dark:hover:bg-[#2E2E2E] rounded-xl p-1.5 transition-all flex items-center justify-center ${
                  icon === emoji ? 'bg-neutral-100 dark:bg-[#333333] ring-2 ring-neutral-400 dark:ring-neutral-500' : ''
                }`}
              >
                {emoji}
              </button>
            ))}
          </div>

          {/* Footer Actions */}
          <div className="mt-3 pt-2.5 border-t border-neutral-100 dark:border-[#2C2C2C] flex items-center justify-between text-xs">
            {/* Custom Emoji Input */}
            <div className="flex items-center gap-1.5 flex-1 min-w-0 mr-2">
              <input
                type="text"
                value={customInput}
                onChange={(e) => setCustomInput(e.target.value)}
                onKeyDown={(e) => {
                  if (e.key === 'Enter' && customInput.trim()) {
                    onSelectEmoji(customInput.trim());
                    setIsOpen(false);
                    setCustomInput('');
                  }
                }}
                placeholder="أو اكتب رمزاً مخصصاً..."
                className="w-full text-xs px-2 py-1 rounded-lg border border-neutral-200 dark:border-[#333333] bg-transparent text-neutral-800 dark:text-neutral-200 focus:outline-none focus:ring-1 focus:ring-neutral-400"
              />
              {customInput.trim() && (
                <button
                  type="button"
                  onClick={() => {
                    onSelectEmoji(customInput.trim());
                    setIsOpen(false);
                    setCustomInput('');
                  }}
                  className="px-2 py-1 rounded-lg bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 text-xs shrink-0"
                >
                  تعيين
                </button>
              )}
            </div>

            {/* Remove / Reset Icon */}
            <button
              type="button"
              onClick={() => {
                onSelectEmoji('📄');
                setIsOpen(false);
              }}
              className="text-neutral-400 hover:text-red-500 hover:bg-red-500/10 p-1.5 rounded-lg transition-colors shrink-0"
              title="إعادة تعيين الأيقونة إلى الافتراضية"
            >
              <Trash2 className="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      )}
    </div>
  );
};
