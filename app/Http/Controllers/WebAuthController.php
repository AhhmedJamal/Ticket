<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WebAuthController extends Controller
{
    /** عرض صفحة تسجيل الدخول */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /** تنفيذ تسجيل الدخول */
    public function login(Request $request): RedirectResponse
    {
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
    }

    /** عرض صفحة إنشاء حساب جديد */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /** إنشاء حساب جديد وتسجيل الدخول */
    public function register(Request $request): RedirectResponse
    {
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
    }

    /** تسجيل الخروج وإنهاء الجلسة */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /** عرض صفحة طلب استعادة كلمة المرور */
    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    /** إرسال رابط إعادة تعيين كلمة المرور عبر البريد */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            Log::error('Password reset email failed: '.$e->getMessage());
            DB::table('password_reset_tokens')->where('email', $request->input('email'))->delete();

            return back()
                ->withInput()
                ->withErrors(['email' => 'تعذر إرسال البريد الإلكتروني. تأكد من إعدادات SMTP و App Password في ملف .env.']);
        }

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.');
        }

        $errorMessage = match ($status) {
            Password::RESET_THROTTLED => 'يرجى الانتظار قليلاً (دقيقة واحدة) قبل إعادة المحاولة.',
            Password::INVALID_USER => 'لم يتم العثور على حساب بهذا البريد الإلكتروني.',
            default => __($status),
        };

        return back()->withInput()->withErrors(['email' => $errorMessage]);
    }

    /** عرض صفحة إدخال كلمة المرور الجديدة */
    public function showResetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /** حفظ كلمة المرور الجديدة */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'تم تغيير كلمة المرور بنجاح. يمكنك تسجيل الدخول الآن.')
            : back()->withErrors(['email' => __($status)]);
    }
}
