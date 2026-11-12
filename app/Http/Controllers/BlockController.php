<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Page;
use App\Models\PageActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    /**
     * إضافة بلوك جديد داخل صفحة
     */
    public function store(Request $request, Page $page): JsonResponse|RedirectResponse
    {
        $allowedTypes = [
            'paragraph', 'heading_1', 'heading_2', 'heading_3',
            'todo', 'code', 'callout', 'quote',
            'bulleted_list', 'numbered_list', 'divider',
            'database', 'image', 'toggle',
        ];

        $request->validate([
            'type' => 'required|string|in:'.implode(',', $allowedTypes),
            'text' => 'nullable|string',
            'position' => 'nullable|integer',
        ]);

        $nextPosition = $request->input('position')
            ?? (($page->blocks()->max('position') ?? 0) + 1);

        $content = ['text' => $request->input('text', '')];

        if ($request->input('type') === 'todo') {
            $content['checked'] = false;
        }

        if ($request->input('type') === 'toggle') {
            $content['open'] = false;
        }

        if ($request->input('type') === 'callout') {
            $content['color'] = 'default';
        }

        if ($request->input('type') === 'code') {
            $content['language'] = 'plaintext';
        }

        $block = $page->blocks()->create([
            'type' => $request->input('type'),
            'content' => $content,
            'position' => $nextPosition,
        ]);

        // تسجيل النشاط
        PageActivity::create([
            'page_id' => $page->id,
            'user_id' => $request->user()->id,
            'action' => 'added_block',
            'description' => "أُضيف بلوك {$block->type}",
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Block created successfully',
                'block' => $block,
            ], 201);
        }

        return redirect()->route('pages.show', $page);
    }

    /**
     * تحديث محتوى البلوك
     */
    public function update(Request $request, Block $block): JsonResponse|RedirectResponse
    {
        $request->validate([
            'text' => 'nullable|string',
            'checked' => 'nullable|boolean',
            'open' => 'nullable|boolean',
            'color' => 'nullable|string|max:30',
            'language' => 'nullable|string|max:50',
            'link_page_id' => 'nullable|integer|exists:pages,id',
            'type' => 'sometimes|string',
            'position' => 'sometimes|integer',
        ]);

        $content = $block->content ?? [];

        if ($request->has('text')) {
            $content['text'] = $request->input('text');
        }

        if ($request->has('checked')) {
            $content['checked'] = (bool) $request->input('checked');
        }

        if ($request->has('open')) {
            $content['open'] = (bool) $request->input('open');
        }

        if ($request->has('color')) {
            $content['color'] = $request->input('color');
        }

        if ($request->has('language')) {
            $content['language'] = $request->input('language');
        }

        if ($request->has('link_page_id')) {
            $content['link_page_id'] = $request->input('link_page_id');
        }

        $data = ['content' => $content];

        if ($request->has('type')) {
            $data['type'] = $request->input('type');
        }

        if ($request->has('position')) {
            $data['position'] = $request->input('position');
        }

        $block->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Block updated successfully',
                'block' => $block,
            ]);
        }

        return redirect()->back();
    }

    /**
     * حذف بلوك
     */
    public function destroy(Request $request, Block $block): JsonResponse|RedirectResponse
    {
        $page = $block->page;

        PageActivity::create([
            'page_id' => $page->id,
            'user_id' => $request->user()->id,
            'action' => 'deleted_block',
            'description' => "حُذف بلوك {$block->type}",
        ]);

        $block->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Block deleted successfully']);
        }

        return redirect()->route('pages.show', $page);
    }

    /**
     * إعادة ترتيب البلوكات دفعة واحدة
     */
    public function reorder(Request $request, Page $page): JsonResponse
    {
        $request->validate([
            'blocks' => 'required|array',
            'blocks.*.id' => 'required|exists:blocks,id',
            'blocks.*.position' => 'required|integer',
        ]);

        foreach ($request->blocks as $item) {
            Block::where('id', $item['id'])
                ->where('page_id', $page->id)
                ->update(['position' => $item['position']]);
        }

        return response()->json(['message' => 'Blocks reordered successfully']);
    }
}
