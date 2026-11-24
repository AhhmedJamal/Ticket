<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    /**
     * تسجيل مستخدم جديد وتوليد Token
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'تم إنشاء الحساب وتسجيل الدخول بنجاح.', 201);
    }

    /**
     * تسجيل الدخول والتحقق من بيانات الاعتماد
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['البيانات المدخلة غير صحيحة.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'تم تسجيل الدخول بنجاح.');
    }

    /**
     * جلب بيانات المستخدم الحالي
     */
    public function user(Request $request): JsonResponse
    {
        return $this->successResponse([
            'user' => new UserResource($request->user()),
        ]);
    }

    /**
     * تسجيل الخروج وإلغاء الـ Token الحالي
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->successResponse(null, 'تم تسجيل الخروج بنجاح.');
    }

    /**
     * إرسال رابط استعادة كلمة المرور
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $email = $request->validated('email');

        try {
            $status = Password::sendResetLink(['email' => $email]);
        } catch (\Throwable $e) {
            Log::error('Password reset email failed: '.$e->getMessage());
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return $this->errorResponse('تعذر إرسال البريد الإلكتروني. تأكد من إعدادات البريد في ملف .env.', 500);
        }

        if ($status === Password::RESET_LINK_SENT) {
            return $this->successResponse(null, 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.');
        }

        $errorMessage = match ($status) {
            Password::RESET_THROTTLED => 'يرجى الانتظار دقيقة واحدة قبل إعادة المحاولة.',
            Password::INVALID_USER => 'لم يتم العثور على حساب بهذا البريد الإلكتروني.',
            default => __($status),
        };

        return $this->errorResponse($errorMessage, 422, ['email' => [$errorMessage]]);
    }

    /**
     * إعادة تعيين كلمة المرور
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
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

        if ($status === Password::PASSWORD_RESET) {
            return $this->successResponse(null, 'تم تغيير كلمة المرور بنجاح. يمكنك تسجيل الدخول الآن.');
        }

        return $this->errorResponse(__($status), 422, ['email' => [__($status)]]);
    }
}
