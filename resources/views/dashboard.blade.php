<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم — Ticket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @font-face {
            font-family: 'Kashif Arabic';
            src: url('/fonts/KashifArabic.otf') format('opentype');
            font-weight: 100 900;
            font-style: normal;
            font-display: swap;
        }
        body { font-family: 'Kashif Arabic', 'Inter', ui-sans-serif, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-[#f7f7f5] dark:bg-[#191919] flex items-center justify-center p-4">

    <div
        x-data="{
            workspaceName: '',
            creating: false,
            error: '',

            createWorkspace() {
                if (!this.workspaceName.trim()) return;
                this.creating = true;
                this.error = '';

                fetch('/workspaces', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ name: this.workspaceName })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.workspace) {
                        window.location.href = '/workspaces/' + data.workspace.id;
                    } else {
                        this.error = 'حدث خطأ، حاول مجدداً';
                        this.creating = false;
                    }
                })
                .catch(() => {
                    this.error = 'حدث خطأ في الاتصال';
                    this.creating = false;
                });
            }
        }"
        class="bg-white dark:bg-[#202020] rounded-2xl shadow-sm border border-[#e9e9e7] dark:border-[#2E2E2E] w-full max-w-md p-10"
    >
        <div class="text-center mb-8">
            <div class="text-5xl mb-4">🏢</div>
            <h1 class="text-2xl font-bold text-[#37352f] dark:text-[#E3E3E0] mb-2">إنشاء مساحة عمل</h1>
            <p class="text-sm text-[#9b9a97]">ابدأ بإنشاء مساحة عمل لتنظيم صفحاتك ومهامك</p>
        </div>

        <div x-show="error" class="mb-4 p-3 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 rounded-lg">
            <p class="text-sm text-red-600 dark:text-red-400" x-text="error"></p>
        </div>

        <form @submit.prevent="createWorkspace()" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-[#37352f] dark:text-[#E3E3E0] mb-1.5">اسم مساحة العمل</label>
                <input
                    type="text"
                    x-model="workspaceName"
                    placeholder="مشروعي الجديد"
                    required
                    autofocus
                    class="w-full px-3 py-2.5 bg-white dark:bg-[#191919] border border-[#e9e9e7] dark:border-[#2E2E2E] rounded-lg text-sm text-[#37352f] dark:text-[#E3E3E0] placeholder-[#9b9a97] focus:outline-none focus:ring-2 focus:ring-[#2eaadc] focus:border-transparent"
                >
            </div>

            <button
                type="submit"
                :disabled="creating || !workspaceName.trim()"
                class="w-full py-2.5 bg-[#37352f] dark:bg-[#2EAADC] text-white text-sm font-medium rounded-lg hover:bg-[#1a1915] dark:hover:bg-[#2596C4] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span x-show="!creating">إنشاء مساحة العمل</span>
                <span x-show="creating">جاري الإنشاء...</span>
            </button>
        </form>

        <div class="mt-6 text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-[#9b9a97] hover:text-[#37352f] hover:underline">
                    تسجيل الخروج
                </button>
            </form>
        </div>
    </div>

</body>
</html>

