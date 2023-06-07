<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private User $userNotHavePermissions;

    private Role $role;

    private $permissions = [];

    public function setUp(): void
    {
        parent::setUp();

        array_push($this->permissions, Permission::create(['name' => 'users.index', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'users.create', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'users.edit', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'users.delete', 'guard_name' => 'web']));

        $this->role = Role::create(['name' => 'admin']);

        $this->role->givePermissionTo($this->permissions);

        $this->user = User::factory()->create();

        $this->user->assignRole('admin');

        $this->userNotHavePermissions = User::factory()->create();
    }

    public function test_can_view_page_user()
    {
        $response = $this->actingAs($this->user)->get(route('apps.users.index'));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/User/Index')
                ->has('users')
        );
    }

    public function test_can_search_user()
    {
        $response = $this->actingAs($this->user)->get(route('apps.users.index', ['q' => $this->user->name]));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/User/Index')
                ->has('users')
        );
    }

    public function test_cant_view_page_user_when_unauthenticated()
    {
        $response = $this->get(route('apps.users.index'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_view_page_user_when_dont_have_permission()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.users.index'));
        $response->assertStatus(403);
    }

    public function test_can_view_page_user_create()
    {
        $response = $this->actingAs($this->user)->get(route('apps.users.create'));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/User/Create')
                ->has('roles')
        );
    }

    public function test_cant_view_page_user_create_when_unauthenticated()
    {
        $response = $this->get(route('apps.users.create'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_view_page_user_create_when_dont_have_permission()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.users.create'));
        $response->assertStatus(403);
    }

    public function test_can_add_user()
    {
        $response = $this->actingAs($this->user)->post(route('apps.users.store'), [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'roles' => Role::all()->pluck('name')->toArray(),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('apps.users.index'));
    }

    public function test_cant_add_user_when_unauthenticated()
    {
        $response = $this->post(route('apps.users.store'), [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'roles' => Role::all()->pluck('name')->toArray(),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_add_user_when_dont_have_permission()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->post(route('apps.users.store'), [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'roles' => Role::all()->pluck('name')->toArray(),
        ]);

        $response->assertStatus(403);
    }

    public function test_can_add_user_when_input_blank()
    {
        $response = $this->actingAs($this->user)->post(route('apps.users.store'), [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'roles' => [],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'email', 'password', 'roles']);
    }

    public function test_can_view_page_user_edit()
    {
        $response = $this->actingAs($this->user)->get(route('apps.users.edit', $this->user->id));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/User/Edit')
                ->has('user')
                ->has('roles')
        );
    }

    public function test_cant_view_page_user_edit_when_unauthenticated()
    {
        $response = $this->get(route('apps.users.edit', $this->user->id));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_view_page_user_edit_when_dont_have_permission()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.users.edit', $this->user->id));
        $response->assertStatus(403);
    }

    public function test_cant_view_page_user_edit_when_not_found()
    {
        $response = $this->actingAs($this->user)->get(route('apps.users.edit', 99));
        $response->assertStatus(404);
    }

    public function test_can_update_user()
    {
        $response = $this->actingAs($this->user)->put(route('apps.users.update', $this->user->id), [
            'name' => fake()->unique()->name,
            'email' => fake()->unique()->safeEmail(),
            'roles' => Role::all()->pluck('name')->toArray(),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('apps.users.index'));
    }

    public function test_cant_update_user_when_unauthenticated()
    {
        $response = $this->put(route('apps.users.update', $this->user->id), [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'roles' => Role::all()->pluck('name')->toArray(),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_update_user_when_dont_have_permission()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->put(route('apps.users.update', $this->user->id), [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'roles' => Role::all()->pluck('name')->toArray(),
        ]);

        $response->assertStatus(403);
    }

    public function test_cant_update_user_when_input_blank()
    {
        $response = $this->actingAs($this->user)->put(route('apps.users.update', $this->user->id), [
            'name' => '',
            'email' => '',
            'roles' => [],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'email', 'roles']);
    }

    public function test_cant_update_when_not_found()
    {
        $response = $this->actingAs($this->user)->put(route('apps.users.update', 99), [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'roles' => Role::all()->pluck('name')->toArray(),
        ]);

        $response->assertStatus(404);
    }

    public function test_can_delete_user()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($this->user)->delete(route('apps.users.destroy', $user->id));
        $response->assertStatus(302);
        $response->assertRedirect(route('apps.users.index'));
        $user = User::find($user->id);
        $this->assertNull($user);
    }

    public function test_cant_delete_user_when_unauthenticated()
    {
        $response = $this->delete(route('apps.users.destroy', $this->user->id));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_delete_user_when_dont_have_permission()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->delete(route('apps.users.destroy', $this->user->id));
        $response->assertStatus(403);
    }

    public function test_cant_delete_user_when_not_found()
    {
        $response = $this->actingAs($this->user)->delete(route('apps.users.destroy', 99));
        $response->assertStatus(404);
    }
}
