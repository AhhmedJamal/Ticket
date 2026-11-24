<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'category',
        'blocks_data',
        'is_default',
    ];

    protected $casts = [
        'blocks_data' => 'array',
        'is_default' => 'boolean',
    ];

    /** فئات القوالب */
    public static function categories(): array
    {
        return [
            'general' => ['label' => 'عام', 'icon' => '📄'],
            'project' => ['label' => 'مشروع', 'icon' => '🗂️'],
            'meeting' => ['label' => 'اجتماع', 'icon' => '🤝'],
            'notes' => ['label' => 'ملاحظات', 'icon' => '📝'],
            'goals' => ['label' => 'أهداف', 'icon' => '🎯'],
        ];
    }
}
