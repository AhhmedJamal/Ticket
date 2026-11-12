<?php

use App\Http\Controllers\BlockController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceMemberController;
use Illuminate\Support\Facades\Route;

// الصفحة الرئيسية
Route::get('/', [DashboardController::class, 'home']);

// ==========================================
// صفحات المصادقة واستعادة كلمة المرور
// ==========================================

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');

    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register'])->name('register.post');

    Route::get('/forgot-password', [WebAuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [WebAuthController::class, 'sendResetLink'])->name('password.email');

    Route::get('/reset-password/{token}', [WebAuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [WebAuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// ==========================================
// الصفحات المحمية (تتطلب تسجيل دخول)
// ==========================================

Route::middleware(['auth'])->group(function () {

    // لوحة التحكم
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // البحث الشامل
    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // مساحات العمل
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

    // الصفحات
    Route::post('/workspaces/{workspace}/pages', [PageController::class, 'store'])->name('pages.store');
    Route::get('/pages/{page}', [PageController::class, 'show'])->name('pages.show');
    Route::put('/pages/{page}', [PageController::class, 'update'])->name('pages.update');
    Route::delete('/pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');
    Route::put('/pages/{page}/favorite', [PageController::class, 'toggleFavorite'])->name('pages.favorite');
    Route::get('/pages/{page}/export/markdown', [PageController::class, 'exportMarkdown'])->name('pages.export.markdown');

    // وسوم الصفحة
    Route::post('/pages/{page}/tags', [TagController::class, 'attachToPage'])->name('pages.tags.attach');
    Route::delete('/pages/{page}/tags/{tag}', [TagController::class, 'detachFromPage'])->name('pages.tags.detach');

    // البلوكات
    Route::post('/pages/{page}/blocks', [BlockController::class, 'store'])->name('blocks.store');
    Route::put('/blocks/{block}', [BlockController::class, 'update'])->name('blocks.update');
    Route::delete('/blocks/{block}', [BlockController::class, 'destroy'])->name('blocks.destroy');
    Route::post('/pages/{page}/blocks/reorder', [BlockController::class, 'reorder'])->name('blocks.reorder');

    // قواعد البيانات المدمجة (Inline Databases)
    Route::post('/pages/{page}/databases', [DatabaseController::class, 'store'])->name('databases.store');
    Route::put('/databases/{database}', [DatabaseController::class, 'update'])->name('databases.update');
    Route::delete('/databases/{database}', [DatabaseController::class, 'destroy'])->name('databases.destroy');
    Route::post('/databases/{database}/rows', [DatabaseController::class, 'addRow'])->name('databases.rows.store');
    Route::put('/database-rows/{row}', [DatabaseController::class, 'updateRow'])->name('databases.rows.update');
    Route::delete('/database-rows/{row}', [DatabaseController::class, 'deleteRow'])->name('databases.rows.destroy');
});
