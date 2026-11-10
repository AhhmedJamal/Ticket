@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="pageEditor()">

    {{-- رأس الصفحة: الأيقونة + العنوان القابل للتعديل --}}
    <div class="pb-4 border-b border-notion-border">
        {{-- الأيقونة --}}
        <div class="text-5xl mb-3 cursor-pointer select-none inline-block hover:scale-105 transition-transform" 
             @click="changeIcon()" title="انقر لتغيير الأيقونة">
            <span x-text="icon"></span>
        </div>

        {{-- العنوان التفاعلي المباشر --}}
        <div class="relative group">
            <input 
                type="text" 
                x-model="title" 
                @blur="updatePage()" 
                @keydown.enter="$event.target.blur()" 
                class="w-full text-4xl font-bold text-notion-text bg-transparent border-0 border-b border-transparent hover:border-notion-border focus:border-notion-accent focus:ring-0 focus:outline-none p-0 transition-colors"
                placeholder="بدون عنوان..."
            />
        </div>

        <div class="flex items-center justify-between text-xs text-notion-muted mt-2">
            <span>{{ $page->blocks->count() }} بلوك &bull; {{ $page->children->count() }} صفحات داخلية</span>
        </div>
    </div>

    {{-- قسم الصفحات الفرعية داخل هذه الصفحة (Sub-pages) --}}
    @if($page->children->count() > 0)
        <div class="space-y-1.5 py-2">
            <div class="text-xs font-semibold text-notion-muted mb-2">📄 صفحات فرعية داخل هذه الصفحة:</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                @foreach($page->children as $sub)
                    <a href="{{ route('pages.show', $sub) }}" class="flex items-center justify-between p-2.5 rounded-lg border border-notion-border hover:bg-notion-hover transition-colors group">
                        <div class="flex items-center gap-2 truncate">
                            <span class="text-base">{{ $sub->icon ?? '📄' }}</span>
                            <span class="text-sm font-medium text-notion-text truncate">{{ $sub->title }}</span>
                        </div>
                        <span class="text-xs text-notion-muted group-hover:text-notion-text transition-colors">&larr;</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- حاوية البلوكات بجميع أنواعها --}}
    <div class="space-y-2 min-h-[80px]">
        @forelse($page->blocks as $block)
            <div 
                x-data="{
                    text: '{{ addslashes($block->content['text'] ?? '') }}',
                    checked: {{ isset($block->content['checked']) && $block->content['checked'] ? 'true' : 'false' }},
                    updateBlock() {
                        fetch('{{ route('blocks.update', $block) }}', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ text: this.text, checked: this.checked })
                        });
                    }
                }" 
                class="group relative flex items-start gap-2 py-1 px-2 -mx-2 rounded hover:bg-notion-hover/50 transition-colors"
            >
                {{-- زر حذف البلوك يظهر عند الـ Hover --}}
                <div class="opacity-0 group-hover:opacity-100 absolute -right-6 top-2 transition-opacity z-10">
                    <form action="{{ route('blocks.destroy', $block) }}" method="POST" onsubmit="return confirm('حذف هذا البلوك؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="حذف البلوك" class="text-notion-muted hover:text-red-500 p-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </form>
                </div>

                {{-- ==================== أنواع البلوكات ==================== --}}
                
                {{-- 1. عنوان كبير H1 --}}
                @if($block->type === 'heading_1' || $block->type === 'heading')
                    <input 
                        type="text" 
                        x-model="text" 
                        @blur="updateBlock()" 
                        @keydown.enter="$event.target.blur()"
                        class="w-full font-bold text-3xl text-notion-text bg-transparent border-none p-0 focus:ring-0 focus:outline-none"
                        placeholder="عنوان كبير (Heading 1)..."
                    />

                {{-- 2. عنوان متوسط H2 --}}
                @elseif($block->type === 'heading_2')
                    <input 
                        type="text" 
                        x-model="text" 
                        @blur="updateBlock()" 
                        @keydown.enter="$event.target.blur()"
                        class="w-full font-bold text-2xl text-notion-text bg-transparent border-none p-0 focus:ring-0 focus:outline-none"
                        placeholder="عنوان فرعي (Heading 2)..."
                    />

                {{-- 3. عنوان صغير H3 --}}
                @elseif($block->type === 'heading_3')
                    <input 
                        type="text" 
                        x-model="text" 
                        @blur="updateBlock()" 
                        @keydown.enter="$event.target.blur()"
                        class="w-full font-semibold text-lg text-notion-text bg-transparent border-none p-0 focus:ring-0 focus:outline-none"
                        placeholder="عنوان قسم (Heading 3)..."
                    />

                {{-- 4. قائمة مهام Todo --}}
                @elseif($block->type === 'todo')
                    <div class="flex items-center gap-2.5 w-full">
                        <input 
                            type="checkbox" 
                            x-model="checked" 
                            @change="updateBlock()"
                            class="w-4 h-4 rounded text-notion-accent focus:ring-0 cursor-pointer border-notion-border"
                        />
                        <input 
                            type="text" 
                            x-model="text" 
                            @blur="updateBlock()" 
                            @keydown.enter="$event.target.blur()"
                            :class="{ 'line-through text-notion-muted': checked }"
                            class="w-full text-base text-notion-text bg-transparent border-none p-0 focus:ring-0 focus:outline-none"
                            placeholder="مهمة لإنجازها..."
                        />
                    </div>

                {{-- 5. كود برمجى (Code Snippet) --}}
                @elseif($block->type === 'code')
                    <div class="w-full bg-[#1E1E1E] text-[#D4D4D4] rounded-lg p-3 font-mono text-sm relative group/code">
                        <div class="flex justify-between items-center text-[11px] text-zinc-400 border-b border-zinc-700 pb-1.5 mb-2 select-none">
                            <span>💻 كود برمجى (Code)</span>
                            <button @click="navigator.clipboard.writeText(text); alert('تم نسخ الكود!')" class="hover:text-white">نسخ</button>
                        </div>
                        <textarea 
                            x-model="text" 
                            @blur="updateBlock()" 
                            rows="2"
                            @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                            class="w-full bg-transparent text-[#9CDCFE] font-mono text-sm border-none p-0 focus:ring-0 focus:outline-none resize-none leading-relaxed"
                            placeholder="// اكتب الكود هنا..."
                        ></textarea>
                    </div>

                {{-- 6. صندوق تنبيه أو ملاحظة بارزة (Callout) --}}
                @elseif($block->type === 'callout')
                    <div class="w-full flex items-start gap-3 bg-[#FBFBFA] border border-notion-border rounded-lg p-3.5">
                        <span class="text-xl select-none">💡</span>
                        <textarea 
                            x-model="text" 
                            @blur="updateBlock()" 
                            rows="1"
                            @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                            class="w-full text-sm leading-relaxed text-notion-text bg-transparent border-none p-0 focus:ring-0 focus:outline-none resize-none overflow-hidden"
                            placeholder="تنبيه أو ملاحظة مهمة..."
                        ></textarea>
                    </div>

                {{-- 7. اقتباس (Quote) --}}
                @elseif($block->type === 'quote')
                    <div class="w-full border-r-4 border-notion-text pr-3 py-0.5">
                        <textarea 
                            x-model="text" 
                            @blur="updateBlock()" 
                            rows="1"
                            @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                            class="w-full italic text-base leading-relaxed text-notion-text bg-transparent border-none p-0 focus:ring-0 focus:outline-none resize-none overflow-hidden"
                            placeholder="اكتب اقتباساً جميلاً..."
                        ></textarea>
                    </div>

                {{-- 8. قائمة نقطية (Bullet List) --}}
                @elseif($block->type === 'bulleted_list')
                    <div class="flex items-start gap-2.5 w-full">
                        <span class="text-notion-text text-lg leading-tight select-none mt-0.5">&bull;</span>
                        <input 
                            type="text" 
                            x-model="text" 
                            @blur="updateBlock()" 
                            @keydown.enter="$event.target.blur()"
                            class="w-full text-base text-notion-text bg-transparent border-none p-0 focus:ring-0 focus:outline-none"
                            placeholder="عنصر في قائمة نقطية..."
                        />
                    </div>

                {{-- 9. قائمة مرقمة (Numbered List) --}}
                @elseif($block->type === 'numbered_list')
                    <div class="flex items-start gap-2 w-full">
                        <span class="text-notion-muted text-sm font-semibold select-none mt-1">1.</span>
                        <input 
                            type="text" 
                            x-model="text" 
                            @blur="updateBlock()" 
                            @keydown.enter="$event.target.blur()"
                            class="w-full text-base text-notion-text bg-transparent border-none p-0 focus:ring-0 focus:outline-none"
                            placeholder="عنصر في قائمة مرقمة..."
                        />
                    </div>

                {{-- 10. فاصل أفقي (Divider) --}}
                @elseif($block->type === 'divider')
                    <div class="w-full py-2">
                        <hr class="border-t border-notion-border" />
                    </div>

                {{-- 11. نص عادي افتراضي (Paragraph) --}}
                @else
                    <textarea 
                        x-model="text" 
                        @blur="updateBlock()" 
                        rows="1"
                        @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                        class="w-full text-base leading-relaxed text-notion-text bg-transparent border-none p-0 focus:ring-0 focus:outline-none resize-none overflow-hidden"
                        placeholder="اكتب نصاً أو فكرة..."
                    ></textarea>
                @endif
            </div>
        @empty
            @if($page->children->count() == 0)
                <div class="text-sm text-notion-muted py-6 text-center border border-dashed border-notion-border rounded-lg">
                    الصفحة فارغة. اختر نوع البلوك أدناه (كود، عنوان، اقتباس، مهام...) للبدء!
                </div>
            @endif
        @endforelse
    </div>

    {{-- منطقة إضافة البلوكات بجميع أنواعها --}}
    <div class="pt-6 border-t border-notion-border space-y-3">
        <form action="{{ route('blocks.store', $page) }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 bg-notion-sidebar p-2.5 rounded-xl border border-notion-border">
            @csrf
            
            {{-- قائمة اختيار نوع البلوك الواسعة --}}
            <select name="type" class="text-xs bg-white border border-notion-border rounded-lg px-3 py-2 focus:outline-none focus:border-notion-text text-notion-text font-medium cursor-pointer shadow-sm">
                <optgroup label="نصوص وعناوين">
                    <option value="paragraph">¶ نص عادي (Paragraph)</option>
                    <option value="heading_1">H1 عنوان رئيسي كبير</option>
                    <option value="heading_2">H2 عنوان متوسط</option>
                    <option value="heading_3">H3 عنوان فرعي صغير</option>
                </optgroup>
                <optgroup label="تطوير وبرمجة وملاحظات">
                    <option value="code">💻 كتلة كود برمجى (Code)</option>
                    <option value="callout">💡 صندوق ملاحظة / تنبيه (Callout)</option>
                    <option value="quote">❝ اقتباس مميز (Quote)</option>
                </optgroup>
                <optgroup label="قوائم وتنظيم">
                    <option value="todo">☑ قائمة مهام (To-do)</option>
                    <option value="bulleted_list">&bull; قائمة نقطية (Bullet List)</option>
                    <option value="numbered_list">1. قائمة مرقمة (Numbered List)</option>
                    <option value="divider">― خط فاصل (Divider)</option>
                </optgroup>
            </select>

            <input 
                type="text" 
                name="text" 
                placeholder="اكتب المحتوى أو الكود ثم اضغط إضافة أو Enter..." 
                class="flex-1 text-sm bg-white border border-notion-border rounded-lg px-3 py-2 focus:outline-none focus:border-notion-text text-notion-text shadow-sm"
            />

            <button type="submit" class="px-4 py-2 bg-[#37352F] text-white rounded-lg text-xs font-medium hover:bg-black transition-colors whitespace-nowrap shadow-sm">
                + إضافة بلوك
            </button>
        </form>

        {{-- زر سريع لإضافة صفحة فرعية داخل الصفحة الحالية --}}
        <div class="flex items-center justify-between px-1">
            <span class="text-xs text-notion-muted">تنظيم هيكلي:</span>
            <form action="{{ route('pages.store', $workspace) }}" method="POST">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $page->id }}">
                <button type="submit" class="flex items-center gap-1.5 text-xs text-notion-accent hover:underline font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>+ إضافة صفحة فرعية (Sub-page داخل هذه الصفحة)</span>
                </button>
            </form>
        </div>
    </div>

</div>

<script>
function pageEditor() {
    return {
        title: '{{ addslashes($page->title) }}',
        icon: '{{ $page->icon ?? '📄' }}',

        changeIcon() {
            const newIcon = prompt('اختر رمز تعبيري (Emoji) جديد:', this.icon);
            if (newIcon) {
                this.icon = newIcon;
                this.updatePage();
            }
        },

        updatePage() {
            fetch('{{ route('pages.update', $page) }}', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ title: this.title, icon: this.icon })
            });
        }
    }
}
</script>
@endsection
