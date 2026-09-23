<?php

namespace App\Http\Requests\Managers\Newsletter;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionNewsletterCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('newsletters.delete');
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:newsletter_campaigns,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'campañas',
        ];
    }
}
