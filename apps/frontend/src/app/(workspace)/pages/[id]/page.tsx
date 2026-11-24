'use client';

import React, { useState, useEffect, useRef } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import { pageService, blockService, databaseService } from '@/services/api';
import { Page, Block, NotionDatabase } from '@/types';
import { useAuth } from '@/lib/auth-context';
import { useWorkspace } from '@/lib/workspace-context';
import { useShell } from '@/lib/shell-context';
import { useToast } from '@/lib/toast-context';
import { PageHeader } from '@/components/workspace/PageHeader';
import { TOCMinimap } from '@/components/editor/TOCMinimap';
import { CommentsDrawer } from '@/components/editor/CommentsDrawer';
import { PageOverviewPanel } from '@/components/editor/PageOverviewPanel';
import { ShareModal } from '@/components/editor/ShareModal';
import { SlashMenu } from '@/components/editor/SlashMenu';
import { BlockItem } from '@/components/editor/BlockItem';
import { BubbleMenu } from '@/components/editor/BubbleMenu';
import { DatabaseBlock } from '@/components/editor/DatabaseBlock';
import { Plus, ArrowLeft, Loader2, Sparkles, FileText } from 'lucide-react';

export default function PageEditorPage() {
  const params = useParams();
  const router = useRouter();
  const pageId = Number(params?.id);
  const { user } = useAuth();
  const { currentWorkspace, refreshCurrentWorkspace } = useWorkspace();
  const { overviewOpen, toggleOverview, setCurrentPage } = useShell();
  const { showToast } = useToast();

  const [page, setPage] = useState<Page | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  // Title, icon & cover state
  const [title, setTitle] = useState('');
  const [icon, setIcon] = useState('📄');
  const [coverColor, setCoverColor] = useState<string | null>(null);
  const [coverImage, setCoverImage] = useState<string | null>(null);
  const [showCoverPicker, setShowCoverPicker] = useState(false);

  // Page mode state (Lock & Full Width)
  const [isLocked, setIsLocked] = useState(false);
  const [isFullWidth, setIsFullWidth] = useState(false);

  // Blocks & Databases state
  const [blocks, setBlocks] = useState<Block[]>([]);
  const [databases, setDatabases] = useState<NotionDatabase[]>([]);

  // Modals and Drawers
  const [shareModalOpen, setShareModalOpen] = useState(false);
  const [activeCommentBlockId, setActiveCommentBlockId] = useState<number | null>(null);
  const [commentDrawerOpen, setCommentDrawerOpen] = useState(false);

  // Slash command state
  const [slashOpen, setSlashOpen] = useState(false);
  const [addBlockText, setAddBlockText] = useState('');
  const [insertAfterBlockId, setInsertAfterBlockId] = useState<number | null>(null);

  // Drag and drop state
  const dragItemIndex = useRef<number | null>(null);

  const loadPage = async () => {
    try {
      const data = await pageService.getById(pageId);
      setPage(data);
      setTitle(data.title || '');
      setIcon(data.icon || '📄');
      setCoverColor(data.cover_color || null);
      setCoverImage(data.cover_image || null);
      setBlocks(data.blocks || []);
      setDatabases(data.databases || []);
      setCurrentPage(data);
    } catch {
      showToast('تعذر تحميل الصفحة', 'error');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    loadPage();
    return () => {
      setCurrentPage(null);
    };
  }, [pageId]);

  const handleUpdateTitleOrMeta = async (fields: {
    title?: string;
    icon?: string;
    cover_color?: string | null;
    cover_image?: string | null;
    is_favorite?: boolean;
  }) => {
    try {
      const updated = await pageService.update(pageId, fields);
      setPage((prev) => (prev ? { ...prev, ...updated } : updated));
      setCurrentPage((prev) => (prev ? { ...prev, ...updated } : updated));
      await refreshCurrentWorkspace();
    } catch {
      showToast('فشل حفظ معلومات الصفحة', 'error');
    }
  };

  // Cover actions
  const handleSetCoverColor = async (color: string) => {
    setCoverColor(color);
    setCoverImage(null);
    await handleUpdateTitleOrMeta({ cover_color: color, cover_image: null });
  };

  const handleRemoveCover = async () => {
    setCoverColor(null);
    setCoverImage(null);
    await handleUpdateTitleOrMeta({ cover_color: '', cover_image: '' });
  };

  // Tags actions
  const handleAttachTag = async (tagId: number) => {
    try {
      const res = await pageService.attachTag(pageId, { tag_id: tagId });
      setPage((prev) => (prev ? { ...prev, tags: res.tags } : prev));
    } catch {
      showToast('فشل إرفاق الوسمة', 'error');
    }
  };

  const handleCreateAndAttachTag = async (tagName: string, tagColor: string) => {
    try {
      const res = await pageService.attachTag(pageId, {
        tag_name: tagName,
        tag_color: tagColor,
      });
      setPage((prev) => (prev ? { ...prev, tags: res.tags } : prev));
    } catch {
      showToast('فشل إضافة الوسمة', 'error');
    }
  };

  const handleDetachTag = async (tagId: number) => {
    try {
      await pageService.detachTag(pageId, tagId);
      setPage((prev) =>
        prev ? { ...prev, tags: (prev.tags || []).filter((t) => t.id !== tagId) } : prev
      );
    } catch {
      showToast('فشل إزالة الوسمة', 'error');
    }
  };

  // Block actions
  const handleCreateBlock = async (type: string, text = '', afterId: number | null = null) => {
    try {
      const newBlock = await blockService.create(pageId, {
        type,
        text,
      });

      if (afterId !== null) {
        setBlocks((prev) => {
          const idx = prev.findIndex((b) => b.id === afterId);
          if (idx === -1) return [...prev, newBlock];
          const next = [...prev];
          next.splice(idx + 1, 0, newBlock);
          return next;
        });
      } else {
        setBlocks((prev) => [...prev, newBlock]);
      }

      setSlashOpen(false);
      setAddBlockText('');
      setInsertAfterBlockId(null);
    } catch {
      showToast('فشل إضافة البلوك', 'error');
    }
  };

  const handleUpdateBlock = async (blockId: number, content: Record<string, any>) => {
    try {
      const updated = await blockService.update(blockId, content);
      setBlocks((prev) => prev.map((b) => (b.id === blockId ? updated : b)));
    } catch {
      showToast('فشل تحديث البلوك', 'error');
    }
  };

  const handleDeleteBlock = async (blockId: number) => {
    try {
      await blockService.delete(blockId);
      setBlocks((prev) => prev.filter((b) => b.id !== blockId));
    } catch {
      showToast('فشل حذف البلوك', 'error');
    }
  };

  const handleDuplicateBlock = async (sourceBlock: Block) => {
    try {
      const newBlock = await blockService.create(pageId, {
        type: sourceBlock.type,
        text: sourceBlock.content?.text || '',
      });
      setBlocks((prev) => {
        const idx = prev.findIndex((b) => b.id === sourceBlock.id);
        const next = [...prev];
        next.splice(idx + 1, 0, newBlock);
        return next;
      });
      showToast('تم تكرار البلوك', 'success');
    } catch {
      showToast('فشل تكرار البلوك', 'error');
    }
  };

  const handleTurnIntoBlock = async (blockId: number, newType: string) => {
    try {
      const updated = await blockService.update(blockId, { type: newType });
      setBlocks((prev) => prev.map((b) => (b.id === blockId ? { ...b, type: newType } : b)));
    } catch {
      showToast('فشل تغيير نوع البلوك', 'error');
    }
  };

  // Drag-and-drop block reorder
  const handleDragStart = (e: React.DragEvent, index: number) => {
    dragItemIndex.current = index;
  };

  const handleDragOver = (e: React.DragEvent, index: number) => {
    e.preventDefault();
  };

  const handleDrop = async (e: React.DragEvent, targetIndex: number) => {
    e.preventDefault();
    const sourceIndex = dragItemIndex.current;
    if (sourceIndex === null || sourceIndex === targetIndex) return;

    const updated = [...blocks];
    const [moved] = updated.splice(sourceIndex, 1);
    updated.splice(targetIndex, 0, moved);
    setBlocks(updated);
    dragItemIndex.current = null;

    const blocksPayload = updated.map((b, i) => ({
      id: b.id,
      position: i + 1,
    }));

    try {
      await blockService.reorder(pageId, blocksPayload);
    } catch {
      showToast('فشل حفظ ترتيب البلوكات', 'error');
    }
  };

  // Database actions
  const handleCreateDatabase = async () => {
    setSlashOpen(false);
    try {
      const newDb = await databaseService.create(pageId, {
        name: 'قاعدة بيانات جديدة',
      });
      setDatabases((prev) => [...prev, newDb]);
      showToast('تم إنشاء قاعدة البيانات', 'success');
    } catch {
      showToast('فشل إنشاء قاعدة البيانات', 'error');
    }
  };

  const handleUpdateDatabase = (updated: NotionDatabase) => {
    setDatabases((prev) => prev.map((d) => (d.id === updated.id ? updated : d)));
  };

  const handleDeleteDatabase = async (id: number) => {
    try {
      await databaseService.delete(id);
      setDatabases((prev) => prev.filter((d) => d.id !== id));
      showToast('تم حذف قاعدة البيانات', 'success');
    } catch {
      showToast('فشل حذف قاعدة البيانات', 'error');
    }
  };

  // Subpage creation from Slash Menu
  const handleCreateSubpage = async () => {
    setSlashOpen(false);
    if (!currentWorkspace) return;
    try {
      const subpage = await pageService.create(currentWorkspace.id, {
        title: 'صفحة فرعية جديدة',
        parent_id: pageId,
      });
      router.push(`/pages/${subpage.id}`);
    } catch {
      showToast('فشل إنشاء الصفحة الفرعية', 'error');
    }
  };

  const handleToggleFavorite = async () => {
    if (!page) return;
    const newFav = !page.is_favorite;
    await handleUpdateTitleOrMeta({ is_favorite: newFav });
  };

  if (isLoading) {
    return (
      <div className="py-32 flex items-center justify-center">
        <Loader2 className="w-7 h-7 animate-spin text-notion-muted" />
      </div>
    );
  }

  if (!page) {
    return (
      <div className="py-32 text-center">
        <p className="text-notion-muted text-sm">الصفحة غير موجودة.</p>
      </div>
    );
  }

  const userRole = (page as any).user_role || 'editor';

  return (
    <div className="relative min-h-[calc(100vh-3.5rem)] pb-36">
      {/* Floating Rich-Text Bubble Formatting Toolbar */}
      <BubbleMenu />

      {/* 1. Authentic Notion Page Header & Properties Table */}
      <PageHeader
        page={page}
        title={title}
        icon={icon}
        coverColor={coverColor}
        coverImage={coverImage}
        showCoverPicker={showCoverPicker}
        userRole={userRole}
        workspaceTags={currentWorkspace?.tags || []}
        blocks={blocks}
        isLocked={isLocked}
        isFullWidth={isFullWidth}
        setShowCoverPicker={setShowCoverPicker}
        onUpdateTitle={(val) => {
          setTitle(val);
          handleUpdateTitleOrMeta({ title: val });
        }}
        onSelectEmoji={(newEmoji) => {
          setIcon(newEmoji);
          handleUpdateTitleOrMeta({ icon: newEmoji });
        }}
        onSelectCoverColor={handleSetCoverColor}
        onRemoveCover={handleRemoveCover}
        onOpenShareModal={() => setShareModalOpen(true)}
        onAttachTag={handleAttachTag}
        onCreateAndAttachTag={handleCreateAndAttachTag}
        onDetachTag={handleDetachTag}
        onToggleFavorite={handleToggleFavorite}
        onToggleLock={() => setIsLocked(!isLocked)}
        onToggleFullWidth={() => setIsFullWidth(!isFullWidth)}
        onOpenComments={() => setCommentDrawerOpen(true)}
      />

      {/* 2. Main Content Canvas */}
      <div
        className={`w-full ${
          isFullWidth ? 'max-w-7xl' : 'max-w-[880px]'
        } mx-auto px-4 sm:px-10 pt-4 transition-all duration-200 space-y-4`}
      >
        {/* Nested Subpages Links */}
        {page.children && page.children.length > 0 && (
          <div className="space-y-1.5 pt-1 pb-2">
            <div className="text-[11px] font-medium text-notion-muted uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
              <FileText className="w-3.5 h-3.5" />
              <span>الصفحات التابعة</span>
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
              {page.children.map((sub) => (
                <Link
                  key={sub.id}
                  href={`/pages/${sub.id}`}
                  className="flex items-center justify-between p-2.5 rounded-lg border border-notion-border dark:border-[#2E2E2E] bg-white dark:bg-[#202020] hover:bg-notion-hover dark:hover:bg-[#282828] transition-colors group"
                >
                  <div className="flex items-center gap-2 truncate">
                    <span className="text-base shrink-0">{sub.icon || '📄'}</span>
                    <span className="text-xs font-medium text-notion-text dark:text-[#E3E3E0] truncate">
                      {sub.title || 'بدون عنوان'}
                    </span>
                  </div>
                  <ArrowLeft className="w-3.5 h-3.5 text-notion-muted group-hover:text-notion-text transition-colors shrink-0" />
                </Link>
              ))}
            </div>
          </div>
        )}

        {/* Sortable Blocks Canvas */}
        <div id="blocks-container" className="space-y-0.5 min-h-[60px]">
          {blocks.map((block, idx) => {
            let numIdx = 1;
            if (block.type === 'numbered_list') {
              numIdx =
                blocks.slice(0, idx + 1).filter((b) => b.type === 'numbered_list').length;
            }

            return (
              <BlockItem
                key={block.id}
                block={block}
                index={idx}
                numberedIndex={numIdx}
                readOnly={isLocked}
                onUpdate={handleUpdateBlock}
                onDelete={handleDeleteBlock}
                onInsertBelow={(afterId) => {
                  setInsertAfterBlockId(afterId);
                  handleCreateBlock('paragraph', '', afterId);
                }}
                onDuplicate={handleDuplicateBlock}
                onTurnInto={handleTurnIntoBlock}
                onOpenComments={(bId) => {
                  setActiveCommentBlockId(bId);
                  setCommentDrawerOpen(true);
                }}
                onDragStart={handleDragStart}
                onDragOver={handleDragOver}
                onDrop={handleDrop}
              />
            );
          })}
        </div>

        {/* Embedded Databases */}
        <div className="space-y-6 pt-2">
          {databases.map((db) => (
            <DatabaseBlock
              key={db.id}
              database={db}
              onUpdateDatabase={handleUpdateDatabase}
              onDeleteDatabase={handleDeleteDatabase}
            />
          ))}
        </div>

        {/* Bottom Quick-Add Block Line / Slash Trigger */}
        {!isLocked && (
          <div className="pt-2 relative select-none">
            <form
              onSubmit={(e) => {
                e.preventDefault();
                if (addBlockText.trim() === '/') {
                  setSlashOpen(true);
                  return;
                }
                if (addBlockText.trim()) {
                  handleCreateBlock('paragraph', addBlockText.trim());
                }
              }}
              className="flex items-center gap-2 py-1 px-1 -mx-1 rounded hover:bg-notion-hover/30 dark:hover:bg-[#252525]/30 transition-colors"
            >
              <button
                type="button"
                onClick={() => setSlashOpen(!slashOpen)}
                className="w-5 h-5 rounded flex items-center justify-center text-xs text-notion-muted hover:text-notion-text hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors shrink-0"
                title="إضافة كتلة أو أمر جديد"
              >
                <Plus className="w-3.5 h-3.5" />
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
                placeholder="اكتب شيئاً، أو اضغط '/' للأوامر السريعة..."
                className="flex-1 text-[15px] bg-transparent border-0 p-0 focus:ring-0 focus:outline-none text-notion-text dark:text-[#E3E3E0] placeholder:text-notion-muted/40"
              />
            </form>

            {/* Notion Slash Command Dropdown */}
            <SlashMenu
              isOpen={slashOpen}
              onClose={() => setSlashOpen(false)}
              onSelectBlockType={(type) => {
                handleCreateBlock(
                  type,
                  addBlockText.trim() === '/' ? '' : addBlockText.trim(),
                  insertAfterBlockId
                );
              }}
              onCreateDatabase={handleCreateDatabase}
              onCreateSubpage={handleCreateSubpage}
            />
          </div>
        )}
      </div>

      {/* Floating TOC Minimap */}
      <TOCMinimap blocks={blocks} />

      {/* Page Overview Panel */}
      <PageOverviewPanel
        isOpen={overviewOpen}
        page={page}
        databasesCount={databases.length}
        onClose={() => toggleOverview()}
      />

      {/* Block Comments Drawer */}
      <CommentsDrawer
        isOpen={commentDrawerOpen}
        blockId={activeCommentBlockId}
        onClose={() => {
          setCommentDrawerOpen(false);
          setActiveCommentBlockId(null);
        }}
        onCommentCountChange={(bId, count) => {
          setBlocks((prev) =>
            prev.map((b) =>
              b.id === bId ? { ...b, comments: Array(count).fill({} as any) } : b
            )
          );
        }}
      />

      {/* Share Modal */}
      <ShareModal
        isOpen={shareModalOpen}
        pageId={page.id}
        ownerEmail={currentWorkspace?.owner?.email}
        onClose={() => setShareModalOpen(false)}
      />
    </div>
  );
}
