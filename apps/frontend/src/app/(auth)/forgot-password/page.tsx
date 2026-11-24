'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { authService } from '@/services/api';
import { useToast } from '@/lib/toast-context';

export default function ForgotPasswordPage() {
  const { success, error } = useToast();
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const [sentMessage, setSentMessage] = useState<string | null>(null);
  const [errors, setErrors] = useState<Record<string, string[]>>({});

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});
    setSentMessage(null);

    try {
      const res = await authService.forgotPassword(email);
      const msg = res.message || 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.';
      setSentMessage(msg);
      success(msg);
    } catch (err: any) {
      if (err.response?.status === 422 && err.response?.data?.errors) {
        setErrors(err.response.data.errors);
      } else {
        const msg = err.response?.data?.message || 'تعذر إرسال البريد الإلكتروني';
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
          <div className="text-4xl mb-3">🔑</div>
          <h1 className="text-2xl font-bold text-[#37352f] dark:text-[#E3E3E0]">نسيت كلمة المرور؟</h1>
          <p className="text-sm text-[#9b9a97] mt-1">أدخل بريدك الإلكتروني وسنرسل لك رابطاً لإعادة تعيينها</p>
        </div>

        {sentMessage && (
          <div className="mb-5 p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-lg">
            <p className="text-sm text-emerald-700 dark:text-emerald-300">{sentMessage}</p>
          </div>
        )}

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

          <button
            type="submit"
            disabled={loading}
            className="w-full py-2.5 bg-[#37352f] dark:bg-[#2EAADC] text-white text-sm font-medium rounded-lg hover:bg-[#1a1915] dark:hover:bg-[#2596C4] transition-colors disabled:opacity-50"
          >
            {loading ? 'جاري الإرسال...' : 'إرسال رابط إعادة التعيين'}
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

