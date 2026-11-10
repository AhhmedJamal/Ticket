@extends('layouts.app')

@section('content')
    <div class="py-12 text-center">
        <div class="text-6xl mb-4">🏠</div>
        <h1 class="text-3xl font-bold text-notion-text mb-2">{{ $workspace->name }}</h1>
        <p class="text-sm text-notion-muted mb-8">مرحباً بك في مساحة عملك. اختر صفحة من القائمة الجانبية أو ابدأ بإنشاء صفحة جديدة.</p>

        {{-- الصفحات الرئيسية المتوفرة --}}
        @if($workspace->pages->whereNull('parent_id')->count() > 0)
            <div class="max-w-md mx-auto text-right mb-8">
                <div class="text-xs font-semibold text-notion-muted mb-2 px-1">الصفحات الحالية:</div>
                <div class="space-y-1 bg-notion-sidebar p-2 rounded-xl border border-notion-border">
                    @foreach($workspace->pages->whereNull('parent_id') as $p)
                        <a href="{{ route('pages.show', $p) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-white text-sm text-notion-text transition-colors">
                            <span class="flex items-center gap-2">
                                <span>{{ $p->icon ?? '📄' }}</span>
                                <span class="font-medium">{{ $p->title }}</span>
                            </span>
                            <span class="text-xs text-notion-muted">{{ $p->children->count() }} صفحات فرعية &larr;</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('pages.store', $workspace) }}" method="POST" class="inline-block">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#37352F] text-white rounded-lg text-sm font-medium hover:bg-black transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>إنشاء صفحة رئيسية جديدة</span>
            </button>
        </form>
    </div>
@endsection
