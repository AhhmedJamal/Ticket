<?php

use App\Http\Controllers\BlockController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\WorkspaceController;
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

        // إعادة التوجيه لأول مساحة عمل أو صفحة الـ dashboard
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

        // إذا لم توجد مساحة عمل → اعرض صفحة إنشاء مساحة عمل
        return view('dashboard');
    })->name('dashboard');

    // إنشاء مساحة عمل جديدة
    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');

    // عرض مساحة عمل معينة وصفحاتها (يعيد Blade View)
    Route::get('/workspaces/{workspace}', [WorkspaceController::class, 'show'])->name('workspaces.show');

    // إنشاء صفحة جديدة داخل مساحة العمل (يعيد JSON للـ Alpine)
    Route::post('/workspaces/{workspace}/pages', [PageController::class, 'store'])->name('pages.store');

    // عرض صفحة معينة ومحتواها (يعيد Blade View أو JSON حسب Accept header)
    Route::get('/pages/{page}', [PageController::class, 'show'])->name('pages.show');

    // تحديث أو حذف الصفحة
    Route::put('/pages/{page}', [PageController::class, 'update'])->name('pages.update');
    Route::delete('/pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');

    // إدارة البلوكات (إضافة، تعديل، حذف)
    Route::post('/pages/{page}/blocks', [BlockController::class, 'store'])->name('blocks.store');
    Route::put('/blocks/{block}', [BlockController::class, 'update'])->name('blocks.update');
    Route::delete('/blocks/{block}', [BlockController::class, 'destroy'])->name('blocks.destroy');
    Route::post('/pages/{page}/blocks/reorder', [BlockController::class, 'reorder'])->name('blocks.reorder');
});

// ==========================================
// الصفحة الرئيسية → إعادة توجيه
// ==========================================

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});
