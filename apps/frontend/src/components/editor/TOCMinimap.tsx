'use client';

import React, { useState } from 'react';
import { Block } from '@/types';

interface TOCMinimapProps {
    blocks: Block[];
}

export const TOCMinimap: React.FC<TOCMinimapProps> = ({ blocks }) => {
    const [isHovered, setIsHovered] = useState(false);

    const headingBlocks = blocks.filter(
        (b) =>
            ['heading_1', 'heading', 'heading_2', 'heading_3'].includes(b.type) &&
            Boolean(b.content?.text?.trim())
    );

    if (headingBlocks.length === 0) return null;

    return (
        <div
            onMouseEnter={() => setIsHovered(true)}
            onMouseLeave={() => setIsHovered(false)}
            className="hidden xl:block fixed left-5 top-1/3 z-20"
        >
            {/* الخطوط المصغرة (Minimap Bars) */}
            {!isHovered ? (
                <div className="flex flex-col items-end gap-2 p-2 cursor-pointer">
                    {headingBlocks.map((hb) => {
                        const widthClass = ['heading_1', 'heading'].includes(hb.type)
                            ? 'w-4'
                            : hb.type === 'heading_2'
                            ? 'w-3'
                            : 'w-2';

                        return (
                            <span
                                key={hb.id}
                                className={`h-[2px] rounded-full bg-notion-muted/40 dark:bg-[#555555] ${widthClass}`}
                            />
                        );
                    })}
                </div>
            ) : (
                /* القائمة الموسعة عند التمرير */
                <div className="w-52 bg-white dark:bg-[#252525] border border-notion-border dark:border-[#333333] rounded-xl shadow-2xl p-2 space-y-0.5">
                    {headingBlocks.map((hb) => {
                        const paddingClass =
                            hb.type === 'heading_2'
                                ? 'pr-4'
                                : hb.type === 'heading_3'
                                ? 'pr-6'
                                : 'font-medium';

                        return (
                            <a
                                key={hb.id}
                                href={`#block-${hb.id}`}
                                className={`block truncate px-2 py-1 rounded text-xs text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors ${paddingClass}`}
                            >
                                {hb.content?.text}
                            </a>
                        );
                    })}
                </div>
            )}
        </div>
    );
};

