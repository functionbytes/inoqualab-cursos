<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Endpoint público (routes/web.php, sin auth): recibe beacons de Core Web
 * Vitals del navegador de visitantes anónimos del sitio.
 */
class StoreSeoWebVitalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'metric' => ['required', 'string', 'in:LCP,INP,CLS,FCP,TTFB'],
            'value' => ['required', 'numeric', 'min:0'],
            'url' => ['required', 'string', 'max:500'],
            'device' => ['nullable', 'string', 'in:mobile,desktop,unknown'],
            'connection' => ['nullable', 'string', 'max:16'],
            'navigation_type' => ['nullable', 'string', 'max:32'],
        ];
    }
}
