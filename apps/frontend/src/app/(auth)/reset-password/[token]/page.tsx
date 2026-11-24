'use client';

import React, { useState, use } from 'react';
import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';
import { authService } from '@/services/api';
import { useToast } from '@/lib/toast-context';

export default function ResetPasswordPage({ params }: { params: Promise<{ token: string }> }) {
  const resolvedParams = use(params);
  const token = resolvedParams.token;
  const router = useRouter();
  const searchParams = useSearchParams();
  const { success, error } = useToast();

  const [email, setEmail] = useState(searchParams.get('email') || '');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState<Record<string, string[]>>({});

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});

    try {
      const res = await authService.resetPassword({
        token,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });
      success(res.message || 'تم تغيير كلمة المرور بنجاح. يمكنك تسجيل الدخول الآن.');
      router.push('/login');
    } catch (err: any) {
      if (err.response?.status === 422 && err.response?.data?.errors) {
        setErrors(err.response.data.errors);
      } else {
        const msg = err.response?.data?.message || 'تعذر تغيير كلمة المرور';
        error(msg);
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-[#f7f7f5] dark:bg-[#191919] flex items-center justify-center p-4">
      <div className="bg-white dark:bg-[#202020] rounded-2xl shadow-sm border border-[#e9e9e7] dark:border-[#2E2E2E] w-full max-w-sm p-8">
        <div className="text-center mb-8">
          <div className="text-4xl mb-3">🔐</div>
          <h1 className="text-2xl font-bold text-[#37352f] dark:text-[#E3E3E0]">كلمة مرور جديدة</h1>
          <p className="text-sm text-[#9b9a97] mt-1">أدخل كلمة المرور الجديدة لحسابك</p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-[#37352f] dark:text-[#E3E3E0] mb-1.5">
              البريد الإلكتروني
            </label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              autoFocus
              placeholder="you@example.com"
              className="w-full px-3 py-2.5 bg-white dark:bg-[#191919] border border-[#e9e9e7] dark:border-[#2E2E2E] rounded-lg text-sm text-[#37352f] dark:text-[#E3E3E0] placeholder-[#9b9a97] focus:outline-none focus:ring-2 focus:ring-[#2eaadc] focus:border-transparent transition-shadow"
            />
            {errors.email && (
              <p className="text-xs text-red-500 mt-1">{errors.email[0]}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-[#37352f] dark:text-[#E3E3E0] mb-1.5">
              كلمة المرور الجديدة
            </label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              placeholder="8 أحرف على الأقل"
              className="w-full px-3 py-2.5 bg-white dark:bg-[#191919] border border-[#e9e9e7] dark:border-[#2E2E2E] rounded-lg text-sm text-[#37352f] dark:text-[#E3E3E0] placeholder-[#9b9a97] focus:outline-none focus:ring-2 focus:ring-[#2eaadc] focus:border-transparent transition-shadow"
            />
            {errors.password && (
              <p className="text-xs text-red-500 mt-1">{errors.password[0]}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-[#37352f] dark:text-[#E3E3E0] mb-1.5">
              تأكيد كلمة المرور
            </label>
            <input
              type="password"
              value={passwordConfirmation}
              onChange={(e) => setPasswordConfirmation(e.target.value)}
              required
              placeholder="••••••••"
              className="w-full px-3 py-2.5 bg-white dark:bg-[#191919] border border-[#e9e9e7] dark:border-[#2E2E2E] rounded-lg text-sm text-[#37352f] dark:text-[#E3E3E0] placeholder-[#9b9a97] focus:outline-none focus:ring-2 focus:ring-[#2eaadc] focus:border-transparent transition-shadow"
            />
            {errors.password_confirmation && (
              <p className="text-xs text-red-500 mt-1">{errors.password_confirmation[0]}</p>
            )}
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full py-2.5 bg-[#37352f] dark:bg-[#2EAADC] text-white text-sm font-medium rounded-lg hover:bg-[#1a1915] dark:hover:bg-[#2596C4] transition-colors disabled:opacity-50"
          >
            {loading ? 'جاري الحفظ...' : 'حفظ كلمة المرور'}
          </button>
        </form>

        <div className="mt-6 text-center">
          <Link href="/login" className="text-sm text-[#2eaadc] hover:underline font-medium">
            العودة لتسجيل الدخول
          </Link>
        </div>
      </div>
    </div>
  );
}

