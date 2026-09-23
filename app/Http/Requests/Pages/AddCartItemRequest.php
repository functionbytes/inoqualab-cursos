<?php

namespace App\Http\Requests\Pages;

use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:course,bundle'],
            'slack' => ['required', 'string'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }
}
