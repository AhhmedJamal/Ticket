'use client';

import React, { useState, useEffect, useRef } from 'react';
import { Bold, Italic, Strikethrough, Code, Link as LinkIcon, Palette } from 'lucide-react';

interface BubbleMenuProps {
  containerRef?: React.RefObject<HTMLElement | null>;
}

export const BubbleMenu: React.FC<BubbleMenuProps> = () => {
  const [visible, setVisible] = useState(false);
  const [position, setPosition] = useState({ top: 0, left: 0 });
  const [showColorPicker, setShowColorPicker] = useState(false);
  const menuRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleSelectionChange = () => {
      const selection = window.getSelection();
      if (!selection || selection.isCollapsed || selection.toString().trim() === '') {
        setVisible(false);
        setShowColorPicker(false);
        return;
      }

      // Check if selection is within a block or contenteditable
      const range = selection.getRangeAt(0);
      const rect = range.getBoundingClientRect();

      // Only show if selection has width
      if (rect.width > 2) {
        setPosition({
          top: Math.max(10, rect.top - 44 + window.scrollY),
          left: Math.max(10, rect.left + rect.width / 2 - 120 + window.scrollX),
        });
        setVisible(true);
      }
    };

    document.addEventListener('selectionchange', handleSelectionChange);
    return () => {
      document.removeEventListener('selectionchange', handleSelectionChange);
    };
  }, []);

  if (!visible) return null;

  const applyFormat = (command: string, value: string | undefined = undefined) => {
    document.execCommand(command, false, value);
  };

  const colors = [
    { name: 'افتراضي', color: 'inherit' },
    { name: 'رمادي', color: '#787774' },
    { name: 'بني', color: '#976D57' },
    { name: 'برتقالي', color: '#ED6C02' },
    { name: 'أصفر', color: '#DFAB01' },
    { name: 'أخضر', color: '#0F7B6C' },
    { name: 'أزرق', color: '#0B6E99' },
    { name: 'بنفسجي', color: '#6940A5' },
    { name: 'أحمر', color: '#E03E3E' },
  ];

  return (
    <div
      ref={menuRef}
      style={{ top: `${position.top}px`, left: `${position.left}px` }}
      className="absolute z-50 flex items-center gap-0.5 px-1 py-1 rounded-lg bg-white dark:bg-[#252525] border border-notion-border dark:border-[#383838] shadow-notion dark:shadow-notion-dark text-notion-text dark:text-[#E3E3E0] select-none text-xs transition-opacity duration-150 animate-in fade-in zoom-in-95"
      onMouseDown={(e) => e.preventDefault()} // prevent blur
    >
      <button
        type="button"
        onClick={() => applyFormat('bold')}
        className="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#333333] transition-colors font-bold"
        title="عريض (Ctrl+B)"
      >
        <Bold className="w-3.5 h-3.5" />
      </button>

      <button
        type="button"
        onClick={() => applyFormat('italic')}
        className="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#333333] transition-colors italic"
        title="مائل (Ctrl+I)"
      >
        <Italic className="w-3.5 h-3.5" />
      </button>

      <button
        type="button"
        onClick={() => applyFormat('strikeThrough')}
        className="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#333333] transition-colors"
        title="يتوسطه خط"
      >
        <Strikethrough className="w-3.5 h-3.5" />
      </button>

      <button
        type="button"
        onClick={() => {
          const selection = window.getSelection();
          if (selection && !selection.isCollapsed) {
            applyFormat('formatBlock', '<code>');
          }
        }}
        className="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#333333] transition-colors font-mono"
        title="كود مضمن (Ctrl+E)"
      >
        <Code className="w-3.5 h-3.5" />
      </button>

      <div className="w-[1px] h-4 bg-notion-border dark:bg-[#383838] mx-0.5" />

      {/* رابط */}
      <button
        type="button"
        onClick={() => {
          const url = prompt('أدخل رابط URL:');
          if (url) applyFormat('createLink', url);
        }}
        className="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#333333] transition-colors"
        title="إضافة رابط"
      >
        <LinkIcon className="w-3.5 h-3.5" />
      </button>

      {/* لوحة الألوان */}
      <div className="relative">
        <button
          type="button"
          onClick={() => setShowColorPicker(!showColorPicker)}
          className="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#333333] transition-colors"
          title="لون النص"
        >
          <Palette className="w-3.5 h-3.5" />
        </button>

        {showColorPicker && (
          <div className="absolute top-full left-0 mt-1 p-1.5 bg-white dark:bg-[#252525] rounded-lg border border-notion-border dark:border-[#383838] shadow-notion grid grid-cols-5 gap-1 w-36">
            {colors.map((c) => (
              <button
                key={c.name}
                type="button"
                onClick={() => {
                  applyFormat('foreColor', c.color);
                  setShowColorPicker(false);
                }}
                className="w-5 h-5 rounded-full border border-black/10 dark:border-white/10 flex items-center justify-center hover:scale-110 transition-transform"
                style={{ backgroundColor: c.color === 'inherit' ? '#888888' : c.color }}
                title={c.name}
              />
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

