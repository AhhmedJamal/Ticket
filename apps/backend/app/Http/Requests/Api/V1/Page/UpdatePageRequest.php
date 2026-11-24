<?php

namespace App\Http\Requests\Api\V1\Page;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePageRequest extends FormRequest
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
            'icon' => ['nullable', 'string', 'max:20'],
            'cover_image' => ['nullable', 'string', 'max:255'],
            'cover_color' => ['nullable', 'string', 'max:50'],
        ];
    }
}
