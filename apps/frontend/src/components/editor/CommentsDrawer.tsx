'use client';

import React, { useState, useEffect } from 'react';
import { blockCommentService } from '@/services/api';
import { BlockComment } from '@/types';
import { useToast } from '@/lib/toast-context';
import { Loader2, MessageSquare, X, Check } from 'lucide-react';

interface CommentsDrawerProps {
    isOpen: boolean;
    blockId: number | null;
    onClose: () => void;
    onCommentCountChange?: (blockId: number, count: number) => void;
}

export const CommentsDrawer: React.FC<CommentsDrawerProps> = ({
    isOpen,
    blockId,
    onClose,
    onCommentCountChange,
}) => {
    const { showToast } = useToast();
    const [comments, setComments] = useState<BlockComment[]>([]);
    const [isLoading, setIsLoading] = useState(false);
    const [newCommentText, setNewCommentText] = useState('');
    const [replyTexts, setReplyTexts] = useState<Record<number, string>>({});
    const [isSubmitting, setIsSubmitting] = useState(false);

    useEffect(() => {
        if (isOpen && blockId) {
            loadComments(blockId);
        } else {
            setComments([]);
            setNewCommentText('');
        }
    }, [isOpen, blockId]);

    const loadComments = async (id: number) => {
        setIsLoading(true);
        try {
            const list = await blockCommentService.getByBlock(id);
            setComments(list);
            if (onCommentCountChange) {
                onCommentCountChange(id, list.length);
            }
        } catch {
            showToast('فشل تحميل التعليقات', 'error');
        } finally {
            setIsLoading(false);
        }
    };

    const handleAddComment = async () => {
        if (!newCommentText.trim() || !blockId) return;
        setIsSubmitting(true);
        try {
            const comment = await blockCommentService.create(blockId, {
                content: newCommentText.trim(),
            });
            comment.replies = [];
            const updated = [comment, ...comments];
            setComments(updated);
            setNewCommentText('');
            if (onCommentCountChange) {
                onCommentCountChange(blockId, updated.length);
            }
            showToast('تمت إضافة التعليق', 'success');
        } catch {
            showToast('فشل إضافة التعليق', 'error');
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleAddReply = async (commentId: number) => {
        const text = replyTexts[commentId];
        if (!text || !text.trim() || !blockId) return;

        try {
            const reply = await blockCommentService.create(blockId, {
                content: text.trim(),
                parent_id: commentId,
            });
            setComments((prev) =>
                prev.map((c) => {
                    if (c.id === commentId) {
                        return {
                            ...c,
                            replies: [...(c.replies || []), reply],
                        };
                    }
                    return c;
                })
            );
            setReplyTexts((prev) => ({ ...prev, [commentId]: '' }));
        } catch {
            showToast('فشل إرسال الرد', 'error');
        }
    };

    const handleToggleResolve = async (comment: BlockComment) => {
        try {
            const updated = await blockCommentService.toggleResolve(comment.id);
            setComments((prev) =>
                prev.map((c) => (c.id === comment.id ? { ...c, is_resolved: updated.is_resolved } : c))
            );
        } catch {
            showToast('فشل تحديث حالة التعليق', 'error');
        }
    };

    const handleDeleteComment = async (commentId: number, isReply = false, parentId?: number) => {
        if (!window.confirm('حذف هذا التعليق؟')) return;
        try {
            await blockCommentService.delete(commentId);
            if (isReply && parentId) {
                setComments((prev) =>
                    prev.map((c) =>
                        c.id === parentId
                            ? { ...c, replies: c.replies?.filter((r) => r.id !== commentId) }
                            : c
                    )
                );
            } else {
                const filtered = comments.filter((c) => c.id !== commentId);
                setComments(filtered);
                if (blockId && onCommentCountChange) {
                    onCommentCountChange(blockId, filtered.length);
                }
            }
            showToast('تم حذف التعليق', 'success');
        } catch {
            showToast('فشل حذف التعليق', 'error');
        }
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 overflow-hidden flex justify-end">
            {/* خلفية التعتيم */}
            <div
                className="fixed inset-0 bg-black/40 backdrop-blur-[1px]"
                onClick={onClose}
            />

            {/* الدرج الجانبي */}
            <div className="relative w-full max-w-sm bg-white dark:bg-[#202020] border-r border-notion-border dark:border-[#2E2E2E] h-full shadow-2xl flex flex-col z-10 transition-transform duration-200">
                {/* رأس الدرج */}
                <div className="flex items-center justify-between px-4 py-3 border-b border-notion-border dark:border-[#2E2E2E] bg-notion-sidebar/40 dark:bg-[#1E1E1E]">
                    <div className="flex items-center gap-2 text-xs font-semibold text-notion-text dark:text-[#E3E3E0]">
                        <MessageSquare className="w-4 h-4" />
                        <span>تعليقات البلوك</span>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="text-xs text-notion-muted hover:text-notion-text dark:hover:text-[#E3E3E0] px-1"
                    >
                        <X className="w-4 h-4" />
                    </button>
                </div>

                {/* قائمة التعليقات */}
                <div className="flex-1 overflow-y-auto p-4 space-y-4">
                    {isLoading ? (
                        <div className="text-center py-8 text-xs text-notion-muted">
                            <Loader2 className="w-5 h-5 animate-spin mx-auto" />
                        </div>
                    ) : comments.length === 0 ? (
                        <div className="text-center py-12 text-xs text-notion-muted dark:text-[#787774]">
                            لا توجد تعليقات بعد. أضف أول تعليق أدناه.
                        </div>
                    ) : (
                        comments.map((comment) => (
                            <div
                                key={comment.id}
                                className={`p-3 rounded-xl border border-notion-border dark:border-[#2D2D2D] bg-notion-sidebar/30 dark:bg-[#252525] space-y-2.5 ${
                                    comment.is_resolved ? 'opacity-60' : ''
                                }`}
                            >
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <span className="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 font-bold text-[10px] flex items-center justify-center">
                                            {comment.user?.name ? comment.user.name.charAt(0) : 'م'}
                                        </span>
                                        <span className="text-xs font-medium text-notion-text dark:text-[#E3E3E0]">
                                            {comment.user?.name || 'مستخدم'}
                                        </span>
                                    </div>
                                    <div className="flex items-center gap-1.5">
                                        <button
                                            type="button"
                                            onClick={() => handleToggleResolve(comment)}
                                            className={`text-[10px] px-1.5 py-0.5 rounded border border-notion-border dark:border-[#383838] transition-colors ${
                                                comment.is_resolved
                                                    ? 'text-green-500 font-bold'
                                                    : 'text-notion-muted hover:text-green-500'
                                            }`}
                                            title={comment.is_resolved ? 'إعادة الفتح' : 'تعيين كمحلول'}
                                        >
                                            {comment.is_resolved ? '✓ محلول' : 'حل'}
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => handleDeleteComment(comment.id)}
                                            className="text-[10px] text-notion-muted hover:text-red-500 p-0.5"
                                        >
                                            <X className="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>

                                <p className="text-xs text-notion-text dark:text-[#E3E3E0] leading-relaxed whitespace-pre-wrap">
                                    {comment.content}
                                </p>

                                {/* الردود (Replies) */}
                                {comment.replies && comment.replies.length > 0 && (
                                    <div className="mr-3 border-r-2 border-notion-border dark:border-[#333333] pr-2.5 space-y-2 mt-2 pt-1">
                                        {comment.replies.map((reply) => (
                                            <div key={reply.id} className="text-[11px] space-y-1">
                                                <div className="flex items-center justify-between text-notion-muted">
                                                    <span className="font-medium text-notion-text dark:text-[#E3E3E0]">
                                                        {reply.user?.name || 'مستخدم'}
                                                    </span>
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            handleDeleteComment(reply.id, true, comment.id)
                                                        }
                                                        className="hover:text-red-500 text-[10px]"
                                                    >
                                                        ✕
                                                    </button>
                                                </div>
                                                <p className="text-notion-text dark:text-[#C5C5C2]">
                                                    {reply.content}
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                )}

                                {/* زر وكتابة الرد */}
                                <div className="pt-1.5 flex items-center gap-1.5">
                                    <input
                                        type="text"
                                        value={replyTexts[comment.id] || ''}
                                        onChange={(e) =>
                                            setReplyTexts((prev) => ({
                                                ...prev,
                                                [comment.id]: e.target.value,
                                            }))
                                        }
                                        onKeyDown={(e) => {
                                            if (e.key === 'Enter') handleAddReply(comment.id);
                                        }}
                                        placeholder="اكتب رداً..."
                                        className="flex-1 text-[11px] px-2 py-1 rounded bg-transparent border border-notion-border dark:border-[#353535] text-notion-text dark:text-[#E3E3E0] focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => handleAddReply(comment.id)}
                                        className="text-[10px] px-2 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded font-medium transition-colors"
                                    >
                                        رد
                                    </button>
                                </div>
                            </div>
                        ))
                    )}
                </div>

                {/* نموذج إضافة تعليق جديد */}
                <div className="p-3 border-t border-notion-border dark:border-[#2E2E2E] bg-notion-sidebar/40 dark:bg-[#1E1E1E]">
                    <textarea
                        rows={2}
                        value={newCommentText}
                        onChange={(e) => setNewCommentText(e.target.value)}
                        placeholder="اكتب تعليقاً على هذا البلوك..."
                        className="w-full text-xs p-2 rounded-lg bg-white dark:bg-[#252525] border border-notion-border dark:border-[#353535] text-notion-text dark:text-[#E3E3E0] focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none"
                    />
                    <div className="flex justify-end mt-2">
                        <button
                            type="button"
                            disabled={isSubmitting || !newCommentText.trim()}
                            onClick={handleAddComment}
                            className="text-xs px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-medium transition-colors shadow-sm disabled:opacity-50"
                        >
                            {isSubmitting ? 'جاري الإرسال...' : 'إرسال التعليق'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
};

