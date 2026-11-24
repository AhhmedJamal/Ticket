'use client';

import React from 'react';

export const COVER_GRADIENTS: Record<string, { label: string; class: string }> = {
    'gradient-blue': { label: 'أزرق', class: 'bg-gradient-to-r from-blue-400 to-indigo-500' },
    'gradient-purple': { label: 'بنفسجي', class: 'bg-gradient-to-r from-purple-400 to-pink-500' },
    'gradient-green': { label: 'أخضر', class: 'bg-gradient-to-r from-green-400 to-teal-500' },
    'gradient-orange': { label: 'برتقالي', class: 'bg-gradient-to-r from-orange-400 to-red-400' },
    'gradient-gray': { label: 'رمادي', class: 'bg-gradient-to-r from-gray-300 to-gray-500' },
};

interface CoverPickerProps {
    coverColor: string | null;
    coverImage: string | null;
    showPicker: boolean;
    setShowPicker: (show: boolean) => void;
    onSelectColor: (color: string) => void;
    onRemoveCover: () => void;
}

export const CoverPicker: React.FC<CoverPickerProps> = ({
    coverColor,
    coverImage,
    showPicker,
    setShowPicker,
    onSelectColor,
    onRemoveCover,
}) => {
    const hasCover = Boolean(coverColor || coverImage);
    const coverClass = coverColor
        ? COVER_GRADIENTS[coverColor]?.class || 'bg-gradient-to-r from-gray-300 to-gray-500'
        : '';

    return (
        <div className="relative group/cover">
            {hasCover ? (
                <div
                    className={`h-44 w-full relative overflow-hidden ${coverClass}`}
                    style={
                        coverImage
                            ? {
                                  backgroundImage: `url('${coverImage}')`,
                                  backgroundSize: 'cover',
                                  backgroundPosition: 'center',
                              }
                            : {}
                    }
                >
                    {/* أزرار تعديل الغلاف */}
                    <div className="absolute bottom-3 left-6 opacity-0 group-hover/cover:opacity-100 transition-opacity flex gap-1.5 z-10">
                        <button
                            type="button"
                            onClick={() => setShowPicker(!showPicker)}
                            className="text-xs bg-white/90 dark:bg-[#252525]/90 hover:bg-white dark:hover:bg-[#2F2F2F] px-2.5 py-1 rounded-md text-gray-800 dark:text-[#E3E3E0] border border-black/10 dark:border-white/10 backdrop-blur-sm shadow-sm transition-colors"
                        >
                            🎨 تغيير الغلاف
                        </button>
                        <button
                            type="button"
                            onClick={onRemoveCover}
                            className="text-xs bg-white/90 dark:bg-[#252525]/90 hover:bg-white dark:hover:bg-[#2F2F2F] px-2.5 py-1 rounded-md text-gray-800 dark:text-[#E3E3E0] border border-black/10 dark:border-white/10 backdrop-blur-sm shadow-sm transition-colors"
                        >
                            ✕ إزالة
                        </button>
                    </div>
                </div>
            ) : (
                /* شريط علوي خفي لإضافة غلاف عند التمرير */
                <div className="h-8 group/addcover flex items-end px-4 sm:px-12">
                    <button
                        type="button"
                        onClick={() => setShowPicker(!showPicker)}
                        className="text-xs text-notion-muted opacity-0 group-hover/addcover:opacity-100 hover:text-notion-text dark:hover:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2C2C2C] px-2 py-0.5 rounded transition-all"
                    >
                        🖼️ إضافة غلاف
                    </button>
                </div>
            )}

            {/* منتقي لون الغلاف */}
            {showPicker && (
                <div className="absolute top-full left-12 bg-white dark:bg-[#252525] border border-notion-border dark:border-[#333333] rounded-xl p-4 shadow-2xl z-30 w-72">
                    <div className="text-xs font-medium text-notion-muted dark:text-[#9B9A97] mb-3">
                        اختر لون الغلاف:
                    </div>
                    <div className="flex flex-wrap gap-2">
                        {Object.entries(COVER_GRADIENTS).map(([key, info]) => (
                            <button
                                key={key}
                                type="button"
                                onClick={() => {
                                    onSelectColor(key);
                                    setShowPicker(false);
                                }}
                                className={`${info.class} w-8 h-8 rounded-lg border-2 hover:scale-110 transition-transform ${
                                    coverColor === key
                                        ? 'border-notion-text dark:border-white'
                                        : 'border-transparent'
                                }`}
                                title={info.label}
                            />
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
};

