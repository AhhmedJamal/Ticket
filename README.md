<p align="center">
  <span style="font-size: 64px;">📝</span>
  <h1 align="center">Notion Clone — نوشن المصغر</h1>
  <p align="center">
    تطبيق متكامل لإدارة مساحات العمل والصفحات والملاحظات والمهام بنظام <strong>Monolith</strong> مبني بإطار عمل <strong>Laravel</strong>، ومدعوم بـ <strong>Blade</strong> و <strong>Tailwind CSS</strong> و <strong>Alpine.js</strong> بدون أي إطارات عمل SPA معقدة.
  </p>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11%2F12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel" />
  <img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/Alpine.js-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white" alt="Alpine.js" />
</p>

---

## ✨ المميزات الرئيسية (Features)

### 1. 🏢 مساحات العمل والمستخدمين (Workspaces & Users)
- تسجيل حساب، تسجيل الدخول، وحفظ الجلسات.
- إنشاء مساحات عمل (Workspaces) متعددة مع توليد `slug` فريد لكل مساحة.
- لوحة تحكم ترحيبية وإحصائيات سريعة بعدد الصفحات وتاريخ الإنشاء.

### 2. 🌲 شجرة صفحات هرمية لا نهائية (Infinite Nested Pages)
- شجرة صفحات متداخلة بدون حدود عبر تقنية **Self-referencing Foreign Keys** (`parent_id`).
- قائمة جانبية (Sidebar) ذكية تتيح:
  - فتح وطي الفروع عبر أسهم الـ (Chevron).
  - زر إضافة صفحة فرعية فورية `(+)` بجانب كل صفحة في أي مستوى.
  - إخفاء وإظهار السايدبار بالكامل بضغطة زر.
  - زر حذف الصفحة مع حذف كل صفحاتها التابعة تلقائياً (`cascadeOnDelete`).

### 3. 🧩 محرر بلوكات متقدم ومرن (Block-based Editor)
يدعم محرر الصفحة تشكيلة واسعة من البلوكات المستوحاة من Notion مباشرة:
- **¶ نصوص عادية (Paragraph):** متعددة الأسطر مع تمدد تلقائي وسلاسة في الكتابة.
- **عناوين متعددة المستويات:** `H1` عنوان رئيسي، `H2` عنوان متوسط، و `H3` عنوان فرعي.
- **💻 كتل برمجية (Code Snippets):** شاشة كود داكنة (Dark Code Editor)، خط Monospace، وزر لنسخ الكود بضغطة واحدة.
- **💡 صناديق ملاحظات وتنبيهات (Callouts):** خلفية مميزة وأيقونة بارزة للملاحظات الهامة.
- **❝ اقتباسات أنيقة (Quotes):** خط عمودي أنيق مع خط مائل.
- **☑ قوائم مهام تفاعلية (To-do Lists):** مربعات اختيار (Checkboxes) تفاعلية تشطب النص عند الإنجاز وتُحفظ لحظياً في قاعدة البيانات.
- **• قوائم نقطية ومرقمة (Bullet & Numbered Lists)**.
- **― فواصل أفقية (Dividers)** لتقسيم محتوى الصفحة.

### 4. ⚡ التفاعل الفوري (Inline Editing & AJAX)
- **العنوان التفاعلي (Inline Editable Title):** تعديل العنوان في مكانه دون إعادة تحميل الصفحة (Auto-save on blur).
- **أيقونة الصفحة الفورية (Emoji Picker):** تغيير أيقونة الصفحة بالنقر المباشر وحفظ التغيير فوراً.
- **إعادة الترتيب بالسحب والإفلات (Drag & Drop):** إعادة ترتيب البلوكات بسهولة عبر SortableJS مع حفظ الترتيب الجديد في الباك إند.
- **نافذة البحث السريع (Command Palette `Ctrl + K`):** بحث فوري في جميع صفحات مساحة العمل والتنقل بينها.

---

## 🛠️ حزمة التقنيات (Tech Stack)

| المجال | التقنية المستخدمة |
|---|---|
| **الباك إند** | Laravel 11/12 (PHP 8.4) |
| **قواعد البيانات** | SQLite / MySQL مع دعم JSON Fields |
| **المصادقة** | Laravel Session Auth + Laravel Sanctum |
| **محرك القوالب** | Laravel Blade Components & Layouts |
| **التصميم** | Tailwind CSS (CDN) مع ألوان نوشن الرسمية |
| **التفاعل بالمتصفح** | Alpine.js (CDN) |
| **السحب والإفلات** | SortableJS |

---

## 🚀 متطلبات التشغيل والتثبيت (Getting Started)

### المتطلبات الأساسية
- **PHP >= 8.2** (يُفضل **PHP 8.4**)
- **Composer**
- **Node.js & NPM** (اختياري، الاعتماد الأساسي على الـ CDN)

### خطوات التثبيت:

1. **استنساخ المستودع (Clone repository):**
   ```bash
   git clone https://github.com/your-username/notion-clone.git
   cd notion-clone
   ```

2. **تثبيت حزم الـ PHP:**
   ```bash
   composer install
   ```

3. **إعداد ملف البيئة (`.env`):**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **تشغيل الـ Migrations لقاعدة البيانات:**
   ```bash
   php artisan migrate
   ```

5. **بدء السيرفر المحلي:**
   ```bash
   php artisan serve
   ```
   افتح المتصفح وتوجه إلى: `http://localhost:8000`

---

## 🗄️ هيكل قاعدة البيانات (Database Schema)

- **`users`:** جدول المستخدمين.
- **`workspaces`:** مساحات العمل التي يملكها المستخدمون مع حقل `slug`.
- **`workspace_user`:** جدول الربط بين مساحات العمل والأعضاء مع تحديد الرتبة (`role`).
- **`pages`:** الصفحات التابعة لمساحة العمل، تحتوي على:
  - `parent_id`: يشير لنفس الجدول لدعم الصفحات المتداخلة (`Self-referencing`).
  - `title`: عنوان الصفحة.
  - `icon`: الرمز التعبيري (Emoji).
  - `cover_image`: صورة الغلاف العلوية.
- **`blocks`:** البلوكات المكونة للصفحة، تحتوي على:
  - `type`: نوع البلوك (`paragraph`, `heading_1`, `code`, `todo`, ... إلخ).
  - `content`: حقل JSON مرن يخزن النصوص وحالة التحديد وغيرها.
  - `position`: ترتيب البلوك داخل الصفحة.

---

## 📚 التوثيق المعماري للمشروع

للاطلاع على التوثيق التفصيلي الشامل لقواعد الكود، القرارات الهندسية، والمخططات التفصيلية، راجع ملف:
👉 **[`PROJECT_CONTEXT.md`](./PROJECT_CONTEXT.md)**

---

## 📄 الترخيص (License)
هذا المشروع مفتوح المصدر ومتاح تحت ترخيص [MIT License](LICENSE).
