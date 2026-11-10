<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Page;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    /**
     * إضافة بلوك جديد داخل صفحة
     */
    public function store(Request $request, Page $page)
    {
        $request->validate([
            'type' => 'required|string|in:paragraph,heading_1,heading_2,heading_3,todo,code,callout,quote,bulleted_list,numbered_list,divider',
            'text' => 'nullable|string',
            'position' => 'nullable|integer',
        ]);

        $nextPosition = $request->input('position') 
            ?? (($page->blocks()->max('position') ?? 0) + 1);

        $content = [
            'text' => $request->input('text', ''),
        ];

        if ($request->input('type') === 'todo') {
            $content['checked'] = false;
        }

        $block = $page->blocks()->create([
            'type' => $request->input('type'),
            'content' => $content,
            'position' => $nextPosition,
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
    public function update(Request $request, Block $block)
    {
        $request->validate([
            'text' => 'nullable|string',
            'checked' => 'nullable|boolean',
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
    public function destroy(Request $request, Block $block)
    {
        $page = $block->page;
        $block->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Block deleted successfully',
            ]);
        }

        return redirect()->route('pages.show', $page);
    }

    /**
     * إعادة ترتيب البلوكات دفعة واحدة
     */
    public function reorder(Request $request, Page $page)
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

        return response()->json([
            'message' => 'Blocks reordered successfully',
        ]);
    }
}
