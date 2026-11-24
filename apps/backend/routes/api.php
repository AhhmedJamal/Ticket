<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BlockCommentController;
use App\Http\Controllers\Api\V1\BlockController;
use App\Http\Controllers\Api\V1\DatabaseController;
use App\Http\Controllers\Api\V1\DatabaseViewController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\PagePermissionController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\TemplateController;
use App\Http\Controllers\Api\V1\WorkspaceController;
use App\Http\Controllers\Api\V1\WorkspaceMemberController;
use Illuminate\Support\Facades\Route;

// ==========================================
// API Version 1 Routes (/api/v1/...)
// ==========================================

Route::prefix('v1')->name('api.v1.')->group(function () {

    // مسارات المصادقة العامة
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
    });

    // مسارات الـ API المحمية بواسطة Sanctum
    Route::middleware('auth:sanctum')->group(function () {

        // معلومات المستخدم الحالي وتسجيل الخروج
        Route::get('/auth/me', [AuthController::class, 'user'])->name('auth.me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        // البحث الشامل والأوامر السريعة
        Route::get('/search', [SearchController::class, 'search'])->name('search');

        // مساحات العمل
        Route::get('/workspaces', [WorkspaceController::class, 'index'])->name('workspaces.index');
        Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');
        Route::get('/workspaces/{workspace}', [WorkspaceController::class, 'show'])->name('workspaces.show');
        Route::match(['put', 'patch'], '/workspaces/{workspace}', [WorkspaceController::class, 'update'])->name('workspaces.update');

        // أعضاء وإعدادات مساحة العمل
        Route::get('/workspaces/{workspace}/members', [WorkspaceMemberController::class, 'index'])->name('workspaces.members.index');
        Route::post('/workspaces/{workspace}/members', [WorkspaceMemberController::class, 'invite'])->name('workspaces.members.invite');
        Route::match(['put', 'patch'], '/workspaces/{workspace}/members/{user}/role', [WorkspaceMemberController::class, 'updateRole'])->name('workspaces.members.role');
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
        Route::match(['put', 'patch'], '/pages/{page}', [PageController::class, 'update'])->name('pages.update');
        Route::delete('/pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');
        Route::match(['put', 'patch'], '/pages/{page}/favorite', [PageController::class, 'toggleFavorite'])->name('pages.favorite');
        Route::get('/pages/{page}/export/markdown', [PageController::class, 'exportMarkdown'])->name('pages.export.markdown');

        // وسوم الصفحة
        Route::post('/pages/{page}/tags', [TagController::class, 'attachToPage'])->name('pages.tags.attach');
        Route::delete('/pages/{page}/tags/{tag}', [TagController::class, 'detachFromPage'])->name('pages.tags.detach');

        // البلوكات
        Route::post('/pages/{page}/blocks', [BlockController::class, 'store'])->name('blocks.store');
        Route::match(['put', 'patch'], '/blocks/{block}', [BlockController::class, 'update'])->name('blocks.update');
        Route::delete('/blocks/{block}', [BlockController::class, 'destroy'])->name('blocks.destroy');
        Route::post('/pages/{page}/blocks/reorder', [BlockController::class, 'reorder'])->name('blocks.reorder');

        // صلاحيات الصفحة المخصصة (RBAC)
        Route::get('/pages/{page}/permissions', [PagePermissionController::class, 'index'])->name('pages.permissions.index');
        Route::post('/pages/{page}/permissions', [PagePermissionController::class, 'store'])->name('pages.permissions.store');
        Route::delete('/pages/{page}/permissions/{user}', [PagePermissionController::class, 'destroy'])->name('pages.permissions.destroy');

        // تعليقات البلوكات
        Route::get('/blocks/{block}/comments', [BlockCommentController::class, 'index'])->name('blocks.comments.index');
        Route::post('/blocks/{block}/comments', [BlockCommentController::class, 'store'])->name('blocks.comments.store');
        Route::patch('/comments/{comment}/resolve', [BlockCommentController::class, 'resolve'])->name('comments.resolve');
        Route::delete('/comments/{comment}', [BlockCommentController::class, 'destroy'])->name('comments.destroy');

        // قواعد البيانات المضمنة
        Route::post('/pages/{page}/databases', [DatabaseController::class, 'store'])->name('databases.store');
        Route::match(['put', 'patch'], '/databases/{database}', [DatabaseController::class, 'update'])->name('databases.update');
        Route::delete('/databases/{database}', [DatabaseController::class, 'destroy'])->name('databases.destroy');
        Route::post('/databases/{database}/rows', [DatabaseController::class, 'addRow'])->name('databases.rows.store');
        Route::match(['put', 'patch'], '/database-rows/{row}', [DatabaseController::class, 'updateRow'])->name('databases.rows.update');
        Route::delete('/database-rows/{row}', [DatabaseController::class, 'deleteRow'])->name('databases.rows.destroy');

        // عروض قواعد البيانات المضمنة (Table, Board, List, Gallery)
        Route::post('/databases/{database}/views', [DatabaseViewController::class, 'store'])->name('databases.views.store');
        Route::match(['put', 'patch'], '/database-views/{view}', [DatabaseViewController::class, 'update'])->name('databases.views.update');
        Route::delete('/database-views/{view}', [DatabaseViewController::class, 'destroy'])->name('databases.views.destroy');
    });
});
