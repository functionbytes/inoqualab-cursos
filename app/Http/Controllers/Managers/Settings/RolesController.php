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
        'superadmin', 'manager', 'customer', 'support', 'distributor', 'enterprise', 'accounting',
    ];

    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('roles.view'), 403);

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

    /**
     * Matriz comparativa: qué permiso tiene cada rol (filas = permisos por
     * módulo, columnas = roles). Solo lectura — para ver de un vistazo qué
     * puede hacer un rol y qué no frente a los demás.
     */
    public function matrix(): View
    {
        abort_unless(auth()->user()->can('roles.view'), 403);

        $roles = Role::query()->with('permissions:id')->orderBy('name')->get();

        $permissionsByModule = Permission::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->groupBy(fn (Permission $p) => explode('.', $p->name)[0]);

        $rolePermissionIds = $roles->mapWithKeys(
            fn (Role $role) => [$role->id => $role->permissions->pluck('id')->flip()]
        );

        return view('managers.views.settings.roles.matrix', [
            'roles' => $roles,
            'permissionsByModule' => $permissionsByModule,
            'rolePermissionIds' => $rolePermissionIds,
            'totalPermissions' => $permissionsByModule->flatten()->count(),
            'protectedRoles' => self::PROTECTED_ROLES,
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('roles.create'), 403);

        return view('managers.views.settings.roles.form', [
            'role' => null,
            'permissions' => Permission::orderBy('name')->get(),
            'rolePermissionIds' => [],
            'protectedRoles' => self::PROTECTED_ROLES,
        ]);
    }

    public function edit(int $id): View
    {
        abort_unless(auth()->user()->can('roles.update'), 403);

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
        abort_unless(auth()->user()->can('roles.create'), 403);
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
        abort_unless(auth()->user()->can('roles.update'), 403);
        $role = Role::findOrFail($id);
        $isProtected = in_array($role->name, self::PROTECTED_ROLES, true);

        $data = $this->validateRole($request, $role->id, skipName: $isProtected);

        if (! $isProtected && isset($data['name'])) {
            $role->update(['name' => $data['name']]);
        }

        // Los roles protegidos (superadmin, etc.) no cambian sus permisos desde
        // esta pantalla: evita que un holder de roles.update reescriba superadmin.
        if (! $isProtected) {
            $role->syncPermissions($this->mergedPermissionsFor($request, $role));
        }

        $this->forgetCache();

        return redirect()
            ->route('manager.roles.edit', $role->id)
            ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        abort_unless(auth()->user()->can('roles.delete'), 403);
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
     * Eliminación en lote. Replica exactamente las protecciones de destroy():
     * los roles del sistema (PROTECTED_ROLES) y los roles con usuarios
     * asignados no se pueden eliminar — en vez de rechazar todo el lote, se
     * omiten y se informa cuántos se omitieron.
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:roles,id'],
        ]);

        abort_unless(auth()->user()->can('roles.delete'), 403);

        $roles = Role::whereIn('id', $request->ids)->withCount('users')->get();

        $deletable = $roles->filter(fn (Role $role) => ! in_array($role->name, self::PROTECTED_ROLES, true)
            && $role->users_count === 0
        );

        $skipped = $roles->count() - $deletable->count();

        $deletable->each->delete();

        $this->forgetCache();

        $message = $deletable->count().' rol(es) eliminados.';
        if ($skipped > 0) {
            $message .= ' '.$skipped.' rol(es) omitidos (del sistema o con usuarios asignados).';
        }

        return response()->json(['success' => true, 'message' => $message]);
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
        $requested = Permission::whereIn('id', $request->input('permissions', []))
            ->where('guard_name', 'web')
            ->get();

        // Techo de privilegios: no se pueden otorgar permisos que el actor no
        // posee (evita que quien tenga roles.create/update se auto-eleve a superadmin).
        $actor = auth()->user();

        return $requested->filter(fn ($permission) => $actor->can($permission->name))->values();
    }

    /**
     * Set de permisos a sincronizar en update(): conserva los permisos actuales
     * que el actor NO puede otorgar (para no quitárselos a un rol más poderoso) y
     * añade los solicitados que sí puede otorgar. Impide escalar Y despojar.
     */
    private function mergedPermissionsFor(Request $request, Role $role)
    {
        $actor = auth()->user();

        $preserved = $role->permissions->filter(fn ($permission) => ! $actor->can($permission->name));

        return $preserved->merge($this->permissionsFrom($request))->unique('id')->values();
    }

    private function forgetCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
