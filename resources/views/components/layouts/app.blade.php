@props(['title' => config('app.name', 'Notion Clone')])

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Alpine.js CDN (defer = يُحمّل بعد HTML) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'sans-serif'] },
                    colors: {
                        notion: {
                            bg:      '#ffffff',
                            sidebar: '#f7f7f5',
                            hover:   '#ebebea',
                            border:  '#e9e9e7',
                            text:    '#37352f',
                            muted:   '#9b9a97',
                            accent:  '#2eaadc',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body { font-family: 'Inter', ui-sans-serif, sans-serif; }

        /* Alpine cloak */
        [x-cloak] { display: none !important; }

        /* Contenteditable placeholder */
        [contenteditable]:focus { outline: none; }
        [contenteditable]:empty:before {
            content: attr(data-placeholder);
            color: #c3c2bf;
            pointer-events: none;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1d1cf; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #9b9a97; }

        /* Sidebar transitions */
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 14px;
            color: #37352f;
            transition: background 0.1s;
            cursor: pointer;
            user-select: none;
        }
        .sidebar-link:hover { background: #ebebea; }
        .sidebar-link.active { background: #ebebea; font-weight: 500; }

        /* Block editor */
        .block-wrapper { position: relative; }
        .block-wrapper:hover .block-handle { opacity: 1; }
        .block-handle { opacity: 0; transition: opacity .15s; }

        /* Spinning animation */
        @keyframes spin { to { transform: rotate(360deg); } }
        .animate-spin { animation: spin 1s linear infinite; }

        /* Fade transition for Alpine */
        .fade-enter-active, .fade-leave-active { transition: opacity .15s, transform .15s; }
        .fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-4px); }
    </style>
</head>
<body class="bg-white text-[#37352f] antialiased select-none">

<div class="flex h-screen overflow-hidden" id="app-root">

    {{-- =====================================================
         القائمة الجانبية
         ===================================================== --}}
    <aside
        x-data="{ open: true }"
        :class="open ? 'w-[260px]' : 'w-0 overflow-hidden'"
        class="flex-shrink-0 bg-[#f7f7f5] border-l border-[#e9e9e7] flex flex-col transition-all duration-200 ease-in-out"
        id="app-sidebar"
        x-ref="sidebar"
    >
        @isset($sidebar)
            {{ $sidebar }}
        @endisset
    </aside>

    {{-- =====================================================
         المنطقة الرئيسية
         ===================================================== --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- الشريط العلوي --}}
        <header class="flex items-center justify-between h-12 px-3 border-b border-[#e9e9e7] bg-white flex-shrink-0">

            {{-- زر طي الـ Sidebar --}}
            <div class="flex items-center gap-1">
                <button
                    @click="document.getElementById('app-sidebar').__x.$data.open = !document.getElementById('app-sidebar').__x.$data.open"
                    class="p-1.5 rounded hover:bg-[#ebebea] text-[#9b9a97] hover:text-[#37352f] transition-colors"
                    title="طي/فتح القائمة"
                >
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

            {{-- معلومات المستخدم --}}
            @auth
            <div x-data="{ menuOpen: false }" class="relative">
                <button
                    @click="menuOpen = !menuOpen"
                    class="flex items-center gap-2 px-2 py-1 rounded-lg hover:bg-[#ebebea] transition-colors text-sm"
                >
                    <span class="w-6 h-6 rounded-full bg-[#2eaadc] text-white text-[11px] font-semibold flex items-center justify-center select-none">
                        {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <span class="max-w-[120px] truncate text-[13px] font-medium text-[#37352f]">{{ auth()->user()->name }}</span>
                    <svg class="w-3 h-3 text-[#9b9a97]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>

                <div
                    x-show="menuOpen"
                    @click.outside="menuOpen = false"
                    x-transition
                    class="absolute left-0 top-full mt-1.5 w-52 bg-white border border-[#e9e9e7] rounded-xl shadow-xl py-1 z-50"
                    x-cloak
                >
                    <div class="px-3 py-2.5 border-b border-[#e9e9e7] mb-1">
                        <p class="text-[13px] font-semibold text-[#37352f] truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-[#9b9a97] truncate mt-0.5">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-right px-3 py-2 text-[13px] text-[#37352f] hover:bg-[#f7f7f5] transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#9b9a97]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            تسجيل الخروج
                        </button>
                    </form>
                </div>
            </div>
            @endauth
        </header>

        {{-- محتوى الصفحة --}}
        <main class="flex-1 overflow-y-auto">
            {{ $slot }}
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
