<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware([
            "permission:roles.index|roles.create|roles.edit|roles.delete"
        ]);
    }

    public function index()
    {
        $roles = Role::when(request()->q, function ($roles) {
            $roles = $roles->where("name", "like", "%" . request()->q . "%");
        })->with("permissions")->latest()->paginate(5);

        return inertia("Apps/Roles/Index", [
            "roles" => $roles
        ]);
    }

    public function create()
    {
        $permissions = Permission::all();

        return inertia("Apps/Roles/Create", [
            "permissions" => $permissions
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            "name" => ["required", "string", "unique:roles,name"],
            "permissions" => ["required", "array", "min:1"],
            "permissions.*" => ["string", "exists:permissions,name"]
        ]);

        $role = Role::create([
            "name" => $request->name
        ]);

        $role->givePermissionTo($request->permissions);

        return redirect()->route("apps.roles.index");
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();

        return inertia("Apps/Roles/Edit", [
            "role" => $role->load("permissions"),
            "permissions" => $permissions
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $this->validate($request, [
            "name" => ["required", "string", "unique:roles,name," . $role->id],
            "permissions" => ["required", "array", "min:1"],
            "permissions.*" => ["string", "exists:permissions,name"]
        ]);

        $role->update([
            "name" => $request->name
        ]);

        $role->syncPermissions($request->permissions);

        return redirect()->route("apps.roles.index");
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route("apps.roles.index");
    }
}
