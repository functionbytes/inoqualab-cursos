<?php

namespace App\Http\Requests\Managers\Blogs;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionBlogCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('action') === 'delete' ? 'blogs.delete' : 'blogs.update';

        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:blog_categories,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'categorías',
        ];
    }
}
