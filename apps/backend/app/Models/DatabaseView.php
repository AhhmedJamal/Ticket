<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatabaseView extends Model
{
    use HasFactory;

    protected $fillable = [
        'notion_database_id',
        'name',
        'type',
        'filter_config',
        'sort_config',
        'visible_properties',
        'position',
    ];

    protected $casts = [
        'filter_config' => 'array',
        'sort_config' => 'array',
        'visible_properties' => 'array',
        'position' => 'integer',
    ];

    public function database(): BelongsTo
    {
        return $this->belongsTo(NotionDatabase::class, 'notion_database_id');
    }
}
