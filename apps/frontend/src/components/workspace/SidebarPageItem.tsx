'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { Page } from '@/types';
import { useWorkspace } from '@/lib/workspace-context';
import { ChevronDown, Plus, Trash2 } from 'lucide-react';

interface SidebarPageItemProps {
    page: Page;
    level?: number;
    onAddSubpage: (parentId: number) => void;
    onDeletePage: (page: Page) => void;
}

export const SidebarPageItem: React.FC<SidebarPageItemProps> = ({
    page,
    level = 0,
    onAddSubpage,
    onDeletePage,
}) => {
    const pathname = usePathname();
    const [isOpen, setIsOpen] = useState(true);
    const hasChildren = Boolean(page.children && page.children.length > 0);
    const isActive = pathname === `/pages/${page.id}`;

    return (
        <div className="text-sm">
            <div
                className={`sidebar-item group flex items-center justify-between px-2 py-1 rounded-md hover:bg-notion-hover dark:hover:bg-[#2F2F2F] cursor-pointer transition-colors ${
                    isActive ? 'bg-notion-hover dark:bg-[#2F2F2F] font-medium' : ''
                }`}
                style={{
                    paddingRight: level > 0 ? `${8 + level * 12}px` : '8px',
                }}
            >
                <div className="flex items-center gap-1 flex-1 min-w-0">
                    {/* سهم الطي */}
                    {hasChildren ? (
                        <button
                            type="button"
                            onClick={(e) => {
                                e.stopPropagation();
                                setIsOpen(!isOpen);
                            }}
                            className="text-notion-muted hover:text-notion-text dark:hover:text-[#E8E8E5] p-0.5 rounded flex-shrink-0"
                            title={isOpen ? 'طي' : 'توسيع'}
                        >
                            <ChevronDown
                                className={`w-3 h-3 transition-transform duration-150 ${
                                    !isOpen ? 'rotate-90' : ''
                                }`}
                            />
                        </button>
                    ) : (
                        <span className="w-4 inline-block flex-shrink-0" />
                    )}

                    <Link
                        href={`/pages/${page.id}`}
                        className="flex items-center gap-1.5 flex-1 min-w-0 py-0.5"
                    >
                        <span className="text-sm flex-shrink-0">{page.icon || '📄'}</span>
                        <span className="truncate text-notion-text dark:text-[#E8E8E5] text-[13px]">
                            {page.title || 'بدون عنوان'}
                        </span>
                    </Link>
                </div>

                {/* أزرار سريعة تظهر عند التمرير */}
                <div className="actions opacity-0 group-hover:opacity-100 flex items-center gap-0.5 transition-opacity flex-shrink-0">
                    <button
                        type="button"
                        onClick={(e) => {
                            e.stopPropagation();
                            onAddSubpage(page.id);
                        }}
                        title="إضافة صفحة فرعية"
                        className="text-notion-muted hover:text-notion-text dark:hover:text-[#E8E8E5] p-1 hover:bg-zinc-200 dark:hover:bg-[#3a3a3a] rounded transition-colors"
                    >
                        <Plus className="w-3 h-3" />
                    </button>
                    <button
                        type="button"
                        onClick={(e) => {
                            e.stopPropagation();
                            onDeletePage(page);
                        }}
                        title="حذف"
                        className="text-notion-muted hover:text-red-500 p-1 transition-colors"
                    >
                        <Trash2 className="w-3 h-3" />
                    </button>
                </div>
            </div>

            {/* الصفحات الفرعية المتداخلة (Recursive) */}
            {hasChildren && isOpen && (
                <div className="mt-0.5 space-y-0.5 border-r border-notion-border dark:border-[#3a3a3a] mr-3">
                    {page.children?.map((child) => (
                        <SidebarPageItem
                            key={child.id}
                            page={child}
                            level={level + 1}
                            onAddSubpage={onAddSubpage}
                            onDeletePage={onDeletePage}
                        />
                    ))}
                </div>
            )}
        </div>
    );
};

