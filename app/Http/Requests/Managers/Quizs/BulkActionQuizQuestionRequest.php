<?php

namespace App\Http\Requests\Managers\Quizs;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionQuizQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('action') === 'delete' ? 'quizzes.delete' : 'quizzes.update';

        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:quiz_questions,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'preguntas',
        ];
    }
}
