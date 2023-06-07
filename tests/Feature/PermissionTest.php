<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private $permissions = [];

    public function setUp(): void
    {
        parent::setUp();

        array_push($this->permissions, Permission::create(['name' => 'permissions.index', 'guard_name' => 'web']));

        $role = Role::create(['name' => 'admin']);

        $role->givePermissionTo($this->permissions);

        $this->user = User::factory()->create();

        $this->user->assignRole('admin');
    }

    public function test_can_view_page_permission()
    {
        $response = $this->actingAs($this->user)->get(route('apps.permissions.index'));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/Permissions/Index')
                ->has('permissions')
        );
    }

    public function test_can_search_permission()
    {
        $response = $this->actingAs($this->user)->get(route('apps.permissions.index', ['q' => 'index']));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/Permissions/Index')
                ->has('permissions')
        );
    }

    public function test_cant_view_page_permission_when_unauthenticated()
    {
        $response = $this->get(route('apps.permissions.index'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_view_page_permission_when_not_have_permission()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('apps.permissions.index'));
        $response->assertStatus(403);
    }
}
