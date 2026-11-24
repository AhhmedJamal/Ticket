<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\BlockCommentResource;
use App\Models\Block;
use App\Models\BlockComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BlockCommentController extends ApiController
{
    /**
     * جلب تعليقات البلوك
     */
    public function index(Block $block): JsonResponse
    {
        Gate::authorize('view', $block->page);

        $comments = $block->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();

        return $this->successResponse(
            BlockCommentResource::collection($comments),
            'تم جلب التعليقات بنجاح.'
        );
    }

    /**
     * إضافة تعليق جديد أو رد على تعليق
     */
    public function store(Request $request, Block $block): JsonResponse
    {
        Gate::authorize('comment', $block->page);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'exists:block_comments,id'],
        ]);

        $comment = $block->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
            'is_resolved' => false,
        ]);

        return $this->successResponse(
            new BlockCommentResource($comment->load(['user', 'replies.user'])),
            'تم إضافة التعليق بنجاح.',
            201
        );
    }

    /**
     * تعيين حالة التعليق كمحلول أو إعادة فتحه
     */
    public function resolve(Request $request, BlockComment $comment): JsonResponse
    {
        Gate::authorize('comment', $comment->block->page);

        $comment->update([
            'is_resolved' => ! $comment->is_resolved,
        ]);

        return $this->successResponse(
            new BlockCommentResource($comment->fresh(['user', 'replies.user'])),
            $comment->is_resolved ? 'تم حل التعليق بنجاح.' : 'تمت إعادة فتح التعليق.'
        );
    }

    /**
     * حذف تعليق
     */
    public function destroy(Request $request, BlockComment $comment): JsonResponse
    {
        $user = $request->user();
        $isAuthor = $comment->user_id === $user->id;
        $isEditor = $comment->block?->page && $comment->block->page->getUserRole($user) === 'editor';

        if (! $isAuthor && ! $isEditor) {
            return $this->errorResponse('غير مصرح لك بحذف هذا التعليق.', 403);
        }

        $comment->delete();

        return $this->successResponse(null, 'تم حذف التعليق بنجاح.');
    }
}
