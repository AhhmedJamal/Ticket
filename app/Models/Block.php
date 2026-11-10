<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Block extends Model
{
    protected $fillable = [
        'page_id',
        'type',
        'content',
        'position'
    ];

    protected $casts = [
        'content' => 'array', // لتخزين وقراءة البيانات بصيغة JSON تلقائياً
    ];

    // الصفحة التي ينتمي إليها البلوك
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}