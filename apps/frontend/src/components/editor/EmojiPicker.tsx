'use client';

import React, { useState } from 'react';

const EMOJIS = [
    '📄', '📝', '📋', '📌', '📎', '🗂️', '📁', '📂', '🗒️', '📓',
    '📔', '📒', '📕', '📗', '📘', '📙', '📚', '🔖', '🏷️', '💡',
    '⭐', '🎯', '✅', '❌', '🔥', '💎', '🚀', '🌟', '⚡', '🎉',
    '🎨', '💻', '🔧', '🛠️', '🏠', '🌍', '🌈', '🎵', '🎬', '📸',
    '🎭', '🏆', '🥇', '💪', '🧠', '❤️', '🌸', '🌺', '🍀', '🦋',
    '🌙', '☀️', '🌤️', '⛅', '🌊', '🏔️', '🌴'
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
    const [customInput, setCustomInput] = useState('');

    return (
        <div className="relative">
            <button
                type="button"
                onClick={() => setIsOpen(!isOpen)}
                className={`text-6xl hover:scale-105 transition-transform select-none flex items-center justify-center leading-none ${
                    hasCover
                        ? 'w-20 h-20 rounded-2xl bg-white dark:bg-[#191919] shadow-md ring-4 ring-white dark:ring-[#191919]'
                        : ''
                }`}
                title="تغيير الأيقونة"
            >
                <span>{icon || '📄'}</span>
            </button>

            {/* قائمة الأيقونات */}
            {isOpen && (
                <div
                    className="absolute top-full mt-2 right-0 z-40 bg-white dark:bg-[#252525] border border-notion-border dark:border-[#333333] rounded-xl shadow-2xl p-4 w-72"
                >
                    <div className="text-xs font-medium text-notion-muted dark:text-[#9B9A97] mb-3">
                        اختر أيقونة:
                    </div>
                    <div className="grid grid-cols-8 gap-1 max-h-48 overflow-y-auto">
                        {EMOJIS.map((emoji) => (
                            <button
                                key={emoji}
                                type="button"
                                onClick={() => {
                                    onSelectEmoji(emoji);
                                    setIsOpen(false);
                                }}
                                className="text-2xl hover:bg-notion-hover dark:hover:bg-[#2C2C2C] rounded p-1 transition-colors"
                            >
                                {emoji}
                            </button>
                        ))}
                    </div>
                    <div className="mt-3 border-t border-notion-border dark:border-[#333333] pt-2">
                        <input
                            type="text"
                            value={customInput}
                            placeholder="أو اكتب emoji هنا..."
                            onChange={(e) => setCustomInput(e.target.value)}
                            onKeyDown={(e) => {
                                if (e.key === 'Enter' && customInput.trim()) {
                                    onSelectEmoji(customInput.trim());
                                    setIsOpen(false);
                                    setCustomInput('');
                                }
                            }}
                            className="w-full text-sm border border-notion-border dark:border-[#333333] rounded-lg px-2.5 py-1.5 focus:outline-none dark:bg-[#191919] dark:text-[#E3E3E0]"
                        />
                    </div>
                </div>
            )}
        </div>
    );
};

