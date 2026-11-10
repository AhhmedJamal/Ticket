<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlockController;
// الـ Routes العامة
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// الـ Routes المحمية
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // مساحات العمل
    Route::get('/workspaces', [WorkspaceController::class, 'index']);
    Route::post('/workspaces', [WorkspaceController::class, 'store']);
    Route::get('/workspaces/{workspace}', [WorkspaceController::class, 'show']);

    // الصفحات المرتبطة بمساحة عمل معينة
    Route::post('/workspaces/{workspace}/pages', [PageController::class, 'store']);
    
    // العمليات على صفحة مفردة (عرض، تحديث، حذف)
    Route::get('/pages/{page}', [PageController::class, 'show']);
    Route::put('/pages/{page}', [PageController::class, 'update']);
    Route::delete('/pages/{page}', [PageController::class, 'destroy']);

    Route::middleware('auth:sanctum')->group(function () {
    // ... المسارات السابقة للـ Workspaces والـ Pages ...

    // إضافة بلوك جديد لصفحة معينة
    Route::post('/pages/{page}/blocks', [BlockController::class, 'store']);
    
    // إعادة ترتيب البلوكات لصفحة معينة
    Route::post('/pages/{page}/blocks/reorder', [BlockController::class, 'reorder']);

    // تحديث أو حذف بلوك مفرد
    Route::put('/blocks/{block}', [BlockController::class, 'update']);
    Route::delete('/blocks/{block}', [BlockController::class, 'destroy']);
});
});