<?php

namespace App\Http\Requests\Api\V1\Block;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $allowedTypes = [
            'paragraph', 'heading_1', 'heading_2', 'heading_3', 'heading',
            'todo', 'code', 'callout', 'quote',
            'bulleted_list', 'numbered_list', 'divider',
            'database', 'image', 'toggle',
        ];

        return [
            'type' => ['required', 'string', 'in:'.implode(',', $allowedTypes)],
            'text' => ['nullable', 'string'],
            'position' => ['nullable', 'integer'],
        ];
    }
}
