<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    protected $fillable = ['name', 'slug', 'user_id'];

    // المالك الأساسي لمساحة العمل
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // الأعضاء المشتركون في مساحة العمل
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    // الصفحات الموجودة داخل مساحة العمل
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }
}