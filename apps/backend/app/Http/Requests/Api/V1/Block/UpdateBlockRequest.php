<?php

namespace App\Http\Requests\Api\V1\Block;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBlockRequest extends FormRequest
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
        return [
            'text' => ['nullable', 'string'],
            'checked' => ['nullable', 'boolean'],
            'open' => ['nullable', 'boolean'],
            'color' => ['nullable', 'string', 'max:30'],
            'language' => ['nullable', 'string', 'max:50'],
            'link_page_id' => ['nullable', 'integer', 'exists:pages,id'],
            'type' => ['sometimes', 'string'],
            'position' => ['sometimes', 'integer'],
        ];
    }
}
