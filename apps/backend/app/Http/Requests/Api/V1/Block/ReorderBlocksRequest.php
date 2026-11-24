<?php

namespace App\Http\Requests\Api\V1\Block;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReorderBlocksRequest extends FormRequest
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
            'blocks' => ['required', 'array'],
            'blocks.*.id' => ['required', 'exists:blocks,id'],
            'blocks.*.position' => ['required', 'integer'],
        ];
    }
}
