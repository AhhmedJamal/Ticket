<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotionDatabase extends Model
{
    protected $table = 'notion_databases';

    protected $fillable = [
        'page_id',
        'name',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    /** الصفحة التي تحتوي هذه القاعدة */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /** صفوف / سجلات القاعدة */
    public function rows(): HasMany
    {
        return $this->hasMany(DatabaseRow::class, 'notion_database_id')->orderBy('position');
    }

    /**
     * أنواع الأعمدة المتاحة
     *
     * @return array<string, string>
     */
    public static function columnTypes(): array
    {
        return [
            'text' => 'نص',
            'number' => 'رقم',
            'checkbox' => 'خانة اختيار',
            'date' => 'تاريخ',
            'select' => 'قائمة منفردة',
            'multi_select' => 'قائمة متعددة',
            'url' => 'رابط URL',
            'email' => 'بريد إلكتروني',
        ];
    }
}
