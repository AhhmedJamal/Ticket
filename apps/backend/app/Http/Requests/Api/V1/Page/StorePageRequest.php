<?php

namespace App\Http\Requests\Api\V1\Page;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePageRequest extends FormRequest
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
            'title' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:pages,id'],
            'icon' => ['nullable', 'string', 'max:20'],
            'template_id' => ['nullable', 'exists:templates,id'],
        ];
    }
}
