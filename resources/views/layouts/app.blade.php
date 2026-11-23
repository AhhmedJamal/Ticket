<!DOCTYPE html>
<html lang="ar" dir="rtl" x-data="appShell()" :class="{ 'dark': darkMode }" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Ticket') }}</title>

    {{-- تطبيق الوضع الليلي افتراضياً قبل الرسم لمنع الوميض --}}
    <script>
        if (localStorage.getItem('darkMode') === 'false') {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
        }
    </script>

    <!-- خطوط نظيفة -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Kufi+Arabic:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS عبر CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <!-- Prism.js Syntax Highlighting -->
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
    <script>
        if (window.Prism && Prism.plugins.autoloader) {
            Prism.plugins.autoloader.languages_path = 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/';
        }
    </script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Kashif Arabic"', '"Noto Kufi Arabic"', 'Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        notion: {
                            sidebar:  '#F7F7F5',
                            hover:    '#EBEBEA',
                            border:   '#E9E9E7',
                            text:     '#37352F',
                            muted:    '#9B9A97',
                            accent:   '#2EAADC'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        @font-face {
            font-family: 'Kashif Arabic';
            src: url('/fonts/KashifArabic.otf') format('opentype');
            font-weight: 100 900;
            font-style: normal;
            font-display: swap;
        }
        [x-cloak] { display: none !important; }
        body { color: #37352F; background-color: #FFFFFF; }
        .dark body { background-color: #191919; color: #E3E3E0; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #D3D3D0; border-radius: 9999px; }
        .dark ::-webkit-scrollbar-thumb { background: #3A3A3A; }
        ::-webkit-scrollbar-thumb:hover { background: #B4B4B0; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #525252; }
        .sidebar-item:hover .actions { opacity: 1; }
        .search-highlight { background: #FFF3CD; border-radius: 2px; }
        
        /* Notion Dark Mode palette */
        .dark .bg-white { background-color: #191919 !important; }
        .dark .bg-notion-sidebar { background-color: #202020 !important; }
        .dark .border-notion-border { border-color: #2E2E2E !important; }
        .dark .text-notion-text { color: #E3E3E0 !important; }
        .dark .text-notion-muted { color: #9B9A97 !important; }
        .dark .hover\:bg-notion-hover:hover { background-color: #2C2C2C !important; }
        .dark .bg-notion-hover { background-color: #2C2C2C !important; }

        /* Notion Code Block Syntax Highlighting */
        .notion-code-pre { margin: 0 !important; padding: 0 !important; background: transparent !important; text-shadow: none !important; }
        .notion-code-pre code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important; font-size: 13px !important; line-height: 1.625 !important; }
        .token.comment, .token.prolog, .token.doctype, .token.cdata { color: #7F848E; }
        .token.punctuation { color: #ABB2BF; }
        .token.property, .token.tag, .token.boolean, .token.number, .token.constant, .token.symbol, .token.deleted { color: #D19A66; }
        .token.selector, .token.attr-name, .token.string, .token.char, .token.builtin, .token.inserted { color: #98C379; }
        .token.operator, .token.entity, .token.url, .language-css .token.string, .style .token.string { color: #56B6C2; }
        .token.atrule, .token.attr-value, .token.keyword { color: #61AFEF; }
        .token.function, .token.class-name { color: #E5C07B; }
        .token.regex, .token.important, .token.variable { color: #E06C75; }
    </style>
</head>
<body
    class="h-screen overflow-hidden flex flex-col font-sans antialiased text-notion-text bg-white dark:bg-[#191919] dark:text-[#E3E3E0]"
    @resize.window="handleResize()"
    @keydown.window.ctrl.k.prevent="searchOpen = true"
    @keydown.window.ctrl.shift.l.prevent="toggleDarkMode()"
    @keydown.window.slash.prevent="if(document.activeElement.tagName === 'BODY') searchOpen = true"
>

    <div class="flex-1 flex overflow-hidden relative">

        {{-- خلفية إغلاق السايدبار على الموبايل --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            @click="sidebarOpen = false"
            x-transition:enter="transition-opacity duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 backdrop-blur-[1px] z-30 md:hidden"
        ></div>

        {{-- ================= الشريط الجانبي (Sidebar) ================= --}}
        <aside
            x-show="sidebarOpen"
            x-cloak
            x-transition:enter="transition-transform duration-200 ease-out"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition-transform duration-150 ease-in"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-y-0 right-0 w-64 md:w-60 md:static bg-notion-sidebar dark:bg-[#202020] border-l border-notion-border dark:border-[#2A2A2A] flex flex-col h-full shrink-0 select-none z-40 md:z-20 shadow-2xl md:shadow-none"
        >
            @include('components.sidebar')
        </aside>

        {{-- ================= منطقة المحتوى الرئيسية ================= --}}
        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-white dark:bg-[#191919] relative">

            {{-- الشريط العلوي (Notion-style flat topbar) --}}
            <header class="h-11 flex items-center justify-between px-2 sm:px-3 shrink-0 bg-white/95 dark:bg-[#191919]/95 backdrop-blur-sm sticky top-0 z-10">
                <div class="flex items-center gap-1 sm:gap-1.5 text-[13px] text-notion-muted min-w-0">
                    {{-- زر إظهار/إخفاء السايدبار --}}
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        class="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] text-notion-muted hover:text-notion-text dark:hover:text-[#E3E3E0] transition-colors shrink-0"
                        title="إخفاء/إظهار القائمة الجانبية"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>

                    <a href="{{ isset($workspace) ? route('workspaces.show', $workspace) : '#' }}" class="px-1.5 py-0.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] hover:text-notion-text dark:hover:text-[#E3E3E0] text-notion-muted truncate max-w-[90px] sm:max-w-[140px] transition-colors">
                        {{ $workspace->name ?? 'Notion' }}
                    </a>

                    @isset($page)
                        <span class="text-notion-muted/40 hidden sm:inline">/</span>
                        @if($page->parent)
                            <a href="{{ route('pages.show', $page->parent) }}" class="hidden sm:inline-block px-1.5 py-0.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] hover:text-notion-text dark:hover:text-[#E3E3E0] text-notion-muted truncate max-w-[140px] transition-colors">
                                {{ $page->parent->icon }} {{ $page->parent->title }}
                            </a>
                            <span class="text-notion-muted/40 hidden sm:inline">/</span>
                        @endif
                        <span class="px-1.5 py-0.5 text-notion-text dark:text-[#E3E3E0] flex items-center gap-1.5 truncate max-w-[110px] sm:max-w-[220px]">
                            <span>{{ $page->icon ?? '📄' }}</span>
                            <span class="truncate">{{ $page->title }}</span>
                        </span>
                    @endisset
                </div>

                <div class="flex items-center gap-0.5 shrink-0">
                    @isset($page)
                        <a href="{{ route('pages.export.markdown', $page) }}"
                           class="px-2 py-1 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] text-xs text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] transition-colors">
                            تصدير
                        </a>

                        <button
                            x-data="{ fav: {{ $page->is_favorite ? 'true' : 'false' }} }"
                            @click="fetch('{{ route('pages.favorite', $page) }}', { method: 'PUT', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }).then(r => r.json()).then(d => fav = d.is_favorite)"
                            class="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] transition-colors"
                            :title="fav ? 'إزالة من المفضلة' : 'إضافة للمفضلة'"
                        >
                            <svg class="w-4 h-4" :class="fav ? 'text-yellow-400 fill-yellow-400' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </button>

                        <button
                            @click="overviewOpen = !overviewOpen"
                            :class="overviewOpen ? 'bg-notion-hover dark:bg-[#2C2C2C] text-notion-text dark:text-[#E3E3E0]' : 'text-notion-muted dark:text-[#9B9A97]'"
                            class="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors"
                            title="تفاصيل الصفحة وسجل التعديلات"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </button>
                    @endisset

                    {{-- زر البحث --}}
                    <button
                        @click="searchOpen = true"
                        class="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] transition-colors"
                        title="بحث (Ctrl+K)"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"></path></svg>
                    </button>

                    {{-- Dark Mode Toggle --}}
                    <button
                        @click="toggleDarkMode()"
                        class="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] text-notion-muted hover:text-notion-text dark:hover:text-[#E3E3E0] transition-colors"
                        title="تبديل المظهر (Ctrl+Shift+L)"
                    >
                        <svg x-show="!darkMode" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <svg x-show="darkMode" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </button>

                    @auth
                    <div class="relative" x-data="{ userMenu: false }">
                        <button
                            @click="userMenu = !userMenu"
                            class="p-1 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors"
                            title="{{ auth()->user()->name }}"
                        >
                            <span class="w-5 h-5 rounded-full bg-[#3A3A3A] text-[#E3E3E0] flex items-center justify-center text-[11px] font-medium">
                                {{ mb_substr(auth()->user()->name, 0, 1) }}
                            </span>
                        </button>

                        <div
                            x-show="userMenu"
                            x-cloak
                            @click.outside="userMenu = false"
                            class="absolute left-0 mt-1.5 w-48 bg-white dark:bg-[#252525] border border-notion-border dark:border-[#333333] rounded-lg shadow-2xl py-1 z-40"
                        >
                            <div class="px-3 py-1.5 border-b border-notion-border dark:border-[#333333]">
                                <div class="text-xs font-medium text-notion-text dark:text-[#E3E3E0] truncate">{{ auth()->user()->name }}</div>
                                <div class="text-[11px] text-notion-muted truncate">{{ auth()->user()->email }}</div>
                            </div>
                            @isset($workspace)
                                <a href="{{ route('workspaces.settings', $workspace) }}" class="flex items-center gap-2 px-3 py-1.5 text-xs text-notion-text dark:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors">
                                    <span>إعدادات مساحة العمل</span>
                                </a>
                            @endisset
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-right flex items-center gap-2 px-3 py-1.5 text-xs text-red-500 dark:text-red-400 hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors">
                                    <span>تسجيل الخروج</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endauth
                </div>
            </header>

            {{-- المحتوى الداخلي --}}
            <div class="flex-1 px-4 sm:px-8 md:px-12 py-6 md:py-8 max-w-5xl w-full mx-auto">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- =================== Search Modal =================== --}}
    <div
        x-show="searchOpen"
        x-cloak
        @keydown.escape.window="searchOpen = false"
        class="fixed inset-0 z-50 flex items-start justify-center pt-24 px-4"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        {{-- خلفية شفافة --}}
        <div class="absolute inset-0 bg-black/40 dark:bg-black/60 backdrop-blur-sm" @click="searchOpen = false"></div>

        {{-- نافذة البحث واللوحة الذكية (Command Palette) --}}
        <div class="relative w-full max-w-xl bg-white dark:bg-[#222222] rounded-2xl shadow-2xl border border-notion-border dark:border-[#333333] overflow-hidden z-10 flex flex-col max-h-[85vh]">
            {{-- حقل البحث --}}
            <div class="flex items-center gap-3 px-4 py-3 border-b border-notion-border dark:border-[#2D2D2D] bg-notion-sidebar/30 dark:bg-[#1E1E1E]">
                <svg class="w-4 h-4 text-notion-muted flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input
                    type="text"
                    id="search-input"
                    x-model="searchQuery"
                    @input.debounce.200ms="performSearch()"
                    @keydown.down.prevent="selectedIndex = Math.min(selectedIndex + 1, searchResults.length - 1)"
                    @keydown.up.prevent="selectedIndex = Math.max(selectedIndex - 1, 0)"
                    @keydown.enter.prevent="if(searchResults[selectedIndex]) executePaletteItem(searchResults[selectedIndex])"
                    placeholder="ابحث عن صفحة، أو نفّذ أمراً سريعاً..."
                    class="flex-1 text-sm bg-transparent border-none outline-none text-notion-text dark:text-[#E8E8E5] placeholder:text-notion-muted/60"
                    x-init="$watch('searchOpen', val => { if(val) { selectedIndex = 0; $nextTick(() => { $el.focus(); performSearch(); }); } })"
                />
                <div class="flex items-center gap-1.5 text-[10px] text-notion-muted">
                    <span class="px-1.5 py-0.5 rounded bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#383838]">Esc</span>
                </div>
            </div>

            {{-- نتائج البحث والأوامر --}}
            <div class="max-h-96 overflow-y-auto p-2 space-y-1">
                {{-- حالة التحميل --}}
                <div x-show="searchLoading" class="px-4 py-6 text-center text-sm text-notion-muted">
                    <div class="inline-block w-4 h-4 border-2 border-notion-muted border-t-transparent rounded-full animate-spin"></div>
                </div>

                {{-- عرض النتائج --}}
                <template x-if="!searchLoading && searchResults.length > 0">
                    <div class="space-y-1">
                        <template x-for="(result, index) in searchResults" :key="result.url || result.id || index">
                            <div
                                @click="executePaletteItem(result)"
                                @mouseenter="selectedIndex = index"
                                :class="selectedIndex === index ? 'bg-notion-hover dark:bg-[#2D2D2D] text-notion-text dark:text-[#F0F0ED]' : 'text-notion-muted dark:text-[#B0B0AC]'"
                                class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition-colors"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="text-base flex-shrink-0" x-text="result.icon"></span>
                                    <div class="min-w-0">
                                        <div class="text-xs font-medium truncate" x-text="result.title"></div>
                                        <div class="flex items-center gap-2 mt-0.5 text-[10px] opacity-75">
                                            <span x-text="result.workspace || result.category"></span>
                                            <template x-if="result.match === 'content' && result.excerpt">
                                                <span class="truncate max-w-xs">· "<span x-text="result.excerpt"></span>"</span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-[9px] px-1.5 py-0.5 rounded-md bg-notion-sidebar dark:bg-[#1E1E1E] border border-notion-border dark:border-[#333333] text-notion-muted" x-text="result.category || 'أمر'"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- لا نتائج --}}
                <template x-if="!searchLoading && searchResults.length === 0">
                    <div class="px-4 py-8 text-center text-xs text-notion-muted">
                        لا توجد نتائج مطابقة لـ "<span x-text="searchQuery" class="font-medium text-notion-text dark:text-white"></span>"
                    </div>
                </template>
            </div>

            {{-- شريط الاختصارات السفلي --}}
            <div class="px-4 py-2 border-t border-notion-border dark:border-[#2D2D2D] bg-notion-sidebar/40 dark:bg-[#1A1A1A] flex items-center justify-between text-[11px] text-notion-muted">
                <div class="flex items-center gap-3">
                    <span><kbd class="font-mono bg-white dark:bg-[#252525] px-1 rounded border border-notion-border dark:border-[#333333]">↑↓</kbd> للتنقل</span>
                    <span><kbd class="font-mono bg-white dark:bg-[#252525] px-1 rounded border border-notion-border dark:border-[#333333]">Enter</kbd> للاختيار</span>
                </div>
                <span><kbd class="font-mono bg-white dark:bg-[#252525] px-1 rounded border border-notion-border dark:border-[#333333]">Esc</kbd> للإغلاق</span>
            </div>
        </div>
    </div>

    <script>
    function appShell() {
        return {
            isDesktop: window.innerWidth >= 768,
            sidebarOpen: window.innerWidth >= 768,
            overviewOpen: false,
            searchOpen: false,
            searchQuery: '',
            searchResults: [],
            searchLoading: false,
            selectedIndex: 0,
            darkMode: localStorage.getItem('darkMode') !== 'false',

            handleResize() {
                const nowDesktop = window.innerWidth >= 768;
                if (nowDesktop !== this.isDesktop) {
                    this.isDesktop = nowDesktop;
                    this.sidebarOpen = nowDesktop;
                }
            },

            toggleDarkMode() {
                this.darkMode = !this.darkMode;
                localStorage.setItem('darkMode', this.darkMode);
            },

            executePaletteItem(item) {
                if (!item) return;
                this.searchOpen = false;

                if (item.type === 'action') {
                    if (item.action === 'toggle_theme') {
                        this.toggleDarkMode();
                    } else if (item.action === 'create_page') {
                        @if(isset($workspace))
                            fetch('{{ route('pages.store', $workspace) }}', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                                body: JSON.stringify({ title: 'صفحة جديدة' })
                            }).then(r => r.json()).then(d => {
                                if (d.page) window.location.href = `/pages/${d.page.id}`;
                            });
                        @else
                            window.location.href = '/dashboard';
                        @endif
                    } else if (item.action === 'open_templates') {
                        @if(isset($workspace))
                            window.location.href = '{{ route('templates.index', $workspace) }}';
                        @else
                            window.location.href = '/dashboard';
                        @endif
                    } else if (item.action === 'export_markdown') {
                        @if(isset($page))
                            window.location.href = '{{ route('pages.export.markdown', $page) }}';
                        @endif
                    }
                } else if (item.url) {
                    window.location.href = item.url;
                }
            },

            async performSearch() {
                this.searchLoading = true;
                try {
                    const res = await fetch(`/search?q=${encodeURIComponent(this.searchQuery)}`, {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                    });
                    const data = await res.json();
                    this.searchResults = data.results ?? [];
                    this.selectedIndex = 0;
                } catch (e) {
                    this.searchResults = [];
                } finally {
                    this.searchLoading = false;
                }
            }
        }
    }
    </script>

</body>
</html>
