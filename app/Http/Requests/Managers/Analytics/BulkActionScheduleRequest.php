<?php

namespace App\Http\Requests\Managers\Analytics;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('analytics.update');
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:activate,deactivate,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:analytics_report_schedules,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'reportes',
        ];
    }
}
