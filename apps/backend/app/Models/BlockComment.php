<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlockComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'block_id',
        'user_id',
        'parent_id',
        'content',
        'is_resolved',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(BlockComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(BlockComment::class, 'parent_id')->orderBy('created_at', 'asc');
    }
}
