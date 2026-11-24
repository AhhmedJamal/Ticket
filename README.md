<p align="center">
  <span style="font-size: 64px;">📝</span>
  <h1 align="center">Ticket — Monorepo Architecture</h1>
  <p align="center">
    تطبيق متكامل لإدارة مساحات العمل والصفحات والملاحظات والمهام وقواعد البيانات بأسلوب <strong>Notion</strong> بنظام <strong>Monorepo</strong> متكامل داخل مجلد <code>apps/</code>:
    <br/>
    <strong>Backend:</strong> <code>apps/backend/</code> — Laravel 13 RESTful JSON API + Laravel Sanctum تحت <code>/api/v1/...</code>
    <br/>
    <strong>Frontend:</strong> <code>apps/frontend/</code> — Next.js 15 (App Router) + React 19 + TypeScript + TailwindCSS + Notion Block Editor
  </p>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Architecture-Monorepo-orange?style=for-the-badge" alt="Monorepo" />
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13" />
  <img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4" />
  <img src="https://img.shields.io/badge/Next.js-15-black?style=for-the-badge&logo=next.js&logoColor=white" alt="Next.js 15" />
  <img src="https://img.shields.io/badge/React-19-61DAFB?style=for-the-badge&logo=react&logoColor=black" alt="React 19" />
  <img src="https://img.shields.io/badge/TypeScript-5.9-blue?style=for-the-badge&logo=typescript&logoColor=white" alt="TypeScript" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS" />
</p>

---

## 🏛️ الهيكلية المعمارية الموحدة للمستودع (Monorepo Structure)

```text
Ticket/
├── apps/
│   ├── backend/                        # خادم Laravel 13 RESTful API الكامل
│   │   ├── app/
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/Api/V1/ # متحكمات الـ API المستقلة (12 Controller)
│   │   │   │   ├── Requests/Api/V1/    # FormRequests لإرجاع أخطاء 422 JSON
│   │   │   │   └── Resources/Api/V1/   # JsonResources لتوحيد مخرجات البيانات
│   │   │   ├── Models/                 # موديلز Eloquent الأصلية
│   │   │   ├── Policies/               # سياسات الصلاحيات والتحقق
│   │   │   └── Services/               # Business Logic Services
│   │   ├── config/
│   │   │   ├── cors.php                # إعدادات CORS لمشاركة الموارد مع الفرونت
│   │   │   └── sanctum.php             # إعدادات مصادقة التوكنات لـ Sanctum
│   │   ├── database/                   # Migrations & Seeders
│   │   ├── routes/
│   │   │   └── api.php                 # 47 Endpoint تحت /api/v1/...
│   │   └── tests/                      # اختبارات Pest (13 passed, 83 assertions)
│   │
│   └── frontend/                       # تطبيق Next.js 15 (App Router + TypeScript)
│       ├── src/
│       │   ├── app/
│       │   │   ├── (auth)/             # صفحات الدخول والتسجيل واسترجاع كلمة المرور
│       │   │   │   ├── login/
│       │   │   │   ├── register/
│       │   │   │   ├── forgot-password/
│       │   │   │   └── reset-password/[token]/
│       │   │   ├── (workspace)/        # غلاف مساحة العمل التفاعلي (Notion Workspace Layout)
│       │   │   │   ├── layout.tsx      # السايدبار القابل للطي + التوب بار + شريط المسار
│       │   │   │   ├── page.tsx        # محول ذكي ينقل لأول مساحة عمل
│       │   │   │   ├── dashboard/      # شاشة Onboarding لإنشاء مساحة العمل
│       │   │   │   ├── workspaces/[id]/# عرض مساحة العمل وشبكة بطاقات الصفحات
│       │   │   │   │   ├── settings/   # إدارة إعدادات مساحة العمل والأعضاء
│       │   │   │   │   └── templates/  # معرض قوالب Notion الجاهزة
│       │   │   │   └── pages/[id]/     # محرر صفحات وبلوكات Notion الشامل
│       │   │   ├── layout.tsx          # Root Layout (الخطوط، الوضع الليلي، الإشعارات)
│       │   │   └── globals.css         # أنماط TailwindCSS + ثيم Notion + تلوين Prism.js
│       │   ├── components/
│       │   │   ├── editor/             # EditorWrapper, BlockItem, DatabaseBlock, SlashMenu, TOCMinimap, CommentsDrawer
│       │   │   ├── workspace/          # Sidebar, SidebarPageItem, Topbar, PageHeader, CoverPicker, EmojiPicker, ShareModal, PageOverviewPanel
│       │   │   └── ui/                 # Reusable UI Primitives (Button, Input, Card, Badge, Avatar)
│       │   ├── lib/
│       │   │   ├── axios.ts            # عميل Axios مع Bearer Token Interceptor ومعالجة 401
│       │   │   ├── utils.ts            # دوال مساعدة (cn, formatDate, timeAgo, truncate)
│       │   │   ├── auth-context.tsx    # إدارة حالة المصادقة والتوكن
│       │   │   ├── workspace-context.tsx # إدارة مساحات العمل الحالية وشجرة الصفحات
│       │   │   ├── shell-context.tsx   # إدارة ظهور السايدبار ولوحة النشاط والبحث
│       │   │   ├── theme-context.tsx   # إدارة المظهر الليلي (Notion Dark Mode)
│       │   │   └── toast-context.tsx   # إدارة الإشعارات المنبثقة
│       │   ├── services/api.ts         # دوال الاستدعاء الموحدة لجميع خدمات الـ API
│       │   ├── types/index.ts          # واجهات TypeScript المطابقة لـ Laravel API Resources
│       │   └── middleware.ts           # Next.js Edge Middleware لحماية مسارات مساحة العمل
│       └── .env.local
└── README.md
```

---

## 🚀 أوامر التشغيل السريع (Running the Monorepo)

### 1. تشغيل الباك إند (Laravel API)
```powershell
cd apps/backend

# تشغيل خادم الـ API
php artisan serve --port=8000
```
> يعمل الـ API على: `http://localhost:8000/api/v1`

### 2. تشغيل الفرونت إند (Next.js Frontend)
```powershell
cd apps/frontend

# تشغيل خادم التطوير
npm run dev
```
> يعمل تطبيق الويب على: `http://localhost:3000`

---

## ⚙️ إعدادات متغيرات البيئة (Environment Variables)

### الباك إند (`apps/backend/.env`):
```env
APP_NAME=Ticket
APP_ENV=local
APP_KEY=base64:7aF6mQv4yY6jH6T9wZ2v1L8k5n0P4q7R9s1T3v5X7z0=
APP_DEBUG=true
APP_URL=http://localhost:8000

FRONTEND_URL=http://localhost:3000
SANCTUM_STATEFUL_DOMAINS="localhost:3000,127.0.0.1:3000"

DB_CONNECTION=sqlite
```

### الفرونت إند (`apps/frontend/.env.local`):
```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
```

---

## 🔍 سجل التحقق المعماري الصارم (Zero-Regression Checklist)

| المكون / الميزة | المتطلب | حالة التنفيذ في Monorepo |
|---|---|---|
| **الهيكلية المعمارية** | Monorepo داخل `apps/backend` و `apps/frontend` | ✅ مكتمل ومفحوص 100% |
| **المصادقة الكاملة** | Sanctum Token Auth + Form Validation (422) | ✅ مكتمل (`AuthController` + `auth-context`) |
| **مسارات مساحة العمل المحمية** | App Router `(workspace)` Group + Next.js Middleware | ✅ مكتمل (`apps/frontend/src/middleware.ts`) |
| **شجرة الصفحات المتداخلة** | شجرة لا نهائية مع طي وتوسيع وإضافة سريعة | ✅ مكتمل (`Sidebar.tsx` + `SidebarPageItem.tsx`) |
| **لوحة الأوامر الذكية** | البحث الفوري مع اختصار `Ctrl+K` والتنقل بـ `↑↓` | ✅ مكتمل (`SearchPaletteModal.tsx`) |
| **محرر البلوكات الـ 13** | Paragraph, Headings (H1/H2/H3), Todo, Code, Callout, Quote, Lists, Toggle, Divider, Database | ✅ مكتمل بالكامل مع حفظ التعديلات وإعادة الترتيب بالسحب والإفلات |
| **تلوين الأكواد** | Prism.js لـ 10 لغات مع Syntax Overlay وزر نسخ | ✅ مكتمل ومدمج في `BlockItem.tsx` |
| **قواعد البيانات المضمنة** | 4 عروض: Table, Board (Kanban), List, Gallery مع التصفية والبحث | ✅ مكتمل في `DatabaseBlock.tsx` |
| **التعليقات المترابطة** | إضافة تعليقات وردود وحل/إعادة فتح البلوك | ✅ مكتمل في `CommentsDrawer.tsx` |
| **المشاركة والصلاحيات** | إدارة أدوار `editor` و `commenter` و `viewer` | ✅ مكتمل في `ShareModal.tsx` |
| **سجل نشاط الصفحة** | Page Overview & Activity Trail بالترتيب الزمني | ✅ مكتمل في `PageOverviewPanel.tsx` |
| **أغلفة وأيقونات ووسوم** | Cover Gradients, Emoji Picker, Tags Manager بـ 8 ألوان | ✅ مكتمل في `components/workspace/` |
| **المظهر والخطوط** | Notion Soft Charcoal (`#191919`) + خط Kashif Arabic و RTL | ✅ مكتمل في `globals.css` و `layout.tsx` |

---

## 🧪 نتائج الاختبارات والبناء (Test & Build Status)

- **Laravel Pest Tests:**
  ```text
  Tests:    13 passed (83 assertions)
  Duration: 1.15s
  Status:   100% PASS
  ```
- **Laravel Pint Code Formatter:**
  ```text
  Result: PASSED (Zero formatting issues)
  ```
- **Next.js Production Build (`npm run build`):**
  ```text
  ✓ Compiled successfully in 8.8s
  ✓ Linting and checking validity of types
  ✓ Generating static pages (8/8)
  ✓ Collecting build traces
  ✓ Middleware generated (34.2 kB)
  Status: 0 Errors, 0 Warnings
  ```
