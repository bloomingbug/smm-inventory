<?php

namespace Tests\Feature;

use App\Models\Customer;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private User $userNotHavePermissions;

    private $permissions = [];

    public function setUp(): void
    {
        parent::setUp();

        array_push($this->permissions, Permission::create(['name' => 'customers.index', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'customers.create', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'customers.edit', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'customers.delete', 'guard_name' => 'web']));

        $role = Role::create(['name' => 'admin']);

        $role->givePermissionTo($this->permissions);

        $this->user = User::factory()->create();

        $this->user->assignRole('admin');

        $this->userNotHavePermissions = User::factory()->create();
    }

    public function test_can_view_page_customer()
    {
        $response = $this->actingAs($this->user)->get(route('apps.customers.index'));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/Customers/Index')
                ->has('customers')
        );
    }

    public function test_can_search_customer()
    {
        $response = $this->actingAs($this->user)->get(route('apps.customers.index', ['q' => $this->user->name]));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/Customers/Index')
                ->has('customers')
        );
    }

    public function test_cant_view_page_customer_when_unauthenticated()
    {
        $response = $this->get(route('apps.customers.index'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_view_page_customer_when_user_not_have_permission()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.customers.index'));
        $response->assertStatus(403);
    }

    public function test_can_view_page_create_customer()
    {
        $response = $this->actingAs($this->user)->get(route('apps.customers.create'));
        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('Apps/Customers/Create'));
    }

    public function test_cant_view_page_create_customer_when_unauthenticated()
    {
        $response = $this->get(route('apps.customers.create'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_view_page_create_customer_when_user_not_have_permission()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.customers.create'));
        $response->assertStatus(403);
    }

    public function test_can_create_customer()
    {
        $response = $this->actingAs($this->user)->post(route('apps.customers.store'), [
            'name' => $this->faker->name,
            'no_telp' => '6281234567890',
            'address' => $this->faker->address
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('apps.customers.index'));
    }

    public function test_cant_create_customer_when_unauthenticated()
    {
        $response = $this->post(route('apps.customers.store'), [
            'name' => $this->faker->name,
            'no_telp' => '6281234567890',
            'address' => $this->faker->address
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_create_customer_when_user_not_have_permission()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->post(route('apps.customers.store'), [
            'name' => $this->faker->name,
            'no_telp' => '6281234567890',
            'address' => $this->faker->address
        ]);

        $response->assertStatus(403);
    }

    public function test_cant_create_customer_when_blank_input()
    {
        $response = $this->actingAs($this->user)->post(route('apps.customers.store'), [
            'name' => '',
            'no_telp' => '',
            'address' => ''
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'no_telp', 'address']);
    }

    public function test_can_view_page_edit_customer()
    {

        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->user)->get(route('apps.customers.edit', $customer->id));
        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('Apps/Customers/Edit')->has('customer'));
    }

    public function test_cant_view_page_edit_customer_when_unauthenticated()
    {
        $customer = Customer::factory()->create();

        $response = $this->get(route('apps.customers.edit', $customer->id));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_view_page_edit_customer_when_user_not_have_permission()
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.customers.edit', $customer->id));
        $response->assertStatus(403);
    }

    public function test_cant_view_page_edit_customer_when_customer_not_found()
    {
        $response = $this->actingAs($this->user)->get(route('apps.customers.edit', 0));
        $response->assertStatus(404);
    }

    public function test_can_update_customer()
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->user)->put(route('apps.customers.update', $customer->id), [
            'name' => $this->faker->name,
            'no_telp' => '6281234567890',
            'address' => $this->faker->address
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('apps.customers.index'));
    }

    public function test_cant_update_customer_when_unauthenticated()
    {
        $customer = Customer::factory()->create();

        $response = $this->put(route('apps.customers.update', $customer->id), [
            'name' => $this->faker->name,
            'no_telp' => '6281234567890',
            'address' => $this->faker->address
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_update_customer_when_user_not_have_permission()
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->userNotHavePermissions)->put(route('apps.customers.update', $customer->id), [
            'name' => $this->faker->name,
            'no_telp' => '6281234567890',
            'address' => $this->faker->address
        ]);

        $response->assertStatus(403);
    }

    public function test_cant_update_customer_when_blank_input()
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->user)->put(route('apps.customers.update', $customer->id), [
            'name' => '',
            'no_telp' => '',
            'address' => ''
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'no_telp', 'address']);
    }

    public function test_cant_update_customer_when_customer_not_found()
    {
        $response = $this->actingAs($this->user)->put(route('apps.customers.update', 0), [
            'name' => $this->faker->name,
            'no_telp' => '6281234567890',
            'address' => $this->faker->address
        ]);

        $response->assertStatus(404);
    }

    public function test_can_delete_customer()
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('apps.customers.destroy', $customer->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('apps.customers.index'));
        $customer = Customer::find($customer->id);
        $this->assertNull($customer);
    }

    public function test_cant_delete_customer_when_unauthenticated()
    {
        $customer = Customer::factory()->create();

        $response = $this->delete(route('apps.customers.destroy', $customer->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_delete_customer_when_user_not_have_permission()
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->userNotHavePermissions)->delete(route('apps.customers.destroy', $customer->id));

        $response->assertStatus(403);
    }

    public function test_cant_delete_customer_when_customer_not_found()
    {
        $response = $this->actingAs($this->user)->delete(route('apps.customers.destroy', 0));

        $response->assertStatus(404);
    }
}
