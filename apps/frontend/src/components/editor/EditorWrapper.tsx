'use client';

import React from 'react';
import { Block, NotionDatabase } from '@/types';
import { BlockItem } from './BlockItem';
import { DatabaseBlock } from './DatabaseBlock';
import { SlashMenu } from './SlashMenu';
import { Plus } from 'lucide-react';

interface EditorWrapperProps {
    blocks: Block[];
    databases: NotionDatabase[];
    slashOpen: boolean;
    addBlockText: string;
    addBlockType: string;
    setSlashOpen: (open: boolean) => void;
    setAddBlockText: (text: string) => void;
    onUpdateBlock: (blockId: number, content: Record<string, any>) => void;
    onDeleteBlock: (blockId: number) => void;
    onOpenComments: (blockId: number) => void;
    onDragStart: (e: React.DragEvent, index: number) => void;
    onDragOver: (e: React.DragEvent, index: number) => void;
    onDrop: (e: React.DragEvent, index: number) => void;
    onCreateBlock: (type: string, text?: string) => void;
    onCreateDatabase: () => void;
    onCreateSubpage: () => void;
    onUpdateDatabase: (updated: NotionDatabase) => void;
    onDeleteDatabase: (id: number) => void;
}

export const EditorWrapper: React.FC<EditorWrapperProps> = ({
    blocks,
    databases,
    slashOpen,
    addBlockText,
    addBlockType,
    setSlashOpen,
    setAddBlockText,
    onUpdateBlock,
    onDeleteBlock,
    onOpenComments,
    onDragStart,
    onDragOver,
    onDrop,
    onCreateBlock,
    onCreateDatabase,
    onCreateSubpage,
    onUpdateDatabase,
    onDeleteDatabase,
}) => {
    return (
        <div className="w-full space-y-4">
            {/* حاوية البلوكات (Sortable Blocks) */}
            <div id="blocks-container" className="space-y-1 min-h-[80px]">
                {blocks.map((block, idx) => {
                    let numIdx = 1;
                    if (block.type === 'numbered_list') {
                        numIdx =
                            blocks
                                .slice(0, idx + 1)
                                .filter((b) => b.type === 'numbered_list').length;
                    }

                    return (
                        <BlockItem
                            key={block.id}
                            block={block}
                            index={idx}
                            numberedIndex={numIdx}
                            onUpdate={onUpdateBlock}
                            onDelete={onDeleteBlock}
                            onOpenComments={onOpenComments}
                            onDragStart={onDragStart}
                            onDragOver={onDragOver}
                            onDrop={onDrop}
                        />
                    );
                })}
            </div>

            {/* قواعد البيانات المضمنة (Embedded Databases) */}
            <div className="space-y-6">
                {databases.map((db) => (
                    <DatabaseBlock
                        key={db.id}
                        database={db}
                        onUpdateDatabase={onUpdateDatabase}
                        onDeleteDatabase={onDeleteDatabase}
                    />
                ))}
            </div>

            {/* إضافة بلوك جديد (Slash & Block Palette) */}
            <div className="pt-1 space-y-2 relative">
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        if (addBlockText.trim() === '/') {
                            setSlashOpen(true);
                            return;
                        }
                        if (addBlockText.trim()) {
                            onCreateBlock(addBlockType, addBlockText.trim());
                        }
                    }}
                    className="group/addline flex items-center gap-2 py-1 -mx-2 px-2 rounded hover:bg-notion-hover/30 dark:hover:bg-[#232323]/60 transition-colors"
                >
                    <button
                        type="button"
                        onClick={() => setSlashOpen(!slashOpen)}
                        className="w-6 h-6 rounded flex items-center justify-center text-xs text-notion-muted dark:text-[#7F7F7F] hover:text-notion-text dark:hover:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors shrink-0"
                        title="إضافة بلوك جديد أو أمر سريع"
                    >
                        <Plus className="w-4 h-4" />
                    </button>

                    <input
                        type="text"
                        value={addBlockText}
                        onChange={(e) => {
                            const val = e.target.value;
                            setAddBlockText(val);
                            if (val === '/') {
                                setSlashOpen(true);
                            }
                        }}
                        placeholder="اكتب شيئاً، أو اكتب '/' للأوامر..."
                        className="flex-1 text-base bg-transparent border-0 p-0 focus:ring-0 focus:outline-none text-notion-text dark:text-[#E3E3E0] placeholder:text-notion-muted/40 dark:placeholder:text-[#555555]"
                    />
                </form>

                {/* قائمة أوامر السلاش (/) */}
                <SlashMenu
                    isOpen={slashOpen}
                    onClose={() => setSlashOpen(false)}
                    onSelectBlockType={(type) => {
                        onCreateBlock(type, addBlockText.trim() === '/' ? '' : addBlockText.trim());
                    }}
                    onCreateDatabase={onCreateDatabase}
                    onCreateSubpage={onCreateSubpage}
                />
            </div>
        </div>
    );
};

