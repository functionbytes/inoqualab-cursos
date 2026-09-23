<?php

namespace App\Http\Requests\Managers\Settings\Roles;

use App\Http\Controllers\Managers\Settings\RolesController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('roles.update');
    }

    public function rules(): array
    {
        $role = Role::find($this->route('id'));
        $isProtected = $role && in_array($role->name, RolesController::PROTECTED_ROLES, true);

        $rules = [
            'permissions' => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];

        // Los roles protegidos (superadmin, etc.) no se pueden renombrar desde
        // esta pantalla -- ver RolesController::update().
        if (! $isProtected) {
            $rules['name'] = ['required', 'string', 'max:125', Rule::unique('roles', 'name')->ignore($role?->id)];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.unique' => 'Ya existe un rol con ese nombre.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'permissions' => 'permisos',
        ];
    }
}
