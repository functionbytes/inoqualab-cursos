<?php

namespace App\Http\Requests\Managers\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHoursSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        $rules = [
            'hoursswitch' => ['nullable'],
            'hourstitle' => ['nullable', 'string', 'max:255'],
            'hourssubtitle' => ['nullable', 'string', 'max:255'],
        ];

        for ($i = 1; $i <= 7; $i++) {
            $rules["bussinessid{$i}"] = ['nullable', 'integer'];
            $rules["bussiness{$i}"] = ['nullable', 'string', 'max:100'];
            $rules["starttime{$i}"] = ['nullable', 'string', 'max:20'];
            $rules["endtime{$i}"] = ['nullable', 'string', 'max:20'];
            $rules["status{$i}"] = ['nullable'];
        }

        return $rules;
    }
}
