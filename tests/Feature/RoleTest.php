<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private User $userNotHavePermissions;

    private $permissions = [];

    public function setUp(): void
    {
        parent::setUp();

        array_push($this->permissions, Permission::create(['name' => 'roles.index', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'roles.create', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'roles.edit', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'roles.delete', 'guard_name' => 'web']));

        $role = Role::create(['name' => 'admin']);

        $role->givePermissionTo($this->permissions);

        $this->user = User::factory()->create();

        $this->user->assignRole('admin');

        $this->userNotHavePermissions = User::factory()->create();
    }

    public function test_can_view_page_role()
    {
        $response = $this->actingAs($this->user)->get(route('apps.roles.index'));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/Roles/Index')
                ->has('roles')
        );
    }

    public function test_can_search_role()
    {
        $response = $this->actingAs($this->user)->get(route('apps.roles.index', ['q' => 'admin']));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/Roles/Index')
                ->has('roles')
        );
    }

    public function test_cant_view_page_role_when_unauthenticated()
    {
        $response = $this->get(route('apps.roles.index'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_view_page_role_when_dont_have_permission()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.roles.index'));
        $response->assertStatus(403);
    }

    public function test_can_view_page_add_role()
    {
        $response = $this->actingAs($this->user)->get(route('apps.roles.create'));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/Roles/Create')
                ->has('permissions')
        );
    }

    public function test_cant_view_page_add_role_when_unauthenticated()
    {
        $response = $this->get(route('apps.roles.create'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_view_page_add_role_when_dont_have_permission()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.roles.create'));
        $response->assertStatus(403);
    }

    public function test_can_add_role()
    {
        $response = $this->actingAs($this->user)->post(route('apps.roles.store'), [
            'name' => 'test',
            'permissions' => ['roles.index']
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('apps.roles.index'));
    }

    public function test_cant_add_role_when_blank_input()
    {
        $response = $this->actingAs($this->user)->post(route('apps.roles.store'), [
            'name' => '',
            'permissions' => []
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'permissions']);
    }

    public function test_cant_add_role_when_unauthenticated()
    {
        $response = $this->post(route('apps.roles.store'), [
            'name' => 'test',
            'permissions' => ['roles.index']
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_add_role_when_dont_have_permission()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->post(route('apps.roles.store'), [
            'name' => 'test',
            'permissions' => ['roles.index']
        ]);

        $response->assertStatus(403);
    }

    public function test_can_view_page_edit_role()
    {
        $role = Role::create(['name' => 'test']);

        $response = $this->actingAs($this->user)->get(route('apps.roles.edit', $role->id));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/Roles/Edit')
                ->has('role')
                ->where('role.id', $role->id)
                ->has('permissions')
        );
    }

    public function test_cant_view_page_edit_role_when_unauthenticated()
    {
        $role = Role::create(['name' => 'test']);

        $response = $this->get(route('apps.roles.edit', $role->id));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_view_page_edit_role_when_dont_have_permission()
    {
        $role = Role::create(['name' => 'test']);

        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.roles.edit', $role->id));
        $response->assertStatus(403);
    }

    public function test_can_view_page_edit_role_when_not_found()
    {
        $response = $this->actingAs($this->user)->get(route('apps.roles.edit', 9999));
        $response->assertStatus(404);
    }

    public function test_can_update_role()
    {
        $role = Role::create(['name' => 'test']);

        $response = $this->actingAs($this->user)->put(route('apps.roles.update', $role->id), [
            'name' => 'test',
            'permissions' => ['roles.index']
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('apps.roles.index'));
    }

    public function test_cant_update_role_when_blank_input()
    {
        $role = Role::create(['name' => 'test']);

        $response = $this->actingAs($this->user)->put(route('apps.roles.update', $role->id), [
            'name' => '',
            'permissions' => []
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'permissions']);
    }

    public function test_cant_update_role_when_unauthenticated()
    {
        $role = Role::create(['name' => 'test']);

        $response = $this->put(route('apps.roles.update', $role->id), [
            'name' => 'test',
            'permissions' => ['roles.index']
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_update_role_when_dont_have_permission()
    {
        $role = Role::create(['name' => 'test']);

        $response = $this->actingAs($this->userNotHavePermissions)->put(route('apps.roles.update', $role->id), [
            'name' => 'test',
            'permissions' => ['roles.index']
        ]);

        $response->assertStatus(403);
    }

    public function test_cant_edit_role_when_not_found()
    {
        $response = $this->actingAs($this->user)->get(route('apps.roles.edit', 9999));
        $response->assertStatus(404);
    }

    public function test_can_delete_role()
    {
        $role = Role::create(['name' => 'test']);

        $response = $this->actingAs($this->user)->delete(route('apps.roles.destroy', $role->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('apps.roles.index'));
        $role = Role::find($role->id);
        $this->assertNull($role);
    }

    public function test_cant_delete_role_when_unauthenticated()
    {
        $role = Role::create(['name' => 'test']);

        $response = $this->delete(route('apps.roles.destroy', $role->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_delete_role_when_dont_have_permission()
    {
        $role = Role::create(['name' => 'test']);

        $response = $this->actingAs($this->userNotHavePermissions)->delete(route('apps.roles.destroy', $role->id));

        $response->assertStatus(403);
    }

    public function test_cant_delete_role_when_not_found()
    {
        $response = $this->actingAs($this->user)->delete(route('apps.roles.destroy', 9999));
        $response->assertStatus(404);
    }
}
