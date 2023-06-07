<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private $permissions = [];

    public function setUp(): void
    {
        parent::setUp();

        array_push($this->permissions, Permission::create(['name' => 'dashboard.index', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'dashboard.sales_chart', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'dashboard.sales_today', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'dashboard.profits_today', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'dashboard.best_selling_product', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'dashboard.product_stock', 'guard_name' => 'web']));

        $role = Role::create(['name' => 'admin']);

        $role->givePermissionTo($this->permissions);

        $this->user = User::factory()->create();

        $this->user->assignRole('admin');
    }

    public function test_can_view_page_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('apps.dashboard'));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/Dashboard/Index')
                ->has('sales_date')
                ->has('grand_total')
                ->has('count_sales_today')
                ->has('sum_sales_today')
                ->has('sum_profits_today')
                ->has('products_limit_stock')
                ->has('product')
                ->has('total')
        );
    }

    public function test_cant_view_page_dashboard_when_unauthenticated()
    {
        $response = $this->get(route('apps.dashboard'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_view_page_dashboard_when_not_have_permission()
    {
        // $this->markTestSkipped('Error Kurang Middleware');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('apps.dashboard'));
        $response->assertStatus(403);
    }
}
