'use client';

import React, { useState, useEffect, useRef } from 'react';
import { Block } from '@/types';
import { highlightCode } from '@/lib/prism-highlighter';
import confetti from 'canvas-confetti';
import {
  GripVertical,
  Plus,
  MessageSquare,
  ChevronRight,
  Copy,
  Check,
  AlertCircle,
} from 'lucide-react';
import { BlockActionMenu } from './BlockActionMenu';

interface BlockItemProps {
  block: Block;
  index: number;
  numberedIndex?: number;
  readOnly?: boolean;
  onUpdate: (blockId: number, content: Record<string, any>) => void;
  onDelete: (blockId: number) => void;
  onInsertBelow?: (afterBlockId: number) => void;
  onDuplicate?: (block: Block) => void;
  onTurnInto?: (blockId: number, newType: string) => void;
  onOpenComments: (blockId: number) => void;
  onDragStart: (e: React.DragEvent, index: number) => void;
  onDragOver: (e: React.DragEvent, index: number) => void;
  onDrop: (e: React.DragEvent, index: number) => void;
}

const CALLOUT_COLORS: Record<string, { bg: string; border: string }> = {
  default: { bg: 'bg-[#F7F6F3] dark:bg-[#202020]', border: 'border-transparent' },
  gray: { bg: 'bg-[#F1F1EF] dark:bg-[#202020]', border: 'border-[#E9E9E7] dark:border-[#2E2E2E]' },
  brown: { bg: 'bg-[#F4EEEE] dark:bg-[#28211E]', border: 'border-[#EADFD9] dark:border-[#382B27]' },
  orange: { bg: 'bg-[#FAEBDD] dark:bg-[#2F231A]', border: 'border-[#F4D7BE] dark:border-[#422D1D]' },
  yellow: { bg: 'bg-[#FBF3DB] dark:bg-[#2E281B]', border: 'border-[#F7E7B8] dark:border-[#43381B]' },
  green: { bg: 'bg-[#EDF3EC] dark:bg-[#1E2B22]', border: 'border-[#D7E9D5] dark:border-[#243F2E]' },
  blue: { bg: 'bg-[#E7F3F8] dark:bg-[#1D282E]', border: 'border-[#CEE7F2] dark:border-[#224054]' },
  purple: { bg: 'bg-[#F4F0F7] dark:bg-[#27212E]', border: 'border-[#E7DEF1] dark:border-[#3A2A4E]' },
  pink: { bg: 'bg-[#F9EFF4] dark:bg-[#2E1E26]', border: 'border-[#F2D7E6] dark:border-[#462337]' },
  red: { bg: 'bg-[#FDEBEC] dark:bg-[#2F1E1E]', border: 'border-[#F9D2D4] dark:border-[#482323]' },
};

export const BlockItem: React.FC<BlockItemProps> = ({
  block,
  index,
  numberedIndex = 1,
  readOnly = false,
  onUpdate,
  onDelete,
  onInsertBelow,
  onDuplicate,
  onTurnInto,
  onOpenComments,
  onDragStart,
  onDragOver,
  onDrop,
}) => {
  const [text, setText] = useState(block.content?.text ?? '');
  const [checked, setChecked] = useState(Boolean(block.content?.checked));
  const [isOpen, setIsOpen] = useState(Boolean(block.content?.open));
  const [color, setColor] = useState(block.content?.color ?? 'default');
  const [language, setLanguage] = useState(block.content?.language ?? 'plaintext');
  const [copied, setCopied] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);

  const textareaRef = useRef<HTMLTextAreaElement>(null);

  useEffect(() => {
    setText(block.content?.text ?? '');
    setChecked(Boolean(block.content?.checked));
    setIsOpen(Boolean(block.content?.open));
    setColor(block.content?.color ?? 'default');
    setLanguage(block.content?.language ?? 'plaintext');
  }, [block.content]);

  // Auto-resize textarea to fit text height
  const adjustHeight = () => {
    if (textareaRef.current) {
      textareaRef.current.style.height = 'auto';
      textareaRef.current.style.height = `${Math.max(28, textareaRef.current.scrollHeight)}px`;
    }
  };

  useEffect(() => {
    adjustHeight();
  }, [text, block.type]);



  const handleSave = (overrides: Record<string, any> = {}) => {
    if (readOnly) return;
    const payload = {
      text,
      checked,
      open: isOpen,
      color,
      language,
      ...overrides,
    };
    onUpdate(block.id, payload);
  };

  const handleCopyCode = () => {
    if (!navigator.clipboard) return;
    navigator.clipboard.writeText(text).then(() => {
      setCopied(true);
      setTimeout(() => setCopied(false), 1500);
    });
  };

  const handleCopyLink = () => {
    const url = `${window.location.origin}${window.location.pathname}#block-${block.id}`;
    navigator.clipboard.writeText(url);
  };

  const handleToggleTodo = (newChecked: boolean) => {
    setChecked(newChecked);
    handleSave({ checked: newChecked });
    if (newChecked) {
      try {
        confetti({
          particleCount: 35,
          spread: 50,
          origin: { y: 0.8 },
          colors: ['#2EAADC', '#0F7B6C', '#DFAB01', '#E03E3E'],
        });
      } catch {
        // graceful ignore
      }
    }
  };

  const handleKeyDown = (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSave();
      if (onInsertBelow) {
        onInsertBelow(block.id);
      }
    } else if (e.key === 'Backspace' && text === '' && !readOnly) {
      e.preventDefault();
      if (block.type !== 'paragraph' && onTurnInto) {
        onTurnInto(block.id, 'paragraph');
      } else {
        onDelete(block.id);
      }
    }
  };

  const isHeading = ['h1', 'heading_1', 'h2', 'heading_2', 'h3', 'heading_3'].includes(block.type);

  return (
    <div
      id={`block-${block.id}`}
      draggable={!readOnly}
      onDragStart={(e) => onDragStart(e, index)}
      onDragOver={(e) => onDragOver(e, index)}
      onDrop={(e) => onDrop(e, index)}
      className="group relative flex items-start py-0.5 px-1 -mx-1 rounded-md transition-colors hover:bg-notion-hover/40 dark:hover:bg-[#252525]/40"
    >
      {/* Notion Gutter Action Handle (+ and ⠿) */}
      {!readOnly && (
        <div className="absolute -right-8 top-1 opacity-0 group-hover:opacity-100 flex items-center gap-0.5 select-none transition-opacity duration-150 z-20">
          {/* Quick Insert Below (+) */}
          {onInsertBelow && (
            <button
              type="button"
              onClick={() => onInsertBelow(block.id)}
              className="p-1 rounded hover:bg-notion-hover dark:hover:bg-[#333333] text-notion-muted hover:text-notion-text transition-colors"
              title="إضافة بلوك بالأسفل"
            >
              <Plus className="w-3.5 h-3.5" />
            </button>
          )}

          {/* 6-dots Drag & Context Menu Handle (⠿) */}
          <div className="relative">
            <button
              type="button"
              onClick={() => setMenuOpen(!menuOpen)}
              className="p-1 rounded cursor-grab active:cursor-grabbing hover:bg-notion-hover dark:hover:bg-[#333333] text-notion-muted hover:text-notion-text transition-colors"
              title="خيارات البلوك وإعادة الترتيب بالسحب"
            >
              <GripVertical className="w-3.5 h-3.5" />
            </button>

            <BlockActionMenu
              currentType={block.type}
              currentColor={color}
              isOpen={menuOpen}
              onClose={() => setMenuOpen(false)}
              onTurnInto={(newType) => {
                if (onTurnInto) onTurnInto(block.id, newType);
                else handleSave({ type: newType });
              }}
              onColorChange={(newColor) => {
                setColor(newColor);
                handleSave({ color: newColor });
              }}
              onDuplicate={() => onDuplicate && onDuplicate(block)}
              onDelete={() => onDelete(block.id)}
              onOpenComments={() => onOpenComments(block.id)}
              onCopyLink={handleCopyLink}
            />
          </div>
        </div>
      )}

      {/* Comment Badge indicator */}
      {block.comments && block.comments.length > 0 && (
        <div className="absolute -left-7 top-1 z-10">
          <button
            type="button"
            onClick={() => onOpenComments(block.id)}
            className="flex items-center gap-1 text-[11px] font-medium text-notion-accent bg-notion-accent/10 px-1.5 py-0.5 rounded-full hover:bg-notion-accent/20 transition-colors"
            title="عرض المناقشة"
          >
            <MessageSquare className="w-3 h-3" />
            <span>{block.comments.length}</span>
          </button>
        </div>
      )}

      {/* ===== 13 Notion Block Types ===== */}
      <div className="w-full flex-1 min-w-0">
        {/* 1. Heading 1 */}
        {(block.type === 'heading_1' || block.type === 'h1') && (
          <textarea
            ref={textareaRef}
            dir="auto"
            rows={1}
            disabled={readOnly}
            value={text}
            onChange={(e) => {
              setText(e.target.value);
              adjustHeight();
            }}
            onBlur={() => handleSave()}
            onKeyDown={handleKeyDown}
            className="w-full font-bold text-2xl sm:text-3xl text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 pt-2 pb-0.5 focus:ring-0 focus:outline-none resize-none leading-tight placeholder:text-notion-muted/40"
            placeholder="عنوان رئيسي 1..."
          />
        )}

        {/* 2. Heading 2 */}
        {(block.type === 'heading_2' || block.type === 'h2') && (
          <textarea
            ref={textareaRef}
            dir="auto"
            rows={1}
            disabled={readOnly}
            value={text}
            onChange={(e) => {
              setText(e.target.value);
              adjustHeight();
            }}
            onBlur={() => handleSave()}
            onKeyDown={handleKeyDown}
            className="w-full font-bold text-xl sm:text-2xl text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 pt-1.5 pb-0.5 focus:ring-0 focus:outline-none resize-none leading-snug placeholder:text-notion-muted/40"
            placeholder="عنوان فرعي 2..."
          />
        )}

        {/* 3. Heading 3 */}
        {(block.type === 'heading_3' || block.type === 'h3') && (
          <textarea
            ref={textareaRef}
            dir="auto"
            rows={1}
            disabled={readOnly}
            value={text}
            onChange={(e) => {
              setText(e.target.value);
              adjustHeight();
            }}
            onBlur={() => handleSave()}
            onKeyDown={handleKeyDown}
            className="w-full font-semibold text-lg text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 pt-1 pb-0.5 focus:ring-0 focus:outline-none resize-none leading-normal placeholder:text-notion-muted/40"
            placeholder="عنوان قسم 3..."
          />
        )}

        {/* 4. Todo */}
        {block.type === 'todo' && (
          <div className="flex items-start gap-2.5 w-full pt-1">
            <button
              type="button"
              disabled={readOnly}
              onClick={() => handleToggleTodo(!checked)}
              className={`mt-1 w-4 h-4 rounded border flex items-center justify-center transition-all ${
                checked
                  ? 'bg-notion-accent border-notion-accent text-white'
                  : 'border-notion-border dark:border-[#4A4A4A] bg-transparent hover:border-notion-accent'
              }`}
            >
              {checked && <Check className="w-3 h-3 stroke-[3]" />}
            </button>
            <textarea
              ref={textareaRef}
              dir="auto"
              rows={1}
              disabled={readOnly}
              value={text}
              onChange={(e) => {
                setText(e.target.value);
                adjustHeight();
              }}
              onBlur={() => handleSave()}
              onKeyDown={handleKeyDown}
              className={`w-full text-[15px] bg-transparent border-none p-0 focus:ring-0 focus:outline-none resize-none leading-relaxed ${
                checked
                  ? 'line-through text-notion-muted dark:text-[#787774]'
                  : 'text-notion-text dark:text-[#E3E3E0]'
              }`}
              placeholder="عنصر في قائمة المهام..."
            />
          </div>
        )}

        {/* 5. Bulleted List */}
        {block.type === 'bulleted_list' && (
          <div className="flex items-start gap-2 w-full pt-0.5">
            <span className="text-notion-muted select-none text-base leading-relaxed shrink-0">•</span>
            <textarea
              ref={textareaRef}
              dir="auto"
              rows={1}
              disabled={readOnly}
              value={text}
              onChange={(e) => {
                setText(e.target.value);
                adjustHeight();
              }}
              onBlur={() => handleSave()}
              onKeyDown={handleKeyDown}
              className="w-full text-[15px] text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 focus:ring-0 focus:outline-none resize-none leading-relaxed placeholder:text-notion-muted/40"
              placeholder="نقطة في قائمة..."
            />
          </div>
        )}

        {/* 6. Numbered List */}
        {block.type === 'numbered_list' && (
          <div className="flex items-start gap-2 w-full pt-0.5">
            <span className="text-notion-muted select-none text-sm font-mono leading-relaxed shrink-0 min-w-[1.25rem]">
              {numberedIndex}.
            </span>
            <textarea
              ref={textareaRef}
              dir="auto"
              rows={1}
              disabled={readOnly}
              value={text}
              onChange={(e) => {
                setText(e.target.value);
                adjustHeight();
              }}
              onBlur={() => handleSave()}
              onKeyDown={handleKeyDown}
              className="w-full text-[15px] text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 focus:ring-0 focus:outline-none resize-none leading-relaxed placeholder:text-notion-muted/40"
              placeholder="عنصر مرقم..."
            />
          </div>
        )}

        {/* 7. Toggle List */}
        {block.type === 'toggle' && (
          <div className="w-full pt-0.5">
            <div className="flex items-start gap-1">
              <button
                type="button"
                onClick={() => {
                  const newOpen = !isOpen;
                  setIsOpen(newOpen);
                  handleSave({ open: newOpen });
                }}
                className="p-0.5 mt-0.5 rounded text-notion-muted hover:text-notion-text transition-transform shrink-0"
              >
                <ChevronRight
                  className={`w-4 h-4 transition-transform duration-200 ${
                    isOpen ? 'rotate-90' : 'rotate-180' // RTL inversion
                  }`}
                />
              </button>
              <textarea
                ref={textareaRef}
                dir="auto"
                rows={1}
                disabled={readOnly}
                value={text}
                onChange={(e) => {
                  setText(e.target.value);
                  adjustHeight();
                }}
                onBlur={() => handleSave()}
                onKeyDown={handleKeyDown}
                className="w-full text-[15px] font-medium text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 focus:ring-0 focus:outline-none resize-none leading-relaxed placeholder:text-notion-muted/40"
                placeholder="قائمة قابلة للطي..."
              />
            </div>
            {isOpen && (
              <div className="mr-6 mt-1.5 p-2 rounded-md border-r-2 border-notion-border dark:border-[#383838] text-sm text-notion-muted dark:text-[#9B9A97]">
                محتوى فرعي داخل القائمة القابلة للطي...
              </div>
            )}
          </div>
        )}

        {/* 8. Quote */}
        {block.type === 'quote' && (
          <div className="w-full border-r-3 border-notion-text dark:border-[#E3E3E0] pr-3 my-1">
            <textarea
              ref={textareaRef}
              dir="auto"
              rows={1}
              disabled={readOnly}
              value={text}
              onChange={(e) => {
                setText(e.target.value);
                adjustHeight();
              }}
              onBlur={() => handleSave()}
              onKeyDown={handleKeyDown}
              className="w-full text-[16px] italic text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 focus:ring-0 focus:outline-none resize-none leading-relaxed placeholder:text-notion-muted/40"
              placeholder="اقتباس مأثور..."
            />
          </div>
        )}

        {/* 9. Callout */}
        {block.type === 'callout' && (
          <div
            className={`w-full flex items-start gap-3 p-3.5 rounded-lg border my-1 transition-colors ${
              CALLOUT_COLORS[color]?.bg || CALLOUT_COLORS.default.bg
            } ${CALLOUT_COLORS[color]?.border || CALLOUT_COLORS.default.border}`}
          >
            <span className="text-xl select-none shrink-0 leading-none mt-0.5">💡</span>
            <textarea
              ref={textareaRef}
              dir="auto"
              rows={1}
              disabled={readOnly}
              value={text}
              onChange={(e) => {
                setText(e.target.value);
                adjustHeight();
              }}
              onBlur={() => handleSave()}
              onKeyDown={handleKeyDown}
              className="w-full text-[15px] text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 focus:ring-0 focus:outline-none resize-none leading-relaxed placeholder:text-notion-muted/40"
              placeholder="ملاحظة أو تنبيه مهم..."
            />
          </div>
        )}

        {/* 10. Code with Line Numbers & Real-Time High-Contrast Syntax Highlighting */}
        {block.type === 'code' && (
          <div className="w-full rounded-lg border border-notion-border dark:border-[#2E2E2E] bg-[#F7F6F3] dark:bg-[#1A1A1A] my-1 overflow-hidden font-mono text-[13px]">
            {/* Code Header Toolbar */}
            <div className="flex items-center justify-between px-3.5 py-1.5 border-b border-notion-border/60 dark:border-[#2A2A2A] bg-black/5 dark:bg-white/5 select-none">
              <div className="flex items-center gap-2">
                <select
                  value={language}
                  disabled={readOnly}
                  dir="ltr"
                  onChange={(e) => {
                    const newLang = e.target.value;
                    setLanguage(newLang);
                    handleSave({ language: newLang });
                  }}
                  className="text-[11px] font-sans font-medium text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] bg-transparent border-0 p-0 focus:ring-0 focus:outline-none cursor-pointer"
                >
                  <option value="javascript" className="dark:bg-[#202020]">JavaScript</option>
                  <option value="typescript" className="dark:bg-[#202020]">TypeScript</option>
                  <option value="php" className="dark:bg-[#202020]">PHP</option>
                  <option value="python" className="dark:bg-[#202020]">Python</option>
                  <option value="sql" className="dark:bg-[#202020]">SQL</option>
                  <option value="html" className="dark:bg-[#202020]">HTML / Markup</option>
                  <option value="css" className="dark:bg-[#202020]">CSS</option>
                  <option value="json" className="dark:bg-[#202020]">JSON</option>
                  <option value="bash" className="dark:bg-[#202020]">Bash</option>
                  <option value="plaintext" className="dark:bg-[#202020]">Plain Text</option>
                </select>
                <span className="text-[10px] text-notion-muted/60">
                  {(text.match(/\n/g) || []).length + 1} أسطر
                </span>
              </div>

              <button
                type="button"
                onClick={handleCopyCode}
                className="flex items-center gap-1 text-[11px] font-sans text-notion-muted hover:text-notion-text transition-colors"
                title="نسخ الكود"
              >
                {copied ? (
                  <>
                    <Check className="w-3.5 h-3.5 text-green-500" />
                    <span className="text-green-500 font-medium">تم النسخ</span>
                  </>
                ) : (
                  <>
                    <Copy className="w-3.5 h-3.5" />
                    <span>نسخ</span>
                  </>
                )}
              </button>
            </div>

            {/* Code Body with Line Numbers & Real-Time Synchronized Highlighting */}
            <div className="relative flex min-h-[56px] p-0 overflow-x-auto text-left" dir="ltr">
              {/* Line Numbers Gutter */}
              <div className="select-none py-3 px-2 text-right text-notion-muted/40 font-mono text-[13px] leading-[1.65] bg-black/5 dark:bg-white/[0.02] border-r border-notion-border/30 dark:border-white/5 min-w-[2.5rem]">
                {Array.from({ length: Math.max(1, (text.match(/\n/g) || []).length + 1) }).map((_, i) => (
                  <div key={i}>{i + 1}</div>
                ))}
              </div>

              {/* Code Edit Container */}
              <div className="relative flex-1 min-w-0">
                {/* 1. Underlying Syntax-Highlighted Layer */}
                <pre
                  aria-hidden="true"
                  className="notion-code-pre pointer-events-none select-none p-3 m-0 whitespace-pre font-mono text-[13px] leading-[1.65] overflow-hidden"
                  dangerouslySetInnerHTML={{
                    __html: (highlightCode(text, language) || '&nbsp;') + (text.endsWith('\n') ? '\n ' : ''),
                  }}
                />

                {/* 2. Interactive Editing Layer (transparent text, visible caret) */}
                <textarea
                  dir="ltr"
                  disabled={readOnly}
                  value={text}
                  onChange={(e) => setText(e.target.value)}
                  onBlur={() => handleSave()}
                  onKeyDown={(e) => {
                    // Support Tab key indentation in code block
                    if (e.key === 'Tab') {
                      e.preventDefault();
                      const start = e.currentTarget.selectionStart;
                      const end = e.currentTarget.selectionEnd;
                      const newText = text.substring(0, start) + '  ' + text.substring(end);
                      setText(newText);
                      setTimeout(() => {
                        if (e.currentTarget) {
                          e.currentTarget.selectionStart = e.currentTarget.selectionEnd = start + 2;
                        }
                      }, 0);
                    }
                  }}
                  spellCheck={false}
                  autoCapitalize="off"
                  autoComplete="off"
                  className="absolute inset-0 w-full h-full p-3 m-0 font-mono text-[13px] leading-[1.65] bg-transparent border-0 text-transparent caret-[#37352F] dark:caret-[#E3E3E0] resize-none focus:outline-none focus:ring-0 whitespace-pre overflow-hidden"
                  placeholder="// اكتب الكود البرمجي هنا..."
                />
              </div>
            </div>
          </div>
        )}

        {/* 11. Divider */}
        {block.type === 'divider' && (
          <div className="w-full py-2">
            <hr className="border-t border-notion-border dark:border-[#2E2E2E]" />
          </div>
        )}

        {/* 12. Paragraph (Default Text) */}
        {(!block.type || block.type === 'paragraph' || block.type === 'text') && (
          <textarea
            ref={textareaRef}
            dir="auto"
            rows={1}
            disabled={readOnly}
            value={text}
            onChange={(e) => {
              setText(e.target.value);
              adjustHeight();
            }}
            onBlur={() => handleSave()}
            onKeyDown={handleKeyDown}
            className="w-full text-[15px] text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 focus:ring-0 focus:outline-none resize-none leading-relaxed placeholder:text-notion-muted/40"
            placeholder="اكتب شيئاً، أو اضغط '/' للأوامر السريعة..."
          />
        )}
      </div>
    </div>
  );
};
