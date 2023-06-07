<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware([
            "permission:users.index|users.create|users.edit|users.delete"
        ]);
    }

    public function index()
    {
        $users = User::when(request()->q, function ($users) {
            $users = $users->where("name", "like", "%" . request()->q . "%");
        })->with("roles")->latest()->paginate(5);

        return Inertia::render("Apps/User/Index", [
            "users" => $users
        ]);
    }

    public function create()
    {
        $roles = Role::all();

        return Inertia::render("Apps/User/Create", ["roles" => $roles]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            "name" => ["required", "string"],
            "email" => ["required", "email", "unique:users,email"],
            "password" => ["required", "min:8", "confirmed"],
            "roles" => ["required", "array", "min:1"],
            "roles.*" => ["string", "exists:roles,name"]
        ]);

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => bcrypt($request->password)
        ]);

        $user->assignRole($request->roles);

        return redirect()->route("apps.users.index");
    }

    public function edit(User $user)
    {
        $roles = Role::all();

        return Inertia::render("Apps/User/Edit", ["user" => $user->load("roles"), "roles" => $roles]);
    }

    public function update(Request $request, User $user)
    {
        $this->validate($request, [
            "name" => ["required", "string"],
            "email" => ["required", "email"],
            "password" => ["nullable", "confirmed", "min:8"],
            "roles" => ["required", "array", "min:1"],
            "roles.*" => ["string", "exists:roles,name"]
        ]);

        if ($request->email == $user->email && $request->name == $user->name  && $request->password == "" && $user->roles->pluck("name")->toArray() == $request->roles) {
            return redirect()->back()->withInput()->withErrors([
                "name_and_email" => "Name or Email must be different",
            ]);
        }

        if ($request->password == "") {
            $user->update([
                "name" => $request->name,
                "email" => $request->email
            ]);
        } else {
            $user->update([
                "name" => $request->name,
                "email" => $request->email,
                "password" => bcrypt($request->password)
            ]);
        }

        $user->syncRoles($request->roles);

        return redirect()->route("apps.users.index");
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route("apps.users.index");
    }
}
