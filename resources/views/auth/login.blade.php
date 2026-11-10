<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول — Notion Clone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Inter', ui-sans-serif, sans-serif; }</style>
</head>
<body class="min-h-screen bg-[#f7f7f5] flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl shadow-sm border border-[#e9e9e7] w-full max-w-sm p-8">

        {{-- الشعار --}}
        <div class="text-center mb-8">
            <div class="text-4xl mb-3">📝</div>
            <h1 class="text-2xl font-bold text-[#37352f]">مرحباً بعودتك</h1>
            <p class="text-sm text-[#9b9a97] mt-1">سجل دخولك للمتابعة</p>
        </div>

        {{-- رسائل الخطأ --}}
        @if($errors->any())
            <div class="mb-5 p-3 bg-red-50 border border-red-200 rounded-lg">
                @foreach($errors->all() as $error)
                    <p class="text-sm text-red-600">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- نموذج الدخول --}}
        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-[#37352f] mb-1.5">البريد الإلكتروني</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    placeholder="you@example.com"
                    class="w-full px-3 py-2.5 border border-[#e9e9e7] rounded-lg text-sm text-[#37352f] placeholder-[#9b9a97] focus:outline-none focus:ring-2 focus:ring-[#2eaadc] focus:border-transparent transition-shadow"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-[#37352f] mb-1.5">كلمة المرور</label>
                <input
                    type="password"
                    name="password"
                    required
                    placeholder="••••••••"
                    class="w-full px-3 py-2.5 border border-[#e9e9e7] rounded-lg text-sm text-[#37352f] placeholder-[#9b9a97] focus:outline-none focus:ring-2 focus:ring-[#2eaadc] focus:border-transparent transition-shadow"
                >
            </div>

            <div class="flex items-center gap-2">
                <input
                    type="checkbox"
                    name="remember"
                    id="remember"
                    class="w-4 h-4 rounded border-[#9b9a97] accent-[#2eaadc]"
                >
                <label for="remember" class="text-sm text-[#9b9a97]">تذكرني</label>
            </div>

            <button
                type="submit"
                class="w-full py-2.5 bg-[#37352f] text-white text-sm font-medium rounded-lg hover:bg-[#1a1915] transition-colors"
            >
                تسجيل الدخول
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-[#9b9a97]">
                ليس لديك حساب؟
                <a href="{{ route('register') }}" class="text-[#2eaadc] hover:underline font-medium">إنشاء حساب</a>
            </p>
        </div>
    </div>

</body>
</html>

