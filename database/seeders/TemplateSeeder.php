<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'ملاحظات الاجتماع',
                'description' => 'قالب جاهز لتدوين ملاحظات الاجتماعات بشكل منظم',
                'icon' => '🤝',
                'category' => 'meeting',
                'is_default' => true,
                'blocks_data' => [
                    ['type' => 'heading_1', 'content' => ['text' => 'ملاحظات الاجتماع']],
                    ['type' => 'paragraph', 'content' => ['text' => '📅 التاريخ: ']],
                    ['type' => 'paragraph', 'content' => ['text' => '👥 الحاضرون: ']],
                    ['type' => 'divider', 'content' => ['text' => '']],
                    ['type' => 'heading_2', 'content' => ['text' => '🎯 أهداف الاجتماع']],
                    ['type' => 'bulleted_list', 'content' => ['text' => 'الهدف الأول']],
                    ['type' => 'bulleted_list', 'content' => ['text' => 'الهدف الثاني']],
                    ['type' => 'heading_2', 'content' => ['text' => '📋 نقاط النقاش']],
                    ['type' => 'numbered_list', 'content' => ['text' => 'النقطة الأولى']],
                    ['type' => 'heading_2', 'content' => ['text' => '✅ المهام والإجراءات']],
                    ['type' => 'todo', 'content' => ['text' => 'مهمة ما بعد الاجتماع', 'checked' => false]],
                    ['type' => 'heading_2', 'content' => ['text' => '📝 ملاحظات إضافية']],
                    ['type' => 'paragraph', 'content' => ['text' => '']],
                ],
            ],
            [
                'name' => 'خطة مشروع',
                'description' => 'هيكل شامل لتخطيط وإدارة المشاريع',
                'icon' => '🗂️',
                'category' => 'project',
                'is_default' => true,
                'blocks_data' => [
                    ['type' => 'heading_1', 'content' => ['text' => 'اسم المشروع']],
                    ['type' => 'callout', 'content' => ['text' => 'وصف مختصر للمشروع وهدفه الرئيسي']],
                    ['type' => 'divider', 'content' => ['text' => '']],
                    ['type' => 'heading_2', 'content' => ['text' => '🎯 الأهداف']],
                    ['type' => 'bulleted_list', 'content' => ['text' => 'الهدف الأول']],
                    ['type' => 'bulleted_list', 'content' => ['text' => 'الهدف الثاني']],
                    ['type' => 'heading_2', 'content' => ['text' => '📅 الجدول الزمني']],
                    ['type' => 'paragraph', 'content' => ['text' => '🟢 البداية: ']],
                    ['type' => 'paragraph', 'content' => ['text' => '🔴 النهاية: ']],
                    ['type' => 'heading_2', 'content' => ['text' => '📌 المهام الرئيسية']],
                    ['type' => 'todo', 'content' => ['text' => 'مرحلة التخطيط', 'checked' => false]],
                    ['type' => 'todo', 'content' => ['text' => 'مرحلة التنفيذ', 'checked' => false]],
                    ['type' => 'todo', 'content' => ['text' => 'مرحلة المراجعة والإطلاق', 'checked' => false]],
                    ['type' => 'heading_2', 'content' => ['text' => '⚠️ المخاطر والتحديات']],
                    ['type' => 'bulleted_list', 'content' => ['text' => 'التحدي الأول وكيفية التعامل معه']],
                    ['type' => 'heading_2', 'content' => ['text' => '📝 ملاحظات']],
                    ['type' => 'paragraph', 'content' => ['text' => '']],
                ],
            ],
            [
                'name' => 'مذكرة يومية',
                'description' => 'قالب للكتابة اليومية والتفكير',
                'icon' => '📝',
                'category' => 'notes',
                'is_default' => true,
                'blocks_data' => [
                    ['type' => 'heading_1', 'content' => ['text' => '📅 يوم ']],
                    ['type' => 'heading_2', 'content' => ['text' => '☀️ كيف حالي اليوم؟']],
                    ['type' => 'paragraph', 'content' => ['text' => '']],
                    ['type' => 'heading_2', 'content' => ['text' => '🎯 أهداف اليوم']],
                    ['type' => 'todo', 'content' => ['text' => '', 'checked' => false]],
                    ['type' => 'todo', 'content' => ['text' => '', 'checked' => false]],
                    ['type' => 'todo', 'content' => ['text' => '', 'checked' => false]],
                    ['type' => 'heading_2', 'content' => ['text' => '💭 أفكار وملاحظات']],
                    ['type' => 'paragraph', 'content' => ['text' => '']],
                    ['type' => 'heading_2', 'content' => ['text' => '🌙 تأمل نهاية اليوم']],
                    ['type' => 'quote', 'content' => ['text' => 'ما الذي تعلمته اليوم؟']],
                    ['type' => 'paragraph', 'content' => ['text' => '']],
                ],
            ],
            [
                'name' => 'قائمة أهداف',
                'description' => 'تتبع أهدافك الكبيرة والصغيرة',
                'icon' => '🎯',
                'category' => 'goals',
                'is_default' => true,
                'blocks_data' => [
                    ['type' => 'heading_1', 'content' => ['text' => '🎯 أهدافي']],
                    ['type' => 'callout', 'content' => ['text' => '"كل هدف كبير يبدأ بخطوة صغيرة."']],
                    ['type' => 'divider', 'content' => ['text' => '']],
                    ['type' => 'heading_2', 'content' => ['text' => '🏆 الأهداف السنوية']],
                    ['type' => 'todo', 'content' => ['text' => 'الهدف الأول', 'checked' => false]],
                    ['type' => 'todo', 'content' => ['text' => 'الهدف الثاني', 'checked' => false]],
                    ['type' => 'heading_2', 'content' => ['text' => '📅 أهداف الشهر']],
                    ['type' => 'todo', 'content' => ['text' => 'هدف هذا الشهر', 'checked' => false]],
                    ['type' => 'heading_2', 'content' => ['text' => '📆 أهداف الأسبوع']],
                    ['type' => 'todo', 'content' => ['text' => 'هدف هذا الأسبوع', 'checked' => false]],
                    ['type' => 'heading_2', 'content' => ['text' => '📝 ملاحظات']],
                    ['type' => 'paragraph', 'content' => ['text' => '']],
                ],
            ],
            [
                'name' => 'وثيقة تقنية',
                'description' => 'قالب لتوثيق المشاريع والأكواد البرمجية',
                'icon' => '💻',
                'category' => 'general',
                'is_default' => true,
                'blocks_data' => [
                    ['type' => 'heading_1', 'content' => ['text' => 'اسم الوثيقة']],
                    ['type' => 'paragraph', 'content' => ['text' => 'وصف مختصر']],
                    ['type' => 'divider', 'content' => ['text' => '']],
                    ['type' => 'heading_2', 'content' => ['text' => '📋 نظرة عامة']],
                    ['type' => 'paragraph', 'content' => ['text' => '']],
                    ['type' => 'heading_2', 'content' => ['text' => '🛠 المتطلبات']],
                    ['type' => 'bulleted_list', 'content' => ['text' => 'المتطلب الأول']],
                    ['type' => 'heading_2', 'content' => ['text' => '📦 التثبيت والإعداد']],
                    ['type' => 'code', 'content' => ['text' => '# اكتب الأوامر هنا']],
                    ['type' => 'heading_2', 'content' => ['text' => '📖 الاستخدام']],
                    ['type' => 'code', 'content' => ['text' => '// مثال على الاستخدام']],
                    ['type' => 'heading_2', 'content' => ['text' => '⚠️ ملاحظات مهمة']],
                    ['type' => 'callout', 'content' => ['text' => 'تنبيه مهم']],
                ],
            ],
        ];

        foreach ($templates as $templateData) {
            Template::updateOrCreate(
                ['name' => $templateData['name']],
                $templateData
            );
        }
    }
}
