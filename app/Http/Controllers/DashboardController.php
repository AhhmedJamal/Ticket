<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** الصفحة الرئيسية: توجيه حسب حالة تسجيل الدخول */
    public function home(): RedirectResponse
    {
        return Auth::check()
            ? redirect()->route('dashboard')
            : redirect()->route('login');
    }

    /** لوحة التحكم أو التوجيه لأول مساحة عمل */
    public function index(): View|RedirectResponse
    {
        $workspace = Auth::user()->ownedWorkspaces()->first()
            ?? Auth::user()->workspaces()->first();

        if ($workspace) {
            return redirect()->route('workspaces.show', $workspace);
        }

        return view('dashboard');
    }
}
