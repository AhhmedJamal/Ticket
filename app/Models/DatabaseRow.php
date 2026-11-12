<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatabaseRow extends Model
{
    protected $fillable = [
        'notion_database_id',
        'values',
        'position',
    ];

    protected $casts = [
        'values' => 'array',
    ];

    /** القاعدة التي ينتمي إليها هذا السجل */
    public function notionDatabase(): BelongsTo
    {
        return $this->belongsTo(NotionDatabase::class, 'notion_database_id');
    }
}
