@php $hasChildren = $p->children && $p->children->count() > 0; @endphp

<div x-data="{ open: {{ $hasChildren ? 'true' : 'false' }} }" class="text-sm">
    <div
        class="sidebar-item group flex items-center justify-between px-2 py-1 rounded-md hover:bg-notion-hover dark:hover:bg-[#2F2F2F] cursor-pointer transition-colors {{ isset($currentActiveId) && $currentActiveId == $p->id ? 'bg-notion-hover dark:bg-[#2F2F2F] font-medium' : '' }}"
        style="{{ $level > 0 ? 'padding-right: ' . (8 + $level * 12) . 'px' : '' }}"
    >
        <div class="flex items-center gap-1 flex-1 min-w-0">
            {{-- سهم الطي --}}
            @if($hasChildren)
                <button @click.stop="open = !open" class="text-notion-muted hover:text-notion-text dark:hover:text-[#E8E8E5] p-0.5 rounded flex-shrink-0">
                    <svg class="w-3 h-3 transition-transform duration-150" :class="{ '-rotate-90': !open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            @else
                <span class="w-4 inline-block flex-shrink-0"></span>
            @endif

            <a href="{{ route('pages.show', $p) }}" class="flex items-center gap-1.5 flex-1 min-w-0 py-0.5">
                <span class="text-sm flex-shrink-0">{{ $p->icon ?? '📄' }}</span>
                <span class="truncate text-notion-text dark:text-[#E8E8E5] text-[13px]">{{ $p->title }}</span>
            </a>
        </div>

        {{-- أزرار سريعة تظهر عند التمرير --}}
        <div class="actions opacity-0 group-hover:opacity-100 flex items-center gap-0.5 transition-opacity flex-shrink-0">
            @isset($ws)
            <form action="{{ route('pages.store', $ws) }}" method="POST">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $p->id }}">
                <button type="submit" title="إضافة صفحة فرعية" class="text-notion-muted hover:text-notion-text dark:hover:text-[#E8E8E5] p-1 hover:bg-zinc-200 dark:hover:bg-[#3a3a3a] rounded">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </button>
            </form>
            @endisset
            <form action="{{ route('pages.destroy', $p) }}" method="POST" onsubmit="return confirm('حذف الصفحة وجميع صفحاتها الفرعية؟')">
                @csrf
                @method('DELETE')
                <button type="submit" title="حذف" class="text-notion-muted hover:text-red-500 p-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </form>
        </div>
    </div>

    {{-- الصفحات الفرعية (recursive) --}}
    @if($hasChildren)
        <div x-show="open" x-cloak class="mt-0.5 space-y-0.5 border-r border-notion-border dark:border-[#3a3a3a] mr-3">
            @foreach($p->children as $child)
                @include('components.sidebar-page-item', ['p' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>
