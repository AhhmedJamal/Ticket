<!DOCTYPE html>
<html lang="ar" dir="rtl" x-data="appShell()" :class="{ 'dark': darkMode }" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Notion') }}</title>

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
    @keydown.window.ctrl.k.prevent="searchOpen = true"
    @keydown.window.ctrl.shift.l.prevent="toggleDarkMode()"
    @keydown.window.slash.prevent="if(document.activeElement.tagName === 'BODY') searchOpen = true"
>

    <div class="flex-1 flex overflow-hidden">

        {{-- ================= الشريط الجانبي (Sidebar) ================= --}}
        <aside
            x-show="sidebarOpen"
            x-transition:enter="transition-transform duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            class="w-60 bg-notion-sidebar dark:bg-[#202020] border-l border-notion-border dark:border-[#2A2A2A] flex flex-col h-full shrink-0 select-none z-20"
        >
            @include('components.sidebar')
        </aside>

        {{-- ================= منطقة المحتوى الرئيسية ================= --}}
        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-white dark:bg-[#191919] relative">

            {{-- الشريط العلوي (Notion-style flat topbar) --}}
            <header class="h-11 flex items-center justify-between px-3 shrink-0 bg-white/95 dark:bg-[#191919]/95 backdrop-blur-sm sticky top-0 z-10">
                <div class="flex items-center gap-1.5 text-[13px] text-notion-muted min-w-0">
                    {{-- زر إظهار/إخفاء السايدبار --}}
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        class="p-1.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] text-notion-muted hover:text-notion-text dark:hover:text-[#E3E3E0] transition-colors shrink-0"
                        title="إخفاء/إظهار القائمة الجانبية"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>

                    <a href="{{ isset($workspace) ? route('workspaces.show', $workspace) : '#' }}" class="px-1.5 py-0.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] hover:text-notion-text dark:hover:text-[#E3E3E0] text-notion-muted truncate max-w-[140px] transition-colors">
                        {{ $workspace->name ?? 'Notion' }}
                    </a>

                    @isset($page)
                        <span class="text-notion-muted/40">/</span>
                        @if($page->parent)
                            <a href="{{ route('pages.show', $page->parent) }}" class="px-1.5 py-0.5 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] hover:text-notion-text dark:hover:text-[#E3E3E0] text-notion-muted truncate max-w-[140px] transition-colors">
                                {{ $page->parent->icon }} {{ $page->parent->title }}
                            </a>
                            <span class="text-notion-muted/40">/</span>
                        @endif
                        <span class="px-1.5 py-0.5 text-notion-text dark:text-[#E3E3E0] flex items-center gap-1.5 truncate max-w-[220px]">
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
            <div class="flex-1 px-12 py-8 max-w-4xl w-full mx-auto">
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

        {{-- نافذة البحث --}}
        <div class="relative w-full max-w-xl bg-white dark:bg-[#252525] rounded-2xl shadow-2xl border border-notion-border dark:border-[#3a3a3a] overflow-hidden z-10">
            {{-- حقل البحث --}}
            <div class="flex items-center gap-3 px-4 py-3 border-b border-notion-border dark:border-[#3a3a3a]">
                <svg class="w-4 h-4 text-notion-muted flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input
                    type="text"
                    id="search-input"
                    x-model="searchQuery"
                    @input.debounce.300ms="performSearch()"
                    placeholder="ابحث في الصفحات والمحتوى..."
                    class="flex-1 text-sm bg-transparent border-none outline-none text-notion-text dark:text-[#E8E8E5] placeholder-notion-muted"
                    x-init="$watch('searchOpen', val => { if(val) $nextTick(() => $el.focus()) })"
                />
                <button @click="searchOpen = false" class="text-xs text-notion-muted hover:text-notion-text dark:hover:text-white px-1">Esc</button>
            </div>

            {{-- نتائج البحث --}}
            <div class="max-h-80 overflow-y-auto">
                {{-- حالة التحميل --}}
                <div x-show="searchLoading" class="px-4 py-6 text-center text-sm text-notion-muted">
                    <div class="inline-block w-4 h-4 border-2 border-notion-muted border-t-transparent rounded-full animate-spin"></div>
                </div>

                {{-- نتائج --}}
                <template x-if="!searchLoading && searchResults.length > 0">
                    <div class="py-1">
                        <template x-for="result in searchResults" :key="result.url">
                            <a :href="result.url"
                               @click="searchOpen = false"
                               class="flex items-center gap-3 px-4 py-2.5 hover:bg-notion-hover dark:hover:bg-[#2F2F2F] transition-colors group">
                                <span class="text-lg flex-shrink-0" x-text="result.icon"></span>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-notion-text dark:text-[#E8E8E5] truncate" x-text="result.title"></div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs text-notion-muted" x-text="result.workspace"></span>
                                        <template x-if="result.match === 'content' && result.excerpt">
                                            <span class="text-xs text-notion-muted truncate max-w-xs">· "<span x-text="result.excerpt"></span>"</span>
                                        </template>
                                    </div>
                                </div>
                                <svg class="w-3 h-3 text-notion-muted opacity-0 group-hover:opacity-100 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </template>
                    </div>
                </template>

                {{-- لا نتائج --}}
                <template x-if="!searchLoading && searchQuery.length >= 2 && searchResults.length === 0">
                    <div class="px-4 py-8 text-center text-sm text-notion-muted">
                        لا توجد نتائج لـ "<span x-text="searchQuery" class="font-medium"></span>"
                    </div>
                </template>

                {{-- الحالة الافتراضية --}}
                <template x-if="!searchLoading && searchQuery.length < 2">
                    <div class="px-4 py-6 text-center text-xs text-notion-muted">
                        اكتب حرفين على الأقل للبدء في البحث
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
    function appShell() {
        return {
            sidebarOpen: true,
            overviewOpen: false,
            searchOpen: false,
            searchQuery: '',
            searchResults: [],
            searchLoading: false,
            darkMode: localStorage.getItem('darkMode') !== 'false',

            toggleDarkMode() {
                this.darkMode = !this.darkMode;
                localStorage.setItem('darkMode', this.darkMode);
            },

            async performSearch() {
                if (this.searchQuery.length < 2) {
                    this.searchResults = [];
                    return;
                }
                this.searchLoading = true;
                try {
                    const res = await fetch(`/search?q=${encodeURIComponent(this.searchQuery)}`, {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                    });
                    const data = await res.json();
                    this.searchResults = data.results ?? [];
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
