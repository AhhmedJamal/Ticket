<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\BlockComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BlockCommentController extends Controller
{
    /**
     * جلب التعليقات لبلوك معين
     */
    public function index(Block $block): JsonResponse
    {
        Gate::authorize('view', $block->page);

        $comments = $block->comments()->with(['user', 'replies.user'])->get();

        return response()->json([
            'comments' => $comments,
        ]);
    }

    /**
     * إضافة تعليق جديد أو رد على تعليق
     */
    public function store(Request $request, Block $block): JsonResponse
    {
        Gate::authorize('comment', $block->page);

        $data = $request->validate([
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:block_comments,id',
        ]);

        $comment = $block->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $data['parent_id'] ?? null,
            'content' => $data['content'],
            'is_resolved' => false,
        ]);

        return response()->json([
            'message' => 'تم إضافة التعليق بنجاح',
            'comment' => $comment->load('user'),
        ], 201);
    }

    /**
     * تعيين حالة التعليق كمحلول أو مفتوح (Resolve / Reopen)
     */
    public function resolve(Request $request, BlockComment $comment): JsonResponse
    {
        Gate::authorize('comment', $comment->block->page);

        $comment->update([
            'is_resolved' => ! $comment->is_resolved,
        ]);

        return response()->json([
            'message' => $comment->is_resolved ? 'تم حل التعليق' : 'تم إعادة فتح التعليق',
            'comment' => $comment,
        ]);
    }

    /**
     * حذف تعليق
     */
    public function destroy(Request $request, BlockComment $comment): JsonResponse
    {
        // فقط صاحب التعليق أو محرر الصفحة يمكنه حذفه
        $user = $request->user();
        $isAuthor = $comment->user_id === $user->id;
        $isEditor = $comment->block->page && $comment->block->page->getUserRole($user) === 'editor';

        if (! $isAuthor && ! $isEditor) {
            abort(403, 'غير مصرح لك بحذف هذا التعليق.');
        }

        $comment->delete();

        return response()->json([
            'message' => 'تم حذف التعليق بنجاح',
        ]);
    }
}
