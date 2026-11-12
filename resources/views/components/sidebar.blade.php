@php
    $currentActiveId = isset($page) ? $page->id : null;
    $ws = $workspace ?? (isset($page) ? $page->workspace : null);

    // الصفحات المفضلة
    $favoritePages = $ws ? $ws->pages()->where('is_favorite', true)->get() : collect();
@endphp

<div class="flex flex-col h-full">

    {{-- رأس مساحة العمل --}}
    <div class="px-2.5 py-2 flex items-center justify-between gap-1">
        @if($ws)
            <a href="{{ route('workspaces.show', $ws) }}" class="flex items-center gap-2 overflow-hidden hover:bg-notion-hover dark:hover:bg-[#2C2C2C] px-1.5 py-1 rounded flex-1 transition-colors">
                <span class="w-5 h-5 rounded bg-[#37352F] dark:bg-[#3A3A3A] text-white flex items-center justify-center text-[11px] font-semibold shrink-0">
                    {{ mb_substr($ws->name, 0, 1) }}
                </span>
                <span class="font-medium text-[13px] truncate text-notion-text dark:text-[#E3E3E0]">{{ $ws->name }}</span>
            </a>
            <form action="{{ route('pages.store', $ws) }}" method="POST" class="shrink-0">
                @csrf
                <button type="submit" title="صفحة جديدة" class="p-1 rounded text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </button>
            </form>
        @else
            <span class="font-medium text-[13px] text-notion-text dark:text-[#E3E3E0]">مساحة العمل</span>
        @endif
    </div>

    {{-- شريط الاختصارات الأفقي السريع بأسلوب Notion --}}
    @if($ws)
    <div class="px-2 pb-2 flex items-center gap-1">
        <a href="{{ route('workspaces.show', $ws) }}" class="flex-1 flex items-center gap-1.5 px-2.5 py-1.5 rounded-md bg-notion-hover/60 dark:bg-[#2A2A2A] hover:bg-notion-hover dark:hover:bg-[#323232] text-xs font-medium text-notion-text dark:text-[#E3E3E0] transition-colors {{ request()->routeIs('workspaces.show') ? 'ring-1 ring-[#3A3A3A]' : '' }}">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>الرئيسية</span>
        </a>
        <a href="{{ route('templates.index', $ws) }}" title="القوالب" class="p-1.5 rounded-md hover:bg-notion-hover dark:hover:bg-[#2C2C2C] text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] transition-colors {{ request()->routeIs('templates.index') ? 'bg-notion-hover dark:bg-[#2C2C2C] text-notion-text dark:text-[#E3E3E0]' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
        </a>
        <button @click="searchOpen = true" title="بحث (Ctrl+K)" class="p-1.5 rounded-md hover:bg-notion-hover dark:hover:bg-[#2C2C2C] text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
        </button>
    </div>
    @endif

    <div class="flex-1 overflow-y-auto px-2 py-1 space-y-2">

        {{-- ==================== المفضلة ==================== --}}
        @if($favoritePages->count() > 0)
        <div x-data="{ open: true }">
            <div class="flex items-center justify-between px-2 py-1 text-[11px] font-medium text-notion-muted dark:text-[#7F7F7F]">
                <button @click="open = !open" class="flex items-center gap-1 hover:text-notion-text dark:hover:text-[#E3E3E0]">
                    <span>المفضلة</span>
                </button>
            </div>
            <div x-show="open" x-cloak class="space-y-0.5">
                @foreach($favoritePages as $favPage)
                    <div class="sidebar-item group flex items-center justify-between px-2 py-1 rounded hover:bg-notion-hover dark:hover:bg-[#2C2C2C] cursor-pointer transition-colors {{ $currentActiveId == $favPage->id ? 'bg-notion-hover dark:bg-[#2C2C2C] font-medium' : '' }}">
                        <a href="{{ route('pages.show', $favPage) }}" class="flex items-center gap-1.5 flex-1 min-w-0">
                            <span class="text-sm shrink-0">{{ $favPage->icon ?? '📄' }}</span>
                            <span class="truncate text-notion-text dark:text-[#E3E3E0] text-[13px]">{{ $favPage->title }}</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ==================== قسم الصفحات ==================== --}}
        <div>
            <div class="flex items-center justify-between px-2 py-1 text-[11px] font-medium text-notion-muted dark:text-[#7F7F7F]">
                <span>الصفحات</span>
                @if($ws)
                    <form action="{{ route('pages.store', $ws) }}" method="POST">
                        @csrf
                        <button type="submit" title="إضافة صفحة رئيسية جديدة" class="hover:text-notion-text dark:hover:text-[#E3E3E0] p-0.5 hover:bg-notion-hover dark:hover:bg-[#2C2C2C] rounded transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                    </form>
                @endif
            </div>

            {{-- شجرة الصفحات المتداخلة --}}
            @if($ws && $ws->pages)
                @forelse($ws->pages->whereNull('parent_id') as $rootPage)
                    @include('components.sidebar-page-item', ['p' => $rootPage, 'level' => 0])
                @empty
                    <div class="px-2 py-3 text-xs text-notion-muted dark:text-[#7F7F7F]">
                        لا توجد صفحات بعد
                    </div>
                @endforelse
            @endif
        </div>
    </div>

    {{-- أسفل السايدبار: الإعدادات --}}
    @if($ws)
        <div class="p-2 border-t border-notion-border dark:border-[#2A2A2A] space-y-0.5">
            <a href="{{ route('workspaces.settings', $ws) }}" class="flex items-center gap-2 px-2 py-1.5 text-[13px] text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2C2C2C] rounded transition-colors {{ request()->routeIs('workspaces.settings') ? 'bg-notion-hover dark:bg-[#2C2C2C] text-notion-text dark:text-[#E3E3E0]' : '' }}">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>الإعدادات والأعضاء</span>
            </a>
        </div>
    @endif
</div>
