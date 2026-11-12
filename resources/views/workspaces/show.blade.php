@extends('layouts.app')

@section('content')
    @php
        $canEditWorkspace = auth()->id() === $workspace->user_id
            || $workspace->members->contains(fn ($m) => $m->id === auth()->id() && $m->pivot->role === 'admin');
    @endphp
    <div class="max-w-4xl mx-auto py-4 space-y-8">
        @if(session('success'))
            <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 rounded-lg px-4 py-2.5 text-xs sm:text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 rounded-lg px-4 py-2.5 text-xs sm:text-sm">
                @foreach($errors->all() as $error) {{ $error }} @endforeach
            </div>
        @endif

        {{-- رأس مساحة العمل --}}
        <div x-data="{ editingWorkspace: false }" class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-notion-border dark:border-[#2E2E2E]">
            <div class="min-w-0 flex-1">
                <div class="text-xs text-notion-muted dark:text-[#9B9A97] mb-1">مساحة العمل</div>

                <div x-show="!editingWorkspace" class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-2xl sm:text-3xl font-bold text-notion-text dark:text-[#E3E3E0] flex items-center gap-2.5 min-w-0">
                        <span>🏠</span>
                        <span class="truncate">{{ $workspace->name }}</span>
                    </h1>
                    @if($canEditWorkspace)
                        <button
                            type="button"
                            @click="editingWorkspace = true; $nextTick(() => $refs.wsNameInput.focus())"
                            title="تعديل اسم مساحة العمل"
                            class="p-1.5 rounded-lg text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </button>
                    @endif
                </div>

                @if($canEditWorkspace)
                    <form
                        x-show="editingWorkspace"
                        x-cloak
                        action="{{ route('workspaces.update', $workspace) }}"
                        method="POST"
                        class="flex flex-wrap items-center gap-2 mt-1"
                    >
                        @csrf
                        @method('PUT')
                        <input
                            x-ref="wsNameInput"
                            type="text"
                            name="name"
                            value="{{ old('name', $workspace->name) }}"
                            required
                            class="flex-1 min-w-[180px] max-w-xs px-3 py-1.5 text-sm sm:text-base font-semibold bg-white dark:bg-[#191919] border border-notion-border dark:border-[#3A3A3A] rounded-lg text-notion-text dark:text-[#E3E3E0] focus:outline-none focus:ring-2 focus:ring-notion-accent"
                        >
                        <button type="submit" class="px-3 py-1.5 bg-[#37352F] dark:bg-[#2EAADC] text-white rounded-lg text-xs font-medium hover:bg-black dark:hover:bg-[#2596C4] transition-colors">
                            حفظ
                        </button>
                        <button type="button" @click="editingWorkspace = false" class="px-3 py-1.5 bg-notion-sidebar dark:bg-[#202020] text-notion-muted dark:text-[#9B9A97] border border-notion-border dark:border-[#2E2E2E] rounded-lg text-xs hover:text-notion-text dark:hover:text-[#E3E3E0] transition-colors">
                            إلغاء
                        </button>
                    </form>
                @endif

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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
