<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware([
            "permission:permissions.index"
        ]);
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $permissions = Permission::when(request()->q, function ($permissions) {
            $permissions = $permissions->where("name", "like", "%" . request()->q . "%");
        })->latest()->paginate(5);

        return Inertia::render("Apps/Permissions/Index", [
            "permissions" => $permissions
        ]);
    }
}
