<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = [
        'workspace_id',
        'name',
        'color',
    ];

    /** مساحة العمل التابعة لها الوسمة */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /** الصفحات التي تحمل هذه الوسمة */
    public function pages(): BelongsToMany
    {
        return $this->belongsToMany(Page::class, 'page_tag');
    }

    /**
     * خريطة ألوان الوسوم لاستخدامها في الواجهة
     *
     * @return array<string, string>
     */
    public static function colorClasses(): array
    {
        return [
            'gray' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
            'red' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200',
            'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-200',
            'yellow' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-200',
            'green' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200',
            'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200',
            'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-200',
            'pink' => 'bg-pink-100 text-pink-700 dark:bg-pink-900 dark:text-pink-200',
        ];
    }
}
