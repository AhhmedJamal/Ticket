<?php

use App\Http\Controllers\BlockController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceMemberController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

// ==========================================
// صفحات المصادقة (تسجيل الدخول / التسجيل)
// ==========================================

Route::get('/login', function () {
    return view('auth.login');
})->name('login')->middleware('guest');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

        $workspace = Auth::user()->ownedWorkspaces()->first()
            ?? Auth::user()->workspaces()->first();

        return $workspace
            ? redirect()->route('workspaces.show', $workspace)
            : redirect()->route('dashboard');
    }

    return back()->withErrors(['email' => 'البيانات المدخلة غير صحيحة.'])->withInput();
})->name('login.post')->middleware('guest');

Route::get('/register', function () {
    return view('auth.register');
})->name('register')->middleware('guest');

Route::post('/register', function (Request $request) {
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
    ]);

    $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
    ]);

    Auth::login($user);
    $request->session()->regenerate();

    return redirect()->route('dashboard');
})->name('register.post')->middleware('guest');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');

// ==========================================
// الصفحات المحمية (تتطلب تسجيل دخول)
// ==========================================

Route::middleware(['auth'])->group(function () {

    // لوحة التحكم → إعادة توجيه لأول مساحة عمل
    Route::get('/dashboard', function () {
        $workspace = Auth::user()->ownedWorkspaces()->first()
            ?? Auth::user()->workspaces()->first();

        if ($workspace) {
            return redirect()->route('workspaces.show', $workspace);
        }

        return view('dashboard');
    })->name('dashboard');

    // ==========================================
    // البحث الشامل
    // ==========================================
    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // ==========================================
    // مساحات العمل
    // ==========================================
    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');
    Route::get('/workspaces/{workspace}', [WorkspaceController::class, 'show'])->name('workspaces.show');

    // إعدادات وأعضاء مساحة العمل
    Route::get('/workspaces/{workspace}/settings', [WorkspaceMemberController::class, 'index'])->name('workspaces.settings');
    Route::post('/workspaces/{workspace}/members', [WorkspaceMemberController::class, 'invite'])->name('workspaces.members.invite');
    Route::put('/workspaces/{workspace}/members/{user}/role', [WorkspaceMemberController::class, 'updateRole'])->name('workspaces.members.role');
    Route::delete('/workspaces/{workspace}/members/{user}', [WorkspaceMemberController::class, 'remove'])->name('workspaces.members.remove');

    // وسوم مساحة العمل
    Route::post('/workspaces/{workspace}/tags', [TagController::class, 'store'])->name('tags.store');
    Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');

    // قوالب الصفحات
    Route::get('/workspaces/{workspace}/templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::post('/workspaces/{workspace}/templates/{template}/apply', [TemplateController::class, 'applyToPage'])->name('templates.apply');

    // ==========================================
    // الصفحات
    // ==========================================
    Route::post('/workspaces/{workspace}/pages', [PageController::class, 'store'])->name('pages.store');
    Route::get('/pages/{page}', [PageController::class, 'show'])->name('pages.show');
    Route::put('/pages/{page}', [PageController::class, 'update'])->name('pages.update');
    Route::delete('/pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');

    // المفضلة
    Route::put('/pages/{page}/favorite', [PageController::class, 'toggleFavorite'])->name('pages.favorite');

    // تصدير الصفحة
    Route::get('/pages/{page}/export/markdown', [PageController::class, 'exportMarkdown'])->name('pages.export.markdown');

    // وسوم الصفحة
    Route::post('/pages/{page}/tags', [TagController::class, 'attachToPage'])->name('pages.tags.attach');
    Route::delete('/pages/{page}/tags/{tag}', [TagController::class, 'detachFromPage'])->name('pages.tags.detach');

    // ==========================================
    // البلوكات
    // ==========================================
    Route::post('/pages/{page}/blocks', [BlockController::class, 'store'])->name('blocks.store');
    Route::put('/blocks/{block}', [BlockController::class, 'update'])->name('blocks.update');
    Route::delete('/blocks/{block}', [BlockController::class, 'destroy'])->name('blocks.destroy');
    Route::post('/pages/{page}/blocks/reorder', [BlockController::class, 'reorder'])->name('blocks.reorder');

    // ==========================================
    // قواعد البيانات المدمجة (Inline Databases)
    // ==========================================
    Route::post('/pages/{page}/databases', [DatabaseController::class, 'store'])->name('databases.store');
    Route::put('/databases/{database}', [DatabaseController::class, 'update'])->name('databases.update');
    Route::delete('/databases/{database}', [DatabaseController::class, 'destroy'])->name('databases.destroy');
    Route::post('/databases/{database}/rows', [DatabaseController::class, 'addRow'])->name('databases.rows.store');
    Route::put('/database-rows/{row}', [DatabaseController::class, 'updateRow'])->name('databases.rows.update');
    Route::delete('/database-rows/{row}', [DatabaseController::class, 'deleteRow'])->name('databases.rows.destroy');
});

// ==========================================
// الصفحة الرئيسية → إعادة توجيه
// ==========================================

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});
