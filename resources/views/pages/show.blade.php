@extends('layouts.app')

@section('content')
<div x-data="pageEditor()" class="flex -mx-12 -mt-8 min-h-[calc(100vh-2.75rem)]">
<div class="flex-1 min-w-0 space-y-0">

    {{-- =================== غلاف الصفحة (Cover Image) =================== --}}
    @php $hasCover = $page->cover_color || $page->cover_image; @endphp
    <div class="relative group/cover">
        @if($hasCover)
            <div class="h-44 w-full relative overflow-hidden
                {{ $page->cover_color ? collect(\App\Models\Page::coverColors())->get($page->cover_color, ['class' => 'bg-gradient-to-r from-gray-300 to-gray-500'])['class'] : '' }}"
                :style="coverImage ? `background-image: url('${coverImage}'); background-size: cover; background-position: center;` : ''"
            >
                {{-- أزرار تعديل الغلاف --}}
                <div class="absolute bottom-3 left-6 opacity-0 group-hover/cover:opacity-100 transition-opacity flex gap-1.5">
                    <button @click="showCoverPicker = !showCoverPicker" class="text-xs bg-white/90 dark:bg-[#252525]/90 hover:bg-white dark:hover:bg-[#2F2F2F] px-2.5 py-1 rounded-md text-gray-800 dark:text-[#E3E3E0] border border-black/10 dark:border-white/10 backdrop-blur-sm shadow-sm transition-colors">
                        🎨 تغيير الغلاف
                    </button>
                    <button @click="removeCover()" class="text-xs bg-white/90 dark:bg-[#252525]/90 hover:bg-white dark:hover:bg-[#2F2F2F] px-2.5 py-1 rounded-md text-gray-800 dark:text-[#E3E3E0] border border-black/10 dark:border-white/10 backdrop-blur-sm shadow-sm transition-colors">
                        ✕ إزالة
                    </button>
                </div>
            </div>
        @else
            {{-- شريط علوي خفي لإضافة غلاف عند التمرير --}}
            <div class="h-8 group/addcover flex items-end px-4 sm:px-12">
                <button @click="showCoverPicker = !showCoverPicker" class="text-xs text-notion-muted opacity-0 group-hover/addcover:opacity-100 hover:text-notion-text dark:hover:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2C2C2C] px-2 py-0.5 rounded transition-all">
                    🖼️ إضافة غلاف
                </button>
            </div>
        @endif

        {{-- منتقي الغلاف --}}
        <div x-show="showCoverPicker" x-cloak @click.outside="showCoverPicker = false"
             class="absolute top-full left-12 bg-white dark:bg-[#252525] border border-notion-border dark:border-[#333333] rounded-xl p-4 shadow-2xl z-30 w-72">
            <div class="text-xs font-medium text-notion-muted dark:text-[#9B9A97] mb-3">اختر لون الغلاف:</div>
            <div class="flex flex-wrap gap-2">
                @foreach(\App\Models\Page::coverColors() as $key => $info)
                    <button
                        @click="setCoverColor('{{ $key }}')"
                        class="{{ $info['class'] }} w-8 h-8 rounded-lg border-2 hover:scale-110 transition-transform {{ $page->cover_color === $key ? 'border-notion-text dark:border-white' : 'border-transparent' }}"
                        title="{{ $info['label'] }}"
                    ></button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- =================== رأس الصفحة =================== --}}
    <div class="max-w-[740px] mx-auto px-4 sm:px-12 {{ $hasCover ? 'pt-0' : 'pt-10' }} pb-2 group/header">

        {{-- الأيقونة --}}
        <div class="flex items-end justify-between mb-4 {{ $hasCover ? '-mt-9 relative z-10' : '' }}">
            <div class="relative" x-data="{ pickerOpen: false }">
                <button
                    @click="pickerOpen = !pickerOpen"
                    class="text-6xl hover:scale-105 transition-transform select-none flex items-center justify-center leading-none {{ $hasCover ? 'w-20 h-20 rounded-2xl bg-white dark:bg-[#191919] shadow-md ring-4 ring-white dark:ring-[#191919]' : '' }}"
                    title="تغيير الأيقونة"
                >
                    <span x-text="icon">{{ $page->icon ?? '📄' }}</span>
                </button>

                {{-- Emoji Picker Panel --}}
                <div x-show="pickerOpen" x-cloak @click.outside="pickerOpen = false"
                     class="absolute top-full mt-2 right-0 z-30 bg-white dark:bg-[#252525] border border-notion-border dark:border-[#333333] rounded-xl shadow-2xl p-4 w-72">
                    <div class="text-xs font-medium text-notion-muted dark:text-[#9B9A97] mb-3">اختر أيقونة:</div>
                    <div class="grid grid-cols-8 gap-1 max-h-48 overflow-y-auto">
                        @foreach(['📄','📝','📋','📌','📎','🗂️','📁','📂','🗒️','📓','📔','📒','📕','📗','📘','📙','📚','🔖','🏷️','💡','⭐','🎯','✅','❌','🔥','💎','🚀','🌟','⚡','🎉','🎨','💻','🔧','🛠️','🏠','🌍','🌈','🎵','🎬','📸','🎭','🏆','🥇','💪','🧠','❤️','🌸','🌺','🍀','🦋','🌙','☀️','🌤️','⛅','🌊','🏔️','🌴'] as $emoji)
                            <button
                                @click="icon = '{{ $emoji }}'; updatePage(); pickerOpen = false"
                                class="text-2xl hover:bg-notion-hover dark:hover:bg-[#2C2C2C] rounded p-1 transition-colors"
                            >{{ $emoji }}</button>
                        @endforeach
                    </div>
                    <div class="mt-3 border-t border-notion-border dark:border-[#333333] pt-2">
                        <input type="text" placeholder="أو اكتب emoji هنا..."
                               class="w-full text-sm border border-notion-border dark:border-[#333333] rounded-lg px-2.5 py-1.5 focus:outline-none dark:bg-[#191919] dark:text-[#E3E3E0]"
                               @change="icon = $event.target.value; updatePage(); pickerOpen = false">
                    </div>
                </div>
            </div>

            {{-- شارة الصلاحية وزر المشاركة --}}
            <div class="flex items-center gap-2">
                @if(isset($userRole))
                    <span class="text-[11px] px-2.5 py-1 rounded-lg border border-notion-border dark:border-[#333333] bg-notion-sidebar dark:bg-[#202020] text-notion-muted font-medium">
                        {{ $userRole === 'editor' ? '✍️ محرر' : ($userRole === 'commenter' ? '💬 معلق' : '👁️ مشاهد') }}
                    </span>
                @endif
                <button
                    type="button"
                    @click="openShareModal()"
                    class="text-xs px-2.5 py-1 rounded-lg bg-notion-sidebar dark:bg-[#252525] hover:bg-notion-hover dark:hover:bg-[#2C2C2C] border border-notion-border dark:border-[#333333] text-notion-text dark:text-[#E3E3E0] transition-colors flex items-center gap-1.5 shadow-sm"
                >
                    <svg class="w-3.5 h-3.5 text-notion-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>المشاركة</span>
                </button>
            </div>
        </div>

        {{-- العنوان التفاعلي --}}
        <input
            type="text"
            dir="auto"
            x-model="title"
            @blur="updatePage()"
            @keydown.enter="$event.target.blur()"
            class="w-full text-[34px] sm:text-[40px] leading-tight font-bold text-notion-text dark:text-[#E3E3E0] bg-transparent border-0 focus:ring-0 focus:outline-none p-0 placeholder:text-notion-muted/30"
            placeholder="بدون عنوان"
        />

        {{-- Tags (هادئة بدون إطارات مزعجة) --}}
        <div class="flex items-center flex-wrap gap-1.5 mt-2 min-h-[24px]">
            @foreach($page->tags as $tag)
                @php $tagColors = \App\Models\Tag::colorClasses(); @endphp
                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded {{ $tagColors[$tag->color] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ $tag->name }}
                    <button
                        @click.prevent="removeTag({{ $tag->id }})"
                        class="mr-0.5 hover:opacity-70"
                    >×</button>
                </span>
            @endforeach

            <div x-data="{ tagPicker: false, newTagName: '', newTagColor: 'gray' }" class="relative">
                <button @click="tagPicker = !tagPicker" class="text-xs text-notion-muted dark:text-[#7F7F7F] hover:text-notion-text dark:hover:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2C2C2C] px-1.5 py-0.5 rounded opacity-0 group-hover/header:opacity-100 focus:opacity-100 transition-opacity">
                    + إضافة وسمة
                </button>

                <div x-show="tagPicker" x-cloak @click.outside="tagPicker = false"
                     class="absolute top-7 right-0 z-30 bg-white dark:bg-[#252525] border border-notion-border dark:border-[#333333] rounded-xl shadow-2xl p-4 w-64 space-y-3">
                    <div class="text-xs font-medium text-notion-muted dark:text-[#9B9A97]">إضافة وسمة:</div>

                    @if($workspace->tags->count() > 0)
                    <div class="flex flex-wrap gap-1">
                        @foreach($workspace->tags as $wsTag)
                            @php $tagColors = \App\Models\Tag::colorClasses(); @endphp
                            <button
                                @click="addExistingTag({{ $wsTag->id }}); tagPicker = false"
                                class="text-xs px-2 py-0.5 rounded {{ $tagColors[$wsTag->color] ?? 'bg-gray-100 text-gray-700' }} hover:opacity-80"
                            >{{ $wsTag->name }}</button>
                        @endforeach
                    </div>
                    <div class="border-t border-notion-border dark:border-[#333333]"></div>
                    @endif

                    <input type="text" x-model="newTagName" placeholder="اسم الوسمة الجديدة..."
                           class="w-full text-xs border border-notion-border dark:border-[#333333] rounded-lg px-2.5 py-1.5 focus:outline-none dark:bg-[#191919] dark:text-[#E3E3E0]"
                           @keydown.enter="addNewTag(newTagName, newTagColor); tagPicker = false">
                    <div class="flex gap-1.5">
                        @foreach(['gray','red','orange','yellow','green','blue','purple','pink'] as $c)
                            <button @click="newTagColor = '{{ $c }}'"
                                    class="w-5 h-5 rounded-full {{ match($c) { 'gray'=>'bg-gray-400','red'=>'bg-red-400','orange'=>'bg-orange-400','yellow'=>'bg-yellow-400','green'=>'bg-green-400','blue'=>'bg-blue-400','purple'=>'bg-purple-400','pink'=>'bg-pink-400', default=>'bg-gray-400' } }} hover:scale-110 transition-transform"
                                    :class="newTagColor === '{{ $c }}' ? 'ring-2 ring-offset-1 ring-notion-text dark:ring-white' : ''"
                            ></button>
                        @endforeach
                    </div>
                    <button @click="addNewTag(newTagName, newTagColor); tagPicker = false"
                            class="w-full text-xs py-1.5 bg-notion-text dark:bg-[#3A3A3A] text-white rounded-lg hover:bg-black dark:hover:bg-[#4A4A4A] transition-colors">
                        إضافة
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- =================== المحتوى الرئيسي =================== --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-12 pt-4 pb-28 space-y-4">

        {{-- الصفحات الفرعية --}}
        @if($page->children->count() > 0)
            <div class="space-y-1.5">
                <div class="text-xs font-semibold text-notion-muted dark:text-[#787774] mb-2">📄 صفحات فرعية:</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($page->children as $sub)
                        <a href="{{ route('pages.show', $sub) }}" class="flex items-center justify-between p-2.5 rounded-lg border border-notion-border dark:border-[#3a3a3a] hover:bg-notion-hover dark:hover:bg-[#2F2F2F] transition-colors group">
                            <div class="flex items-center gap-2 truncate">
                                <span class="text-base">{{ $sub->icon ?? '📄' }}</span>
                                <span class="text-sm font-medium text-notion-text dark:text-[#E8E8E5] truncate">{{ $sub->title }}</span>
                            </div>
                            <span class="text-xs text-notion-muted dark:text-[#787774] group-hover:text-notion-text dark:group-hover:text-[#E8E8E5] transition-colors">←</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- =================== حاوية البلوكات (Sortable) =================== --}}
        <div id="blocks-container" class="space-y-1 min-h-[80px]">
            @forelse($page->blocks as $block)
                <div
                    data-block-id="{{ $block->id }}"
                    x-data="{
                        text: {{ json_encode($block->content['text'] ?? '') }},
                        checked: {{ isset($block->content['checked']) && $block->content['checked'] ? 'true' : 'false' }},
                        open: {{ isset($block->content['open']) && $block->content['open'] ? 'true' : 'false' }},
                        color: {{ json_encode($block->content['color'] ?? 'default') }},
                        language: {{ json_encode($block->content['language'] ?? 'plaintext') }},
                        updateBlock() {
                            fetch('{{ route('blocks.update', $block) }}', {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    text: this.text,
                                    checked: this.checked,
                                    open: this.open,
                                    color: this.color,
                                    language: this.language
                                })
                            });
                        }
                    }"
                    class="group relative flex items-start gap-2 py-1.5 px-2 -mx-2 rounded-lg hover:bg-notion-hover/50 dark:hover:bg-[#2F2F2F]/50 transition-colors"
                >
                    {{-- Drag Handle --}}
                    <div class="drag-handle opacity-0 group-hover:opacity-100 transition-opacity cursor-grab active:cursor-grabbing absolute -right-6 top-2 text-notion-muted dark:text-[#787774] hover:text-notion-text dark:hover:text-[#E8E8E5]">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="9" cy="7" r="1.5"/><circle cx="15" cy="7" r="1.5"/>
                            <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                            <circle cx="9" cy="17" r="1.5"/><circle cx="15" cy="17" r="1.5"/>
                        </svg>
                    </div>

                    {{-- زر تعليقات البلوك --}}
                    <div class="opacity-0 group-hover:opacity-100 absolute -left-14 top-2 transition-opacity z-10">
                        <button type="button" @click="openComments({{ $block->id }})" title="التعليقات" class="flex items-center gap-0.5 text-notion-muted hover:text-blue-500 p-0.5 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            @if($block->comments->count() > 0)
                                <span class="text-[9px] font-bold text-blue-400 bg-blue-500/10 px-1 rounded-full">{{ $block->comments->count() }}</span>
                            @endif
                        </button>
                    </div>

                    {{-- زر حذف البلوك --}}
                    <div class="opacity-0 group-hover:opacity-100 absolute -left-6 top-2 transition-opacity z-10">
                        <form action="{{ route('blocks.destroy', $block) }}" method="POST" onsubmit="return confirm('حذف هذا البلوك؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="حذف البلوك" class="text-notion-muted hover:text-red-500 dark:hover:text-red-400 p-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </form>
                    </div>

                    {{-- ===== أنواع البلوكات ===== --}}

                    @if($block->type === 'heading_1' || $block->type === 'heading')
                        <input id="block-{{ $block->id }}" dir="auto" type="text" x-model="text" @blur="updateBlock()" @keydown.enter="$event.target.blur()"
                            class="w-full font-bold text-3xl text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 pt-3 focus:ring-0 focus:outline-none"
                            placeholder="عنوان كبير..."/>

                    @elseif($block->type === 'heading_2')
                        <input id="block-{{ $block->id }}" dir="auto" type="text" x-model="text" @blur="updateBlock()" @keydown.enter="$event.target.blur()"
                            class="w-full font-semibold text-2xl text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 pt-2 focus:ring-0 focus:outline-none"
                            placeholder="عنوان متوسط..."/>

                    @elseif($block->type === 'heading_3')
                        <input id="block-{{ $block->id }}" dir="auto" type="text" x-model="text" @blur="updateBlock()" @keydown.enter="$event.target.blur()"
                            class="w-full font-semibold text-lg text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 pt-1 focus:ring-0 focus:outline-none"
                            placeholder="عنوان فرعي..."/>

                    @elseif($block->type === 'todo')
                        <div class="flex items-center gap-2.5 w-full">
                            <input type="checkbox" x-model="checked" @change="updateBlock()"
                                class="w-4 h-4 rounded text-notion-accent focus:ring-0 cursor-pointer border-notion-border dark:border-[#4A4A4A] dark:bg-transparent"/>
                            <input dir="auto" type="text" x-model="text" @blur="updateBlock()" @keydown.enter="$event.target.blur()"
                                :class="{ 'line-through text-notion-muted dark:text-[#787774]': checked }"
                                class="w-full text-[15px] text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 focus:ring-0 focus:outline-none"
                                placeholder="مهمة..."/>
                        </div>

                    @elseif($block->type === 'code')
                        <div
                            x-data="{
                                highlightCode() {
                                    this.$nextTick(() => {
                                        const el = this.$refs.codeHighlight;
                                        if (!el) return;
                                        const lang = this.language === 'html' ? 'markup' : this.language;
                                        el.className = 'language-' + lang;
                                        el.textContent = (this.text || '') + '\n';
                                        if (window.Prism && lang !== 'plaintext') {
                                            Prism.highlightElement(el);
                                        }
                                    });
                                }
                            }"
                            x-init="highlightCode()"
                            class="group/code relative w-full bg-[#F7F6F3] dark:bg-[#202020] text-[#37352F] dark:text-[#E3E3E0] rounded-lg px-5 py-4 font-mono text-[13px]"
                        >
                            {{-- شريط علوي هادئ يظهر اللغة وزر النسخ --}}
                            <div class="flex items-center justify-between mb-2">
                                <select
                                    x-model="language"
                                    @change="updateBlock(); highlightCode()"
                                    dir="ltr"
                                    class="text-[11px] text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] bg-transparent border-0 p-0 focus:ring-0 focus:outline-none cursor-pointer font-sans"
                                >
                                    <option value="plaintext" class="dark:bg-[#252525]">Plain Text</option>
                                    <option value="php" class="dark:bg-[#252525]">PHP</option>
                                    <option value="javascript" class="dark:bg-[#252525]">JavaScript</option>
                                    <option value="typescript" class="dark:bg-[#252525]">TypeScript</option>
                                    <option value="python" class="dark:bg-[#252525]">Python</option>
                                    <option value="html" class="dark:bg-[#252525]">HTML</option>
                                    <option value="css" class="dark:bg-[#252525]">CSS</option>
                                    <option value="sql" class="dark:bg-[#252525]">SQL</option>
                                    <option value="json" class="dark:bg-[#252525]">JSON</option>
                                    <option value="bash" class="dark:bg-[#252525]">Bash</option>
                                </select>
                                <button type="button" @click="navigator.clipboard.writeText(text).then(() => { $event.target.textContent = '✓ تم النسخ'; setTimeout(() => $event.target.textContent = 'نسخ', 1500) })"
                                    class="opacity-0 group-hover/code:opacity-100 text-[11px] text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-white px-2 py-0.5 rounded bg-white/80 dark:bg-[#2C2C2C] transition-all font-sans">نسخ</button>
                            </div>

                            {{-- محرر كود مع تلوين حي (Syntax Highlighting Overlay) --}}
                            <div class="relative min-h-[64px]" dir="ltr">
                                <pre class="notion-code-pre pointer-events-none whitespace-pre-wrap break-words min-h-[64px] text-[#37352F] dark:text-[#E3E3E0]" aria-hidden="true"><code x-ref="codeHighlight"></code></pre>
                                <textarea dir="ltr" x-model="text" @blur="updateBlock()" rows="3" spellcheck="false"
                                    @input="highlightCode(); $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                    placeholder="// Write code..."
                                    class="absolute inset-0 w-full h-full bg-transparent text-transparent caret-[#37352F] dark:caret-white placeholder:text-notion-muted/50 font-mono text-[13px] border-none p-0 focus:ring-0 focus:outline-none resize-none leading-relaxed whitespace-pre-wrap break-words"></textarea>
                            </div>
                        </div>

                    @elseif($block->type === 'callout')
                        <div
                            :class="{
                                'bg-[#F1F1EF] dark:bg-[#202020]': color === 'default' || color === 'gray',
                                'bg-[#E7F3F8] dark:bg-[#1D282E]': color === 'blue',
                                'bg-[#EDF3EC] dark:bg-[#1E2B22]': color === 'green',
                                'bg-[#FBF3DB] dark:bg-[#2E281B]': color === 'yellow',
                                'bg-[#FAEBDD] dark:bg-[#2F231A]': color === 'orange',
                                'bg-[#FDEBEC] dark:bg-[#2F1E1E]': color === 'red',
                                'bg-[#F4F0F7] dark:bg-[#27212E]': color === 'purple'
                            }"
                            class="group/callout relative w-full flex items-start gap-3 rounded-lg p-4 transition-colors"
                        >
                            <span class="text-lg select-none shrink-0">💡</span>
                            <textarea dir="auto" x-model="text" @blur="updateBlock()" rows="1"
                                @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                class="w-full text-[15px] leading-relaxed text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 focus:ring-0 focus:outline-none resize-none"
                                placeholder="تنبيه أو ملاحظة..."></textarea>

                            {{-- منتقي لون الملاحظة يظهر عند التمرير --}}
                            <div class="opacity-0 group-hover/callout:opacity-100 absolute top-2 left-2 flex items-center gap-1 bg-white/90 dark:bg-[#252525]/95 border border-notion-border dark:border-[#333333] rounded-full px-1.5 py-1 shadow-sm transition-opacity">
                                <button type="button" @click="color = 'default'; updateBlock()" title="افتراضي" class="w-3.5 h-3.5 rounded-full bg-zinc-400 hover:scale-110 transition-transform" :class="color === 'default' ? 'ring-1 ring-white' : ''"></button>
                                <button type="button" @click="color = 'blue'; updateBlock()" title="أزرق" class="w-3.5 h-3.5 rounded-full bg-sky-500 hover:scale-110 transition-transform" :class="color === 'blue' ? 'ring-1 ring-white' : ''"></button>
                                <button type="button" @click="color = 'green'; updateBlock()" title="أخضر" class="w-3.5 h-3.5 rounded-full bg-emerald-500 hover:scale-110 transition-transform" :class="color === 'green' ? 'ring-1 ring-white' : ''"></button>
                                <button type="button" @click="color = 'yellow'; updateBlock()" title="أصفر" class="w-3.5 h-3.5 rounded-full bg-amber-400 hover:scale-110 transition-transform" :class="color === 'yellow' ? 'ring-1 ring-white' : ''"></button>
                                <button type="button" @click="color = 'orange'; updateBlock()" title="برتقالي" class="w-3.5 h-3.5 rounded-full bg-orange-500 hover:scale-110 transition-transform" :class="color === 'orange' ? 'ring-1 ring-white' : ''"></button>
                                <button type="button" @click="color = 'red'; updateBlock()" title="أحمر" class="w-3.5 h-3.5 rounded-full bg-rose-500 hover:scale-110 transition-transform" :class="color === 'red' ? 'ring-1 ring-white' : ''"></button>
                                <button type="button" @click="color = 'purple'; updateBlock()" title="بنفسجي" class="w-3.5 h-3.5 rounded-full bg-purple-500 hover:scale-110 transition-transform" :class="color === 'purple' ? 'ring-1 ring-white' : ''"></button>
                            </div>
                        </div>

                    @elseif($block->type === 'quote')
                        <div class="w-full border-r-3 border-notion-text dark:border-[#E3E3E0] pr-4 py-0.5">
                            <textarea dir="auto" x-model="text" @blur="updateBlock()" rows="1"
                                @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                class="w-full text-[15px] leading-relaxed text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 focus:ring-0 focus:outline-none resize-none"
                                placeholder="اقتباس..."></textarea>
                        </div>

                    @elseif($block->type === 'bulleted_list')
                        <div class="flex items-start gap-2.5 w-full">
                            <span class="text-notion-text dark:text-[#E3E3E0] text-base leading-snug select-none">•</span>
                            <input dir="auto" type="text" x-model="text" @blur="updateBlock()" @keydown.enter="$event.target.blur()"
                                class="w-full text-[15px] text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 focus:ring-0 focus:outline-none"
                                placeholder="عنصر في قائمة..."/>
                        </div>

                    @elseif($block->type === 'numbered_list')
                        <div class="flex items-start gap-2 w-full">
                            <span class="text-notion-muted dark:text-[#9B9A97] text-sm font-medium select-none mt-0.5 w-5 shrink-0">
                                {{ $page->blocks->where('type', 'numbered_list')->search($block) + 1 }}.
                            </span>
                            <input dir="auto" type="text" x-model="text" @blur="updateBlock()" @keydown.enter="$event.target.blur()"
                                class="w-full text-[15px] text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 focus:ring-0 focus:outline-none"
                                placeholder="عنصر مرقم..."/>
                        </div>

                    @elseif($block->type === 'toggle')
                        <div class="w-full">
                            <div class="flex items-center gap-2 cursor-pointer" @click="open = !open; updateBlock()">
                                <svg class="w-4 h-4 text-notion-muted transition-transform shrink-0" :class="{ 'rotate-90': open }" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5l8 7-8 7V5z"/>
                                </svg>
                                <input dir="auto" type="text" x-model="text" @blur="updateBlock()" @keydown.enter="$event.target.blur()" @click.stop
                                    class="flex-1 font-medium text-[15px] text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 focus:ring-0 focus:outline-none"
                                    placeholder="قائمة قابلة للطي..."/>
                            </div>
                            <div x-show="open" x-cloak class="mt-2 pr-6 text-sm text-notion-muted dark:text-[#9B9A97] border-r border-notion-border dark:border-[#2E2E2E]">
                                المحتوى المخفي...
                            </div>
                        </div>

                    @elseif($block->type === 'divider')
                        <div class="w-full py-2">
                            <hr class="border-t border-notion-border dark:border-[#2E2E2E]"/>
                        </div>

                    @else {{-- paragraph --}}
                        <textarea dir="auto" x-model="text" @blur="updateBlock()" rows="1"
                            @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                            class="w-full text-[15px] leading-relaxed text-notion-text dark:text-[#E3E3E0] bg-transparent border-none p-0 focus:ring-0 focus:outline-none resize-none"
                            placeholder="اكتب شيئاً..."></textarea>
                    @endif

                </div>
            @empty
            @endforelse
        </div>

        {{-- =================== قواعد البيانات المضمنة (Multi-View Databases) =================== --}}
        <div class="space-y-6">
            <template x-for="db in databases" :key="db.id">
                <div class="rounded-xl border border-notion-border dark:border-[#2E2E2E] bg-notion-sidebar/50 dark:bg-[#202020] overflow-hidden" x-data="{ showNewViewModal: false, newViewName: '', newViewType: 'table' }">
                    {{-- رأس قاعدة البيانات + شريط العروض المحفوظة --}}
                    <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 border-b border-notion-border dark:border-[#2E2E2E]">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-base">🗂️</span>
                            <input
                                type="text"
                                x-model="db.name"
                                @blur="updateDatabaseName(db)"
                                @keydown.enter="$event.target.blur()"
                                class="text-sm font-semibold bg-transparent border-0 focus:ring-0 focus:outline-none text-notion-text dark:text-[#E3E3E0] p-0"
                            />
                        </div>

                        <div class="flex items-center gap-1.5 flex-wrap">
                            {{-- View Tabs (Multi-View System: Table, Board, List, Gallery) --}}
                            <div class="inline-flex items-center rounded-lg bg-white dark:bg-[#191919] border border-notion-border dark:border-[#2E2E2E] p-0.5 text-[11px]">
                                <template x-for="v in (db.views || [])" :key="v.id || v.name">
                                    <button
                                        type="button"
                                        @click="db.activeView = v.type"
                                        :class="db.activeView === v.type ? 'bg-notion-hover dark:bg-[#2C2C2C] text-notion-text dark:text-[#E3E3E0] font-semibold shadow-xs' : 'text-notion-muted dark:text-[#9B9A97]'"
                                        class="px-2.5 py-1 rounded-md transition-colors flex items-center gap-1"
                                    >
                                        <span x-text="v.type === 'table' ? '📊' : (v.type === 'board' ? '📋' : (v.type === 'list' ? '📝' : '🃏'))"></span>
                                        <span x-text="v.name"></span>
                                    </button>
                                </template>
                            </div>

                            {{-- إضافة عرض مخصص جديد --}}
                            <div class="relative">
                                <button
                                    type="button"
                                    @click="showNewViewModal = !showNewViewModal"
                                    class="text-[11px] px-2 py-1 rounded-lg border border-notion-border dark:border-[#2E2E2E] bg-white dark:bg-[#191919] text-notion-muted hover:text-notion-text dark:hover:text-[#E3E3E0] transition-colors"
                                    title="إضافة عرض مخصص جديد"
                                >
                                    + عرض
                                </button>
                                <div
                                    x-show="showNewViewModal"
                                    x-cloak
                                    @click.outside="showNewViewModal = false"
                                    class="absolute left-0 mt-1 w-56 bg-white dark:bg-[#252525] border border-notion-border dark:border-[#333333] rounded-xl shadow-2xl p-3 z-30 space-y-2.5 text-xs"
                                >
                                    <div class="font-semibold text-notion-text dark:text-[#E3E3E0]">إضافة عرض جديد</div>
                                    <input
                                        type="text"
                                        x-model="newViewName"
                                        placeholder="اسم العرض (مثل: مهامي)..."
                                        class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-notion-border dark:border-[#383838] bg-transparent text-notion-text dark:text-[#E3E3E0] focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    />
                                    <select
                                        x-model="newViewType"
                                        class="w-full text-xs px-2 py-1.5 rounded-lg border border-notion-border dark:border-[#383838] bg-white dark:bg-[#1E1E1E] text-notion-text dark:text-[#E3E3E0] focus:outline-none"
                                    >
                                        <option value="table">📊 جدول (Table)</option>
                                        <option value="board">📋 لوحة كانبان (Board)</option>
                                        <option value="list">📝 قائمة بسيطة (List)</option>
                                        <option value="gallery">🃏 معرض بطاقات (Gallery)</option>
                                    </select>
                                    <div class="flex justify-end gap-1.5 pt-1">
                                        <button
                                            type="button"
                                            @click="showNewViewModal = false"
                                            class="px-2 py-1 rounded text-notion-muted hover:text-notion-text"
                                        >إلغاء</button>
                                        <button
                                            type="button"
                                            @click="if(newViewName.trim()) { addView(db, newViewName, newViewType); newViewName = ''; showNewViewModal = false; }"
                                            class="px-3 py-1 rounded bg-blue-600 hover:bg-blue-500 text-white font-medium transition-colors"
                                        >إضافة</button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" @click="addDatabaseRow(db)" class="text-[11px] px-2.5 py-1 rounded-md bg-[#37352F] dark:bg-[#2EAADC] text-white hover:bg-black dark:hover:bg-[#2596C4] transition-colors">
                                + سجل
                            </button>
                            <button type="button" @click="deleteDatabase(db)" class="text-xs text-notion-muted hover:text-red-500 p-1" title="حذف قاعدة البيانات">
                                ✕
                            </button>
                        </div>
                    </div>

                    {{-- شريط الفلترة والبحث السريع (Filter & Search Bar) --}}
                    <div class="px-4 py-2 border-b border-notion-border/60 dark:border-[#2A2A2A] bg-white/20 dark:bg-[#181818]/40 flex items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2 flex-1 max-w-xs">
                            <svg class="w-3.5 h-3.5 text-notion-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input
                                type="text"
                                x-model="db.searchQuery"
                                placeholder="تصفية السجلات..."
                                class="w-full bg-transparent border-0 p-0 text-xs text-notion-text dark:text-[#E3E3E0] placeholder:text-notion-muted/50 focus:outline-none focus:ring-0"
                            />
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-[11px] text-notion-muted">الحالة:</span>
                            <select
                                x-model="db.filterStatus"
                                class="bg-transparent text-xs text-notion-text dark:text-[#E3E3E0] focus:outline-none cursor-pointer border border-notion-border dark:border-[#333333] rounded px-1.5 py-0.5"
                            >
                                <option value="" class="dark:bg-[#252525]">الكل</option>
                                <template x-for="opt in getBoardColumns(db)" :key="opt">
                                    <option :value="opt" x-text="opt" class="dark:bg-[#252525]"></option>
                                </template>
                            </select>
                            <button
                                type="button"
                                x-show="db.searchQuery || db.filterStatus"
                                @click="db.searchQuery = ''; db.filterStatus = ''"
                                class="text-[10px] text-blue-400 hover:underline px-1"
                            >مسح الفلتر</button>
                        </div>
                    </div>

                    {{-- عرض 1: Table View --}}
                    <div x-show="db.activeView === 'table'" class="overflow-x-auto">
                        <table class="w-full text-right text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-notion-border dark:border-[#2E2E2E] text-notion-muted dark:text-[#9B9A97] bg-white/30 dark:bg-[#191919]/60">
                                    <template x-for="prop in db.properties" :key="prop.name">
                                        <th class="px-3 py-2.5 font-semibold border-l border-notion-border dark:border-[#2E2E2E] last:border-l-0" x-text="prop.name"></th>
                                    </template>
                                    <th class="w-10 px-2 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="row in getFilteredRows(db)" :key="row.id">
                                    <tr class="border-b border-notion-border dark:border-[#2E2E2E] last:border-b-0 hover:bg-notion-hover/30 dark:hover:bg-[#1E1E1E]/80 group/row">
                                        <template x-for="prop in db.properties" :key="prop.name">
                                            <td class="px-3 py-2 border-l border-notion-border dark:border-[#2E2E2E] last:border-l-0">
                                                <template x-if="prop.type === 'select'">
                                                    <div x-data="{ open: false }" class="relative">
                                                        <button
                                                            type="button"
                                                            @click="open = !open"
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium transition-colors"
                                                            :class="row.values[prop.name]
                                                                ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300'
                                                                : 'bg-notion-hover dark:bg-[#2C2C2C] text-notion-muted'"
                                                        >
                                                            <span x-text="row.values[prop.name] || '—'"></span>
                                                            <svg class="w-2.5 h-2.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                                        </button>
                                                        <div x-show="open" @click.outside="open = false" x-cloak
                                                             class="absolute z-20 mt-1 bg-white dark:bg-[#252525] border border-notion-border dark:border-[#333333] rounded-xl shadow-xl p-1.5 min-w-[120px]"
                                                             :class="$el.getBoundingClientRect().left < 100 ? 'left-0' : 'right-0'">
                                                            <button type="button" @click="row.values[prop.name] = ''; updateDatabaseRow(row); open = false"
                                                                class="w-full text-right px-2.5 py-1.5 rounded-lg text-[11px] text-notion-muted hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors">— بلا حالة</button>
                                                            <template x-for="opt in (prop.options || [])" :key="opt">
                                                                <button type="button"
                                                                    @click="row.values[prop.name] = opt; updateDatabaseRow(row); open = false"
                                                                    class="w-full text-right px-2.5 py-1.5 rounded-lg text-[11px] text-notion-text dark:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors flex items-center gap-2"
                                                                    :class="row.values[prop.name] === opt ? 'font-semibold' : ''"
                                                                >
                                                                    <span class="w-2 h-2 rounded-full bg-blue-400 dark:bg-blue-500 shrink-0" x-show="row.values[prop.name] === opt"></span>
                                                                    <span x-text="opt"></span>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template x-if="prop.type === 'date'">
                                                    <input type="date"
                                                        :value="row.values[prop.name] || ''"
                                                        @change="row.values[prop.name] = $event.target.value; updateDatabaseRow(row)"
                                                        class="bg-transparent text-xs text-notion-text dark:text-[#E3E3E0] focus:outline-none"/>
                                                </template>
                                                <template x-if="prop.type !== 'select' && prop.type !== 'date'">
                                                    <input type="text"
                                                        :value="row.values[prop.name] || ''"
                                                        @blur="row.values[prop.name] = $event.target.value; updateDatabaseRow(row)"
                                                        @keydown.enter="$event.target.blur()"
                                                        placeholder="اكتب..."
                                                        class="w-full bg-transparent text-xs text-notion-text dark:text-[#E3E3E0] placeholder:text-notion-muted/50 focus:outline-none"/>
                                                </template>
                                            </td>
                                        </template>
                                        <td class="px-2 py-2 text-center">
                                            <button type="button" @click="deleteDatabaseRow(db, row)" class="opacity-0 group-hover/row:opacity-100 text-notion-muted hover:text-red-500 transition-opacity">✕</button>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="getFilteredRows(db).length === 0">
                                    <tr>
                                        <td :colspan="db.properties.length + 1" class="px-4 py-8 text-center text-xs text-notion-muted dark:text-[#9B9A97]">
                                            لا توجد سجلات مطابقة للفلتر.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- عرض 2: Kanban Board View --}}
                    <div x-show="db.activeView === 'board'" class="p-4 sm:p-5 overflow-x-auto">
                        <div class="flex gap-4 min-w-0" style="align-items: flex-start;">
                            <template x-for="col in getBoardColumns(db)" :key="col">
                                <div
                                    @dragover.prevent="if (draggingDbId === db.id) dragOverCol = col"
                                    @dragleave="if (dragOverCol === col && !$el.contains($event.relatedTarget)) dragOverCol = null"
                                    @drop.prevent="moveBoardRow(db, col)"
                                    :class="dragOverCol === col && draggingDbId === db.id ? 'ring-2 ring-blue-400/60 bg-blue-50/40 dark:bg-blue-900/10' : 'bg-notion-hover/30 dark:bg-[#1A1A1A]'"
                                    class="rounded-2xl border border-notion-border dark:border-[#2A2A2A] p-3 space-y-2.5 transition-all flex-1 min-w-[220px] max-w-xs"
                                    style="min-height:200px;"
                                >
                                    {{-- عنوان العمود --}}
                                    <div class="flex items-center justify-between px-1 pb-1">
                                        <span class="text-[11px] font-semibold uppercase tracking-wide text-notion-muted dark:text-[#9B9A97]" x-text="col"></span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-white dark:bg-[#252525] border border-notion-border dark:border-[#333333] text-notion-muted" x-text="getBoardRows(db, col).length"></span>
                                    </div>

                                    {{-- الكروت --}}
                                    <div class="space-y-2">
                                        <template x-for="row in getBoardRows(db, col)" :key="row.id">
                                            <div
                                                draggable="true"
                                                @dragstart="startBoardDrag($event, db, row)"
                                                @dragend="draggingRowId = null; draggingDbId = null; dragOverCol = null"
                                                :class="draggingRowId === row.id ? 'opacity-40 scale-95' : 'hover:shadow-md hover:-translate-y-0.5'"
                                                class="group/card bg-white dark:bg-[#252525] rounded-xl border border-notion-border dark:border-[#333333] p-3.5 cursor-grab active:cursor-grabbing transition-all shadow-sm"
                                            >
                                                {{-- العنوان --}}
                                                <div class="flex items-start gap-1.5 mb-2.5">
                                                    <textarea
                                                        rows="1"
                                                        :value="row.values[db.properties[0]?.name] || ''"
                                                        x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'; })"
                                                        @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                                        @blur="row.values[db.properties[0]?.name] = $event.target.value; updateDatabaseRow(row)"
                                                        @mousedown.stop
                                                        placeholder="بدون عنوان..."
                                                        class="flex-1 min-w-0 bg-transparent text-[13px] font-semibold leading-snug text-notion-text dark:text-[#E8E8E5] placeholder:text-notion-muted/50 focus:outline-none resize-none overflow-hidden"
                                                    ></textarea>
                                                    <button type="button" @click="deleteDatabaseRow(db, row)" class="opacity-0 group-hover/card:opacity-100 text-[10px] text-notion-muted hover:text-red-500 shrink-0 p-0.5 transition-opacity">✕</button>
                                                </div>

                                                {{-- شارة الحالة والتاريخ --}}
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <template x-if="row.values['التاريخ']">
                                                        <span class="inline-flex items-center gap-1 text-[10px] text-notion-muted dark:text-[#787774]">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                            <span x-text="row.values['التاريخ']"></span>
                                                        </span>
                                                    </template>
                                                    <template x-if="!row.values['التاريخ']">
                                                        <input
                                                            type="date"
                                                            :value="row.values['التاريخ'] || ''"
                                                            @mousedown.stop
                                                            @change="row.values['التاريخ'] = $event.target.value; updateDatabaseRow(row)"
                                                            class="bg-transparent text-[10px] text-notion-muted dark:text-[#787774] focus:outline-none opacity-0 group-hover/card:opacity-70 hover:!opacity-100 transition-opacity w-28"
                                                            title="أضف تاريخ"
                                                        />
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- زر إضافة بطاقة --}}
                                    <button
                                        type="button"
                                        @click="addDatabaseRow(db)"
                                        class="w-full text-right text-[11px] text-notion-muted dark:text-[#787774] hover:text-notion-text dark:hover:text-[#E3E3E0] hover:bg-white/60 dark:hover:bg-[#2A2A2A] px-3 py-1.5 rounded-lg transition-colors"
                                    >+ إضافة</button>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- عرض 3: List View (عرض القائمة البسيطة الجديد) --}}
                    <div x-show="db.activeView === 'list'" class="divide-y divide-notion-border/60 dark:divide-[#2E2E2E] bg-white/20 dark:bg-transparent">
                        <template x-for="row in getFilteredRows(db)" :key="row.id">
                            <div class="flex items-center justify-between gap-3 px-4 py-2.5 hover:bg-notion-hover/30 dark:hover:bg-[#252525]/40 transition-colors group/lrow">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <span class="text-xs text-notion-muted">📄</span>
                                    <input
                                        type="text"
                                        :value="row.values[db.properties[0]?.name] || ''"
                                        @blur="row.values[db.properties[0]?.name] = $event.target.value; updateDatabaseRow(row)"
                                        @keydown.enter="$event.target.blur()"
                                        placeholder="عنصر في القائمة..."
                                        class="flex-1 min-w-0 bg-transparent text-xs font-medium text-notion-text dark:text-[#E3E3E0] placeholder:text-notion-muted/50 focus:outline-none"
                                    />
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <template x-if="row.values['الحالة']">
                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-medium" x-text="row.values['الحالة']"></span>
                                    </template>
                                    <template x-if="row.values['التاريخ']">
                                        <span class="text-[11px] text-notion-muted" x-text="row.values['التاريخ']"></span>
                                    </template>
                                    <button type="button" @click="deleteDatabaseRow(db, row)" class="opacity-0 group-hover/lrow:opacity-100 text-xs text-notion-muted hover:text-red-500 transition-opacity p-1">✕</button>
                                </div>
                            </div>
                        </template>
                        <template x-if="getFilteredRows(db).length === 0">
                            <div class="px-4 py-6 text-center text-xs text-notion-muted">
                                لا توجد عناصر مطابقة.
                            </div>
                        </template>
                        <div class="px-4 py-2">
                            <button
                                type="button"
                                @click="addDatabaseRow(db)"
                                class="text-xs text-notion-muted hover:text-notion-text dark:hover:text-[#E3E3E0] py-1 transition-colors flex items-center gap-1.5"
                            >
                                <span>+</span>
                                <span>عنصر جديد</span>
                            </button>
                        </div>
                    </div>

                    {{-- عرض 4: Gallery View (2-Column Card Grid) --}}
                    <div x-show="db.activeView === 'gallery'" class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="row in getFilteredRows(db)" :key="row.id">
                            <div class="p-3.5 rounded-xl bg-white dark:bg-[#191919] border border-notion-border dark:border-[#2E2E2E] hover:border-notion-muted/50 transition-colors space-y-2.5">
                                <div class="flex items-center justify-between gap-2">
                                    <input
                                        type="text"
                                        :value="row.values[db.properties[0]?.name] || ''"
                                        @blur="row.values[db.properties[0]?.name] = $event.target.value; updateDatabaseRow(row)"
                                        @keydown.enter="$event.target.blur()"
                                        placeholder="بدون عنوان"
                                        class="flex-1 min-w-0 bg-transparent text-sm font-semibold text-notion-text dark:text-[#E3E3E0] placeholder:text-notion-muted/60 focus:outline-none focus:ring-1 focus:ring-notion-accent rounded px-1 py-0.5"
                                    />
                                    <button type="button" @click="deleteDatabaseRow(db, row)" class="text-xs text-notion-muted hover:text-red-500 shrink-0">✕</button>
                                </div>
                                <div class="space-y-1.5 text-xs">
                                    <template x-for="prop in db.properties.slice(1)" :key="prop.name">
                                        <div class="flex items-center justify-between gap-2 px-2 py-1 rounded-lg bg-notion-sidebar dark:bg-[#252525] border border-notion-border dark:border-[#333333]">
                                            <span class="text-[11px] text-notion-muted dark:text-[#9B9A97] shrink-0" x-text="prop.name"></span>
                                            <template x-if="prop.type === 'select'">
                                                <select
                                                    :value="row.values[prop.name] || ''"
                                                    @change="row.values[prop.name] = $event.target.value; updateDatabaseRow(row)"
                                                    class="bg-transparent text-xs text-notion-text dark:text-[#E3E3E0] focus:outline-none cursor-pointer text-left"
                                                >
                                                    <option value="" class="dark:bg-[#252525]">—</option>
                                                    <template x-for="opt in (prop.options || [])" :key="opt">
                                                        <option :value="opt" x-text="opt" class="dark:bg-[#252525]"></option>
                                                    </template>
                                                </select>
                                            </template>
                                            <template x-if="prop.type === 'date'">
                                                <input
                                                    type="date"
                                                    :value="row.values[prop.name] || ''"
                                                    @change="row.values[prop.name] = $event.target.value; updateDatabaseRow(row)"
                                                    class="bg-transparent text-xs text-notion-text dark:text-[#E3E3E0] focus:outline-none"
                                                />
                                            </template>
                                            <template x-if="prop.type !== 'select' && prop.type !== 'date'">
                                                <input
                                                    type="text"
                                                    :value="row.values[prop.name] || ''"
                                                    @blur="row.values[prop.name] = $event.target.value; updateDatabaseRow(row)"
                                                    @keydown.enter="$event.target.blur()"
                                                    placeholder="اكتب..."
                                                    class="flex-1 min-w-0 bg-transparent text-xs text-notion-text dark:text-[#E3E3E0] placeholder:text-notion-muted/50 focus:outline-none text-left"
                                                />
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <template x-if="getFilteredRows(db).length === 0">
                            <div class="sm:col-span-2 px-4 py-6 text-center text-xs text-notion-muted dark:text-[#9B9A97]">
                                لا توجد سجلات مطابقة للفلتر.
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- =================== إضافة بلوك جديد (Slash / Block Palette) =================== --}}
        <div
            x-data="{
                slashOpen: false,
                blockType: 'paragraph',
                blockLabel: '¶ نص عادي',
                blockText: '',
                search: '',
                selectBlock(type, label) {
                    this.blockType = type;
                    this.blockLabel = label;
                    this.slashOpen = false;
                    this.search = '';
                    if (this.blockText.trim() === '/') {
                        this.blockText = '';
                    }
                    this.$nextTick(() => this.$refs.addBlockForm.submit());
                }
            }"
            class="pt-1 space-y-2 relative"
        >
            <form x-ref="addBlockForm" id="add-block-form" action="{{ route('blocks.store', $page) }}" method="POST"
                  class="group/addline flex items-center gap-2 py-1 -mx-2 px-2 rounded hover:bg-notion-hover/30 dark:hover:bg-[#232323]/60 transition-colors">
                @csrf
                <input type="hidden" name="type" :value="blockType">

                <button
                    type="button"
                    @click="slashOpen = !slashOpen; $nextTick(() => slashOpen && $refs.slashSearch?.focus())"
                    class="w-6 h-6 rounded flex items-center justify-center text-xs text-notion-muted dark:text-[#7F7F7F] hover:text-notion-text dark:hover:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors shrink-0"
                    :title="blockLabel"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"/></svg>
                </button>

                <input
                    x-ref="blockInput"
                    x-model="blockText"
                    @input="if (blockText === '/') { slashOpen = true; $nextTick(() => $refs.slashSearch?.focus()); }"
                    type="text"
                    name="text"
                    placeholder="اكتب شيئاً، أو اكتب '/' للأوامر..."
                    class="flex-1 text-base bg-transparent border-0 p-0 focus:ring-0 focus:outline-none text-notion-text dark:text-[#E3E3E0] placeholder:text-notion-muted/40 dark:placeholder:text-[#555555]"
                />
            </form>

            {{-- قائمة أوامر السلاش (/) --}}
            <div
                x-show="slashOpen"
                x-cloak
                @click.outside="slashOpen = false; search = ''"
                @keydown.escape.window="slashOpen = false; search = ''"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="mt-2 mb-6 w-full bg-white dark:bg-[#1E1E1E] border border-notion-border dark:border-[#303030] rounded-2xl shadow-2xl overflow-hidden"
            >
                {{-- Header with search --}}
                <div class="flex items-center gap-2 px-4 py-3 border-b border-notion-border dark:border-[#2A2A2A] bg-notion-sidebar/40 dark:bg-[#252525]/60">
                    <svg class="w-3.5 h-3.5 text-notion-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input
                        x-ref="slashSearch"
                        x-model="search"
                        type="text"
                        placeholder="ابحث عن نوع البلوك..."
                        class="flex-1 text-xs bg-transparent border-0 p-0 focus:ring-0 focus:outline-none text-notion-text dark:text-[#E3E3E0] placeholder:text-notion-muted/50"
                    />
                    <button type="button" @click="slashOpen = false; search = ''" class="text-[11px] px-1.5 py-0.5 rounded bg-notion-hover dark:bg-[#2E2E2E] text-notion-muted hover:text-notion-text dark:hover:text-[#E3E3E0]">Esc</button>
                </div>

                {{-- Body --}}
                <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="px-1.5 pb-1.5 text-[11px] font-semibold text-notion-muted dark:text-[#9B9A97]">نصوص وعناوين</div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <button type="button" @click="selectBlock('paragraph', '¶ نص عادي')" x-show="!search || 'نص عادي فقرة'.includes(search)" class="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0]">
                                <span class="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center font-bold text-xs shrink-0">¶</span>
                                <div class="min-w-0"><div class="text-xs font-medium truncate">نص عادي</div><div class="text-[10px] text-notion-muted truncate">فقرة بسيطة</div></div>
                            </button>
                            <button type="button" @click="selectBlock('heading_1', 'H1 عنوان كبير')" x-show="!search || 'عنوان رئيسي كبير h1'.includes(search)" class="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0]">
                                <span class="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center font-bold text-xs shrink-0">H1</span>
                                <div class="min-w-0"><div class="text-xs font-medium truncate">عنوان رئيسي</div><div class="text-[10px] text-notion-muted truncate">قسم كبير</div></div>
                            </button>
                            <button type="button" @click="selectBlock('heading_2', 'H2 عنوان متوسط')" x-show="!search || 'عنوان متوسط h2'.includes(search)" class="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0]">
                                <span class="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center font-bold text-xs shrink-0">H2</span>
                                <div class="min-w-0"><div class="text-xs font-medium truncate">عنوان متوسط</div><div class="text-[10px] text-notion-muted truncate">فرعي متوسط</div></div>
                            </button>
                            <button type="button" @click="selectBlock('heading_3', 'H3 عنوان صغير')" x-show="!search || 'عنوان صغير h3'.includes(search)" class="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0]">
                                <span class="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center font-bold text-xs shrink-0">H3</span>
                                <div class="min-w-0"><div class="text-xs font-medium truncate">عنوان صغير</div><div class="text-[10px] text-notion-muted truncate">فرعي صغير</div></div>
                            </button>
                        </div>
                    </div>

                    <div>
                        <div class="px-1.5 pb-1.5 text-[11px] font-semibold text-notion-muted dark:text-[#9B9A97]">قوائم وتنظيم</div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <button type="button" @click="selectBlock('todo', '☑ قائمة مهام')" x-show="!search || 'قائمة مهام todo'.includes(search)" class="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0]">
                                <span class="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center text-sm shrink-0">☑</span>
                                <div class="min-w-0"><div class="text-xs font-medium truncate">قائمة مهام</div><div class="text-[10px] text-notion-muted truncate">مربع اختيار</div></div>
                            </button>
                            <button type="button" @click="selectBlock('bulleted_list', '• قائمة نقطية')" x-show="!search || 'قائمة نقطية bullet'.includes(search)" class="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0]">
                                <span class="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center text-base shrink-0">•</span>
                                <div class="min-w-0"><div class="text-xs font-medium truncate">قائمة نقطية</div><div class="text-[10px] text-notion-muted truncate">نقاط مرتبة</div></div>
                            </button>
                            <button type="button" @click="selectBlock('numbered_list', '1. قائمة مرقمة')" x-show="!search || 'قائمة مرقمة رقمية'.includes(search)" class="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0]">
                                <span class="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center font-mono text-xs shrink-0">1.</span>
                                <div class="min-w-0"><div class="text-xs font-medium truncate">قائمة مرقمة</div><div class="text-[10px] text-notion-muted truncate">تعداد رقمي</div></div>
                            </button>
                            <button type="button" @click="selectBlock('toggle', '▶ قائمة مطوية')" x-show="!search || 'مطوية toggle'.includes(search)" class="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0]">
                                <span class="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center text-xs shrink-0">▶</span>
                                <div class="min-w-0"><div class="text-xs font-medium truncate">Toggle قابل للطي</div><div class="text-[10px] text-notion-muted truncate">إظهار وإخفاء</div></div>
                            </button>
                        </div>
                    </div>

                    <div class="border-t border-notion-border dark:border-[#2A2A2A] pt-3">
                        <div class="px-1.5 pb-1.5 text-[11px] font-semibold text-notion-muted dark:text-[#9B9A97]">وسائط وكود</div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <button type="button" @click="selectBlock('callout', '💡 تنبيه')" x-show="!search || 'تنبيه callout'.includes(search)" class="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0]">
                                <span class="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                </span>
                                <div class="min-w-0"><div class="text-xs font-medium truncate">تنبيه (Callout)</div><div class="text-[10px] text-notion-muted truncate">ملاحظة مهمة</div></div>
                            </button>
                            <button type="button" @click="selectBlock('quote', '❝ اقتباس')" x-show="!search || 'اقتباس quote'.includes(search)" class="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0]">
                                <span class="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center text-sm shrink-0">❝</span>
                                <div class="min-w-0"><div class="text-xs font-medium truncate">اقتباس</div><div class="text-[10px] text-notion-muted truncate">نص مقتبس</div></div>
                            </button>
                            <button type="button" @click="selectBlock('code', '💻 كود برمجي')" x-show="!search || 'كود code برمجي'.includes(search)" class="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0]">
                                <span class="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                </span>
                                <div class="min-w-0"><div class="text-xs font-medium truncate">كود برمجي</div><div class="text-[10px] text-notion-muted truncate">صندوق كود</div></div>
                            </button>
                            <button type="button" @click="selectBlock('divider', '― فاصل')" x-show="!search || 'فاصل divider'.includes(search)" class="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0]">
                                <span class="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center text-sm shrink-0">―</span>
                                <div class="min-w-0"><div class="text-xs font-medium truncate">خط فاصل</div><div class="text-[10px] text-notion-muted truncate">فصل الأقسام</div></div>
                            </button>
                        </div>
                    </div>

                    <div class="border-t border-notion-border dark:border-[#2A2A2A] pt-3">
                        <div class="px-1.5 pb-1.5 text-[11px] font-semibold text-notion-muted dark:text-[#9B9A97]">صفحات وبيانات</div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <button
                                type="button"
                                @click="slashOpen = false; search = ''; if (blockText.trim() === '/') blockText = ''; createDatabase()"
                                x-show="!search || 'قاعدة بيانات'.includes(search)"
                                class="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0]"
                            >
                                <span class="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                                </span>
                                <div class="min-w-0"><div class="text-xs font-medium truncate">قاعدة بيانات</div><div class="text-[10px] text-notion-muted truncate">جدول أو لوحة مدمجة</div></div>
                            </button>
                            <form action="{{ route('pages.store', $workspace) }}" method="POST" class="contents">
                                @csrf
                                <input type="hidden" name="parent_id" value="{{ $page->id }}">
                                <button type="submit" x-show="!search || 'صفحة فرعية'.includes(search)" class="w-full text-right flex items-center gap-2.5 p-2 rounded-xl hover:bg-notion-hover dark:hover:bg-[#2D2D2D] transition-colors text-notion-text dark:text-[#E3E3E0]">
                                    <span class="w-8 h-8 rounded-lg bg-notion-sidebar dark:bg-[#282828] border border-notion-border dark:border-[#3A3A3A] flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </span>
                                    <div class="min-w-0"><div class="text-xs font-medium truncate">صفحة فرعية</div><div class="text-[10px] text-notion-muted truncate">إنشاء صفحة بالداخل</div></div>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
        {{-- =================== Backlinks =================== --}}
        @if($backlinks->count() > 0)
            <div class="pt-4 border-t border-notion-border dark:border-[#3a3a3a]">
                <div class="text-xs font-semibold text-notion-muted dark:text-[#787774] mb-3">🔗 مُشار إليها من:</div>
                <div class="space-y-1">
                    @foreach($backlinks as $backPage)
                        <a href="{{ route('pages.show', $backPage) }}"
                           class="flex items-center gap-2 p-2 rounded-lg hover:bg-notion-hover dark:hover:bg-[#2F2F2F] transition-colors text-sm text-notion-muted dark:text-[#787774] hover:text-notion-text dark:hover:text-[#E8E8E5]">
                            <span>{{ $backPage->icon ?? '📄' }}</span>
                            <span>{{ $backPage->title }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    {{-- =================== خريطة العناوين العائمة (Notion Floating TOC Minimap) =================== --}}
    @php
        $headingBlocks = $page->blocks->filter(fn ($b) => in_array($b->type, ['heading_1', 'heading', 'heading_2', 'heading_3'], true) && ! empty($b->content['text']));
    @endphp
    @if($headingBlocks->count() > 0)
        <div x-data="{ tocHover: false }" @mouseenter="tocHover = true" @mouseleave="tocHover = false"
             class="hidden xl:block fixed left-5 top-1/3 z-10">
            {{-- الخطوط المصغرة (Minimap Bars) --}}
            <div x-show="!tocHover" class="flex flex-col items-end gap-2 p-2 cursor-pointer">
                @foreach($headingBlocks as $hb)
                    <span class="h-[2px] rounded-full bg-notion-muted/40 dark:bg-[#555555] {{ in_array($hb->type, ['heading_1', 'heading']) ? 'w-4' : ($hb->type === 'heading_2' ? 'w-3' : 'w-2') }}"></span>
                @endforeach
            </div>

            {{-- القائمة الموسعة عند التمرير --}}
            <div x-show="tocHover" x-cloak
                 class="w-52 bg-white dark:bg-[#252525] border border-notion-border dark:border-[#333333] rounded-xl shadow-2xl p-2 space-y-0.5">
                @foreach($headingBlocks as $hb)
                    <a href="#block-{{ $hb->id }}"
                       class="block truncate px-2 py-1 rounded text-xs text-notion-muted dark:text-[#9B9A97] hover:text-notion-text dark:hover:text-[#E3E3E0] hover:bg-notion-hover dark:hover:bg-[#2C2C2C] transition-colors {{ $hb->type === 'heading_2' ? 'pr-4' : ($hb->type === 'heading_3' ? 'pr-6' : 'font-medium') }}">
                        {{ $hb->content['text'] }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>

    {{-- =================== لوحة تفاصيل الصفحة والنشاط (Page Overview & Activity Panel) =================== --}}
    <aside
        x-show="overviewOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-x-4"
        x-transition:enter-end="opacity-100 translate-x-0"
        class="fixed inset-y-0 left-0 z-40 lg:static lg:z-auto w-72 shrink-0 border-r border-notion-border dark:border-[#2E2E2E] bg-notion-sidebar lg:bg-notion-sidebar/60 dark:bg-[#202020] p-5 space-y-6 overflow-y-auto shadow-2xl lg:shadow-none"
    >
        {{-- نظرة عامة --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold text-notion-muted dark:text-[#9B9A97]">نظرة عامة على الصفحة</h3>
                <button @click="overviewOpen = false" class="text-xs text-notion-muted hover:text-notion-text dark:hover:text-[#E3E3E0]">✕</button>
            </div>
            <div class="space-y-2.5 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-notion-muted dark:text-[#9B9A97]">أنشئت بواسطة</span>
                    <span class="font-medium text-notion-text dark:text-[#E3E3E0]">{{ $page->creator->name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-notion-muted dark:text-[#9B9A97]">تاريخ الإنشاء</span>
                    <span class="font-medium text-notion-text dark:text-[#E3E3E0]">{{ $page->created_at->format('Y/m/d') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-notion-muted dark:text-[#9B9A97]">آخر تعديل</span>
                    <span class="font-medium text-notion-text dark:text-[#E3E3E0]">{{ $page->updated_at->diffForHumans() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-notion-muted dark:text-[#9B9A97]">البلوكات</span>
                    <span class="font-medium text-notion-text dark:text-[#E3E3E0]">{{ $page->blocks->count() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-notion-muted dark:text-[#9B9A97]">قواعد البيانات</span>
                    <span class="font-medium text-notion-text dark:text-[#E3E3E0]" x-text="databases.length"></span>
                </div>
            </div>
        </div>

        <div class="border-t border-notion-border dark:border-[#2E2E2E]"></div>

        {{-- سجل النشاط --}}
        <div>
            <h3 class="text-xs font-semibold text-notion-muted dark:text-[#9B9A97] mb-3">سجل التعديلات ({{ $page->activities->count() }})</h3>
            <div class="space-y-3">
                @forelse($page->activities as $activity)
                    @php $icons = \App\Models\PageActivity::actionIcons(); @endphp
                    <div class="flex items-start gap-2.5 text-xs">
                        <span class="w-6 h-6 rounded-full bg-notion-hover dark:bg-[#2C2C2C] flex items-center justify-center text-[11px] font-bold text-notion-text dark:text-[#E3E3E0] shrink-0">
                            {{ mb_substr($activity->user->name ?? 'م', 0, 1) }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <span class="font-medium text-notion-text dark:text-[#E3E3E0] truncate">{{ $activity->user->name ?? 'مستخدم' }}</span>
                                <span class="text-[10px] text-notion-muted dark:text-[#9B9A97] shrink-0">{{ $activity->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-notion-muted dark:text-[#9B9A97] mt-0.5 leading-relaxed">
                                {{ $icons[$activity->action] ?? '•' }} {{ $activity->description ?? $activity->action }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-notion-muted dark:text-[#9B9A97]">لا يوجد نشاط مسجل بعد.</p>
                @endforelse
            </div>
        </div>
    </aside>

    {{-- ================= درج تعليقات البلوك (Block Comments Drawer) ================= --}}
    <div x-show="commentDrawerOpen" x-cloak class="fixed inset-0 z-50 overflow-hidden flex justify-end">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-[1px]" @click="commentDrawerOpen = false"></div>
        <div class="relative w-full max-w-sm bg-white dark:bg-[#202020] border-r border-notion-border dark:border-[#2E2E2E] h-full shadow-2xl flex flex-col z-10"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full">
            <div class="flex items-center justify-between px-4 py-3 border-b border-notion-border dark:border-[#2E2E2E] bg-notion-sidebar/40 dark:bg-[#1E1E1E]">
                <div class="flex items-center gap-2 text-xs font-semibold text-notion-text dark:text-[#E3E3E0]">
                    <span>💬</span>
                    <span>تعليقات البلوك</span>
                </div>
                <button @click="commentDrawerOpen = false" class="text-xs text-notion-muted hover:text-notion-text dark:hover:text-[#E3E3E0] px-1">✕</button>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                <div x-show="commentsLoading" class="text-center py-8 text-xs text-notion-muted">
                    <div class="inline-block w-4 h-4 border-2 border-notion-muted border-t-transparent rounded-full animate-spin"></div>
                </div>

                <template x-if="!commentsLoading && blockComments.length === 0">
                    <div class="text-center py-12 text-xs text-notion-muted dark:text-[#787774]">
                        لا توجد تعليقات بعد. أضف أول تعليق أدناه.
                    </div>
                </template>

                <template x-for="comment in blockComments" :key="comment.id">
                    <div class="p-3 rounded-xl border border-notion-border dark:border-[#2D2D2D] bg-notion-sidebar/30 dark:bg-[#252525] space-y-2.5"
                         :class="comment.is_resolved ? 'opacity-60' : ''">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 font-bold text-[10px] flex items-center justify-center" x-text="comment.user?.name ? comment.user.name.charAt(0) : 'م'"></span>
                                <span class="text-xs font-medium text-notion-text dark:text-[#E3E3E0]" x-text="comment.user?.name || 'مستخدم'"></span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="toggleResolveComment(comment)"
                                    :class="comment.is_resolved ? 'text-green-500 font-bold' : 'text-notion-muted hover:text-green-500'"
                                    class="text-[10px] px-1.5 py-0.5 rounded border border-notion-border dark:border-[#383838]"
                                    :title="comment.is_resolved ? 'إعادة الفتح' : 'تعيين كمحلول'">
                                    <span x-text="comment.is_resolved ? '✓ محلول' : 'حل'"></span>
                                </button>
                                <button type="button" @click="deleteComment(comment)" class="text-[10px] text-notion-muted hover:text-red-500 p-0.5">✕</button>
                            </div>
                        </div>
                        <p class="text-xs text-notion-text dark:text-[#E3E3E0] leading-relaxed whitespace-pre-wrap" x-text="comment.content"></p>

                        {{-- الردود (Replies) --}}
                        <div x-show="comment.replies && comment.replies.length > 0" class="mr-3 border-r-2 border-notion-border dark:border-[#333333] pr-2.5 space-y-2 mt-2 pt-1">
                            <template x-for="reply in (comment.replies || [])" :key="reply.id">
                                <div class="text-[11px] space-y-1">
                                    <div class="flex items-center justify-between text-notion-muted">
                                        <span class="font-medium text-notion-text dark:text-[#E3E3E0]" x-text="reply.user?.name || 'مستخدم'"></span>
                                        <button type="button" @click="deleteComment(reply)" class="hover:text-red-500 text-[10px]">✕</button>
                                    </div>
                                    <p class="text-notion-text dark:text-[#C5C5C2]" x-text="reply.content"></p>
                                </div>
                            </template>
                        </div>

                        {{-- زر وكتابة الرد --}}
                        <div class="pt-1.5 flex items-center gap-1.5">
                            <input
                                type="text"
                                x-model="replyText[comment.id]"
                                @keydown.enter.prevent="addReply(comment)"
                                placeholder="اكتب رداً..."
                                class="flex-1 text-[11px] px-2 py-1 rounded bg-transparent border border-notion-border dark:border-[#353535] text-notion-text dark:text-[#E3E3E0] focus:outline-none focus:ring-1 focus:ring-blue-500"
                            />
                            <button
                                type="button"
                                @click="addReply(comment)"
                                class="text-[10px] px-2 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded font-medium transition-colors"
                            >رد</button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- نموذج إضافة تعليق جديد --}}
            <div class="p-3 border-t border-notion-border dark:border-[#2E2E2E] bg-notion-sidebar/40 dark:bg-[#1E1E1E]">
                <textarea
                    rows="2"
                    x-model="newCommentText"
                    placeholder="اكتب تعليقاً على هذا البلوك..."
                    class="w-full text-xs p-2 rounded-lg bg-white dark:bg-[#252525] border border-notion-border dark:border-[#353535] text-notion-text dark:text-[#E3E3E0] focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none"
                ></textarea>
                <div class="flex justify-end mt-2">
                    <button
                        type="button"
                        @click="addComment()"
                        class="text-xs px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-medium transition-colors shadow-sm"
                    >إرسال التعليق</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= نافذة مشاركة الصفحة والصلاحيات (Share & Permissions Modal) ================= --}}
    <div x-show="shareModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="shareModalOpen = false"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-[#222222] border border-notion-border dark:border-[#333333] rounded-2xl shadow-2xl p-5 space-y-4 z-10">
            <div class="flex items-center justify-between border-b border-notion-border dark:border-[#303030] pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-base">👥</span>
                    <h3 class="text-sm font-semibold text-notion-text dark:text-[#E3E3E0]">مشاركة الصفحة وإدارة الصلاحيات</h3>
                </div>
                <button @click="shareModalOpen = false" class="text-xs text-notion-muted hover:text-notion-text">✕</button>
            </div>

            {{-- نموذج إضافة مستخدم --}}
            <div class="space-y-2">
                <div class="text-xs font-medium text-notion-muted dark:text-[#9B9A97]">إضافة عضو أو تعديل صلاحيته:</div>
                <div class="flex items-center gap-2">
                    <input
                        type="email"
                        x-model="newPermEmail"
                        placeholder="بريد المستخدم الإلكتروني..."
                        class="flex-1 text-xs px-3 py-2 rounded-xl border border-notion-border dark:border-[#353535] bg-transparent text-notion-text dark:text-[#E3E3E0] focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                    <select
                        x-model="newPermRole"
                        class="text-xs px-2.5 py-2 rounded-xl border border-notion-border dark:border-[#353535] bg-white dark:bg-[#1E1E1E] text-notion-text dark:text-[#E3E3E0] focus:outline-none"
                    >
                        <option value="editor">محرر (Editor)</option>
                        <option value="commenter">معلق (Commenter)</option>
                        <option value="viewer">مشاهد (Viewer)</option>
                    </select>
                    <button
                        type="button"
                        @click="addPermission()"
                        class="text-xs px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium transition-colors shrink-0"
                    >حفظ</button>
                </div>
            </div>

            {{-- قائمة الصلاحيات الحالية --}}
            <div class="space-y-2 pt-2">
                <div class="text-xs font-semibold text-notion-muted dark:text-[#9B9A97]">المخولون بالوصول:</div>
                <div class="max-h-52 overflow-y-auto divide-y divide-notion-border/60 dark:divide-[#2E2E2E]">
                    <div class="flex items-center justify-between py-2 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-amber-500/20 text-amber-400 font-bold text-[10px] flex items-center justify-center">👑</span>
                            <div>
                                <div class="font-medium text-notion-text dark:text-[#E3E3E0]">مالك مساحة العمل</div>
                                <div class="text-[10px] text-notion-muted">{{ $workspace->owner->email ?? '' }}</div>
                            </div>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-notion-sidebar dark:bg-[#2C2C2C] text-notion-muted">محرر كامل</span>
                    </div>

                    <template x-for="perm in permissions" :key="perm.id">
                        <div class="flex items-center justify-between py-2 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 font-bold text-[10px] flex items-center justify-center" x-text="perm.user?.name ? perm.user.name.charAt(0) : 'م'"></span>
                                <div>
                                    <div class="font-medium text-notion-text dark:text-[#E3E3E0]" x-text="perm.user?.name || 'مستخدم'"></div>
                                    <div class="text-[10px] text-notion-muted" x-text="perm.user?.email"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 font-medium" x-text="perm.role === 'editor' ? 'محرر' : (perm.role === 'commenter' ? 'معلق' : 'مشاهد')"></span>
                                <button type="button" @click="removePermission(perm.user_id)" class="text-notion-muted hover:text-red-500 p-1">✕</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

</div>

@php
    $databasesData = $page->databases->map(function ($db) {
        $views = $db->views->map(fn ($v) => [
            'id' => $v->id,
            'name' => $v->name,
            'type' => $v->type,
            'filter_config' => $v->filter_config,
            'sort_config' => $v->sort_config,
        ])->values();

        return [
            'id'         => $db->id,
            'name'       => $db->name,
            'properties' => $db->properties ?? [],
            'rows'       => $db->rows->map(fn ($r) => [
                'id'     => $r->id,
                'values' => $r->values ?? (object) [],
            ])->values(),
            'views'      => $views->count() > 0 ? $views : [
                ['id' => null, 'name' => 'جدول', 'type' => 'table'],
                ['id' => null, 'name' => 'لوحة', 'type' => 'board'],
                ['id' => null, 'name' => 'قائمة', 'type' => 'list'],
                ['id' => null, 'name' => 'معرض', 'type' => 'gallery'],
            ],
            'activeView' => $views->first()['type'] ?? 'table',
            'searchQuery' => '',
            'filterStatus' => '',
        ];
    })->values();
@endphp

<script>
function pageEditor() {
    return {
        title: @json($page->title),
        icon: @json($page->icon ?? '📄'),
        isFavorite: {{ $page->is_favorite ? 'true' : 'false' }},
        coverColor: @json($page->cover_color),
        coverImage: @json($page->cover_image),
        showCoverPicker: false,
        pageId: {{ $page->id }},
        workspaceId: {{ $workspace->id }},
        databases: {{ \Illuminate\Support\Js::from($databasesData) }},
        draggingRowId: null,
        draggingDbId: null,
        dragOverCol: null,

        // التعليقات
        commentDrawerOpen: false,
        activeBlockId: null,
        blockComments: [],
        commentsLoading: false,
        newCommentText: '',
        replyText: {},

        // الصلاحيات والمشاركة
        shareModalOpen: false,
        permissions: [],
        newPermEmail: '',
        newPermRole: 'editor',

        // تصفية السجلات في قواعد البيانات
        getFilteredRows(db) {
            let rows = db.rows || [];
            if (db.searchQuery && db.searchQuery.trim()) {
                const q = db.searchQuery.toLowerCase();
                rows = rows.filter(r => {
                    return Object.values(r.values || {}).some(v => String(v).toLowerCase().includes(q));
                });
            }
            if (db.filterStatus) {
                const statusProp = this.getStatusPropName(db);
                rows = rows.filter(r => (r.values[statusProp] || '') === db.filterStatus);
            }
            return rows;
        },

        async addView(db, name, type) {
            const res = await fetch(`/databases/${db.id}/views`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name, type })
            });
            const data = await res.json();
            if (data.view) {
                db.views.push({
                    id: data.view.id,
                    name: data.view.name,
                    type: data.view.type,
                    filter_config: data.view.filter_config,
                    sort_config: data.view.sort_config
                });
                db.activeView = data.view.type;
            }
        },

        async openComments(blockId) {
            this.activeBlockId = blockId;
            this.commentDrawerOpen = true;
            this.commentsLoading = true;
            this.newCommentText = '';
            try {
                const res = await fetch(`/blocks/${blockId}/comments`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.blockComments = data.comments || [];
            } catch (e) {
                this.blockComments = [];
            } finally {
                this.commentsLoading = false;
            }
        },

        async addComment() {
            if (!this.newCommentText.trim() || !this.activeBlockId) return;
            const res = await fetch(`/blocks/${this.activeBlockId}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ content: this.newCommentText })
            });
            const data = await res.json();
            if (data.comment) {
                data.comment.replies = [];
                this.blockComments.unshift(data.comment);
                this.newCommentText = '';
            }
        },

        async addReply(comment) {
            const text = this.replyText[comment.id];
            if (!text || !text.trim() || !this.activeBlockId) return;
            const res = await fetch(`/blocks/${this.activeBlockId}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ content: text, parent_id: comment.id })
            });
            const data = await res.json();
            if (data.comment) {
                if (!comment.replies) comment.replies = [];
                comment.replies.push(data.comment);
                this.replyText[comment.id] = '';
            }
        },

        async toggleResolveComment(comment) {
            const res = await fetch(`/comments/${comment.id}/resolve`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.comment) {
                comment.is_resolved = data.comment.is_resolved;
            }
        },

        async deleteComment(comment) {
            if (!confirm('حذف هذا التعليق؟')) return;
            await fetch(`/comments/${comment.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                }
            });
            this.blockComments = this.blockComments.filter(c => c.id !== comment.id);
        },

        async openShareModal() {
            this.shareModalOpen = true;
            try {
                const res = await fetch(`/pages/${this.pageId}/permissions`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.permissions = data.permissions || [];
            } catch (e) {
                this.permissions = [];
            }
        },

        async addPermission() {
            if (!this.newPermEmail.trim()) return;
            const res = await fetch(`/pages/${this.pageId}/permissions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: this.newPermEmail, role: this.newPermRole })
            });
            const data = await res.json();
            if (data.permission) {
                const idx = this.permissions.findIndex(p => p.id === data.permission.id);
                if (idx >= 0) {
                    this.permissions[idx] = data.permission;
                } else {
                    this.permissions.push(data.permission);
                }
                this.newPermEmail = '';
            } else if (data.message) {
                alert(data.message);
            }
        },

        async removePermission(userId) {
            await fetch(`/pages/${this.pageId}/permissions/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                }
            });
            this.permissions = this.permissions.filter(p => p.user_id !== userId);
        },

        async createDatabase() {
            const res = await fetch('{{ route('databases.store', $page) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: 'قاعدة بيانات جديدة' })
            });
            const data = await res.json();
            if (data.database) {
                this.databases.push({
                    id: data.database.id,
                    name: data.database.name,
                    properties: data.database.properties || [],
                    rows: data.database.rows || [],
                    views: [
                        { id: null, name: 'جدول', type: 'table' },
                        { id: null, name: 'لوحة', type: 'board' },
                        { id: null, name: 'قائمة', type: 'list' },
                        { id: null, name: 'معرض', type: 'gallery' }
                    ],
                    activeView: 'table',
                    searchQuery: '',
                    filterStatus: ''
                });
            }
        },

        async updateDatabaseName(db) {
            await fetch(`/databases/${db.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: db.name })
            });
        },

        async deleteDatabase(db) {
            if (!confirm('حذف قاعدة البيانات بالكامل؟')) return;
            await fetch(`/databases/${db.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                }
            });
            this.databases = this.databases.filter(d => d.id !== db.id);
        },

        async addDatabaseRow(db) {
            const defaultValues = {};
            const statusProp = db.properties.find(p => p.type === 'select');
            if (statusProp && statusProp.options?.length) {
                defaultValues[statusProp.name] = statusProp.options[0];
            }
            const res = await fetch(`/databases/${db.id}/rows`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ values: defaultValues })
            });
            const data = await res.json();
            if (data.row) {
                db.rows.push({ id: data.row.id, values: data.row.values || {} });
            }
        },

        async updateDatabaseRow(row) {
            await fetch(`/database-rows/${row.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ values: row.values })
            });
        },

        async deleteDatabaseRow(db, row) {
            await fetch(`/database-rows/${row.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                }
            });
            db.rows = db.rows.filter(r => r.id !== row.id);
        },

        getStatusPropName(db) {
            const prop = db.properties.find(p => p.type === 'select');
            return prop ? prop.name : 'الحالة';
        },

        getBoardColumns(db) {
            const prop = db.properties.find(p => p.type === 'select');
            return prop && prop.options?.length ? prop.options : ['لم يبدأ', 'جاري', 'مكتمل'];
        },

        getBoardRows(db, col) {
            const statusKey = this.getStatusPropName(db);
            const cols = this.getBoardColumns(db);
            return this.getFilteredRows(db).filter(r => {
                const val = r.values[statusKey];
                return val ? val === col : col === cols[0];
            });
        },

        startBoardDrag(e, db, row) {
            if (document.activeElement && ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
                e.preventDefault();
                return;
            }
            this.draggingRowId = row.id;
            this.draggingDbId = db.id;
            if (e.dataTransfer) {
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', String(row.id));
            }
        },

        async moveBoardRow(db, targetCol) {
            this.dragOverCol = null;
            if (!this.draggingRowId || this.draggingDbId !== db.id) return;
            const row = db.rows.find(r => r.id === this.draggingRowId);
            this.draggingRowId = null;
            this.draggingDbId = null;
            if (!row) return;

            const statusKey = this.getStatusPropName(db);
            const currentVal = row.values[statusKey] || this.getBoardColumns(db)[0];
            if (currentVal === targetCol) return;

            row.values[statusKey] = targetCol;
            await this.updateDatabaseRow(row);
        },

        async updatePage() {
            await fetch('{{ route('pages.update', $page) }}', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ title: this.title, icon: this.icon, cover_color: this.coverColor, cover_image: this.coverImage })
            });
        },

        async setCoverColor(color) {
            this.coverColor = color;
            this.coverImage = null;
            this.showCoverPicker = false;
            await this.updatePage();
            window.location.reload();
        },

        async removeCover() {
            this.coverColor = null;
            this.coverImage = null;
            await fetch('{{ route('pages.update', $page) }}', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ title: this.title, icon: this.icon, cover_color: '', cover_image: '' })
            });
            window.location.reload();
        },

        async toggleFavorite() {
            const res = await fetch('{{ route('pages.favorite', $page) }}', {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            this.isFavorite = data.is_favorite;
        },

        async addExistingTag(tagId) {
            await fetch('{{ route('pages.tags.attach', $page) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ tag_id: tagId })
            });
            window.location.reload();
        },

        async addNewTag(name, color) {
            if (!name.trim()) return;
            await fetch('{{ route('pages.tags.attach', $page) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ tag_name: name, tag_color: color })
            });
            window.location.reload();
        },

        async removeTag(tagId) {
            await fetch(`/pages/{{ $page->id }}/tags/${tagId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                }
            });
            window.location.reload();
        },

        initSortable() {
            const container = document.getElementById('blocks-container');
            if (!container || typeof Sortable === 'undefined') return;

            Sortable.create(container, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'opacity-30',
                chosenClass: 'bg-notion-hover',
                onEnd: async (evt) => {
                    const items = [...container.querySelectorAll('[data-block-id]')];
                    const blocks = items.map((el, index) => ({
                        id: parseInt(el.dataset.blockId),
                        position: index + 1
                    }));

                    await fetch('{{ route('blocks.reorder', $page) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ blocks })
                    });
                }
            });
        },

        init() {
            this.$nextTick(() => this.initSortable());
        }
    }
}
</script>
@endsection
