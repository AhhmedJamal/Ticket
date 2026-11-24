'use client';

import React, { useState, useEffect, useRef } from 'react';
import { useRouter } from 'next/navigation';
import { useWorkspace } from '@/lib/workspace-context';
import { useTheme } from '@/lib/theme-context';
import { searchService } from '@/services/api';
import { SearchResult } from '@/types';
import { Search, Loader2 } from 'lucide-react';

interface SearchPaletteModalProps {
    isOpen: boolean;
    onClose: () => void;
    currentPageId?: number;
}

export const SearchPaletteModal: React.FC<SearchPaletteModalProps> = ({
    isOpen,
    onClose,
    currentPageId,
}) => {
    const router = useRouter();
    const { currentWorkspace, createPage } = useWorkspace();
    const { toggleTheme } = useTheme();

    const [query, setQuery] = useState('');
    const [results, setResults] = useState<SearchResult[]>([]);
    const [isLoading, setIsLoading] = useState(false);
    const [selectedIndex, setSelectedIndex] = useState(0);
    const inputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        if (isOpen) {
            setQuery('');
            setSelectedIndex(0);
            setTimeout(() => {
                inputRef.current?.focus();
                performSearch('');
            }, 50);
        }
    }, [isOpen]);

    const performSearch = async (q: string) => {
        setIsLoading(true);
        try {
            const data = await searchService.search(q);
            setResults(data);
            setSelectedIndex(0);
        } catch {
            setResults([]);
        } finally {
            setIsLoading(false);
        }
    };

    // Debounced query change
    useEffect(() => {
        if (!isOpen) return;
        const timer = setTimeout(() => {
            performSearch(query);
        }, 200);
        return () => clearTimeout(timer);
    }, [query, isOpen]);

    const executeItem = async (item: SearchResult) => {
        onClose();
        if (item.type === 'action') {
            if (item.action === 'toggle_theme') {
                toggleTheme();
            } else if (item.action === 'create_page') {
                const newPage = await createPage();
                if (newPage) {
                    router.push(`/pages/${newPage.id}`);
                }
            } else if (item.action === 'open_templates') {
                if (currentWorkspace) {
                    router.push(`/workspaces/${currentWorkspace.id}/templates`);
                }
            } else if (item.action === 'export_markdown') {
                if (currentPageId) {
                    window.open(
                        `${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1'}/pages/${currentPageId}/export/markdown`,
                        '_blank'
                    );
                }
            }
        } else if (item.url) {
            router.push(item.url);
        }
    };

    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setSelectedIndex((prev) => Math.min(prev + 1, results.length - 1));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setSelectedIndex((prev) => Math.max(prev - 1, 0));
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (results[selectedIndex]) {
                executeItem(results[selectedIndex]);
            }
        } else if (e.key === 'Escape') {
            onClose();
        }
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-start justify-center pt-20 px-4">
            {/* الخلفية المظلمة */}
            <div
                className="fixed inset-0 bg-black/40 dark:bg-black/60 backdrop-blur-sm"
                onClick={onClose}
            />

            {/* نافذة البحث الذكية (Command Palette) */}
            <div className="relative w-full max-w-xl bg-white dark:bg-[#222222] rounded-2xl shadow-2xl border border-notion-border dark:border-[#333333] overflow-hidden z-10 flex flex-col max-h-[85vh]">
                {/* حقل البحث */}
                <div className="flex items-center gap-3 px-4 py-3 border-b border-notion-border dark:border-[#2D2D2D] bg-notion-sidebar/30 dark:bg-[#1E1E1E]">
                    <Search className="w-4 h-4 text-notion-muted flex-shrink-0" />
                    <input
                        ref={inputRef}
                        type="text"
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        onKeyDown={handleKeyDown}
                        placeholder="ابحث عن صفحة، أو نفّذ أمراً سريعاً..."
                        className="flex-1 text-sm bg-transparent border-none outline-none text-notion-text dark:text-[#E8E8E5] placeholder:text-notion-muted/60"
                    />
                    <div className="flex items-center gap-1.5 text-[10px] text-notion-muted">
                        <span className="px-1.5 py-0.5 rounded bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#383838]">
                            Esc
                        </span>
                    </div>
                </div>

                {/* نتائج البحث */}
                <div className="max-h-96 overflow-y-auto p-2 space-y-1">
                    {isLoading ? (
                        <div className="px-4 py-6 text-center text-sm text-notion-muted">
                            <Loader2 className="w-4 h-4 animate-spin inline-block" />
                        </div>
                    ) : results.length > 0 ? (
                        <div className="space-y-1">
                            {results.map((result, index) => (
                                <div
                                    key={result.url || result.id || index}
                                    onClick={() => executeItem(result)}
                                    onMouseEnter={() => setSelectedIndex(index)}
                                    className={`flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition-colors ${
                                        selectedIndex === index
                                            ? 'bg-notion-hover dark:bg-[#2D2D2D] text-notion-text dark:text-[#F0F0ED]'
                                            : 'text-notion-muted dark:text-[#B0B0AC]'
                                    }`}
                                >
                                    <div className="flex items-center gap-3 min-w-0">
                                        <span className="text-base flex-shrink-0">{result.icon}</span>
                                        <div className="min-w-0">
                                            <div className="text-xs font-medium truncate">
                                                {result.title}
                                            </div>
                                            <div className="flex items-center gap-2 mt-0.5 text-[10px] opacity-75">
                                                <span>{result.workspace || result.category}</span>
                                                {result.match === 'content' && result.excerpt && (
                                                    <span className="truncate max-w-xs">
                                                        · &quot;{result.excerpt}&quot;
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2 shrink-0">
                                        <span className="text-[9px] px-1.5 py-0.5 rounded-md bg-notion-sidebar dark:bg-[#1E1E1E] border border-notion-border dark:border-[#333333] text-notion-muted">
                                            {result.category || 'أمر'}
                                        </span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="px-4 py-8 text-center text-xs text-notion-muted">
                            لا توجد نتائج مطابقة لـ &quot;
                            <span className="font-medium text-notion-text dark:text-white">
                                {query}
                            </span>
                            &quot;
                        </div>
                    )}
                </div>

                {/* شريط الاختصارات السفلي */}
                <div className="px-4 py-2 border-t border-notion-border dark:border-[#2D2D2D] bg-notion-sidebar/40 dark:bg-[#1A1A1A] flex items-center justify-between text-[11px] text-notion-muted">
                    <div className="flex items-center gap-3">
                        <span>
                            <kbd className="font-mono bg-white dark:bg-[#252525] px-1 rounded border border-notion-border dark:border-[#333333]">
                                ↑↓
                            </kbd>{' '}
                            للتنقل
                        </span>
                        <span>
                            <kbd className="font-mono bg-white dark:bg-[#252525] px-1 rounded border border-notion-border dark:border-[#333333]">
                                Enter
                            </kbd>{' '}
                            للاختيار
                        </span>
                    </div>
                    <span>
                        <kbd className="font-mono bg-white dark:bg-[#252525] px-1 rounded border border-notion-border dark:border-[#333333]">
                            Esc
                        </kbd>{' '}
                        للإغلاق
                    </span>
                </div>
            </div>
        </div>
    );
};

