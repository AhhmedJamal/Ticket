<p align="center">
  <span style="font-size: 64px;">📝</span>
  <h1 align="center">Ticket</h1>
  <p align="center">
    تطبيق متكامل لإدارة مساحات العمل والصفحات والملاحظات والمهام وقواعد البيانات بنظام <strong>Hybrid Monolith</strong> مبني بإطار عمل <strong>Laravel 13</strong>، ومدعوم بـ <strong>Blade</strong> و <strong>Tailwind CSS</strong> و <strong>Alpine.js</strong>.
  </p>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13" />
  <img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/Alpine.js-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white" alt="Alpine.js" />
</p>

---

## ✨ المميزات الرئيسية (Features)

### 1. 🏢 مساحات العمل والأعضاء (Workspaces & Members)
- تسجيل حساب، تسجيل الدخول، وحفظ الجلسات (Session Auth + Sanctum API).
- إنشاء مساحات عمل (Workspaces) متعددة مع توليد `slug` فريد لكل مساحة.
- إدارة أعضاء مساحة العمل ودعوتهم وتحديد صلاحياتهم (`admin` / `member`) من صفحة الإعدادات.
- عرض الصفحات الرئيسية في شبكة بطاقات منظمة من عمودين (2-Column Card Grid).

### 2. 🌲 شجرة صفحات هرمية لا نهائية (Infinite Nested Pages)
- شجرة صفحات متداخلة بدون حدود عبر **Self-referencing Foreign Keys** (`parent_id`).
- قائمة جانبية (Sidebar) بأسلوب Notion تتضمن:
  - شريط اختصارات أفقي (`الرئيسية`، `القوالب`، `بحث سريع`).
  - قسم **المفضلة (Favorites)** القابل للطي.
  - فتح وطي الفروع المتداخلة (`sidebar-page-item`) مع زر إضافة صفحة فرعية `(+)` وحذف الصفحة تلقائياً (`cascadeOnDelete`).
  - إخفاء وإظهار السايدبار بالكامل بضغطة زر.

### 3. 🧩 محرر بلوكات متقدم ومرن (Block-based Editor)
- **كتابة مباشرة وأوامر السلاش (`/`):** سطر كتابة فوري يفتح قائمة البلوكات عند كتابة `/` أو الضغط على `+`.
- **نصوص وعناوين:** فقرات متمددة تلقائياً (`paragraph`) وعناوين (`H1`, `H2`, `H3`) مع دعم `dir="auto"` للنصوص المختلطة العربية والإنجليزية.
- **💻 كتل برمجية (Code Blocks):** اختيار لغة البرمجة (`PHP`, `JavaScript`, `TypeScript`, `Python`, `HTML`, `CSS`, `SQL`, `JSON`, `Bash`) مع **تلوين حي للأكواد (Syntax Highlighting)** عبر **Prism.js** وزر نسخ يظهر عند التمرير.
- **💡 صناديق الملاحظات (Callouts):** دعم اختيار لون الخلفية (افتراضي، أزرق، أخضر، أصفر، برتقالي، أحمر، بنفسجي).
- **▶ قوائم قابلة للطي (Toggle)**، **☑ قوائم مهام تفاعلية (To-do)**، **❝ اقتباسات (Quote)**، **قوائم نقطية ومرقمة**، و**فواصل أفقية (Divider)**.
- **خريطة العناوين العائمة (Floating TOC Minimap):** شريط مصغر جانبي يفتح قائمة العناوين عند التمرير للانتقال السريع.

### 4. 🗂️ قواعد بيانات متعددة العروض (Multi-View Databases)
- إنشاء قواعد بيانات مضمنة داخل الصفحات بأعمدة قابلة للتخصيص (`text`, `select`, `date`, وغيرها).
- التبديل الفوري بين **3 عروض**:
  - **📊 جدول (Table View):** تعديل مباشر للخلايا وإضافة/حذف السجلات.
  - **📋 لوحة مهام (Kanban Board):** تجميع تلقائي حسب الحالة ونقل البطاقات بين الأعمدة.
  - **🃏 معرض (Gallery View):** شبكة بطاقات من عمودين.

### 5. 🎨 تجربة Notion الكاملة والوضع الليلي
- **الوضع الليلي افتراضياً (Dark Mode First):** ألوان Notion الداكنة (`#191919` / `#202020`) مع إمكانية التبديل باختصار `Ctrl + Shift + L`.
- **خط عربي مخصص:** تفعيل خط **Kashif Arabic** (`public/fonts/KashifArabic.otf`) على مستوى النظام بالكامل.
- **أغلفة وأيقونات ووسوم:** منتقي ألوان الغلاف، Emoji Picker للأيقونة، المفضلة (`⭐`)، ووسوم ملونة (`Tags`).
- **قوالب جاهزة (Templates):** مكتبة قوالب مصنفة عبر `TemplateSeeder` لإنشاء صفحات جاهزة بضغطة زر.
- **لوحة تفاصيل الصفحة وسجل التعديلات (Page Overview & Activity):** لوحة جانبية تعرض معلومات الصفحة وتاريخ التعديلات.
- **الروابط الخلفية وتصدير Markdown:** عرض الصفحات التي تشير للصفحة الحالية (`Backlinks`) وتصدير المحتوى بصيغة `.md`.
- **البحث السريع (`Ctrl + K`):** نافذة بحث فورية في عناوين ومحتوى الصفحات.

---

## 🛠️ حزمة التقنيات (Tech Stack)

| المجال | التقنية المستخدمة |
|---|---|
| **الباك إند** | Laravel 13 (PHP 8.4) |
| **قواعد البيانات** | MySQL / SQLite مع دعم حقول JSON |
| **المصادقة** | Laravel Session Auth + Laravel Sanctum |
| **محرك القوالب** | Laravel Blade Components & Layouts |
| **التصميم والخطوط** | Tailwind CSS (CDN) + خط Kashif Arabic |
| **التفاعل بالمتصفح** | Alpine.js (CDN) |
| **السحب والإفلات** | SortableJS |
| **تلوين الأكواد** | Prism.js |

---

## 🚀 متطلبات التشغيل والتثبيت (Getting Started)

1. **تثبيت حزم الـ PHP:**
   ```bash
   composer install
   ```

2. **إعداد ملف البيئة (`.env`):**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **تشغيل الـ Migrations وبذر القوالب:**
   ```bash
   php artisan migrate
   php artisan db:seed --class=TemplateSeeder
   ```

4. **بدء السيرفر المحلي:**
   ```bash
   php artisan serve
   ```
   افتح المتصفح وتوجه إلى: `http://127.0.0.1:8000`

---

## 🗄️ هيكل قاعدة البيانات (Database Schema)

- **`users`:** جدول المستخدمين.
- **`workspaces` & `workspace_user`:** مساحات العمل والأعضاء مع الرتبة (`admin` / `member`).
- **`pages`:** الصفحات الهرمية (`parent_id`) مع `icon`, `cover_image`, `cover_color`, `is_favorite`.
- **`blocks`:** البلوكات (`type`, `content` بصيغة JSON تشمل `text`, `checked`, `open`, `color`, `language`, `position`).
- **`tags` & `page_tag`:** الوسوم الملونة وربطها بالصفحات.
- **`notion_databases` & `database_rows`:** قواعد البيانات المضمنة وسجلاتها بصيغة JSON.
- **`templates`:** قوالب الصفحات الجاهزة.
- **`page_activities`:** سجل النشاط والتعديلات على كل صفحة.

---

## 📚 التوثيق المعماري للمشروع

للاطلاع على التوثيق التفصيلي الشامل لقواعد الكود، القرارات الهندسية، والمخططات التفصيلية، راجع:
- [`PROJECT_CONTEXT.md`](./PROJECT_CONTEXT.md)
- [`PROGRESS.md`](./PROGRESS.md)

---

## 📄 الترخيص (License)
هذا المشروع مفتوح المصدر ومتاح تحت ترخيص [MIT License](LICENSE).
