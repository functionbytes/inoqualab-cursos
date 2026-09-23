<?php

namespace App\Http\Requests\Managers\Blogs;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlogThumbnailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('blogs.update');
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'file' => 'imagen',
        ];
    }
}
