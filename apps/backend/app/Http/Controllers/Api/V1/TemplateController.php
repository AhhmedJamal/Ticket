<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\PageResource;
use App\Http\Resources\Api\V1\TemplateResource;
use App\Models\Block;
use App\Models\Page;
use App\Models\Template;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplateController extends ApiController
{
    /**
     * استعراض جميع القوالب المتاحة
     */
    public function index(Workspace $workspace): JsonResponse
    {
        $templates = Template::where('is_default', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            TemplateResource::collection($templates),
            'تم جلب القوالب بنجاح.'
        );
    }

    /**
     * تطبيق قالب لإنشاء صفحة جديدة وبلوكاتها
     */
    public function applyToPage(Request $request, Workspace $workspace, Template $template): JsonResponse
    {
        $user = $request->user();

        $page = Page::create([
            'workspace_id' => $workspace->id,
            'parent_id' => $request->input('parent_id'),
            'title' => $template->name,
            'icon' => $template->icon,
            'created_by' => $user->id,
        ]);

        foreach ($template->blocks_data as $index => $blockData) {
            Block::create([
                'page_id' => $page->id,
                'type' => $blockData['type'],
                'content' => $blockData['content'],
                'position' => $index + 1,
            ]);
        }

        return $this->successResponse(
            new PageResource($page->load(['creator', 'blocks'])),
            'تم تطبيق القالب وإنشاء الصفحة بنجاح.',
            201
        );
    }
}
