<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageActivity extends Model
{
    protected $fillable = [
        'page_id',
        'user_id',
        'action',
        'description',
    ];

    /** الصفحة المرتبطة بهذا النشاط */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /** المستخدم الذي قام بالنشاط */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * أيقونات الأفعال المختلفة
     *
     * @return array<string, string>
     */
    public static function actionIcons(): array
    {
        return [
            'created' => '✨',
            'updated_title' => '✏️',
            'updated_icon' => '🎨',
            'added_block' => '➕',
            'deleted_block' => '🗑️',
            'changed_cover' => '🖼️',
            'favorited' => '⭐',
            'unfavorited' => '☆',
        ];
    }
}
