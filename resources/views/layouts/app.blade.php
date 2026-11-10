<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Notion') }}</title>

    <!-- خط Inter و الخطوط النظيفة -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Kufi+Arabic:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS عبر CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js عبر CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Noto Kufi Arabic"', 'Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        notion: {
                            sidebar: '#F7F7F5',
                            hover: '#EBEBEA',
                            border: '#E9E9E7',
                            text: '#37352F',
                            muted: '#9B9A97',
                            accent: '#2EAADC'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        body { color: #37352F; background-color: #FFFFFF; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #D3D3D0; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #B4B4B0; }
        .sidebar-item:hover .actions { opacity: 1; }
    </style>
</head>
<body 
    x-data="{ sidebarOpen: true, searchOpen: false, searchQuery: '' }"
    class="h-screen overflow-hidden flex flex-col font-sans antialiased text-notion-text"
>

    <div class="flex-1 flex overflow-hidden">
        
        {{-- ================= الشريط الجانبي (Sidebar) ================= --}}
        <aside 
            x-show="sidebarOpen"
            x-transition
            class="w-64 bg-notion-sidebar border-l border-notion-border flex flex-col h-full flex-shrink-0 select-none z-20"
        >
            @include('components.sidebar')
        </aside>

        {{-- ================= منطقة المحتوى الرئيسية ================= --}}
        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-white relative">
            
            {{-- الشريط العلوي --}}
            <header class="h-11 border-b border-notion-border flex items-center justify-between px-4 flex-shrink-0 bg-white sticky top-0 z-10">
                <div class="flex items-center space-x-2 space-x-reverse text-sm text-notion-muted">
                    {{-- زر إظهار/إخفاء السايدبار --}}
                    <button 
                        @click="sidebarOpen = !sidebarOpen" 
                        class="p-1 rounded hover:bg-notion-hover text-notion-muted hover:text-notion-text transition-colors"
                        title="إخفاء/إظهار القائمة الجانبية"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>

                    <a href="{{ isset($workspace) ? route('workspaces.show', $workspace) : '#' }}" class="hover:text-notion-text font-medium text-xs text-notion-muted">
                        {{ $workspace->name ?? 'Notion' }}
                    </a>

                    @isset($page)
                        <span>/</span>
                        @if($page->parent)
                            <a href="{{ route('pages.show', $page->parent) }}" class="hover:underline text-xs text-notion-muted">
                                {{ $page->parent->icon }} {{ $page->parent->title }}
                            </a>
                            <span>/</span>
                        @endif
                        <span class="text-notion-text font-medium text-xs flex items-center gap-1">
                            <span>{{ $page->icon ?? '📄' }}</span>
                            <span>{{ $page->title }}</span>
                        </span>
                    @endisset
                </div>

                <div class="flex items-center gap-3">
                    @auth
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-notion-muted">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">خروج</button>
                        </form>
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

</body>
</html>
