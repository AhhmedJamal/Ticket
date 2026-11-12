@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto">
    {{-- رأس الصفحة --}}
    <div class="mb-6 sm:mb-8">
        <h1 class="text-xl sm:text-2xl font-bold text-notion-text dark:text-white mb-1">📋 قوالب الصفحات</h1>
        <p class="text-xs sm:text-sm text-notion-muted dark:text-gray-400">ابدأ بقالب جاهز لتوفير وقتك</p>
    </div>

    {{-- القوالب --}}
    @php
        $cats = \App\Models\Template::categories();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($templates as $category => $categoryTemplates)
            @php
                $catInfo = $cats[$category] ?? ['label' => $category, 'icon' => '📄'];
            @endphp
            @foreach($categoryTemplates as $template)
                <div class="border border-notion-border dark:border-[#2E2E2E] rounded-xl overflow-hidden hover:border-notion-muted/60 dark:hover:border-[#4A4A4A] transition-all group bg-white dark:bg-[#202020] flex flex-col justify-between">
                    <div>
                        <div class="h-24 bg-notion-sidebar dark:bg-[#252525] border-b border-notion-border dark:border-[#2E2E2E] flex items-center justify-center">
                            <span class="text-4xl group-hover:scale-110 transition-transform">{{ $template->icon }}</span>
                        </div>
                        <div class="p-4">
                            <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-md bg-notion-sidebar dark:bg-[#2A2A2A] text-notion-muted dark:text-[#9B9A97] border border-notion-border dark:border-[#333333] mb-2">
                                <span>{{ $catInfo['icon'] }}</span>
                                <span>{{ $catInfo['label'] }}</span>
                            </span>
                            <h3 class="font-semibold text-notion-text dark:text-[#E3E3E0] text-sm truncate">{{ $template->name }}</h3>
                            <p class="text-xs text-notion-muted dark:text-[#9B9A97] mt-1 line-clamp-2 leading-relaxed">{{ $template->description }}</p>
                        </div>
                    </div>
                    <form action="{{ route('templates.apply', [$workspace, $template]) }}" method="POST" class="px-4 pb-4">
                        @csrf
                        <input type="hidden" name="parent_id" value="">
                        <button type="submit" class="w-full text-xs py-2 px-3 bg-notion-text dark:bg-[#2C2C2C] text-white dark:text-[#E3E3E0] border border-transparent dark:border-[#3A3A3A] rounded-lg hover:bg-black dark:hover:bg-[#3A3A3A] transition-colors font-medium">
                            استخدام هذا القالب
                        </button>
                    </form>
                </div>
            @endforeach
        @endforeach
    </div>
</div>
@endsection
