<?php

namespace App\Http\Requests\Managers\Courses;

use Illuminate\Foundation\Http\FormRequest;

class ReorderChapterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('courses.update');
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'ids' => 'temas',
        ];
    }
}
