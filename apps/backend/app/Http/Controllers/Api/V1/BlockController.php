<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Block\ReorderBlocksRequest;
use App\Http\Requests\Api\V1\Block\StoreBlockRequest;
use App\Http\Requests\Api\V1\Block\UpdateBlockRequest;
use App\Http\Resources\Api\V1\BlockResource;
use App\Models\Block;
use App\Models\Page;
use App\Models\PageActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BlockController extends ApiController
{
    /**
     * إضافة بلوك جديد لصفحة معينة
     */
    public function store(StoreBlockRequest $request, Page $page): JsonResponse
    {
        Gate::authorize('update', $page);

        $type = $request->validated('type');
        $text = $request->input('text', '');

        $nextPosition = $request->input('position')
            ?? (($page->blocks()->max('position') ?? 0) + 1);

        $content = ['text' => $text];

        if ($type === 'todo') {
            $content['checked'] = false;
        }

        if ($type === 'toggle') {
            $content['open'] = false;
        }

        if ($type === 'callout') {
            $content['color'] = 'default';
        }

        if ($type === 'code') {
            $content['language'] = 'plaintext';
        }

        $block = $page->blocks()->create([
            'type' => $type,
            'content' => $content,
            'position' => $nextPosition,
        ]);

        PageActivity::create([
            'page_id' => $page->id,
            'user_id' => $request->user()->id,
            'action' => 'added_block',
            'description' => "أُضيف بلوك {$block->type}",
        ]);

        return $this->successResponse(
            new BlockResource($block),
            'تم إنشاء البلوك بنجاح.',
            201
        );
    }

    /**
     * تحديث محتوى البلوك
     */
    public function update(UpdateBlockRequest $request, Block $block): JsonResponse
    {
        Gate::authorize('update', $block);

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

        return $this->successResponse(
            new BlockResource($block),
            'تم تحديث البلوك بنجاح.'
        );
    }

    /**
     * حذف بلوك
     */
    public function destroy(Request $request, Block $block): JsonResponse
    {
        Gate::authorize('delete', $block);

        $page = $block->page;

        PageActivity::create([
            'page_id' => $page->id,
            'user_id' => $request->user()->id,
            'action' => 'deleted_block',
            'description' => "حُذف بلوك {$block->type}",
        ]);

        $block->delete();

        return $this->successResponse(null, 'تم حذف البلوك بنجاح.');
    }

    /**
     * إعادة ترتيب البلوكات دفعة واحدة
     */
    public function reorder(ReorderBlocksRequest $request, Page $page): JsonResponse
    {
        Gate::authorize('update', $page);

        foreach ($request->validated('blocks') as $item) {
            Block::where('id', $item['id'])
                ->where('page_id', $page->id)
                ->update(['position' => $item['position']]);
        }

        return $this->successResponse(null, 'تمت إعادة ترتيب البلوكات بنجاح.');
    }
}
