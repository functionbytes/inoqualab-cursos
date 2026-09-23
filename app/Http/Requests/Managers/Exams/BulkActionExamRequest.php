<?php

namespace App\Http\Requests\Managers\Exams;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('action') === 'delete' ? 'exams.delete' : 'exams.update';

        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:exam_topics,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'examenes',
        ];
    }
}
