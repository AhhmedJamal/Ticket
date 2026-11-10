<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'workspace_id',
        'parent_id',
        'title',
        'icon',
        'created_by'
    ];

    // مساحة العمل التابعة لها الصفحة
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    // الصفحة الأب (إذا كانت صفحة فرعية)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    // الصفحات الفرعية (Nested Sub-pages)
    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id');
    }

    // المستخدم الذي أنشأ الصفحة
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // البلوكات (محتوى الصفحة)
    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class)->orderBy('position', 'asc');
    }
}