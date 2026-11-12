@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-4 space-y-8">
        {{-- رأس مساحة العمل --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-notion-border dark:border-[#2E2E2E]">
            <div>
                <div class="text-xs text-notion-muted dark:text-[#9B9A97] mb-1">مساحة العمل</div>
                <h1 class="text-3xl font-bold text-notion-text dark:text-[#E3E3E0] flex items-center gap-2.5">
                    <span>🏠</span>
                    <span>{{ $workspace->name }}</span>
                </h1>
                <p class="text-sm text-notion-muted dark:text-[#9B9A97] mt-1">اختر صفحة للمتابعة أو ابدأ بإنشاء صفحة أو قالب جديد.</p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <form action="{{ route('pages.store', $workspace) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#37352F] dark:bg-[#2EAADC] text-white rounded-lg text-xs font-medium hover:bg-black dark:hover:bg-[#2596C4] transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>صفحة جديدة</span>
                    </button>
                </form>
                <a href="{{ route('templates.index', $workspace) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-notion-sidebar dark:bg-[#202020] text-notion-text dark:text-[#E3E3E0] border border-notion-border dark:border-[#2E2E2E] rounded-lg text-xs font-medium hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors">
                    <span>📋</span>
                    <span>القوالب</span>
                </a>
            </div>
        </div>

        {{-- الصفحات الرئيسية بنظام بطاقات عمودين (2-Column Card Grid) --}}
        @php $rootPages = $workspace->pages->whereNull('parent_id'); @endphp
        @if($rootPages->count() > 0)
            <div>
                <div class="text-xs font-semibold text-notion-muted dark:text-[#9B9A97] mb-4">الصفحات ({{ $rootPages->count() }})</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($rootPages as $p)
                        <a href="{{ route('pages.show', $p) }}" class="group flex flex-col justify-between rounded-xl border border-notion-border dark:border-[#2E2E2E] bg-white dark:bg-[#202020] overflow-hidden hover:border-notion-muted/60 dark:hover:border-[#4A4A4A] transition-all">
                            <div class="h-24 w-full flex items-center justify-center border-b border-notion-border dark:border-[#2E2E2E] {{ $p->cover_color ? collect(\App\Models\Page::coverColors())->get($p->cover_color, ['class' => 'bg-notion-sidebar dark:bg-[#252525]'])['class'] : 'bg-notion-sidebar dark:bg-[#252525]' }}">
                                <span class="text-4xl group-hover:scale-110 transition-transform">{{ $p->icon ?? '📄' }}</span>
                            </div>
                            <div class="p-4 space-y-1.5">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="font-semibold text-sm text-notion-text dark:text-[#E3E3E0] truncate">{{ $p->title }}</h3>
                                    @if($p->is_favorite)
                                        <span class="text-xs shrink-0">⭐</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between text-xs text-notion-muted dark:text-[#9B9A97]">
                                    <span>{{ $p->children->count() }} صفحات فرعية</span>
                                    <span class="group-hover:text-notion-text dark:group-hover:text-[#E3E3E0] transition-colors">فتح ←</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            <div class="py-16 text-center rounded-2xl border border-dashed border-notion-border dark:border-[#2E2E2E]">
                <div class="text-4xl mb-3">📝</div>
                <div class="text-sm font-medium text-notion-text dark:text-[#E3E3E0] mb-1">لا توجد صفحات بعد</div>
                <p class="text-xs text-notion-muted dark:text-[#9B9A97]">ابدأ بإنشاء صفحة جديدة أو اختر من القوالب الجاهزة.</p>
            </div>
        @endif
    </div>
@endsection
