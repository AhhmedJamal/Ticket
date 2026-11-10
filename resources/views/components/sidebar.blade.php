@php
    $currentActiveId = isset($page) ? $page->id : null;
    $ws = $workspace ?? (isset($page) ? $page->workspace : null);
@endphp

<div class="flex flex-col h-full">
    {{-- رأس مساحة العمل --}}
    <div class="p-3 border-b border-notion-border flex items-center justify-between">
        @if($ws)
            <a href="{{ route('workspaces.show', $ws) }}" class="flex items-center gap-2 overflow-hidden hover:bg-notion-hover p-1.5 rounded-md flex-1 transition-colors">
                <span class="w-6 h-6 rounded bg-[#37352F] text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                    {{ mb_substr($ws->name, 0, 1) }}
                </span>
                <span class="font-semibold text-sm truncate text-notion-text">{{ $ws->name }}</span>
            </a>
        @else
            <span class="font-semibold text-sm text-notion-text">مساحة العمل</span>
        @endif
    </div>

    {{-- قسم الصفحات والشجرة الهرمية --}}
    <div class="flex-1 overflow-y-auto px-2 py-3 space-y-0.5">
        <div class="flex items-center justify-between px-2 py-1 text-xs font-medium text-notion-muted">
            <span>الصفحات (Pages)</span>
            @if($ws)
                <form action="{{ route('pages.store', $ws) }}" method="POST">
                    @csrf
                    <button type="submit" title="إضافة صفحة رئيسية جديدة" class="hover:text-notion-text p-1 hover:bg-notion-hover rounded transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                </form>
            @endif
        </div>

        {{-- شجرة الصفحات المتداخلة --}}
        @if($ws && $ws->pages)
            @forelse($ws->pages->whereNull('parent_id') as $rootPage)
                <div x-data="{ open: true }" class="text-sm">
                    {{-- الصفحة الجذرية --}}
                    <div class="sidebar-item group flex items-center justify-between px-2 py-1 rounded-md hover:bg-notion-hover cursor-pointer transition-colors {{ $currentActiveId == $rootPage->id ? 'bg-notion-hover font-medium' : '' }}">
                        <div class="flex items-center gap-1 flex-1 min-w-0">
                            {{-- سهم الطي/الفتح --}}
                            @if($rootPage->children && $rootPage->children->count() > 0)
                                <button @click.stop="open = !open" class="text-notion-muted hover:text-notion-text p-0.5 rounded">
                                    <svg class="w-3.5 h-3.5 transition-transform duration-150" :class="{ '-rotate-90': !open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            @else
                                <span class="w-4 inline-block"></span>
                            @endif

                            <a href="{{ route('pages.show', $rootPage) }}" class="flex items-center gap-1.5 flex-1 min-w-0 py-0.5">
                                <span class="text-sm">{{ $rootPage->icon ?? '📄' }}</span>
                                <span class="truncate text-notion-text text-[13px]">{{ $rootPage->title }}</span>
                            </a>
                        </div>

                        {{-- أزرار سريعة تظهر عند التمرير: إضافة صفحة فرعية (+) أو حذف --}}
                        <div class="actions opacity-0 group-hover:opacity-100 flex items-center gap-0.5 transition-opacity">
                            <form action="{{ route('pages.store', $ws) }}" method="POST">
                                @csrf
                                <input type="hidden" name="parent_id" value="{{ $rootPage->id }}">
                                <button type="submit" title="إضافة صفحة فرعية داخل هذه الصفحة" class="text-notion-muted hover:text-notion-text p-1 hover:bg-zinc-200 rounded">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                            </form>
                            <form action="{{ route('pages.destroy', $rootPage) }}" method="POST" onsubmit="return confirm('حذف الصفحة وجميع صفحاتها الفرعية؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="حذف" class="text-notion-muted hover:text-red-500 p-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- الصفحات الفرعية (Level 2: صفحة جوه صفحة) --}}
                    @if($rootPage->children && $rootPage->children->count() > 0)
                        <div x-show="open" x-cloak class="pr-5 mt-0.5 space-y-0.5 border-r border-notion-border mr-2.5">
                            @foreach($rootPage->children as $child)
                                <div class="sidebar-item group flex items-center justify-between px-2 py-1 rounded-md hover:bg-notion-hover cursor-pointer transition-colors {{ $currentActiveId == $child->id ? 'bg-notion-hover font-medium' : '' }}">
                                    <a href="{{ route('pages.show', $child) }}" class="flex items-center gap-1.5 flex-1 min-w-0">
                                        <span class="text-xs">{{ $child->icon ?? '📄' }}</span>
                                        <span class="truncate text-notion-text text-xs">{{ $child->title }}</span>
                                    </a>

                                    <form action="{{ route('pages.destroy', $child) }}" method="POST" onsubmit="return confirm('حذف هذه الصفحة الفرعية؟')" class="actions opacity-0 group-hover:opacity-100">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-notion-muted hover:text-red-500 p-0.5">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="px-3 py-6 text-xs text-notion-muted text-center">
                    لا توجد صفحات بعد. اضغط (+) للبدء!
                </div>
            @endforelse
        @endif
    </div>

    {{-- زر سفلي سريع لإضافة صفحة رئيسية --}}
    @if($ws)
        <div class="p-2 border-t border-notion-border">
            <form action="{{ route('pages.store', $ws) }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-2.5 py-1.5 text-xs text-notion-muted hover:text-notion-text hover:bg-notion-hover rounded-md transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>صفحة جديدة</span>
                </button>
            </form>
        </div>
    @endif
</div>
