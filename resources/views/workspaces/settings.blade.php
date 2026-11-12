@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">

    {{-- رأس الصفحة --}}
    <div class="pb-4 border-b border-notion-border dark:border-[#2E2E2E]">
        <div class="flex items-center gap-3 mb-4">
            <span class="text-3xl">⚙️</span>
            <div>
                <h1 class="text-xl font-bold text-notion-text dark:text-[#E3E3E0]">إعدادات مساحة العمل</h1>
                <p class="text-sm text-notion-muted dark:text-[#9B9A97]">{{ $workspace->name }}</p>
            </div>
        </div>
    </div>

    {{-- رسائل النجاح والخطأ --}}
    @if(session('success'))
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 rounded-lg px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 rounded-lg px-4 py-3 text-sm">
            @foreach($errors->all() as $error) {{ $error }} @endforeach
        </div>
    @endif

    {{-- =================== معلومات المساحة =================== --}}
    <section>
        <h2 class="text-xs font-semibold text-notion-muted dark:text-[#9B9A97] uppercase tracking-wider mb-3">المعلومات الأساسية</h2>
        <div class="bg-notion-sidebar dark:bg-[#202020] border border-notion-border dark:border-[#2E2E2E] rounded-xl p-4 space-y-3">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-lg bg-notion-text dark:bg-[#3A3A3A] text-white flex items-center justify-center font-bold text-lg">
                    {{ mb_substr($workspace->name, 0, 1) }}
                </span>
                <div>
                    <div class="font-semibold text-notion-text dark:text-[#E3E3E0]">{{ $workspace->name }}</div>
                    <div class="text-xs text-notion-muted dark:text-[#9B9A97]">المالك: {{ $workspace->owner->name }}</div>
                </div>
            </div>
        </div>
    </section>

    {{-- =================== الأعضاء الحاليون =================== --}}
    <section>
        <h2 class="text-xs font-semibold text-notion-muted dark:text-[#9B9A97] uppercase tracking-wider mb-3">
            الأعضاء ({{ $workspace->members->count() + 1 }})
        </h2>
        <div class="border border-notion-border dark:border-[#2E2E2E] rounded-xl overflow-hidden">
            {{-- المالك --}}
            <div class="flex items-center justify-between px-4 py-3 bg-white dark:bg-[#202020] border-b border-notion-border dark:border-[#2E2E2E]">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-notion-text dark:bg-[#3A3A3A] text-white flex items-center justify-center text-xs font-bold">
                        {{ mb_substr($workspace->owner->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="text-sm font-medium text-notion-text dark:text-[#E3E3E0]">{{ $workspace->owner->name }}</div>
                        <div class="text-xs text-notion-muted dark:text-[#9B9A97]">{{ $workspace->owner->email }}</div>
                    </div>
                </div>
                <span class="text-xs bg-notion-text dark:bg-[#3A3A3A] text-white px-2.5 py-0.5 rounded-full">مالك</span>
            </div>

            {{-- بقية الأعضاء --}}
            @forelse($workspace->members as $member)
                <div class="flex items-center justify-between px-4 py-3 bg-white dark:bg-[#202020] border-b border-notion-border dark:border-[#2E2E2E] last:border-0">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-xs font-bold">
                            {{ mb_substr($member->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="text-sm font-medium text-notion-text dark:text-[#E3E3E0]">{{ $member->name }}</div>
                            <div class="text-xs text-notion-muted dark:text-[#9B9A97]">{{ $member->email }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs {{ $member->pivot->role === 'admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300' : 'bg-gray-100 text-gray-600 dark:bg-[#2C2C2C] dark:text-[#9B9A97]' }} px-2 py-0.5 rounded-full">
                            {{ $member->pivot->role === 'admin' ? 'مشرف' : 'عضو' }}
                        </span>
                        @if(auth()->id() === $workspace->user_id)
                            <form action="{{ route('workspaces.members.remove', [$workspace, $member]) }}" method="POST"
                                  onsubmit="return confirm('إزالة {{ $member->name }} من مساحة العمل؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 p-1">إزالة</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-6 text-center text-sm text-notion-muted dark:text-[#9B9A97] bg-white dark:bg-[#202020]">
                    لا يوجد أعضاء آخرون حتى الآن.
                </div>
            @endforelse
        </div>
    </section>

    {{-- =================== دعوة عضو جديد =================== --}}
    @if(auth()->id() === $workspace->user_id)
    <section>
        <h2 class="text-xs font-semibold text-notion-muted dark:text-[#9B9A97] uppercase tracking-wider mb-3">دعوة عضو جديد</h2>
        <form action="{{ route('workspaces.members.invite', $workspace) }}" method="POST"
              class="bg-notion-sidebar dark:bg-[#202020] border border-notion-border dark:border-[#2E2E2E] rounded-xl p-4 space-y-3">
            @csrf
            <div class="flex flex-col sm:flex-row gap-3">
                <input
                    type="email"
                    name="email"
                    placeholder="البريد الإلكتروني للمستخدم"
                    required
                    class="flex-1 text-sm bg-white dark:bg-[#191919] border border-notion-border dark:border-[#2E2E2E] rounded-lg px-3 py-2 focus:outline-none focus:border-notion-accent text-notion-text dark:text-[#E3E3E0] placeholder-notion-muted"
                />
                <select name="role" class="text-sm bg-white dark:bg-[#191919] border border-notion-border dark:border-[#2E2E2E] rounded-lg px-3 py-2 focus:outline-none text-notion-text dark:text-[#E3E3E0]">
                    <option value="member">عضو</option>
                    <option value="admin">مشرف</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-notion-text dark:bg-[#2EAADC] text-white rounded-lg text-sm hover:bg-black dark:hover:bg-[#2596C4] transition-colors whitespace-nowrap">
                    دعوة
                </button>
            </div>
            <p class="text-xs text-notion-muted dark:text-[#9B9A97]">يجب أن يكون المستخدم مسجلاً في التطبيق مسبقاً.</p>
        </form>
    </section>
    @endif

</div>
@endsection
