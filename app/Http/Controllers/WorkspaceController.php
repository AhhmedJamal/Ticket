<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    /**
     * عرض لوحة التحكم الرئيسية / قائمة مساحات العمل
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $workspaces = $user->workspaces()
            ->with('owner')
            ->get()
            ->merge($user->ownedWorkspaces()->with('owner')->get())
            ->unique('id')
            ->values();

        $currentWorkspace = $workspaces->first();

        if ($currentWorkspace) {
            return redirect()->route('workspaces.show', $currentWorkspace);
        }

        return view('dashboard', compact('workspaces'));
    }

    /**
     * إنشاء مساحة عمل جديدة
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $workspace = Workspace::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name).'-'.Str::random(5),
            'user_id' => $request->user()->id,
        ]);

        // ربط المالك كـ admin في جدول الـ Pivot
        $workspace->members()->attach($request->user()->id, ['role' => 'admin']);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Workspace created successfully',
                'workspace' => $workspace,
            ], 201);
        }

        return redirect()->route('workspaces.show', $workspace);
    }

    /**
     * عرض مساحة عمل معينة وشجرة صفحاتها
     */
    public function show(Request $request, Workspace $workspace)
    {
        $user = $request->user();

        // التحقق من الصلاحيات
        if ($workspace->user_id !== $user->id && ! $workspace->members->contains($user->id)) {
            abort(403, 'غير مخول بالوصول لمساحة العمل هذه.');
        }

        // تحميل الصفحات الجذرية مع صفحاتها الفرعية
        $workspace->load([
            'pages' => function ($query) {
                $query->whereNull('parent_id')
                    ->with('children')
                    ->orderBy('id', 'asc');
            },
            'members',
        ]);

        $allWorkspaces = $user->ownedWorkspaces->merge($user->workspaces)->unique('id');

        return view('workspaces.show', compact('workspace', 'allWorkspaces'));
    }
}
