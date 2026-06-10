<?php

namespace App\Http\Requests\Supports;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateSupportNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'support';
    }

    public function rules(): array
    {
        return [
            'mail_notification' => ['nullable'],
            'inscription_notification' => ['nullable'],
            'invoice_notification' => ['nullable'],
        ];
    }
}
