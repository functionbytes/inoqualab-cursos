<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Gestión de roles y sus permisos (Spatie) para el panel manager.
 * Adaptado del módulo Role de atlelitetransport a la estructura monolítica
 * de training: nombre + matriz de permisos en una sola pantalla.
 */
class RolesController extends Controller
{
    /** Roles del sistema que no se pueden renombrar ni eliminar. */
    private const PROTECTED_ROLES = [
        'manager', 'customer', 'support', 'distributor', 'enterprise', 'accounting',
    ];

    public function index(Request $request): View
    {
        $searchKey = $request->input('search');

        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->when($searchKey, fn ($q) => $q->where('name', 'like', "%{$searchKey}%"))
            ->orderBy('name')
            ->paginate(paginationNumber());

        return view('managers.views.settings.roles.index', [
            'roles' => $roles,
            'searchKey' => $searchKey,
            'protectedRoles' => self::PROTECTED_ROLES,
            'totalPermissions' => Permission::count(),
        ]);
    }

    public function create(): View
    {
        return view('managers.views.settings.roles.form', [
            'role' => null,
            'permissions' => Permission::orderBy('name')->get(),
            'rolePermissionIds' => [],
            'protectedRoles' => self::PROTECTED_ROLES,
        ]);
    }

    public function edit(int $id): View
    {
        $role = Role::findOrFail($id);

        return view('managers.views.settings.roles.form', [
            'role' => $role,
            'permissions' => Permission::orderBy('name')->get(),
            'rolePermissionIds' => $role->permissions->pluck('id')->all(),
            'protectedRoles' => self::PROTECTED_ROLES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRole($request);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($this->permissionsFrom($request));

        $this->forgetCache();

        return redirect()
            ->route('manager.roles.edit', $role->id)
            ->with('success', 'Rol creado correctamente.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $role = Role::findOrFail($id);
        $isProtected = in_array($role->name, self::PROTECTED_ROLES, true);

        $data = $this->validateRole($request, $role->id, skipName: $isProtected);

        if (! $isProtected && isset($data['name'])) {
            $role->update(['name' => $data['name']]);
        }

        $role->syncPermissions($this->permissionsFrom($request));

        $this->forgetCache();

        return redirect()
            ->route('manager.roles.edit', $role->id)
            ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            $message = 'Este es un rol del sistema y no se puede eliminar.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        if ($role->users()->exists()) {
            $message = 'No se puede eliminar un rol con usuarios asignados.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        $role->delete();
        $this->forgetCache();

        $message = 'Rol eliminado correctamente.';

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $message])
            : redirect()->route('manager.roles.index')->with('success', $message);
    }

    /**
     * @return array{name?: string}
     */
    private function validateRole(Request $request, ?int $ignoreId = null, bool $skipName = false): array
    {
        $rules = [
            'permissions' => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];

        if (! $skipName) {
            $unique = 'unique:roles,name'.($ignoreId ? ",{$ignoreId}" : '');
            $rules['name'] = ['required', 'string', 'max:125', $unique];
        }

        return $request->validate($rules, [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.unique' => 'Ya existe un rol con ese nombre.',
        ]);
    }

    private function permissionsFrom(Request $request)
    {
        return Permission::whereIn('id', $request->input('permissions', []))
            ->where('guard_name', 'web')
            ->get();
    }

    private function forgetCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
