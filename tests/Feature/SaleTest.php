<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SaleTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private User $userNotHavePermissions;

    private $permissions = [];

    public function setUp(): void
    {
        parent::setUp();

        array_push($this->permissions, Permission::create(['name' => 'sales.index', 'guard_name' => 'web']));

        $role = Role::create(['name' => 'admin']);

        $role->givePermissionTo($this->permissions);

        $this->user = User::factory()->create();

        $this->user->assignRole('admin');

        $this->userNotHavePermissions = User::factory()->create();
    }

    public function test_can_view_page_sales_report()
    {
        $response = $this->actingAs($this->user)->get(route('apps.sales.index'));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/Sales/Index')
        );
    }

    public function test_cant_view_page_sales_report_when_not_have_permissions()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.sales.index'));
        $response->assertStatus(403);
    }

    public function test_can_view_page_sales_report_when_unauthenticated()
    {
        $response = $this->get(route('apps.sales.index'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_can_view_filter_sales_report()
    {
        $response = $this->actingAs($this->user)->get(route('apps.sales.filter', [
            "start_date" => "2023-06-02",
            "end_date" => "2023-06-02",
        ]));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/Sales/Index')
                ->has('sales')
                ->has('total')
        );
    }

    public function test_cant_view_filter_sales_report_when_not_have_permissions()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.sales.filter', [
            "start_date" => "2023-06-02",
            "end_date" => "2023-06-02"
        ]));
        $response->assertStatus(403);
    }

    public function test_can_view_filter_sales_report_when_unauthenticated()
    {
        $response = $this->get(route('apps.sales.filter', [
            "start_date" => "2023-06-02",
            "end_date" => "2023-06-02"
        ]));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_can_export()
    {
        $response = $this->actingAs($this->user)->get(route('apps.sales.export', [
            "start_date" => "2023-06-02",
            "end_date" => "2023-06-02"
        ]));
        $response->assertStatus(200);
    }

    public function test_cant_export_when_not_have_permissions()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.sales.export', [
            "start_date" => "2023-06-02",
            "end_date" => "2023-06-02"
        ]));
        $response->assertStatus(403);
    }

    public function test_can_export_when_unauthenticated()
    {
        $response = $this->get(route('apps.sales.export', [
            "start_date" => "2023-06-02",
            "end_date" => "2023-06-02"
        ]));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_can_export_pdf()
    {
        $response = $this->actingAs($this->user)->get(route('apps.sales.pdf', [
            "start_date" => "2023-06-02",
            "end_date" => "2023-06-02"
        ]));
        $response->assertStatus(200);
    }

    public function test_cant_export_pdf_when_not_have_permissions()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.sales.pdf', [
            "start_date" => "2023-06-02",
            "end_date" => "2023-06-02"
        ]));
        $response->assertStatus(403);
    }

    public function test_can_export_pdf_when_unauthenticated()
    {
        $response = $this->get(route('apps.sales.pdf', [
            "start_date" => "2023-06-02",
            "end_date" => "2023-06-02"
        ]));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }
}
