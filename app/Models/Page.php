<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'workspace_id',
        'parent_id',
        'title',
        'icon',
        'cover_image',
        'cover_color',
        'is_favorite',
        'created_by',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
    ];

    // =================== العلاقات (Relations) ===================

    /** مساحة العمل التابعة لها الصفحة */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /** الصفحة الأب (إذا كانت صفحة فرعية) */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    /** الصفحات الفرعية (Nested Sub-pages) */
    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id');
    }

    /** المستخدم الذي أنشأ الصفحة */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** البلوكات (محتوى الصفحة) */
    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class)->orderBy('position', 'asc');
    }

    /** وسوم الصفحة */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'page_tag');
    }

    /** سجل نشاط الصفحة */
    public function activities(): HasMany
    {
        return $this->hasMany(PageActivity::class)->latest();
    }

    /** قواعد البيانات المضمنة داخل الصفحة */
    public function databases(): HasMany
    {
        return $this->hasMany(NotionDatabase::class, 'page_id');
    }

    // =================== Scopes ===================

    /** فلترة الصفحات المفضلة فقط */
    public function scopeFavorites(Builder $query): Builder
    {
        return $query->where('is_favorite', true);
    }

    // =================== Helpers ===================

    /**
     * خيارات ألوان غلاف الصفحة (Tailwind gradient classes)
     *
     * @return array<string, array{label: string, class: string}>
     */
    public static function coverColors(): array
    {
        return [
            'gradient-blue' => ['label' => 'أزرق', 'class' => 'bg-gradient-to-r from-blue-400 to-indigo-500'],
            'gradient-purple' => ['label' => 'بنفسجي', 'class' => 'bg-gradient-to-r from-purple-400 to-pink-500'],
            'gradient-green' => ['label' => 'أخضر', 'class' => 'bg-gradient-to-r from-green-400 to-teal-500'],
            'gradient-orange' => ['label' => 'برتقالي', 'class' => 'bg-gradient-to-r from-orange-400 to-red-400'],
            'gradient-gray' => ['label' => 'رمادي', 'class' => 'bg-gradient-to-r from-gray-300 to-gray-500'],
            'gradient-yellow' => ['label' => 'ذهبي', 'class' => 'bg-gradient-to-r from-yellow-300 to-orange-400'],
            'gradient-teal' => ['label' => 'تيل', 'class' => 'bg-gradient-to-r from-teal-400 to-cyan-500'],
            'gradient-rose' => ['label' => 'وردي', 'class' => 'bg-gradient-to-r from-rose-400 to-pink-600'],
        ];
    }
}
