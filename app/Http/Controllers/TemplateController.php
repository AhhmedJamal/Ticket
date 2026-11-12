<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Page;
use App\Models\Template;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplateController extends Controller
{
    /**
     * عرض جميع القوالب المتاحة
     */
    public function index(Workspace $workspace): View
    {
        $templates = Template::where('is_default', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('templates.index', compact('templates', 'workspace'));
    }

    /**
     * إنشاء صفحة من قالب
     */
    public function applyToPage(Request $request, Workspace $workspace, Template $template): RedirectResponse
    {
        // إنشاء الصفحة
        $page = Page::create([
            'workspace_id' => $workspace->id,
            'parent_id' => $request->input('parent_id'),
            'title' => $template->name,
            'icon' => $template->icon,
            'created_by' => $request->user()->id,
        ]);

        // إضافة البلوكات من القالب
        foreach ($template->blocks_data as $index => $blockData) {
            Block::create([
                'page_id' => $page->id,
                'type' => $blockData['type'],
                'content' => $blockData['content'],
                'position' => $index + 1,
            ]);
        }

        return redirect()->route('pages.show', $page);
    }
}
